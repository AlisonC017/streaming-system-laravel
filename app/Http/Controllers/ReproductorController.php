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
        $contenido = Contenido::findOrFail($contenidoId);

        $episodio = null;
        $temporada = null;
        $episodios = collect();

        if ($contenido->tipo != 'pelicula') {

            $temporada = Temporada::where('contenido_id', $contenido->_id)
                ->orderBy('numero')
                ->first();

            if ($temporada) {

                $episodios = Episodio::where('temporada_id', $temporada->_id)
                    ->orderBy('numero')
                    ->get();

                $episodio = $episodios->first();
            } else {

                $episodios = Episodio::where('contenido_id', $contenido->_id)
                    ->orderBy('numero')
                    ->get();

                $episodio = $episodios->first();
            }
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

        $episodios = collect();

        if ($temporada) {

            $episodios = Episodio::where('temporada_id', $temporada->_id)
                ->orderBy('numero')
                ->get();
        } else {

            $episodios = Episodio::where('contenido_id', $contenido->_id)
                ->orderBy('numero')
                ->get();
        }

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
}
