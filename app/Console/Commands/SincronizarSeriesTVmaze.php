<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contenido;
use App\Models\Identificador;
use App\Services\Catalogo\TVmazeService;
use App\Services\Catalogo\TMDBService;

class SincronizarSeriesTVmaze extends Command
{
    protected $signature = 'tvmaze:sincronizar';

    protected $description = 'Importa temporadas y episodios de todas las series';

    public function handle()
    {
        $tvmaze = new TVmazeService();
        $tmdb = new TMDBService();

        $series = Contenido::where('tipo', 'serie')->get();

        $procesadas = 0;
        $omitidas = 0;
        $totalTemporadas = 0;
        $totalEpisodios = 0;

        foreach ($series as $serie) {

            $this->info("Procesando: {$serie->titulo}");

            $identificador = Identificador::where('contenido_id', $serie->_id)->first();

            // Identificador primero; el campo anterior de Contenido por compatibilidad
            $tvmazeId = $identificador->tvmaze_id ?? $serie->tvmaze_id;

            if (!$tvmazeId) {

                // 1) Buscar por ID de IMDb (viene de TMDB, no depende del idioma del titulo)
                $tmdbId = $identificador->tmdb_id
                    ?? $serie->tmdb_id
                    ?? ($serie->proveedor_catalogo == 'tmdb' ? $serie->proveedor_id : null);

                if ($tmdbId) {
                    $externos = $tmdb->obtenerIdsExternos($tmdbId);
                    $imdbId = $externos['imdb_id'] ?? null;

                    if ($imdbId) {
                        $encontrada = $tvmaze->buscarPorImdb($imdbId);

                        if ($encontrada && isset($encontrada['id'])) {
                            $tvmazeId = $encontrada['id'];
                            $this->line("Encontrada por IMDb ({$imdbId}).");
                        }
                    }
                }
            }

            if (!$tvmazeId) {

                // 2) Respaldo: buscar por nombre con coincidencia exacta
                $resultados = $tvmaze->buscarSeries($serie->titulo);

                if (empty($resultados)) {
                    $this->warn("No encontrada en TVmaze.");
                    $omitidas++;
                    continue;
                }

                $coincidencia = null;

                foreach ($resultados as $resultado) {

                    $show = $resultado['show'] ?? $resultado;

                    if (strcasecmp(
                        trim($show['name'] ?? ''),
                        trim($serie->titulo)
                    ) == 0) {
                        $coincidencia = $show;
                        break;
                    }
                }

                if (!$coincidencia) {
                    $this->warn("No se encontro una coincidencia exacta. Se omitio.");
                    $omitidas++;
                    continue;
                }

                $tvmazeId = $coincidencia['id'];
            }

            // Se registra en Identificador sin tocar los demas identificadores
            if (($identificador->tvmaze_id ?? null) != $tvmazeId) {
                Identificador::updateOrCreate(
                    ['contenido_id' => $serie->_id],
                    ['tvmaze_id' => (int) $tvmazeId]
                );
            }

            $resultado = $tvmaze->importarTemporadasYEpisodios(
                $serie->_id,
                $tvmazeId
            );

            if (!$resultado) {
                $this->error("Error importando {$serie->titulo}");
                $omitidas++;
                continue;
            }

            $procesadas++;
            $totalTemporadas += $resultado['temporadas'];
            $totalEpisodios += $resultado['episodios'];

            $serie->tvmaze_actualizado_en = now();
            $serie->save();

            $this->info(
                "Temporadas: {$resultado['temporadas']} | Episodios: {$resultado['episodios']}"
            );
        }

        $this->newLine();
        $this->info('SINCRONIZACION FINALIZADA');
        $this->info("Series procesadas: {$procesadas}");
        $this->info("Series omitidas: {$omitidas}");
        $this->info("Temporadas importadas: {$totalTemporadas}");
        $this->info("Episodios importados: {$totalEpisodios}");

        return Command::SUCCESS;
    }
}
