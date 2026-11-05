<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contenido;
use App\Models\Temporada;
use App\Models\Episodio;
use App\Services\Catalogo\TMDBService;

class SincronizarAnimes extends Command
{
    protected $signature = 'animes:sincronizar';

    protected $description = 'Sincroniza temporadas y episodios de animes con TMDB';

    public function handle(TMDBService $tmdb)
    {
        $animes = Contenido::where('tipo', 'anime')->get();

        $totalTemporadas = 0;
        $totalEpisodios = 0;

        foreach ($animes as $anime) {

            $this->info("Procesando: " . $anime->titulo);

            // Buscar en TMDB si todavia no tiene identificador
            if (!$anime->tmdb_id) {

                $resultado = $tmdb->buscarSerie($anime->titulo);

                if (!$resultado) {
                    $this->warn("No encontrado en TMDB");
                    continue;
                }

                $anime->tmdb_id = $resultado['id'];
                $anime->save();
            }

            // Obtener informacion de la serie
            $serie = $tmdb->obtenerTemporadas($anime->tmdb_id);

            if (!$serie || !isset($serie['seasons'])) {
                $this->warn("No se encontraron temporadas");
                continue;
            }

            foreach ($serie['seasons'] as $datosTemporada) {

                $numero = $datosTemporada['season_number'];

                // Obtener episodios de la temporada
                $datos = $tmdb->obtenerEpisodios(
                    $anime->tmdb_id,
                    $numero
                );

                if (!$datos) {
                    continue;
                }

                // Guardar temporada
                $temporada = Temporada::updateOrCreate(
                    [
                        'contenido_id' => $anime->getKey(),
                        'proveedor_id' => $datos['id'] ?? null
                    ],
                    [
                        'numero' => $numero,
                        'titulo' => $datos['name'] ?? 'Temporada ' . $numero,
                        'descripcion' => $datos['overview'] ?? null,
                        'fecha_estreno' => $datos['air_date'] ?? null,
                        'cantidad_episodios' => count($datos['episodes'] ?? [])
                    ]
                );

                $totalTemporadas++;

                // Guardar episodios
                foreach ($datos['episodes'] ?? [] as $datosEpisodio) {

                    Episodio::updateOrCreate(
                        [
                            'contenido_id' => $anime->getKey(),
                            'proveedor_id' => $datosEpisodio['id']
                        ],
                        [
                            'temporada_id' => $temporada->getKey(),
                            'numero' => $datosEpisodio['episode_number'],
                            'titulo' => $datosEpisodio['name'] ?? 'Episodio sin titulo',
                            'descripcion' => $datosEpisodio['overview'] ?? null,
                            'imagen' => $datosEpisodio['still_path'] ?? null,
                            'duracion' => $datosEpisodio['runtime'] ?? null,
                            'fecha_estreno' => $datosEpisodio['air_date'] ?? null
                        ]
                    );

                    $totalEpisodios++;
                }
            }
        }

        $this->info("Sincronizacion completada.");
        $this->info("Temporadas procesadas: " . $totalTemporadas);
        $this->info("Episodios procesados: " . $totalEpisodios);

        return Command::SUCCESS;
    }
}
