<?php

namespace App\Services\Catalogo;

use Illuminate\Support\Facades\Http;
use App\Models\Contenido;
use App\Models\Identificador;
use App\Models\Temporada;
use App\Models\Episodio;

class TenraiService
{
    protected $url = 'https://api.tenrai.org/v1';

    public function obtenerEpisodios($idMal, $pagina = 1)
    {
        $respuesta = Http::connectTimeout(30)
            ->timeout(60)
            ->get($this->url . "/anime/{$idMal}/episodes", [
                'page' => $pagina
            ]);

        if (!$respuesta->successful()) {
            throw new \Exception('Error consultando Tenrai: ' . $respuesta->status());
        }

        return $respuesta->json();
    }

    public function importarEpisodios(Contenido $contenido)
    {
        if ($contenido->tipo != 'anime') {
            return null;
        }

        $identificador = Identificador::where(
            'contenido_id',
            $contenido->_id
        )->first();

        $idMal = $identificador->mal_id ?? $contenido->id_mal;

        if (!$idMal) {
            return null;
        }

        $temporada = Temporada::updateOrCreate(
            [
                'contenido_id' => $contenido->_id,
                'proveedor_id' => $idMal
            ],
            [
                'numero' => 1,
                'titulo' => 'Episodios',
                'descripcion' => null
            ]
        );

        $pagina = 1;
        $total = 0;
        $continuar = true;

        while ($continuar) {
            $respuesta = $this->obtenerEpisodios(
                $idMal,
                $pagina
            );

            $episodios = $respuesta['data'] ?? [];

            foreach ($episodios as $datos) {
                $fecha = null;

                if (!empty($datos['aired'])) {
                    $fecha = substr($datos['aired'], 0, 10);
                }

                Episodio::updateOrCreate(
                    [
                        'contenido_id' => $contenido->_id,
                        'proveedor_id' => $datos['mal_id']
                    ],
                    [
                        'temporada_id' => $temporada->_id,
                        'numero' => $datos['mal_id'],
                        'titulo' => $datos['title'] ?? 'Episodio sin titulo',
                        'descripcion' => null,
                        'imagen' => null,
                        'duracion' => null,
                        'fecha_estreno' => $fecha
                    ]
                );

                $total++;
            }

            $continuar = $respuesta['pagination']['has_next_page'] ?? false;

            $pagina++;

            if ($continuar) {
                usleep(500000);
            }
        }

        $temporada->cantidad_episodios = $total;
        $temporada->save();

        return [
            'anime' => $contenido->titulo,
            'temporadas' => 1,
            'episodios' => $total
        ];
    }
}
