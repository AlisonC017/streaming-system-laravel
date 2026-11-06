<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contenido;
use App\Models\Identificador;
use App\Services\Reproductor\ProveedorReproduccionService;

class SincronizarFuentes extends Command
{
    protected $signature = 'fuentes:sincronizar {--limite= : Maximo de contenidos a procesar} {--titulo= : Solo contenidos cuyo titulo contenga este texto}';

    protected $description = 'Sincroniza las fuentes de reproduccion del catalogo';

    public function handle(ProveedorReproduccionService $proveedorService)
    {
        // Contenidos con tmdb_id en Identificador, mas los registros anteriores de TMDB
        $idsConTmdb = Identificador::whereNotNull('tmdb_id')
            ->pluck('contenido_id')
            ->all();

        $consulta = Contenido::where(function ($q) use ($idsConTmdb) {
            $q->where('proveedor_catalogo', 'tmdb')
                ->orWhereIn('_id', $idsConTmdb);
        });

        if ($this->option('titulo')) {
            $consulta->where(
                'titulo',
                'regexp',
                '/' . preg_quote($this->option('titulo'), '/') . '/i'
            );
        }

        if ($this->option('limite')) {
            $consulta->limit((int) $this->option('limite'));
        }

        $contenidos = $consulta->get();

        $this->info("Contenidos a sincronizar: {$contenidos->count()}");

        $totalFuentes = 0;

        foreach ($contenidos as $contenido) {
            try {
                $cantidad = $proveedorService->sincronizarContenido($contenido);

                $totalFuentes += $cantidad;

                $this->info(
                    "Contenido {$contenido->titulo}: {$cantidad} fuentes registradas."
                );
            } catch (\Exception $e) {
                $this->error(
                    "Error con {$contenido->titulo}: " . $e->getMessage()
                );
            }
        }

        $this->info("Sincronizacion finalizada.");
        $this->info("Total de fuentes registradas: {$totalFuentes}");

        return Command::SUCCESS;
    }
}
