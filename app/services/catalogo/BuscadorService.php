<?php

namespace App\Services\Catalogo;

use App\Models\Contenido;
use MongoDB\BSON\ObjectId;

class BuscadorService
{
    protected $embeddings;
    private $UMBRAL = 0.60;

    public function __construct(EmbeddingService $embeddings)
    {
        $this->embeddings = $embeddings;
    }

    public function buscar($nombre)
    {
        if (!$nombre) {
            return [];
        }

        $contenidos = Contenido::conFuentes()->where(
            'titulo',
            'regexp',
            "/" . preg_quote($nombre, '/') . "/i"
        )
            ->get();

        return $contenidos;
    }

    // Busqueda hibrida: combina resultados por titulo y por significado.
    public function buscarHibrido($texto, $tipo = null)
    {
        $tradicionales = collect($this->buscar($texto));

        if ($tipo) {
            $tradicionales = $tradicionales->where('tipo', $tipo)->values();
        }

        $semanticos = $this->buscarPorSignificado($texto, $tipo);

        return $this->fusionarResultados($tradicionales, $semanticos);
    }

    // Busqueda por significado: compara el texto con los embeddings de contenidos.
    // Solo devuelve contenidos con fuentes activas, ordenados del mas parecido al menos
    public function buscarPorSignificado($texto, $tipo = null)
    {
        $texto = trim((string) $texto);

        if ($texto === '') {
            return collect();
        }

        $consulta = $this->embeddings->generar([$texto], 'RETRIEVAL_QUERY')[0];
        $conFuentes = array_flip(array_map('strval', Contenido::idsConFuentes()));

        $filtro = ['embedding' => ['$exists' => true]];

        if ($tipo) {
            $filtro['tipo'] = $tipo;
        }

        $documentos = Contenido::raw(function ($coleccion) use ($filtro) {
            return $coleccion->find($filtro, ['projection' => ['embedding' => 1]]);
        });

        $puntajes = [];

        foreach ($documentos as $doc) {
            $id = (string) $doc['_id'];

            if (!isset($conFuentes[$id])) {
                continue;
            }

            $vector = $doc['embedding'];

            if (is_object($vector) && method_exists($vector, 'getArrayCopy')) {
                $vector = $vector->getArrayCopy();
            }

            $vector = array_map('floatval', (array) $vector);

            if (count($consulta) !== count($vector)) {
                continue;
            }

            $puntajes[$id] = $this->similitudCoseno($consulta, $vector);
        }

        arsort($puntajes);

        $ids = [];

        foreach ($puntajes as $id => $puntaje) {
            if ($puntaje >= $this->UMBRAL) {
                $ids[] = $id;
            }
        }

        if (empty($ids)) {
            return collect();
        }

        return Contenido::whereIn('_id', array_map(fn($id) => new ObjectId($id), $ids))->get()
            ->sortBy(fn($c) => array_search((string) $c->_id, $ids))
            ->values();
    }

    // Combina las posiciones de los resultados tradicionales y semanticos.
    protected function fusionarResultados($tradicionales, $semanticos)
    {
        $puntajes = [];
        $contenidos = [];

        foreach ([$tradicionales, $semanticos] as $resultados) {
            foreach ($resultados as $posicion => $contenido) {
                $id = (string) $contenido->_id;

                $contenidos[$id] = $contenido;

                if (!isset($puntajes[$id])) {
                    $puntajes[$id] = 0;
                }

                $puntajes[$id] += 1 / (60 + $posicion + 1);
            }
        }

        arsort($puntajes);

        $ids = array_keys($puntajes);

        return collect($ids)
            ->map(fn($id) => $contenidos[$id])
            ->values();
    }

    protected function similitudCoseno(array $a, array $b)
    {
        $punto = 0;
        $normaA = 0;
        $normaB = 0;

        foreach ($a as $i => $valor) {
            $punto += $valor * $b[$i];
            $normaA += $valor * $valor;
            $normaB += $b[$i] * $b[$i];
        }

        return $punto / (sqrt($normaA) * sqrt($normaB) ?: 1);
    }
}
