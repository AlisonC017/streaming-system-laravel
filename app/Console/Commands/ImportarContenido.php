<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Catalogo\TMDBService;

class ImportarContenido extends Command
{
    protected $signature = 'contenido:importar {tipo} {id}';

    protected $description = 'Importa peliculas o series desde TMDB';

    public function handle()
    {
        $tipo = $this->argument('tipo');
        $id = $this->argument('id');

        if (!is_numeric($id) || $id <= 0) {
            $this->error('El ID debe ser un numero valido.');
            return self::FAILURE;
        }

        if (!in_array($tipo, ['pelicula', 'serie'])) {
            $this->error('El tipo debe ser pelicula o serie.');
            return self::FAILURE;
        }

        $this->info("Importando {$tipo} desde TMDB...");

        $tmdbService = new TMDBService();

        $contenido = $tmdbService->importarContenido($id, $tipo);

        if (!$contenido) {
            $this->error('No se pudo importar el contenido.');
            return self::FAILURE;
        }

        $this->info('Contenido importado correctamente.');
        $this->info('Titulo: ' . $contenido->titulo);
        $this->info('Tipo: ' . $contenido->tipo);
        $this->info('ID MongoDB: ' . $contenido->_id);

        return self::SUCCESS;
    }
}
