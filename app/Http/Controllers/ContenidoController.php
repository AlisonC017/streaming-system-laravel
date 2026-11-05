<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contenido;

class ContenidoController extends Controller
{
    public function catalogo()
    {
        $contenidos = Contenido::orderBy('titulo')->get();

        return view('catalogo.index', compact('contenidos'));
    }

    public function buscar(Request $request)
    {
        $nombre = $request->input('nombre');
        $tipo = $request->input('tipo');

        $consulta = Contenido::query();

        if ($nombre) {
            $nombre = preg_quote($nombre, '/');

            $consulta->where('titulo', 'regexp', '/' . $nombre . '/i');
        }

        if ($tipo) {
            $consulta->where('tipo', $tipo);
        }

        $contenidos = $consulta->orderBy('titulo')->get();

        return view('catalogo.index', compact('contenidos'));
    }

    public function mostrar($id)
    {
        $contenido = Contenido::findOrFail($id);

        return view('catalogo.detalle', compact('contenido'));
    }
}
