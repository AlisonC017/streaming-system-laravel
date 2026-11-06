<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Temporada extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'temporadas';

    protected $fillable = [
        'contenido_id',
        'proveedor_id',
        'numero',
        'titulo',
        'descripcion',
        'fecha_estreno',
        'cantidad_episodios'
    ];

    protected $casts = [
        'numero' => 'integer',
        'fecha_estreno' => 'date',
        'cantidad_episodios' => 'integer'
    ];

    public function contenido()
    {
        return $this->belongsTo(Contenido::class, 'contenido_id', '_id');
    }

    public function episodios()
    {
        return $this->hasMany(Episodio::class, 'temporada_id', '_id');
    }
}
