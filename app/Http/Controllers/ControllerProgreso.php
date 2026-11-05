<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Progreso;
use App\Models\Contenido;
use App\Models\Episodio;

class ControllerProgreso extends Controller
{
    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'contenido_id' => 'required',
            'episodio_id' => 'nullable',
            'segundo_actual' => 'required|integer|min:0',
            'duracion_total' => 'required|integer|min:0'
        ]);

        $usuarioId = Auth::id();

        $contenido = Contenido::findOrFail($datos['contenido_id']);

        if (!empty($datos['episodio_id'])) {

            Episodio::where('_id', $datos['episodio_id'])
                ->where('contenido_id', $contenido->_id)
                ->firstOrFail();
        }

        $porcentaje = 0;

        if ($datos['duracion_total'] > 0) {
            $porcentaje = ($datos['segundo_actual'] / $datos['duracion_total']) * 100;
        }

        $completado = $porcentaje >= 90;

        $consulta = Progreso::where('usuario_id', $usuarioId)
            ->where('contenido_id', $datos['contenido_id']);

        if (!empty($datos['episodio_id'])) {
            $consulta->where('episodio_id', $datos['episodio_id']);
        } else {
            $consulta->whereNull('episodio_id');
        }

        $progreso = $consulta->first();

        if (!$progreso) {
            $progreso = new Progreso();
        }

        $progreso->usuario_id = $usuarioId;
        $progreso->contenido_id = $datos['contenido_id'];
        $progreso->episodio_id = $datos['episodio_id'] ?? null;
        $progreso->segundo_actual = $datos['segundo_actual'];
        $progreso->duracion_total = $datos['duracion_total'];
        $progreso->porcentaje = $porcentaje;
        $progreso->completado = $completado;
        $progreso->ultima_reproduccion = now();

        $progreso->save();

        return response()->json([
            'mensaje' => 'Progreso guardado',
            'progreso' => $progreso
        ]);
    }

    public function obtener($contenidoId)
    {
        $progresos = Progreso::where('usuario_id', Auth::id())
            ->where('contenido_id', $contenidoId)
            ->get();

        return response()->json([
            'progresos' => $progresos
        ]);
    }

    public function continuar()
    {
        $progresos = Progreso::where('usuario_id', Auth::id())
            ->where('completado', false)
            ->orderBy('ultima_reproduccion', 'desc')
            ->get();

        return response()->json([
            'progresos' => $progresos
        ]);
    }
}
