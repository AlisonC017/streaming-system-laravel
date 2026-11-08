<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;


class Contenido extends Model
{
    // Clave y segundos de la cache de contenidos con fuentes
    private const CACHE_IDS_FUENTES = 'contenidos_ids_con_fuentes';
    private const CACHE_SEGUNDOS = 300;

    protected $connection = 'mongodb';
    protected $collection = 'contenidos';

    protected $fillable = [
        'tmdb_id',
        'id_mal',
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
        'generos' => 'json',
        'fecha_estreno' => 'date',
        'puntuacion' => 'float',
        'popularidad' => 'float',
        'tmdb_id' => 'integer',
        'id_mal' => 'integer'
    ];

    public function identificador()
    {
        return $this->hasOne(Identificador::class, 'contenido_id', '_id');
    }

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

    protected static function fuentesActivas()
    {
        return Fuente::where('estado', 'disponible')
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')
                    ->orWhere('fecha_expiracion', '>', now());
            });
    }

    // id de los contenidos que tienen al menos una fuente activa.
    // Se guarda en cache unos minutos para no consultar todas las fuentes cada que vuelve a catalogo
    public static function idsConFuentes()
    {
        return Cache::remember(self::CACHE_IDS_FUENTES, self::CACHE_SEGUNDOS, function () {
            return self::fuentesActivas()
                ->pluck('contenido_id')
                ->unique()
                ->values()
                ->all();
        });
    }

    // Borra la cache para que el catalogo refleje cambios de inmediato
    public static function olvidarCacheFuentes()
    {
        Cache::forget(self::CACHE_IDS_FUENTES);
    }

    public function scopeConFuentes($query)
    {
        $ids = [];

        foreach (self::idsConFuentes() as $id) {
            if (preg_match('/^[a-f0-9]{24}$/i', (string) $id)) {
                //Error de inteliphense, por si acaso no se asusten gente xD
                $ids[] = new ObjectId((string) $id);
            }
        }

        return $query->whereIn('_id', $ids);
    }

    // id de los episodios de este contenido que tienen fuente activa
    public function idsEpisodiosConFuentes()
    {
        return self::fuentesActivas()->where('contenido_id', (string) $this->_id)
            ->pluck('episodio_id')->filter()->map(fn($id) => (string) $id)
            ->unique()->values()->all();
    }
}
