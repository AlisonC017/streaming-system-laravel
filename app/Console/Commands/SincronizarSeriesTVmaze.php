<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\BSON\ObjectId;
use App\Models\Contenido;
use App\Models\Identificador;
use App\Models\Sincronizacion;
use App\Services\Catalogo\TVmazeService;
use App\Services\Catalogo\TMDBService;

class SincronizarSeriesTVmaze extends Command
{
    protected $signature = 'tvmaze:sincronizar {--limite= : Maximo de series a procesar en esta corrida} {--reiniciar : Reinicia el avance y empieza desde la primera serie}';

    protected $description = 'Importa temporadas y episodios de las series, continuando desde la ultima procesada';

    public function handle()
    {
        $tvmaze = new TVmazeService();
        $tmdb = new TMDBService();

        $clave = 'tvmaze_series';

        if ($this->option('reiniciar')) {
            Sincronizacion::where('_id', $clave)->delete();

            $this->info('Avance de sincronizacion reiniciado.');
        }

        $sincronizacion = Sincronizacion::firstOrCreate(
            ['_id' => $clave],
            [
                'ultima_pagina' => 0,
                'total_importados' => 0
            ]
        );

        $ultimoId = $sincronizacion->ultimo_contenido_id;

        $consulta = Contenido::where('tipo', 'serie');

        if ($ultimoId && preg_match('/^[a-f0-9]{24}$/i', (string) $ultimoId)) {
            $consulta->where('_id', '>', new ObjectId((string) $ultimoId));
            $this->info("Continuando despues de la serie con id {$ultimoId}.");
        } else {
            $this->info('Iniciando desde la primera serie.');
        }

        $consulta->orderBy('_id');

        if ($this->option('limite')) {
            $consulta->limit((int) $this->option('limite'));
        }

        $series = $consulta->get();

        if ($series->isEmpty()) {
            $this->info('No hay series pendientes. Use --reiniciar para procesarlas todas de nuevo.');
            return Command::SUCCESS;
        }

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
                    $this->avanzar($sincronizacion, $serie);
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
                    $this->avanzar($sincronizacion, $serie);
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
                $this->avanzar($sincronizacion, $serie);
                continue;
            }

            $procesadas++;
            $totalTemporadas += $resultado['temporadas'];
            $totalEpisodios += $resultado['episodios'];

            $serie->tvmaze_actualizado_en = now();
            $serie->save();

            $sincronizacion->total_importados = ($sincronizacion->total_importados ?? 0) + 1;
            $this->avanzar($sincronizacion, $serie);

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

    // Guarda la ultima serie procesada para continuar desde ahi
    protected function avanzar($sincronizacion, $serie)
    {
        $sincronizacion->ultimo_contenido_id = (string) $serie->_id;
        $sincronizacion->actualizado_en = now();
        $sincronizacion->save();
    }
}
