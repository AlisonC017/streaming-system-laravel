<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contenido;
use App\Models\Temporada;
use App\Models\Episodio;
use App\Models\Fuente;

class SincronizarFuentesVidSrc extends Command
{
    protected $signature = 'vidsrc:sincronizar';

    protected $description = 'Sincroniza las fuentes embed de VidSrc con MongoDB';

    public function handle()
    {
        $contenidos = Contenido::where('proveedor_catalogo', 'tmdb')->get();

        $creadas = 0;
        $actualizadas = 0;
        $omitidas = 0;

        foreach ($contenidos as $contenido) {

            $id = (string) $contenido->proveedor_id;

            if ($id == '') {
                $omitidas++;
                continue;
            }

            if ($contenido->tipo == 'pelicula') {

                $url = "https://vidsrc.sh/embed/movie/{$id}";

                $fuente = Fuente::updateOrCreate(
                    [
                        'contenido_id' => $contenido->_id,
                        'episodio_id' => null,
                        'proveedor' => 'vidsrc'
                    ],
                    [
                        'url' => $url,
                        'tipo' => 'embed',
                        'idioma' => 'multi',
                        'subtitulos' => [],
                        'estado' => 'activa',
                        'prioridad' => 1,
                        'ultima_verificacion' => now()
                    ]
                );

                if ($fuente->wasRecentlyCreated) {
                    $creadas++;
                } else {
                    $actualizadas++;
                }
            } else {

                $episodios = Episodio::where(
                    'contenido_id',
                    $contenido->_id
                )->get();

                foreach ($episodios as $episodio) {

                    $temporada = Temporada::find($episodio->temporada_id);

                    if (!$temporada) {
                        $omitidas++;
                        continue;
                    }

                    $numeroTemporada = $temporada->numero;
                    $numeroEpisodio = $episodio->numero;

                    $url = "https://vidsrc.sh/embed/tv/{$id}/{$numeroTemporada}/{$numeroEpisodio}";

                    $fuente = Fuente::updateOrCreate(
                        [
                            'contenido_id' => $contenido->_id,
                            'episodio_id' => $episodio->_id,
                            'proveedor' => 'vidsrc'
                        ],
                        [
                            'url' => $url,
                            'tipo' => 'embed',
                            'idioma' => 'multi',
                            'subtitulos' => [],
                            'estado' => 'activa',
                            'prioridad' => 1,
                            'ultima_verificacion' => now()
                        ]
                    );

                    if ($fuente->wasRecentlyCreated) {
                        $creadas++;
                    } else {
                        $actualizadas++;
                    }
                }
            }
        }

        $this->info('Sincronizacion finalizada.');
        $this->info("Fuentes creadas: {$creadas}");
        $this->info("Fuentes actualizadas: {$actualizadas}");
        $this->info("Registros omitidos: {$omitidas}");

        return Command::SUCCESS;
    }
}
