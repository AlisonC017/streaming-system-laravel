<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contenido;
use App\Models\Temporada;
use App\Models\Episodio;
use App\Services\Catalogo\BuscadorService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ContenidoController extends Controller
{
    // Cantidad de contenidos por pagina en los listados
    private const POR_PAGINA = 25;

    // Cantidad de contenidos en la lista de destacados del inicio
    private const POR_FILA_INICIO = 10;

    // Puntuacion minima para destacar. TMDB guarda vote_average (0 a 10)
    // y AniList averageScore (0 a 100)
    private const PUNTUACION_MINIMA = [
        'pelicula' => 6,
        'serie' => 6,
        'anime' => 60,
    ];

    public function obtenerTodos(Request $request)
    {
        $contenidos = Contenido::conFuentes()->orderBy('titulo')->paginate(self::POR_PAGINA)->withQueryString();

        // Las filas del inicio solo se calculan en la primera pagina (igual que $esInicio en la vista)
        $datosInicio = (int) $request->input('page', 1) === 1 ? $this->datosInicio() : [];

        return view('modules.catalogo.index', array_merge(compact('contenidos'), $datosInicio));
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

    // Catalogo con filtros opcionales
    public function buscar(Request $request, BuscadorService $buscador)
    {
        $nombre = $request->input('nombre');
        $tipo = $request->input('tipo');
        $genero = $request->input('genero');

        if (!$nombre && !$tipo && !$genero) {
            $contenidos = Contenido::conFuentes()->orderBy('titulo')->paginate(self::POR_PAGINA)->withQueryString();
            $datosInicio = (int) $request->input('page', 1) === 1 ? $this->datosInicio() : [];
            return view('modules.catalogo.index', array_merge(compact('contenidos'), $datosInicio));
        }

        // Con texto (y sin genero) primero se intenta la busqueda por significado
        if ($nombre && !$genero) {
            try {
                $resultados = $buscador->buscarHibrido($nombre, $tipo);

                if ($resultados->isNotEmpty()) {
                    $pagina = LengthAwarePaginator::resolveCurrentPage();

                    $contenidos = new LengthAwarePaginator(
                        $resultados->forPage($pagina, self::POR_PAGINA),
                        $resultados->count(),
                        self::POR_PAGINA,
                        $pagina,
                        ['path' => $request->url(), 'query' => $request->query()]
                    );

                    return view('modules.catalogo.index', compact('contenidos'));
                }
            } catch (\Throwable $e) {
                // Si falla la API o no hay vectores, se usa la busqueda por titulo
                Log::warning('Busqueda por significado fallo', ['mensaje' => $e->getMessage()]);
            }
        }

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

        $contenidos = $consulta->orderBy('titulo')->paginate(self::POR_PAGINA)->withQueryString();
        return view('modules.catalogo.index', compact('contenidos'));
    }

    public function porGenero($genero)
    {
        $consulta = Contenido::conFuentes();
        $this->filtrarGenero($consulta, $genero);
        $contenidos = $consulta->orderBy('titulo')->paginate(self::POR_PAGINA)->withQueryString();
        return view('modules.catalogo.index', compact('contenidos', 'genero'));
    }

    // el contenido con sus temporadas y episodios reproducibles
    public function mostrar($id)
    {
        $contenido = Contenido::conFuentes()->findOrFail($id);
        [$temporadas, $episodiosPorTemporada] = $this->temporadasConEpisodios($contenido);
        return view('modules.catalogo.detalle', compact(
            'contenido',
            'temporadas',
            'episodiosPorTemporada'
        ));
    }

    private function listarPorTipo($tipo)
    {
        $contenidos = Contenido::conFuentes()->where('tipo', $tipo)->orderBy('titulo')
            ->paginate(self::POR_PAGINA)->withQueryString();

        return view('modules.catalogo.index', compact('contenidos', 'tipo'));
    }

    // Temporadas y episodios que tienen fuentes reproducibles
    // Devuelve [temporadas, episodiosPorTemporada]
    private function temporadasConEpisodios(Contenido $contenido)
    {
        $idsEpisodios = $contenido->idsEpisodiosConFuentes();
        $temporadas = collect();
        $episodiosPorTemporada = [];

        $todasTemporadas = Temporada::where('contenido_id', $contenido->_id)
            ->orderBy('numero')->get();

        foreach ($todasTemporadas as $temporada) {
            $episodios = Episodio::where('temporada_id', $temporada->_id)->orderBy('numero')
                ->get()->filter(fn($episodio) => in_array((string) $episodio->_id, $idsEpisodios))
                ->values();

            if ($episodios->isEmpty()) {
                continue;
            }

            $temporadas->push($temporada);
            $episodiosPorTemporada[(string) $temporada->_id] = $episodios;
        }

        return [$temporadas, $episodiosPorTemporada];
    }

    private function filtrarGenero($consulta, $genero)
    {
        $escapado = json_encode($genero) ?: '""';
        $nativo = '^' . preg_quote($genero, '/') . '$';
        $texto = '"' . preg_quote(substr($escapado, 1, -1), '/') . '"';
        $consulta->where('generos', 'regexp', '/(' . $nativo . ')|(' . $texto . ')/i');
    }

    // Datos de la portada
    private function datosInicio()
    {
        $peliculas = $this->destacadosPorTipo('pelicula', self::POR_FILA_INICIO);
        $series = $this->destacadosPorTipo('serie', self::POR_FILA_INICIO);
        $anime = $this->destacadosPorTipo('anime', self::POR_FILA_INICIO);

        // La popularidad de TMDB y AniList no es comparable, por eso se eligio por tipo
        // y la mezcla se ordena con la puntuacion pasada a escala 0-10
        $masDestacados = $peliculas->concat($series)->concat($anime)
            ->sortByDesc(fn($c) => $c->tipo === 'anime' ? $c->puntuacion / 10 : $c->puntuacion)
            ->take(self::POR_FILA_INICIO)
            ->values();

        $destacado = $masDestacados->first(fn($c) => $c->imagen && $c->descripcion);
        return compact('destacado', 'peliculas', 'series', 'anime', 'masDestacados');
    }

    // Los mas populares de un tipo que tengan fuentes y una puntuacion minima
    private function destacadosPorTipo($tipo, $limite)
    {
        return Contenido::conFuentes()
            ->where('tipo', $tipo)
            ->where('puntuacion', '>=', self::PUNTUACION_MINIMA[$tipo])
            ->orderBy('popularidad', 'desc')
            ->limit($limite)
            ->get();
    }
}
