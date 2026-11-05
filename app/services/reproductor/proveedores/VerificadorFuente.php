<?php

namespace App\Services\Reproductor;

use App\Models\Fuente;
use App\Models\Verificacion;
use Illuminate\Support\Facades\Http;

class VerificadorFuente
{
    public function verificar($fuenteId)
    {
        $fuente = Fuente::find($fuenteId);

        if (!$fuente) {
            return false;
        }

        $inicio = microtime(true);

        try {
            $respuesta = Http::timeout(15)
                ->get($fuente->url);

            $tiempo = round((microtime(true) - $inicio) * 1000);

            $resultado = $respuesta->successful();

            $fuente->estado = $resultado ? 'disponible' : 'no_disponible';
            $fuente->ultima_verificacion = now();
            $fuente->tiempo_respuesta = $tiempo;
            $fuente->save();

            $verificacion = new Verificacion();
            $verificacion->fuente_id = $fuente->_id;
            $verificacion->fecha = now();
            $verificacion->resultado = $resultado;
            $verificacion->codigo = $respuesta->status();
            $verificacion->tiempo_respuesta = $tiempo;
            $verificacion->mensaje = $resultado ? 'Respuesta correcta' : 'Error HTTP';

            $verificacion->save();

            return $resultado;
        } catch (\Exception $e) {

            $verificacion = new Verificacion();
            $verificacion->fuente_id = $fuente->_id;
            $verificacion->fecha = now();
            $verificacion->resultado = false;
            $verificacion->codigo = 0;
            $verificacion->mensaje = $e->getMessage();

            $verificacion->save();

            $fuente->estado = 'no_disponible';
            $fuente->ultima_verificacion = now();
            $fuente->save();

            return false;
        }
    }
}
