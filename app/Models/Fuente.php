<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Fuente extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'fuentes';

    protected $fillable = [
        'contenido_id',
        'episodio_id',
        'proveedor',
        'url',
        'tipo',
        'idioma',
        'subtitulos',
        'estado',
        'prioridad',
        'tiempo_respuesta',
        'ultima_verificacion',
        'fecha_expiracion'
    ];

    protected $casts = [
        'subtitulos' => 'array',
        'prioridad' => 'integer',
        'tiempo_respuesta' => 'integer',
        'ultima_verificacion' => 'datetime',
        'fecha_expiracion' => 'datetime'
    ];

    public function contenido()
    {
        return $this->belongsTo(Contenido::class, 'contenido_id', '_id');
    }

    public function episodio()
    {
        return $this->belongsTo(Episodio::class, 'episodio_id', '_id');
    }

    public function verificaciones()
    {
        return $this->hasMany(Verificacion::class, 'fuente_id', '_id');
    }
}
