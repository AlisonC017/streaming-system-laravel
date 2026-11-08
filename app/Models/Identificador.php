<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Identificador extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'identificadores';

    protected $fillable = [
        'contenido_id',
        'tmdb_id',
        'tmdb_temporada',
        'anilist_id',
        'mal_id',
        'tvmaze_id',
        'animedoc_subject_id',
        'animedoc_detail_path'
    ];

    protected $casts = [
        'tmdb_id' => 'integer',
        'tmdb_temporada' => 'integer',
        'anilist_id' => 'integer',
        'mal_id' => 'integer',
        'tvmaze_id' => 'integer'
    ];

    public function contenido()
    {
        return $this->belongsTo(Contenido::class, 'contenido_id', '_id');
    }
}
