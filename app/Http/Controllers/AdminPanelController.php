<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\ErroresSql;
use Illuminate\Support\Facades\Concurrency;

class AdminPanelController extends Controller
{
    use ErroresSql;

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

        if (session('nivel') === 'Contenido') {
            $datos['ultimosCobros'] = $datos['ultimosCobros']->values()->map(function ($c, $i) {
                $c->cliente = 'Cliente #' . ($c->id_suscripcion ?? ($i + 1));
                return $c;
            });
        }

        return view('modules.admin.dashboard', $datos);
    }

    public function usuarios(Request $request)
    {
        abort_unless(in_array(session('nivel'), ['Total', 'Soporte'], true), 403);

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
        abort_unless(in_array(session('nivel'), ['Total', 'Soporte'], true), 403);

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
        abort_unless(in_array(session('nivel'), ['Total', 'Soporte'], true), 403);

        try {
            DB::statement('EXEC sp_cancelar_cuenta_cliente ?, ?', [$id, 'Cancelada desde el panel de administración']);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $this->mensajeSql($e));
        }

        return back()->with('status', 'Cuenta cancelada.');
    }

    public function administradores(Request $request)
    {
        abort_unless(session('nivel') === 'Total', 403);

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

        if ((int) $id === (int) $request->user()->id_usuario) {
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
        abort_unless(in_array(session('nivel'), ['Total', 'Soporte'], true), 403);

        try {
            // solo suscripciones que se pueden cobrar
            $suscripciones = collect(DB::select('EXEC sp_admin_suscripciones'))
                ->filter(fn($s) => ($s->estado ?? null) !== 'Cancelada')
                ->values();
            $cobros = collect(DB::select('EXEC sp_admin_cobros ?', [50]));
        } catch (\Throwable $e) {
            report($e);
            $suscripciones = collect();
            $cobros = collect();
        }

        $hoy = now('America/Costa_Rica')->toDateString();
        $porCobrar = $suscripciones
            ->filter(fn($s) => isset($s->fecha_proximo_cobro) && substr((string) $s->fecha_proximo_cobro, 0, 10) <= $hoy)
            ->values();

        return view('modules.admin.cobros', [
            'suscripciones' => $suscripciones,
            'porCobrar' => $porCobrar,
            'cobros' => $cobros,
        ]);
    }

    public function reportes(Request $request)
    {
        try {
            $rendimiento = collect(DB::select('EXEC sp_reporte_rendimiento_planes ?, ?', [
                $request->input('desde') ?: null,
                $request->input('hasta') ?: null,
            ]));
        } catch (\Throwable $e) {
            report($e);
            $rendimiento = collect();
            session()->now('error', $this->mensajeSql($e));
        }

        return view('modules.admin.reportes', ['rendimiento' => $rendimiento]);
    }

    public function planEliminar(Request $request, $id)
    {
        abort_unless(session('nivel') === 'Total', 403);

        $d = $request->validate(['id_plan_destino' => 'required|integer']);

        try {
            DB::statement('EXEC sp_descontinuar_y_migrar_plan ?, ?', [$id, $d['id_plan_destino']]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $this->mensajeSql($e));
        }

        return redirect()->route('admin.planes')
            ->with('status', 'Plan descontinuado y suscripciones migradas.');
    }

    public function cobroProcesar(Request $request)
    {
        abort_unless(session('nivel') === 'Total', 403);

        $d = $request->validate(['id_suscripcion' => 'required|integer']);

        try {
            $idMetodo = $this->metodoDeSuscripcion((int) $d['id_suscripcion']);
            if (! $idMetodo) {
                return back()->with('error', 'El cliente no tiene una tarjeta activa.');
            }

            DB::statement('EXEC sp_procesar_cobro_suscripcion ?, ?', [$d['id_suscripcion'], $idMetodo]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $this->mensajeSql($e));
        }

        $estado = DB::table('suscripcion')->where('id_suscripcion', $d['id_suscripcion'])->value('estado');

        return redirect()->route('admin.cobros')
            ->with('status', "Cobro procesado. Estado de la suscripción: {$estado}.");
    }

    public function cobroConcurrencia(Request $request)
    {
        abort_unless(session('nivel') === 'Total', 403);

        $d = $request->validate(['id_suscripcion' => 'required|integer']);
        $id = (int) $d['id_suscripcion'];

        $idMetodo = $this->metodoDeSuscripcion($id);
        if (! $idMetodo) {
            return back()->with('error', 'El cliente no tiene una tarjeta activa.');
        }

        $cobrar = function (int $n) use ($id, $idMetodo) {
            try {
                DB::statement('EXEC sp_procesar_cobro_suscripcion ?, ?', [$id, $idMetodo]);
                return "Cobro {$n}: realizado";
            } catch (\Throwable $e) {
                $msg = preg_match('/\[SQL Server\](.*?)\s*\(Connection:/s', $e->getMessage(), $m)
                    ? trim($m[1])
                    : get_class($e) . ': ' . $e->getMessage();
                return "Cobro {$n}: rechazado ({$msg})";
            }
        };

        $root = getenv('SystemRoot') ?: 'C:\\Windows';
        foreach (['SystemRoot' => $root, 'windir' => $root] as $k => $v) {
            putenv("$k=$v");
            $_SERVER[$k] = $_ENV[$k] = $v;
        }

        // Dos procesos PHP distintos, cada uno con su propia conexión a SQL Server
        $resultado = Concurrency::run([
            fn() => $cobrar(1),
            fn() => $cobrar(2),
        ]);

        return $this->cobros()->with('resultadoConcurrencia', $resultado);
    }

    private function metodoDeSuscripcion(int $idSuscripcion): ?int
    {
        $idUsuario = DB::table('suscripcion')->where('id_suscripcion', $idSuscripcion)->value('id_usuario');
        if (! $idUsuario) {
            return null;
        }

        foreach (DB::select('EXEC sp_listar_metodos_cliente ?', [$idUsuario]) as $m) {
            if ($m->estado === 'Activo') {
                return (int) $m->id_metodo;
            }
        }

        return null;
    }
}
