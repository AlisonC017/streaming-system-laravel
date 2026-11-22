<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contenido;
use App\Services\Catalogo\EmbeddingService;

class GenerarEmbeddings extends Command
{
    protected $signature = 'catalogo:generar-embeddings
        {--limite= : Maximo de contenidos a procesar en esta ejecucion}';

    protected $description = 'Genera el embedding de los contenidos que todavia no lo tienen';

    private const LOTE = 5;

    public function handle(EmbeddingService $servicio)
    {
        // whereNull tambien incluye los documentos que no tienen el campo
        $consulta = Contenido::whereNull('embedding')->orderBy('_id');

        if ($this->option('limite')) {
            $consulta->limit((int) $this->option('limite'));
        }

        $contenidos = $consulta->get();

        if ($contenidos->isEmpty()) {
            $this->warn('No hay contenidos sin embedding.');

            return Command::SUCCESS;
        }

        $this->info("Contenidos a procesar: {$contenidos->count()}");

        $total = 0;

        foreach ($contenidos->chunk(self::LOTE) as $lote) {
            try {
                $lote = $lote->values();
                $textos = $lote->map(fn($c) => $this->texto($c))->all();
                $vectores = $servicio->generar($textos);

                foreach ($lote as $i => $contenido) {
                    $contenido->embedding = $vectores[$i];
                    $contenido->save();
                    $total++;
                }

                $this->info("Embeddings generados: {$total}");
            } catch (\Exception $e) {
                $this->error('Lote omitido: ' . $e->getMessage());
            }

            sleep(3); // pausa para respetar el limite de la capa gratuita
        }

        $this->info("Finalizado. Total: {$total}");

        return Command::SUCCESS;
    }

    // Texto que representa al contenido: titulo, generos y descripcion
    private function texto(Contenido $contenido)
    {
        $generos = is_array($contenido->generos) ? implode(', ', $contenido->generos) : '';

        return trim($contenido->titulo . '. ' . $generos . '. ' . $contenido->descripcion);
    }
}
