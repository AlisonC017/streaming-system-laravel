<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contenido;
use App\Models\Temporada;
use App\Models\Episodio;
use App\Models\Fuente;

class ReproductorController extends Controller
{
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

        /* 
        Esto es para cuando si se use una vista real, uso la de abajo porque es de prueba
        return view('reproductor.player', compact(
            'contenido',
            'episodio',
            'temporada',
            'episodios'
        ));
        */
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

        /* 
        Esto es para cuando si se use una vista real, uso la de abajo porque es de prueba
        return view('reproductor.player', compact(
            'contenido',
            'episodio',
            'temporada',
            'episodios'
        ));
        */
        return view('reproductor', compact(
            'contenido',
            'episodio',
            'temporada',
            'episodios'
        ));
    }

    public function obtenerFuentes(Request $request)
    {
        $request->validate([
            'contenido_id' => 'required',
            'episodio_id' => 'nullable'
        ]);

        $consulta = Fuente::where('contenido_id', $request->contenido_id)
            ->where('estado', 'activa');

        if ($request->episodio_id) {
            $consulta->where('episodio_id', $request->episodio_id);
        } else {
            $consulta->whereNull('episodio_id');
        }

        $fuentes = $consulta->orderBy('prioridad', 'desc')->get();

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
            'idioma' => 'required|string|max:50',
            'subtitulos' => 'nullable|array',
            'prioridad' => 'nullable|integer|min:0',
        ]);

        $contenido = Contenido::findOrFail($datos['contenido_id']);

        if (!empty($datos['episodio_id'])) {

            $episodio = Episodio::where('_id', $datos['episodio_id'])
                ->where('contenido_id', $contenido->_id)
                ->firstOrFail();
        }

        $datos['estado'] = 'activa';

        Fuente::create($datos);

        return response()->json([
            'mensaje' => 'Fuente guardada correctamente'
        ]);
    }

    public function eliminarFuente($id)
    {
        $fuente = Fuente::findOrFail($id);

        $fuente->delete();

        return response()->json([
            'mensaje' => 'Fuente eliminada correctamente'
        ]);
    }
}
