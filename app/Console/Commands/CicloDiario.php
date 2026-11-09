<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CicloDiario extends Command
{
    protected $signature = 'catalogo:ciclo-diario
        {--limite= : Limite para esta ejecucion (paginas en catalogo:importar, contenidos en los pasos que tengan --limite)}
        {--reiniciar : Reinicia el avance guardado en los pasos que lo permiten}';

    protected $description = 'Ejecuta en orden la importacion del catalogo y la sincronizacion de fuentes';

    public function handle()
    {
        $limite = $this->option('limite');

        if ($limite !== null && (!ctype_digit((string) $limite) || (int) $limite < 1)) {
            $this->error('--limite debe ser un entero mayor o igual a 1.');

            return Command::FAILURE;
        }

        $limite = $limite !== null ? (int) $limite : null;

        // Sin --limite, la importacion sigue usando las 3 paginas de siempre
        $paginas = $limite ?? 3;

        $pasos = [
            ['catalogo:importar', ['fuente' => 'tmdb', 'tipo' => 'peliculas', 'paginas' => $paginas]],
            ['catalogo:importar', ['fuente' => 'tmdb', 'tipo' => 'series', 'paginas' => $paginas]],
            ['catalogo:importar', ['fuente' => 'anilist', 'tipo' => 'anime', 'paginas' => $paginas]],
            ['tvmaze:sincronizar', []],
            ['fuentes:sincronizar', []],
            ['catalogo:actualizar-emision', []],
        ];

        if (now('America/Costa_Rica')->isSunday()) {
            $pasos[] = ['fuentes:sincronizar', ['--pendientes' => true]];
        }

        $reiniciar = (bool) $this->option('reiniciar');
        $fallos = 0;

        foreach ($pasos as [$comando, $argumentos]) {

            $inicio = microtime(true);

            try {
                if ($reiniciar) {
                    if ($this->aceptaOpcion($comando, 'reiniciar')) {
                        $argumentos['--reiniciar'] = true;
                    } else {
                        $this->warn("{$comando} no tiene --reiniciar, se ejecuta normal.");
                    }
                }

                // catalogo:importar ya recibe el limite como "paginas".
                // Los demas pasos lo reciben como --limite solo si lo definen.
                if (
                    $limite !== null
                    && $comando !== 'catalogo:importar'
                    && $this->aceptaOpcion($comando, 'limite')
                ) {
                    $argumentos['--limite'] = $limite;
                }

                $this->info('>>> ' . $comando . ' ' . json_encode($argumentos));

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

    // Revisa si el comando define la opcion indicada antes de pasarsela
    protected function aceptaOpcion(string $comando, string $opcion): bool
    {
        return $this->getApplication()
            ->find($comando)
            ->getDefinition()
            ->hasOption($opcion);
    }
}
