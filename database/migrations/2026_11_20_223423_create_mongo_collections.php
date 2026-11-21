<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Driver\Exception\CommandException;
use MongoDB\Laravel\Schema\Blueprint;

/*
 * Colecciones e indices de MongoDB (base streaming_catalogo).
 * Los indices son los mismos que existen hoy en Atlas, con sus mismos nombres,
 * asi que correrla sobre esa base no cambia nada y en una base nueva los crea.
 */

return new class extends Migration
{
    private const CONEXION = 'mongodb';

    // Codigos de MongoDB cuando el indice ya existe con otro nombre u opciones
    private const INDICE_EXISTENTE = [85, 86];

    // coleccion => lista de [nombre del indice, campos]
    private function definicion(): array
    {
        return [
            'contenidos' => [
                ['titulo_1', ['titulo' => 1]],
                ['tipo_1', ['tipo' => 1]],
                ['tmdb_id_1', ['tmdb_id' => 1]],
                ['id_mal_1', ['id_mal' => 1]],
            ],
            'temporadas' => [
                ['contenido_id_1', ['contenido_id' => 1]],
            ],
            'episodios' => [
                ['contenido_id_1', ['contenido_id' => 1]],
                ['temporada_id_1', ['temporada_id' => 1]],
            ],
            'identificadores' => [
                ['contenido_id_1', ['contenido_id' => 1]],
            ],
            'fuentes' => [
                ['contenido_id_1_estado_1', ['contenido_id' => 1, 'estado' => 1]],
                ['episodio_id_1', ['episodio_id' => 1]],
                ['updated_at_1', ['updated_at' => 1]],
                ['contenido_id_1_episodio_id_1_proveedor_1_huella_1', [
                    'contenido_id' => 1,
                    'episodio_id' => 1,
                    'proveedor' => 1,
                    'huella' => 1,
                ]],
            ],
            'verificaciones' => [
                ['created_at_1', ['created_at' => 1]],
            ],
            'progresos' => [
                ['usuario_id_1_contenido_id_1_episodio_id_1', [
                    'usuario_id' => 1,
                    'contenido_id' => 1,
                    'episodio_id' => 1,
                ]],
                ['usuario_id_1_ultima_reproduccion_-1', [
                    'usuario_id' => 1,
                    'ultima_reproduccion' => -1,
                ]],
            ],
            // Solo usa _id (la clave del proceso)
            'sincronizaciones' => [],
        ];
    }

    public function up(): void
    {
        $schema = Schema::connection(self::CONEXION);

        foreach ($this->definicion() as $coleccion => $indices) {

            if (!$schema->hasCollection($coleccion)) {
                $schema->create($coleccion);
            }

            if (empty($indices)) {
                continue;
            }

            $schema->table($coleccion, function (Blueprint $col) use ($indices) {
                foreach ($indices as [$nombre, $campos]) {
                    try {
                        $col->index($campos, $nombre);
                    } catch (CommandException $e) {
                        // Si ya existe un indice equivalente con otro nombre se omite
                        if (!in_array($e->getCode(), self::INDICE_EXISTENTE)) {
                            throw $e;
                        }
                    }
                }
            });
        }
    }

    // Solo quita los indices de esta migracion. No borra colecciones ni datos
    public function down(): void
    {
        $schema = Schema::connection(self::CONEXION);

        foreach ($this->definicion() as $coleccion => $indices) {

            if (empty($indices) || !$schema->hasCollection($coleccion)) {
                continue;
            }

            $schema->table($coleccion, function (Blueprint $col) use ($indices) {
                foreach ($indices as [$nombre]) {
                    $col->dropIndexIfExists($nombre);
                }
            });
        }
    }
};
