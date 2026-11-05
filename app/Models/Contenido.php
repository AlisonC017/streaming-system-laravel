<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Contenido extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'contenidos';

    protected $fillable = [
        'tmdb_id',
        'titulo',
        'tipo',
        'proveedor_catalogo',
        'proveedor_id',
        'descripcion',
        'imagen',
        'generos',
        'fecha_estreno',
        'estado',
        'puntuacion',
        'popularidad'
    ];

    protected $casts = [
        'generos' => 'array',
        'fecha_estreno' => 'date',
        'puntuacion' => 'float'
    ];

    public function temporadas()
    {
        return $this->hasMany(Temporada::class, 'contenido_id', '_id');
    }

    public function episodios()
    {
        return $this->hasMany(Episodio::class, 'contenido_id', '_id');
    }

    public function fuentes()
    {
        return $this->hasMany(Fuente::class, 'contenido_id', '_id');
    }

    public function progresos()
    {
        return $this->hasMany(Progreso::class, 'contenido_id', '_id');
    }
}
