<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = ['nombre', 'apellidos', 'correo', 'estado', 'password'];

    protected $hidden = ['password'];
    
    public function getRememberTokenName()
    {
        return '';
    }
}
