<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Episodio extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'episodios';

    protected $fillable = [
        'contenido_id',
        'temporada_id',
        'proveedor_id',
        'numero',
        'titulo',
        'descripcion',
        'imagen',
        'duracion',
        'fecha_estreno'
    ];

    protected $casts = [
        'numero' => 'integer',
        'duracion' => 'integer',
        'fecha_estreno' => 'date'
    ];

    public function contenido()
    {
        return $this->belongsTo(Contenido::class, 'contenido_id', '_id');
    }

    public function temporada()
    {
        return $this->belongsTo(Temporada::class, 'temporada_id', '_id');
    }

    public function fuentes()
    {
        return $this->hasMany(Fuente::class, 'episodio_id', '_id');
    }

    public function progresos()
    {
        return $this->hasMany(Progreso::class, 'episodio_id', '_id');
    }
}
