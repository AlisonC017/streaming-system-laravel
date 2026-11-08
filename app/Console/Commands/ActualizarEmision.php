<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contenido;
use App\Services\Catalogo\AniListService;
use App\Services\Catalogo\NormalizadorIdsAnime;
use App\Services\Catalogo\TenraiService;
use App\Services\Reproductor\ProveedorReproduccionService;

class ActualizarEmision extends Command
{
    protected $signature = 'catalogo:actualizar-emision
        {--paginas=2 : Paginas de animes en emision a revisar (20 por pagina)}';

    protected $description = 'Actualiza los animes en emision: nuevas entradas, episodios nuevos y sus fuentes';

    public function handle(
        AniListService $anilist,
        TenraiService $tenrai,
        NormalizadorIdsAnime $normalizador,
        ProveedorReproduccionService $proveedorService
    ) {
        $paginas = max(1, (int) $this->option('paginas'));
        $ids = [];

        // Animes que AniList marca como en emision (incluye temporadas nuevas)
        for ($pagina = 1; $pagina <= $paginas; $pagina++) {
            try {
                $animes = $anilist->obtenerEnEmision($pagina, 20);
            } catch (\Exception $e) {
                $this->error('Error consultando AniList: ' . $e->getMessage());
                break;
            }

            if (empty($animes)) {
                break;
            }

            foreach ($animes as $anime) {
                $ids[] = (int) $anime['id'];
            }

            // AniList permite unas 30 peticiones por minuto
            usleep(2500000);
        }

        // Tambien los que ya estan guardados como en emision, para que
        // se actualicen cuando terminen de emitirse
        $guardados = Contenido::where('tipo', 'anime')
            ->where('estado', 'releasing')
            ->pluck('proveedor_id')
            ->all();

        foreach ($guardados as $guardado) {
            $ids[] = (int) $guardado;
        }

        $ids = array_values(array_unique($ids));

        if (empty($ids)) {
            $this->warn('No hay animes en emision para actualizar.');
            return Command::SUCCESS;
        }

        $this->info('Animes a revisar: ' . count($ids));

        $totalFuentes = 0;
        $fallidos = [];

        foreach ($ids as $id) {
            try {
                // Crea el anime si es nuevo o actualiza su estado y fecha
                $contenido = $anilist->importarAnime($id);

                usleep(2500000);

                if (!$contenido) {
                    $this->warn("AniList no devolvio el anime {$id}.");
                    continue;
                }

                $tenrai->importarEpisodios($contenido);

                if (!$normalizador->normalizar($contenido)) {
                    $this->warn("Sin equivalente en TMDB (se omite): {$contenido->titulo}");
                    continue;
                }

                $cantidad = $proveedorService->sincronizarEpisodiosNuevos($contenido);

                $totalFuentes += $cantidad;

                $this->info("{$contenido->titulo}: {$cantidad} fuentes nuevas.");
            } catch (\Exception $e) {
                $fallidos[] = $id;

                $this->error("Error con el anime {$id}: " . $e->getMessage());
            }
        }

        //se limpia la cache de contenidos con fuentes
        Contenido::olvidarCacheFuentes();

        $this->newLine();
        $this->info("Actualizacion terminada. Fuentes nuevas: {$totalFuentes}");

        if (!empty($fallidos)) {
            $this->warn('Animes con error (se reintentan en la proxima ejecucion): ' . implode(', ', $fallidos));
        }

        return Command::SUCCESS;
    }
}
