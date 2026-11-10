<?php

namespace App\Services\Reproductor;

use App\Models\Contenido;
use App\Models\Episodio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProveedorReproduccionService
{
    protected $fuenteManager;
    protected $verificador;
    protected $proveedores;

    public function __construct(
        FuenteManager $fuenteManager,
        VerificadorFuente $verificador,
        iterable $proveedores
    ) {
        $this->fuenteManager = $fuenteManager;
        $this->verificador = $verificador;
        $this->proveedores = $proveedores;
    }

    public function sincronizarContenido(Contenido $contenido)
    {
        if ($contenido->tipo == 'pelicula') {
            return $this->sincronizarFuentes($contenido, null);
        }

        $total = 0;

        $episodios = Episodio::where('contenido_id', $contenido->_id)->get();

        foreach ($episodios as $episodio) {
            $total += $this->sincronizarFuentes($contenido, $episodio);
        }

        return $total;
    }

    public function sincronizarEpisodiosNuevos(Contenido $contenido)
    {
        $total = 0;

        $episodios = Episodio::where('contenido_id', $contenido->_id)->get();

        foreach ($episodios as $episodio) {
            $guardadas = $this->fuenteManager
                ->obtenerFuentes($contenido->_id, $episodio->_id)
                ->toArray();

            if (count($guardadas) > 0) {
                continue;
            }

            $total += $this->sincronizarFuentes($contenido, $episodio);
        }

        return $total;
    }

    // Si el titulo tiene video propio (subido por el admin) se usa ese.
    // Si no, se piden las fuentes a demanda (trailer de YouTube).
    public function obtenerFuentesReproduccion(Contenido $contenido, ?Episodio $episodio = null)
    {
        $guardadas = $this->fuenteManager
            ->obtenerFuentes($contenido->_id, $episodio ? $episodio->_id : null)
            ->toArray();

        if (!empty($guardadas)) {
            return $guardadas;
        }

        $bajoDemanda = [];

        foreach ($this->proveedores as $proveedor) {
            if (!$this->esBajoDemanda($proveedor)) {
                continue;
            }

            $bajoDemanda = array_merge(
                $bajoDemanda,
                $this->resolverBajoDemanda($proveedor, $contenido, $episodio)
            );
        }

        return $bajoDemanda;
    }

    protected function sincronizarFuentes(Contenido $contenido, ?Episodio $episodio)
    {
        $total = 0;

        foreach ($this->proveedores as $proveedor) {

            if ($this->esBajoDemanda($proveedor)) {
                continue;
            }

            try {
                $fuentes = $proveedor->obtenerFuentes($contenido, $episodio);
            } catch (\Throwable $e) {
                Log::warning('Proveedor de reproduccion fallo', [
                    'proveedor' => $proveedor->obtenerNombre(),
                    'mensaje' => $e->getMessage()
                ]);
                continue;
            }

            foreach ($fuentes as $datos) {
                $fuente = $this->fuenteManager->guardarFuente($datos);

                $this->verificador->verificar($fuente->_id);

                $total++;
            }
        }

        return $total;
    }

    protected function esBajoDemanda($proveedor)
    {
        return in_array(
            $proveedor->obtenerNombre(),
            config('services.reproduccion.bajo_demanda', [])
        );
    }

    protected function resolverBajoDemanda($proveedor, Contenido $contenido, ?Episodio $episodio)
    {
        $clave = 'fuentes_demanda:' . $proveedor->obtenerNombre()
            . ':' . $contenido->_id
            . ':' . ($episodio ? $episodio->_id : 'pelicula');

        $cacheadas = Cache::get($clave);

        if (!empty($cacheadas)) {
            return $cacheadas;
        }

        try {
            $fuentes = $proveedor->obtenerFuentes($contenido, $episodio);
        } catch (\Throwable $e) {
            Log::warning('Proveedor bajo demanda fallo', [
                'proveedor' => $proveedor->obtenerNombre(),
                'mensaje' => $e->getMessage()
            ]);
            return [];
        }

        $fuentes = array_map(function ($fuente) {
            $fuente['contenido_id'] = (string) $fuente['contenido_id'];
            $fuente['episodio_id'] = $fuente['episodio_id'] ? (string) $fuente['episodio_id'] : null;
            return $fuente;
        }, $fuentes);

        if (!empty($fuentes)) {
            Cache::put(
                $clave,
                $fuentes,
                now()->addMinutes((int) config('services.reproduccion.cache_minutos', 5))
            );
        }

        return $fuentes;
    }
}
