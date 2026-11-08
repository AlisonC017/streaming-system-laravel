<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contenido;
use App\Models\Temporada;
use App\Models\Episodio;
use App\Services\Reproductor\FuenteManager;
use App\Services\Reproductor\ProveedorReproduccionService;

class ReproductorController extends Controller
{
    protected $fuenteManager;
    protected $reproduccion;

    public function __construct(
        FuenteManager $fuenteManager,
        ProveedorReproduccionService $reproduccion
    ) {
        $this->fuenteManager = $fuenteManager;
        $this->reproduccion = $reproduccion;
    }

    public function mostrar($contenidoId)
    {
        // 1. Cargar el contenido directamente (permitiendo bajo demanda)
        $contenido = Contenido::findOrFail($contenidoId);

        $episodio = null;
        $temporada = null;
        $episodios = collect();

        if ($contenido->tipo != 'pelicula') {

            $idsEpisodios = $contenido->idsEpisodiosConFuentes();

            $temporadas = Temporada::where('contenido_id', $contenido->_id)
                ->orderBy('numero')
                ->get();

            // Buscar la primera temporada que tenga episodios
            foreach ($temporadas as $candidata) {

                $listaEpisodios = Episodio::where('temporada_id', $candidata->_id)
                    ->orderBy('numero')
                    ->get();

                // Si hay fuentes guardadas filtramos, de lo contrario permitimos los episodios para que operen bajo demanda
                $reproducibles = !empty($idsEpisodios)
                    ? $this->soloReproducibles($listaEpisodios, $idsEpisodios)
                    : $listaEpisodios;

                if ($reproducibles->isNotEmpty()) {
                    $temporada = $candidata;
                    $episodios = $reproducibles;
                    break;
                }
            }

            // Episodios sueltos del contenido
            if (!$temporada) {
                $listaEpisodios = Episodio::where('contenido_id', $contenido->_id)
                    ->orderBy('numero')
                    ->get();

                $episodios = !empty($idsEpisodios)
                    ? $this->soloReproducibles($listaEpisodios, $idsEpisodios)
                    : $listaEpisodios;
            }

            $episodio = $episodios->first();
        }

        return view('reproductor', compact(
            'contenido',
            'episodio',
            'temporada',
            'episodios'
        ));
    }

    public function mostrarEpisodio($contenidoId, $episodioId)
    {
        $contenido = Contenido::findOrFail($contenidoId);

        $episodio = Episodio::where('_id', $episodioId)
            ->where('contenido_id', $contenido->_id)
            ->firstOrFail();

        $temporada = null;

        if ($episodio->temporada_id) {
            $temporada = Temporada::find($episodio->temporada_id);
        }

        if ($temporada) {
            $consulta = Episodio::where('temporada_id', $temporada->_id);
        } else {
            $consulta = Episodio::where('contenido_id', $contenido->_id);
        }

        $idsEpisodios = $contenido->idsEpisodiosConFuentes();
        $listaEpisodios = $consulta->orderBy('numero')->get();

        // Si no hay fuentes pre-guardadas en BD, permitimos la lista para resolver vía bajo demanda
        $episodios = !empty($idsEpisodios)
            ? $this->soloReproducibles($listaEpisodios, $idsEpisodios)
            : $listaEpisodios;

        return view('reproductor', compact(
            'contenido',
            'episodio',
            'temporada',
            'episodios'
        ));
    }

    public function obtenerFuentes(Request $request)
    {
        $datos = $request->validate([
            'contenido_id' => 'required',
            'episodio_id' => 'nullable'
        ]);

        $contenido = Contenido::findOrFail($datos['contenido_id']);

        $episodio = null;

        if (!empty($datos['episodio_id'])) {
            $episodio = Episodio::where('_id', $datos['episodio_id'])
                ->where('contenido_id', $contenido->_id)
                ->firstOrFail();
        }

        // Aquí se invoca el script Python de vidsrc_direct
        $fuentes = $this->reproduccion->obtenerFuentesReproduccion(
            $contenido,
            $episodio
        );

        return response()->json([
            'fuentes' => $fuentes
        ]);
    }

    public function guardarFuente(Request $request)
    {
        $datos = $request->validate([
            'contenido_id' => 'required',
            'episodio_id' => 'nullable',
            'proveedor' => 'required|string|max:100',
            'url' => 'required|url',
            'tipo' => 'required|in:embed,mp4,hls',
            'idioma' => 'nullable|string|max:50',
            'subtitulos' => 'nullable|array',
            'prioridad' => 'nullable|integer|min:0'
        ]);

        $contenido = Contenido::findOrFail($datos['contenido_id']);

        if (!empty($datos['episodio_id'])) {
            Episodio::where('_id', $datos['episodio_id'])
                ->where('contenido_id', $contenido->_id)
                ->firstOrFail();
        }

        $datos['contenido_id'] = $contenido->_id;

        $fuente = $this->fuenteManager->guardarFuente($datos);

        return response()->json([
            'mensaje' => 'Fuente registrada correctamente',
            'fuente' => $fuente
        ]);
    }

    public function eliminarFuente($id)
    {
        $resultado = $this->fuenteManager->eliminarFuente($id);

        if (!$resultado) {
            return response()->json([
                'mensaje' => 'No se encontro la fuente'
            ], 404);
        }

        return response()->json([
            'mensaje' => 'Fuente eliminada correctamente'
        ]);
    }

    private function soloReproducibles($episodios, $idsEpisodios)
    {
        return $episodios
            ->filter(fn($episodio) => in_array((string) $episodio->_id, $idsEpisodios))
            ->values();
    }
}
