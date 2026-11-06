<?php

namespace App\Services\Catalogo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AnimeDocService
{
    protected $url;

    public function __construct()
    {
        $this->url = rtrim(config('services.animedoc.url'), '/');
    }

    public function buscar($palabra, $pagina = 1, $porPagina = 10)
    {
        $apiKey = config('services.animedoc.key');

        if (!$apiKey) {
            throw new Exception('No se configuro la API Key de AnimeDoc.');
        }

        try {
            $respuesta = Http::timeout(20)
                ->withHeaders([
                    'X-API-Key' => $apiKey
                ])
                ->post($this->url . '/search/', [
                    'keyword' => $palabra,
                    'page' => $pagina,
                    'perPage' => $porPagina
                ]);
        } catch (\Throwable $e) {
            Log::warning('AnimeDoc: error de red en busqueda', ['mensaje' => $e->getMessage()]);
            return [];
        }

        if (!$respuesta->successful()) {
            Log::warning('AnimeDoc: busqueda no exitosa', [
                'status' => $respuesta->status(),
                'keyword' => $palabra
            ]);
            return [];
        }

        return $respuesta->json('data') ?? [];
    }

    public function obtenerFuentes($subjectId, $detailPath, $temporada, $episodio)
    {
        $apiKey = config('services.animedoc.key');

        if (!$apiKey) {
            throw new Exception('No se configuro la API Key de AnimeDoc.');
        }

        try {
            $respuesta = Http::timeout(20)
                ->withHeaders([
                    'X-API-Key' => $apiKey
                ])
                ->get($this->url . '/media-sources/', [
                    'subjectId' => $subjectId,
                    'detailPath' => $detailPath,
                    'se' => $temporada,
                    'ep' => $episodio
                ]);
        } catch (\Throwable $e) {
            Log::warning('AnimeDoc: error de red', ['mensaje' => $e->getMessage()]);
            return [];
        }

        if (!$respuesta->successful()) {
            Log::warning('AnimeDoc: respuesta no exitosa', [
                'status' => $respuesta->status(),
                'subjectId' => $subjectId,
                'se' => $temporada,
                'ep' => $episodio
            ]);
            return [];
        }

        return $respuesta->json('data') ?? [];
    }
}
