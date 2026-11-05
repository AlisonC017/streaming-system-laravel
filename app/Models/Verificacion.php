<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Verificacion extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'verificaciones';

    protected $fillable = [
        'fuente_id',
        'fecha',
        'resultado',
        'codigo',
        'tiempo_respuesta',
        'mensaje'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'codigo' => 'integer',
        'tiempo_respuesta' => 'integer'
    ];

    public function fuente()
    {
        return $this->belongsTo(Fuente::class, 'fuente_id', '_id');
    }
}
