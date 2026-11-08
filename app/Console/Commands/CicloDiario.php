<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CicloDiario extends Command
{
    protected $signature = 'catalogo:ciclo-diario';

    protected $description = 'Ejecuta en orden la importacion del catalogo y la sincronizacion de fuentes';

    public function handle()
    {
        $pasos = [
            ['catalogo:importar', ['fuente' => 'tmdb', 'tipo' => 'peliculas', 'paginas' => 3]],
            ['catalogo:importar', ['fuente' => 'tmdb', 'tipo' => 'series', 'paginas' => 3]],
            ['catalogo:importar', ['fuente' => 'anilist', 'tipo' => 'anime', 'paginas' => 3]],
            ['tvmaze:sincronizar', []],
            ['fuentes:sincronizar', []],
            ['catalogo:actualizar-emision', []],
        ];

        if (now('America/Costa_Rica')->isSunday()) {
            $pasos[] = ['fuentes:sincronizar', ['--pendientes' => true]];
        }

        $fallos = 0;

        foreach ($pasos as [$comando, $argumentos]) {

            $this->info('>>> ' . $comando . ' ' . json_encode($argumentos));

            $inicio = microtime(true);

            try {
                $codigo = $this->call($comando, $argumentos);
            } catch (\Throwable $e) {
                $codigo = Command::FAILURE;

                $this->error("Excepcion en {$comando}: " . $e->getMessage());

                Log::error('Ciclo diario: excepcion', [
                    'comando' => $comando,
                    'mensaje' => $e->getMessage()
                ]);
            }

            $segundos = round(microtime(true) - $inicio);

            if ($codigo !== Command::SUCCESS) {
                $fallos++;

                $this->error("<<< {$comando} fallo ({$segundos} s). Se continua con el siguiente paso.");
            } else {
                $this->info("<<< {$comando} termino ({$segundos} s).");
            }
        }

        $this->newLine();
        $this->info($fallos === 0
            ? 'Ciclo diario completo.'
            : "Ciclo diario terminado con {$fallos} paso(s) con error.");

        return $fallos === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
