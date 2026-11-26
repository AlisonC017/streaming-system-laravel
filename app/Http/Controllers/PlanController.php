<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
     public function index()
    {
        // si es cliente se marca su plan actual; un admin o un visitante solo ven los planes
        $suscripcion = null;
        if (auth()->check() && ! session('nivel')) {
            try {
                $suscripcion = SuscripcionController::delCliente(auth()->id());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return view('modules.planes.index', [
            'planes' => self::listar(),
            'suscripcion' => $suscripcion,
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
