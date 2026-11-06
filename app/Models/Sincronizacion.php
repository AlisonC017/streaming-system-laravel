<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Sincronizacion extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sincronizaciones_catalogo';

    protected $fillable = [
        '_id',
        'ultima_pagina',
        'total_importados',
        'actualizado_en'
    ];

    protected $casts = [
        'ultima_pagina' => 'integer',
        'total_importados' => 'integer',
        'actualizado_en' => 'datetime'
    ];
}
