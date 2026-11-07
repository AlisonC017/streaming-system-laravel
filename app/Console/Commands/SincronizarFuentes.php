<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\BSON\ObjectId;
use App\Models\Contenido;
use App\Models\Identificador;
use App\Models\Sincronizacion;
use App\Services\Reproductor\ProveedorReproduccionService;

class SincronizarFuentes extends Command
{
    protected $signature = 'fuentes:sincronizar
        {--limite= : Maximo de contenidos a procesar en esta ejecucion}
        {--titulo= : Solo contenidos cuyo titulo contenga este texto (no usa ni guarda el avance)}
        {--reiniciar : Reinicia el avance y empieza desde el primer contenido}';

    protected $description = 'Sincroniza las fuentes de reproduccion del catalogo';

    public function handle(ProveedorReproduccionService $proveedorService)
    {
        $clave = 'fuentes_sincronizar';
        $porTitulo = (bool) $this->option('titulo');

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

        // Contenidos con tmdb_id en Identificador, mas los registros anteriores de TMDB
        $idsConTmdb = Identificador::whereNotNull('tmdb_id')
            ->pluck('contenido_id')
            ->all();

        $consulta = Contenido::where(function ($q) use ($idsConTmdb) {
            $q->where('proveedor_catalogo', 'tmdb')
                ->orWhereIn('_id', $idsConTmdb);
        });

        if ($porTitulo) {
            $consulta->where(
                'titulo',
                'regexp',
                '/' . preg_quote($this->option('titulo'), '/') . '/i'
            );
        } elseif ($ultimoId) {
            $consulta->where('_id', '>', new ObjectId($ultimoId));

            $this->info("Continuando despues del contenido {$ultimoId}.");
        }

        $consulta->orderBy('_id');

        if ($this->option('limite')) {
            $consulta->limit((int) $this->option('limite'));
        }

        $contenidos = $consulta->get();

        if ($contenidos->isEmpty()) {
            if (!$porTitulo && $ultimoId) {
                $this->warn('No hay contenidos nuevos desde la ultima vez. Use --reiniciar para empezar de cero.');
            } else {
                $this->warn('No hay contenidos para sincronizar.');
            }

            return Command::SUCCESS;
        }

        $this->info("Contenidos a sincronizar: {$contenidos->count()}");

        $totalFuentes = 0;
        $fallidos = [];

        foreach ($contenidos as $contenido) {
            $cantidad = 0;

            try {
                $cantidad = $proveedorService->sincronizarContenido($contenido);

                $totalFuentes += $cantidad;

                $this->info(
                    "Contenido {$contenido->titulo}: {$cantidad} fuentes registradas."
                );
            } catch (\Exception $e) {
                $fallidos[] = $contenido->titulo;

                $this->error(
                    "Error con {$contenido->titulo}: " . $e->getMessage()
                );
            }

            // El avance se guarda contenido por contenido
            if (!$porTitulo) {
                $sincronizacion->ultimo_contenido_id = (string) $contenido->_id;
                $sincronizacion->total_importados += $cantidad;
                $sincronizacion->actualizado_en = now();

                $sincronizacion->save();
            }
        }

        $this->newLine();
        $this->info('Sincronizacion finalizada.');
        $this->info("Total de fuentes registradas: {$totalFuentes}");

        if (!empty($fallidos)) {
            $this->warn('Contenidos con error (no se reintentan solos): ' . implode(', ', $fallidos));
        }

        return Command::SUCCESS;
    }
}
