<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contenido;
use App\Models\Fuente;
use App\Services\Reproductor\VerificadorFuente;

class VerificarFuentes extends Command
{
    protected $signature = 'fuentes:verificar
        {--estado=pendiente : Fuentes a verificar: pendiente, disponible, no_disponible o todas}
        {--limite= : Maximo de fuentes a verificar en esta ejecucion}
        {--concurrencia=10 : Fuentes verificadas a la vez (1 = una por una)}
        {--pausa=0 : Milisegundos de pausa entre verificaciones (solo con --concurrencia=1)}';

    protected $description = 'Verifica las fuentes de reproduccion (separado de la sincronizacion)';

    public function handle(VerificadorFuente $verificador)
    {
        $estado = $this->option('estado');

        if (!in_array($estado, ['pendiente', 'disponible', 'no_disponible', 'todas'])) {
            $this->error('--estado debe ser pendiente, disponible, no_disponible o todas.');

            return Command::FAILURE;
        }

        $filtro = $estado !== 'todas' ? ['estado' => $estado] : [];

        $opciones = [
            'projection' => ['_id' => 1],
            // Primero las que nunca se verificaron o llevan mas tiempo sin verificarse
            'sort' => ['ultima_verificacion' => 1],
            // Evita el limite de memoria de MongoDB al ordenar muchas fuentes
            'allowDiskUse' => true,
        ];

        if ($this->option('limite')) {
            $opciones['limit'] = (int) $this->option('limite');
        }

        $modelo = new Fuente();
        $coleccion = $modelo->getConnection()->getCollection($modelo->getTable());

        // Solo los id, como texto, sin crear modelos (ahorra memoria)
        $ids = collect();

        foreach ($coleccion->find($filtro, $opciones) as $documento) {
            $ids->push((string) $documento['_id']);
        }

        if ($ids->isEmpty()) {
            $this->warn("No hay fuentes con estado '{$estado}' para verificar.");

            return Command::SUCCESS;
        }

        $this->info("Fuentes a verificar: {$ids->count()}");

        $concurrencia = max(1, (int) $this->option('concurrencia'));
        $pausa = max(0, (int) $this->option('pausa'));
        $correctas = 0;
        $otras = 0;
        $hechas = 0;

        if ($concurrencia === 1) {
            foreach ($ids as $id) {
                try {
                    $verificador->verificar((string) $id) ? $correctas++ : $otras++;
                } catch (\Throwable $e) {
                    $otras++;

                    $this->error("Error verificando la fuente {$id}: " . $e->getMessage());
                }

                $hechas++;

                if ($hechas % 100 === 0) {
                    $this->info("Verificadas: {$hechas}");
                }

                if ($pausa > 0) {
                    usleep($pausa * 1000);
                }
            }
        } else {
            $ultimoAviso = 0;

            try {
                [$correctas, $otras] = $verificador->verificarVentana(
                    $ids->all(),
                    $concurrencia,
                    function ($hechas) use ($ids, &$ultimoAviso) {
                        if (intdiv($hechas, 500) > $ultimoAviso) {
                            $ultimoAviso = intdiv($hechas, 500);
                            $this->info("Verificadas: {$hechas} de {$ids->count()}");
                        }
                    }
                );
            } catch (\Throwable $e) {
                $this->error('Error durante la verificacion: ' . $e->getMessage());

                return Command::FAILURE;
            }
        }

        // Para que el catalogo refleje los cambios de estado
        Contenido::olvidarCacheFuentes();

        $this->newLine();
        $this->info('Verificacion finalizada.');
        $this->info("Disponibles: {$correctas}");
        $this->info("No disponibles o no concluyentes: {$otras}");

        return Command::SUCCESS;
    }
}
