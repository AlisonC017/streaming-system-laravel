<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPanelController extends Controller
{

    public function dashboard()
    {
        $datos = [
            'suscripcionesActivas' => 0,
            'ingresosMes' => 0,
            'cobrosFallidos' => 0,
            'cancelaciones' => 0,
            'ultimosCobros' => collect(),
        ];

        try {
            $r = DB::select('EXEC sp_admin_resumen')[0] ?? null;
            if ($r) {
                $datos['suscripcionesActivas'] = (int) $r->suscripcionesActivas;
                $datos['ingresosMes'] = (float) $r->ingresosMes;
                $datos['cobrosFallidos'] = (int) $r->cobrosFallidos;
                $datos['cancelaciones'] = (int) $r->cancelaciones;
            }
            $datos['ultimosCobros'] = collect(DB::select('EXEC sp_admin_cobros ?', [10]));
        } catch (\Throwable $e) {
            report($e);
        }

        return view('modules.admin.dashboard', $datos);
    }

    public function usuarios(Request $request)
    {
        try {
            $clientes = collect(DB::select('EXEC sp_admin_usuarios ?, ?', [
                $request->input('buscar'),
                $request->input('estado'),
            ]));
        } catch (\Throwable $e) {
            report($e);
            $clientes = collect();
        }

        return view('modules.admin.usuarios', ['clientes' => $clientes]);
    }

    public function usuarioShow($id)
    {
        try {
            $cliente = DB::select('EXEC sp_admin_cliente ?', [$id])[0] ?? null;
            $pagos = collect(DB::select('EXEC sp_admin_cliente_pagos ?', [$id]));
            $metodos = collect(DB::select('EXEC sp_admin_cliente_metodos ?', [$id]));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.usuarios')->with('error', 'No se pudo cargar el cliente.');
        }
        abort_if(!$cliente, 404);

        return view('modules.admin.usuario-show', compact('cliente', 'pagos', 'metodos'));
    }

    public function usuarioEdit($id)
    {
        abort_unless(session('nivel') === 'Total', 403);

        try {
            $cliente = DB::select('EXEC sp_admin_cliente ?', [$id])[0] ?? null;
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.usuarios')->with('error', 'No se pudo cargar el cliente.');
        }
        abort_if(!$cliente, 404);

        return view('modules.admin.usuario-edit', compact('cliente'));
    }

    public function usuarioUpdate(Request $request, $id)
    {
        abort_unless(session('nivel') === 'Total', 403);

        $d = $request->validate([
            'nombre'    => 'required|string|max:50',
            'apellidos' => 'required|string|max:80',
            'pais'      => 'required|string|max:50',
            'estado'    => 'required|in:Activo,Inactivo',
        ]);

        try {
            DB::statement(
                'EXEC sp_admin_actualizar_cliente ?, ?, ?, ?, ?',
                [$id, $d['nombre'], $d['apellidos'], $d['pais'], $d['estado']]
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudieron guardar los cambios.');
        }

        return redirect()->route('admin.usuarios.show', $id)->with('status', 'Cliente actualizado.');
    }

    public function cancelarCuenta($id)
    {
        abort_unless(session('nivel') === 'Total', 403);

        try {
            $r = DB::select('EXEC sp_admin_cancelar_cuenta ?', [$id]);
            $n = (int) ($r[0]->afectadas ?? 0);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo cancelar la cuenta.');
        }

        return back()->with('status', $n > 0 ? 'Cuenta cancelada.' : 'El cliente no tenía suscripciones activas.');
    }

    public function administradores(Request $request)
    {
        try {
            $administradores = collect(DB::select('EXEC sp_admin_administradores ?, ?', [
                $request->input('buscar'),
                $request->input('nivel'),
            ]));
        } catch (\Throwable $e) {
            report($e);
            $administradores = collect();
        }

        return view('modules.admin.administradores', ['administradores' => $administradores]);
    }

    public function administradorEdit($id)
    {
        abort_unless(session('nivel') === 'Total', 403);

        try {
            $admin = DB::select('EXEC sp_admin_administrador_obtener ?', [$id])[0] ?? null;
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.administradores')->with('error', 'No se pudo cargar el administrador.');
        }
        abort_if(!$admin, 404);

        return view('modules.admin.administrador-edit', compact('admin'));
    }

    public function administradorUpdate(Request $request, $id)
    {
        abort_unless(session('nivel') === 'Total', 403);

        if ((int) $id === (int) auth()->id()) {
            return back()->with('error', 'No podés cambiar tu propio nivel ni tu estado.');
        }

        $d = $request->validate([
            'nivel_acceso' => 'required|in:Total,Soporte,Contenido',
            'estado'       => 'required|in:Activo,Inactivo',
        ]);

        try {
            DB::statement('EXEC sp_admin_actualizar_administrador ?, ?, ?', [$id, $d['nivel_acceso'], $d['estado']]);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudieron guardar los cambios.');
        }

        return redirect()->route('admin.administradores')->with('status', 'Administrador actualizado.');
    }

    public function planes()
    {
        try {
            $planes = collect(DB::select('EXEC sp_admin_planes'));
        } catch (\Throwable $e) {
            report($e);
            $planes = collect();
        }

        return view('modules.admin.planes', ['planes' => $planes]);
    }

    public function planStore(Request $request)
    {
        abort_unless(session('nivel') === 'Total', 403);

        $d = $request->validate([
            'nombre_plan' => 'required|string|max:50',
            'precio'      => 'required|numeric|gt:0|max:99999999',
            'calidad'     => 'required|in:SD,HD,FullHD,4K',
        ]);

        try {
            DB::statement('EXEC sp_admin_plan_crear ?, ?, ?', [$d['nombre_plan'], $d['precio'], $d['calidad']]);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo crear el plan.');
        }

        return redirect()->route('admin.planes')->with('status', 'Plan creado.');
    }

    public function planEdit($id)
    {
        abort_unless(session('nivel') === 'Total', 403);

        try {
            $plan = DB::select('EXEC sp_admin_plan_obtener ?', [$id])[0] ?? null;
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.planes')->with('error', 'No se pudo cargar el plan.');
        }
        abort_if(!$plan, 404);

        return view('modules.admin.plan-edit', compact('plan'));
    }

    public function planUpdate(Request $request, $id)
    {
        abort_unless(session('nivel') === 'Total', 403);

        $d = $request->validate([
            'nombre_plan' => 'required|string|max:50',
            'precio'      => 'required|numeric|gt:0|max:99999999',
            'calidad'     => 'required|in:SD,HD,FullHD,4K',
        ]);

        try {
            DB::statement('EXEC sp_admin_plan_actualizar ?, ?, ?, ?', [$id, $d['nombre_plan'], $d['precio'], $d['calidad']]);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar el plan.');
        }

        return redirect()->route('admin.planes')->with('status', 'Plan actualizado.');
    }

    public function cobros()
    {
        try {
            $suscripciones = collect(DB::select('EXEC sp_admin_suscripciones'));
            $cobros = collect(DB::select('EXEC sp_admin_cobros ?', [50]));
        } catch (\Throwable $e) {
            report($e);
            $suscripciones = collect();
            $cobros = collect();
        }

        return view('modules.admin.cobros', ['suscripciones' => $suscripciones, 'cobros' => $cobros]);
    }

    public function reportes(Request $request)
    {
        try {
            $rendimiento = collect(DB::select('EXEC sp_admin_reporte_planes ?, ?', [
                $request->input('desde'),
                $request->input('hasta'),
            ]));
        } catch (\Throwable $e) {
            report($e);
            $rendimiento = collect();
        }

        return view('modules.admin.reportes', ['rendimiento' => $rendimiento]);
    }
}
