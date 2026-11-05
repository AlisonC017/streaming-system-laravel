<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Catalogo\TVmazeService;

class ImportarTVmaze extends Command
{
    protected $signature = 'tvmaze:importar {contenidoId} {tvmazeId}';

    protected $description = 'Importa temporadas y episodios desde TVmaze';

    public function handle()
    {
        $contenidoId = $this->argument('contenidoId');
        $tvmazeId = $this->argument('tvmazeId');

        if (!is_numeric($tvmazeId) || $tvmazeId <= 0) {
            $this->error('El ID de TVmaze debe ser valido.');
            return self::FAILURE;
        }

        $this->info('Importando temporadas y episodios desde TVmaze...');

        $tvmazeService = new TVmazeService();

        $resultado = $tvmazeService->importarTemporadasYEpisodios(
            $contenidoId,
            $tvmazeId
        );

        if (!$resultado) {
            $this->error('No se pudieron importar las temporadas y episodios.');
            return self::FAILURE;
        }

        $this->info('Importacion completada.');
        $this->info('Serie: ' . $resultado['serie']);
        $this->info('Temporadas: ' . $resultado['temporadas']);
        $this->info('Episodios: ' . $resultado['episodios']);

        return self::SUCCESS;
    }
}
