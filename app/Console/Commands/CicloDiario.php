<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CicloDiario extends Command
{
    protected $signature = 'catalogo:ciclo-diario
        {--limite= : Limite para esta ejecucion (paginas en catalogo:importar, contenidos en los pasos que tengan --limite)}
        {--peliculas= : Paginas de peliculas a importar (por defecto 3)}
        {--series= : Paginas de series a importar (por defecto 3)}
        {--anime= : Paginas de anime a importar (por defecto 3)}
        {--reiniciar : Reinicia el avance guardado en los pasos que lo permiten}';

    protected $description = 'Ejecuta en orden la importacion del catalogo, la sincronizacion y verificacion de fuentes y los embeddings';

    public function handle()
    {
        $limite = $this->option('limite');

        if ($limite !== null && (!ctype_digit((string) $limite) || (int) $limite < 1)) {
            $this->error('--limite debe ser un entero mayor o igual a 1.');

            return Command::FAILURE;
        }

        $limite = $limite !== null ? (int) $limite : null;

        // Paginas por tipo: 3 por defecto, o --limite si se indica, o la opcion de cada tipo
        $paginas = [];

        foreach (['peliculas', 'series', 'anime'] as $tipo) {
            $valor = $this->option($tipo);

            if ($valor !== null && (!ctype_digit((string) $valor) || (int) $valor < 1)) {
                $this->error("--{$tipo} debe ser un entero mayor o igual a 1.");

                return Command::FAILURE;
            }

            $paginas[$tipo] = $valor !== null ? (int) $valor : ($limite ?? 3);
        }

        $pasos = [
            ['catalogo:importar', ['fuente' => 'tmdb', 'tipo' => 'peliculas', 'paginas' => $paginas['peliculas']]],
            ['catalogo:importar', ['fuente' => 'tmdb', 'tipo' => 'series', 'paginas' => $paginas['series']]],
            ['catalogo:importar', ['fuente' => 'anilist', 'tipo' => 'anime', 'paginas' => $paginas['anime']]],
            ['tvmaze:sincronizar', []],
            ['fuentes:sincronizar', []],
            ['catalogo:actualizar-emision', []],
        ];

        $esDomingo = now('America/Costa_Rica')->isSunday();

        if ($esDomingo) {
            $pasos[] = ['fuentes:sincronizar', ['--pendientes' => true]];
        }

        // La verificacion va separada de la sincronizacion: primero se crean
        // las fuentes y luego se verifican las nuevas o modificadas
        $pasos[] = ['fuentes:verificar', ['--concurrencia' => 50]];

        // Los domingos tambien se vuelven a revisar las que estaban caidas
        if ($esDomingo) {
            $pasos[] = ['fuentes:verificar', ['--estado' => 'no_disponible'], ['--concurrencia' => 50]];
        }

        // Embeddings de los contenidos nuevos (busqueda por significado)
        $pasos[] = ['catalogo:generar-embeddings', []];

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
