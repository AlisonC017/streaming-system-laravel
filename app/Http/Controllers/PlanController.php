<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
     public function index()
    {
        return view('modules.planes.index', [
            'planes' => self::listar(),
            'suscripcion' => null, // TODO: cargar la suscripcion del cliente si hay sesion
        ]);
    }

    public static function listar(): array
    {
        try {
            return DB::select('EXEC sp_listar_planes');
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }
}
