<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Progreso extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'progresos';

    protected $fillable = [
        'usuario_id',
        'contenido_id',
        'episodio_id',
        'segundo_actual',
        'duracion_total',
        'porcentaje',
        'completado',
        'ultima_reproduccion'
    ];

    protected $casts = [
        'segundo_actual' => 'integer',
        'duracion_total' => 'integer',
        'porcentaje' => 'float',
        'completado' => 'boolean',
        'ultima_reproduccion' => 'datetime'
    ];

    public function contenido()
    {
        return $this->belongsTo(Contenido::class, 'contenido_id', '_id');
    }

    public function episodio()
    {
        return $this->belongsTo(Episodio::class, 'episodio_id', '_id');
    }
}
