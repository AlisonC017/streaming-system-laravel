<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contenido;
use App\Services\Catalogo\TVmazeService;

class SincronizarSeriesTVmaze extends Command
{
    protected $signature = 'tvmaze:sincronizar';

    protected $description = 'Importa temporadas y episodios de todas las series';

    public function handle()
    {
        $tvmaze = new TVmazeService();

        $series = Contenido::where('tipo', 'serie')->get();

        $procesadas = 0;
        $omitidas = 0;
        $totalTemporadas = 0;
        $totalEpisodios = 0;

        foreach ($series as $serie) {

            $this->info("Procesando: {$serie->titulo}");

            $tvmazeId = $serie->tvmaze_id;

            if (!$tvmazeId) {

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

                $serie->tvmaze_id = $tvmazeId;
                $serie->save();
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
