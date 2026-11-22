<?php

namespace App\Services\Catalogo;

use Illuminate\Support\Facades\Http;

class EmbeddingService
{
    private const MODELO = 'gemini-embedding-001';
    private const URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:batchEmbedContents';
    public const DIMENSIONES = 768;

    // Devuelve un vector por cada texto, en el mismo orden.
    // $tarea: RETRIEVAL_DOCUMENT para guardar contenidos, RETRIEVAL_QUERY para la busqueda
    public function generar(array $textos, $tarea = 'RETRIEVAL_DOCUMENT')
    {
        $textos = array_values($textos);

        $peticiones = array_map(fn($texto) => [
            'model' => 'models/' . self::MODELO,
            'content' => ['parts' => [['text' => $texto]]],
            'taskType' => $tarea,
            'outputDimensionality' => self::DIMENSIONES,
        ], $textos);

        $intento = 0;
        $maxIntentos = 5;
        $respuesta = null;

        while ($intento < $maxIntentos) {

            $respuesta = Http::withHeaders([
                'x-goog-api-key' => config('services.gemini.key')
            ])
                ->timeout(60)
                ->post(self::URL, ['requests' => $peticiones]);

            if ($respuesta->successful()) {
                break;
            }

            if ($respuesta->status() != 429 && $respuesta->status() < 500) {
                throw new \Exception(
                    'Error al generar embeddings: ' . $respuesta->status()
                );
            }

            $intento++;

            if ($intento >= $maxIntentos) {
                break;
            }

            $espera = $respuesta->header('Retry-After');

            if ($espera !== null && is_numeric($espera)) {
                $espera = (int) $espera;
            } else {
                $espera = min(60, 10 * pow(2, $intento - 1));
            }

            sleep($espera);
        }

        if (!$respuesta || $respuesta->failed()) {
            throw new \Exception(
                'Gemini no respondio despues de ' . $maxIntentos . ' intentos. HTTP: ' .
                    ($respuesta ? $respuesta->status() : 'sin respuesta')
            );
        }

        $vectores = collect($respuesta->json('embeddings'))
            ->pluck('values')
            ->all();

        if (
            count($vectores) !== count($textos) ||
            count($vectores[0] ?? []) !== self::DIMENSIONES
        ) {
            throw new \Exception('La API devolvio vectores con un tamano inesperado');
        }

        return $vectores;
    }
}
