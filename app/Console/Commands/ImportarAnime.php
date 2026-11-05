<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Catalogo\AniListService;

class ImportarAnime extends Command
{
    protected $signature = 'anime:importar {id}';

    protected $description = 'Importa un anime desde AniList';

    public function handle()
    {
        $id = $this->argument('id');

        if (!is_numeric($id) || $id <= 0) {
            $this->error('El ID debe ser un numero valido.');
            return self::FAILURE;
        }

        $this->info("Importando anime {$id} desde AniList...");

        $aniListService = new AniListService();

        $contenido = $aniListService->importarAnime($id);

        if (!$contenido) {
            $this->error('No se pudo importar el anime.');
            return self::FAILURE;
        }

        $this->info('Anime importado correctamente.');
        $this->info('Titulo: ' . $contenido->titulo);
        $this->info('ID MongoDB: ' . $contenido->_id);

        return self::SUCCESS;
    }
}
