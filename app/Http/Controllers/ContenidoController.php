<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contenido;
use App\Models\Temporada;
use App\Models\Episodio;

class ContenidoController extends Controller
{
    public function obtenerTodos()
    {
        $contenidos = Contenido::conFuentes()->orderBy('titulo')->get();

        return view('catalogo.index', compact('contenidos'));
    }

    public function obtenerPeliculas()
    {
        return $this->listarPorTipo('pelicula');
    }

    public function obtenerSeries()
    {
        return $this->listarPorTipo('serie');
    }

    public function obtenerAnime()
    {
        return $this->listarPorTipo('anime');
    }

    public function buscar(Request $request)
    {
        $nombre = $request->input('nombre');
        $tipo = $request->input('tipo');
        $genero = $request->input('genero');

        $consulta = Contenido::conFuentes();

        if ($nombre) {
            $nombre = preg_quote($nombre, '/');

            $consulta->where('titulo', 'regexp', '/' . $nombre . '/i');
        }

        if ($tipo) {
            $consulta->where('tipo', $tipo);
        }

        if ($genero) {
            $this->filtrarGenero($consulta, $genero);
        }

        $contenidos = $consulta->orderBy('titulo')->get();

        return view('catalogo.index', compact('contenidos'));
    }

    public function porGenero($genero)
    {
        $consulta = Contenido::conFuentes();

        $this->filtrarGenero($consulta, $genero);

        $contenidos = $consulta->orderBy('titulo')->get();

        return view('catalogo.index', compact('contenidos', 'genero'));
    }

    // Detalle: el contenido con sus temporadas y episodios reproducibles
    public function mostrar($id)
    {
        $contenido = Contenido::conFuentes()->findOrFail($id);

        [$temporadas, $episodiosPorTemporada] = $this->temporadasConEpisodios($contenido);

        return view('catalogo.detalle', compact(
            'contenido',
            'temporadas',
            'episodiosPorTemporada'
        ));
    }

    // Vista de prueba del catalogo (no es parte del proyecto final)
    public function pruebaCatalogo(Request $request)
    {
        $consulta = Contenido::conFuentes();

        if ($request->tipo) {
            $consulta->where('tipo', $request->tipo);
        }

        if ($request->q) {
            $consulta->where('titulo', 'regexp', '/' . preg_quote($request->q, '/') . '/i');
        }

        $contenidos = $consulta->orderBy('titulo')->get();

        $contenidoSeleccionado = null;
        $temporadas = collect();
        $episodiosPorTemporada = [];

        if ($request->contenido_id) {

            $contenidoSeleccionado = Contenido::conFuentes()->find($request->contenido_id);

            if ($contenidoSeleccionado) {
                [$temporadas, $episodiosPorTemporada] = $this->temporadasConEpisodios($contenidoSeleccionado);
            }
        }

        return view('prueba-catalogo', compact(
            'contenidos',
            'contenidoSeleccionado',
            'temporadas',
            'episodiosPorTemporada'
        ));
    }

    // Evita repetir la misma consulta en peliculas, series y anime
    private function listarPorTipo($tipo)
    {
        $contenidos = Contenido::conFuentes()
            ->where('tipo', $tipo)
            ->orderBy('titulo')
            ->get();

        return view('catalogo.index', compact('contenidos', 'tipo'));
    }

    // Temporadas y episodios que tienen fuentes reproducibles.
    // Devuelve [temporadas, episodiosPorTemporada]
    private function temporadasConEpisodios(Contenido $contenido)
    {
        $idsEpisodios = $contenido->idsEpisodiosConFuentes();

        $temporadas = collect();
        $episodiosPorTemporada = [];

        $todasTemporadas = Temporada::where('contenido_id', $contenido->_id)
            ->orderBy('numero')
            ->get();

        foreach ($todasTemporadas as $temporada) {

            $episodios = Episodio::where('temporada_id', $temporada->_id)
                ->orderBy('numero')
                ->get()
                ->filter(fn($episodio) => in_array((string) $episodio->_id, $idsEpisodios))
                ->values();

            if ($episodios->isEmpty()) {
                continue;
            }

            $temporadas->push($temporada);
            $episodiosPorTemporada[(string) $temporada->_id] = $episodios;
        }

        return [$temporadas, $episodiosPorTemporada];
    }

    // Los generos estan guardados como texto JSON (cast array), por ejemplo
    // ["Acci\u00f3n","Suspense"]. El patron busca el genero exacto en ese texto
    // y tambien como elemento de un arreglo nativo, por si luego se normalizan.
    private function filtrarGenero($consulta, $genero)
    {
        $escapado = json_encode($genero) ?: '""';

        $nativo = '^' . preg_quote($genero, '/') . '$';
        $texto = '"' . preg_quote(substr($escapado, 1, -1), '/') . '"';

        $consulta->where('generos', 'regexp', '/(' . $nativo . ')|(' . $texto . ')/i');
    }
}
