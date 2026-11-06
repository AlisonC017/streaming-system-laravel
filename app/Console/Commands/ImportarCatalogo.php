<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Sincronizacion;
use App\Models\Identificador;

use App\Services\Catalogo\TMDBService;
use App\Services\Catalogo\AniListService;
use App\Services\Catalogo\TenraiService;

class ImportarCatalogo extends Command
{
    protected $signature = 'catalogo:importar {fuente} {tipo} {paginas=3} {--reiniciar : Reinicia el avance de la importacion}';

    protected $description = 'Importa contenido y sus temporadas y episodios';

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

        if ($this->option('reiniciar')) {
            Sincronizacion::where('_id', $clave)->delete();

            $this->info('Avance de importacion reiniciado.');
        }

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
        $tenrai = new TenraiService();

        $total = 0;

        $this->info("Iniciando desde la pagina {$paginaInicial}.");

        for ($pagina = $paginaInicial; $pagina <= $paginaFinal; $pagina++) {

            $this->info("Importando pagina {$pagina}...");

            $importadosPagina = 0;

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
                    $this->warn("No hay resultados en la pagina {$pagina}.");
                    break;
                }

                foreach ($resultados as $datos) {

                    $contenido = $tmdb->importarContenido(
                        $datos['id'],
                        $tipoContenido
                    );

                    if ($contenido) {
                        $importadosPagina++;
                        $this->info('Contenido: ' . $contenido->titulo);
                    }
                }
            } else {

                $animes = $anilist->obtenerPopulares($pagina, 20);

                if (empty($animes)) {
                    $this->warn("No hay resultados en la pagina {$pagina}.");
                    break;
                }

                foreach ($animes as $anime) {

                    try {

                        $contenido = $anilist->importarAnime($anime['id']);

                        if ($contenido) {

                            $importadosPagina++;

                            $this->info('Anime: ' . $contenido->titulo);

                            $identificador = Identificador::where(
                                'contenido_id',
                                $contenido->_id
                            )->first();

                            $idMal = $identificador->mal_id ?? $contenido->id_mal;

                            if ($idMal) {

                                $resultado = $tenrai->importarEpisodios($contenido);

                                if ($resultado) {
                                    $this->info(
                                        'Episodios importados: ' . $resultado['episodios']
                                    );
                                }
                            } else {

                                $this->warn(
                                    'No tiene identificador de MyAnimeList. Se omitieron sus episodios.'
                                );
                            }
                        }
                    } catch (\Exception $e) {

                        $this->error(
                            'Error importando anime ID ' . $anime['id'] . ': ' . $e->getMessage()
                        );
                    }

                    usleep(2200000);
                }
            }

            $total += $importadosPagina;

            $sincronizacion->ultima_pagina = $pagina;
            $sincronizacion->total_importados += $importadosPagina;
            $sincronizacion->actualizado_en = now();

            $sincronizacion->save();

            $this->info(
                "Pagina {$pagina} completada. Contenidos importados: {$importadosPagina}"
            );
        }

        $this->newLine();

        $this->info("Importaciones realizadas: {$total}");

        $this->info(
            "Ultima pagina procesada: {$sincronizacion->ultima_pagina}"
        );

        return Command::SUCCESS;
    }
}
