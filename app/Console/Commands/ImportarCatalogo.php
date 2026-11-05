<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sincronizacion;
use App\Services\Catalogo\TMDBService;
use App\Services\Catalogo\AniListService;

class ImportarCatalogo extends Command
{
    protected $signature = 'catalogo:importar {fuente} {tipo} {paginas=3}';

    protected $description = 'Importa contenido por paginas y recuerda el avance';

    public function handle()
    {
        $fuente = strtolower($this->argument('fuente'));
        $tipo = strtolower($this->argument('tipo'));
        $paginas = (int) $this->argument('paginas');

        if ($paginas < 1) {
            $this->error('La cantidad de paginas debe ser mayor que cero.');
            return Command::FAILURE;
        }

        if (!(($fuente == 'tmdb' && in_array($tipo, ['peliculas', 'series'])) ||
            ($fuente == 'anilist' && $tipo == 'anime'))) {
            $this->error('Combinacion de fuente y tipo no valida.');
            return Command::FAILURE;
        }

        $clave = $fuente . '_' . $tipo;

        $sincronizacion = Sincronizacion::firstOrCreate(
            ['_id' => $clave],
            [
                'ultima_pagina' => 0,
                'total_importados' => 0
            ]
        );

        $paginaInicial = $sincronizacion->ultima_pagina + 1;
        $paginaFinal = $sincronizacion->ultima_pagina + $paginas;

        $tmdb = new TMDBService();
        $anilist = new AniListService();

        $total = 0;

        $this->info("Iniciando desde la pagina {$paginaInicial}.");
        $importadosPagina = 0;

        for ($pagina = $paginaInicial; $pagina <= $paginaFinal; $pagina++) {

            $this->info("Importando pagina {$pagina}...");

            if ($fuente == 'tmdb') {

                $tipoContenido = $tipo == 'peliculas' ? 'pelicula' : 'serie';

                $ruta = $tipoContenido == 'pelicula'
                    ? '/movie/popular'
                    : '/tv/popular';

                $respuesta = $tmdb->consultar($ruta, [
                    'page' => $pagina
                ]);

                $resultados = $respuesta['results'] ?? [];

                if (empty($resultados)) {
                    $this->warn('No se encontraron resultados.');
                    break;
                }

                foreach ($resultados as $contenido) {
                    $tmdb->importarContenido(
                        $contenido['id'],
                        $tipoContenido
                    );

                    $total++;
                }
            } else {

                $animes = $anilist->obtenerPopulares($pagina, 20);

                if (empty($animes)) {
                    $this->warn('No se encontraron resultados.');
                    break;
                }


                foreach ($animes as $anime) {
                    $contenido = $anilist->importarAnime($anime['id']);

                    if ($contenido) {
                        $total++;
                        $importadosPagina++;
                    }
                }
            }

            $sincronizacion->ultima_pagina = $pagina;
            $sincronizacion->total_importados += $total;
            $sincronizacion->actualizado_en = now();
            $sincronizacion->save();

            $this->info("Pagina {$pagina} completada.");
        }

        $this->info("Importaciones realizadas: {$total}");
        $this->info("Ultima pagina procesada: {$sincronizacion->ultima_pagina}");

        return Command::SUCCESS;
    }
}
