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
        // Solo contenido con fuentes reproducibles
        $contenido = Contenido::conFuentes()->findOrFail($contenidoId);

        $episodio = null;
        $temporada = null;
        $episodios = collect();

        if ($contenido->tipo != 'pelicula') {

            $idsEpisodios = $contenido->idsEpisodiosConFuentes();

            $temporadas = Temporada::where('contenido_id', $contenido->_id)
                ->orderBy('numero')
                ->get();

            // Primera temporada que tenga episodios reproducibles
            foreach ($temporadas as $candidata) {

                $reproducibles = $this->soloReproducibles(
                    Episodio::where('temporada_id', $candidata->_id)
                        ->orderBy('numero')
                        ->get(),
                    $idsEpisodios
                );

                if ($reproducibles->isNotEmpty()) {
                    $temporada = $candidata;
                    $episodios = $reproducibles;
                    break;
                }
            }

            //episodios sueltos del contenido
            if (!$temporada) {

                $episodios = $this->soloReproducibles(
                    Episodio::where('contenido_id', $contenido->_id)
                        ->orderBy('numero')
                        ->get(),
                    $idsEpisodios
                );
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
        $contenido = Contenido::conFuentes()->findOrFail($contenidoId);

        $idsEpisodios = $contenido->idsEpisodiosConFuentes();

        $episodio = Episodio::where('_id', $episodioId)
            ->where('contenido_id', $contenido->_id)
            ->firstOrFail();

        // Un episodio sin fuentes reproducibles no se muestra
        if (!in_array((string) $episodio->_id, $idsEpisodios)) {
            abort(404);
        }

        $temporada = null;

        if ($episodio->temporada_id) {
            $temporada = Temporada::find($episodio->temporada_id);
        }

        if ($temporada) {

            $consulta = Episodio::where('temporada_id', $temporada->_id);
        } else {

            $consulta = Episodio::where('contenido_id', $contenido->_id);
        }

        $episodios = $this->soloReproducibles(
            $consulta->orderBy('numero')->get(),
            $idsEpisodios
        );

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

    // Administracion: aqui NO se filtra por fuentes, porque se usa
    // justamente para registrar la primera fuente de un contenido
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
