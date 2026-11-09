<?php

namespace App\Services\Reproductor;

use App\Models\Contenido;
use App\Models\Episodio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProveedorReproduccionService
{
    protected $fuenteManager;
    protected $proveedores;

    public function __construct(FuenteManager $fuenteManager, iterable $proveedores)
    {
        $this->fuenteManager = $fuenteManager;
        $this->proveedores = $proveedores;
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
