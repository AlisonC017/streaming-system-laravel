<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ErroresSql;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Suscripcion del cliente (rutas con middleware auth + solo.cliente) */
class SuscripcionController extends Controller
{
    use ErroresSql;

    /** Suscripcion vigente (o la ultima cancelada) del cliente, o null */
    public static function delCliente(int $idUsuario): ?object
    {
        return DB::select('EXEC sp_obtener_suscripcion_cliente ?', [$idUsuario])[0] ?? null;
    }

    public function mia()
    {
        try {
            $suscripcion = self::delCliente(Auth::id());
        } catch (\Throwable $e) {
            $suscripcion = null;
            session()->now('error', $this->mensajeSql($e));
        }

        return view('modules.suscripcion.mi-suscripcion', [
            'suscripcion' => $suscripcion,
            'planes' => PlanController::listar(),
        ]);
    }

    public function checkout(int $idPlan)
    {
        $plan = DB::select('EXEC sp_obtener_plan_por_id ?', [$idPlan])[0] ?? null;
        abort_unless($plan, 404);

        $metodos = array_values(array_filter(
            MetodoPagoController::delCliente(Auth::id()),
            fn($m) => $m->estado === 'Activo'
        ));

        return view('modules.suscripcion.checkout', [
            'plan' => $plan,
            'suscripcion' => self::delCliente(Auth::id()),
            'metodos' => $metodos,
        ]);
    }

    /**
     * Confirmar plan:
     *  - sin suscripcion vigente  -> sp_insert_suscripcion + sp_procesar_cobro_suscripcion
     *  - mismo plan Pendiente/Suspendida -> solo sp_procesar_cobro_suscripcion (pagar)
     *  - otro plan -> sp_update_suscripcion (y cobra si no estaba Activa)
     */
    public function confirmar(Request $request)
    {
        $data = $request->validate([
            'id_plan' => ['required', 'integer'],
            'id_metodo' => ['required'],
            'numero_tarjeta' => ['required_if:id_metodo,nueva', 'nullable', 'regex:/^[0-9 -]{13,23}$/'],
            'fecha_vencimiento' => ['required_if:id_metodo,nueva', 'nullable', 'date_format:Y-m'],
        ], [
            'numero_tarjeta.required_if' => 'Escribe el número de la tarjeta nueva.',
            'numero_tarjeta.regex' => 'El número de tarjeta debe tener de 13 a 19 dígitos.',
            'fecha_vencimiento.required_if' => 'Indica el vencimiento de la tarjeta nueva.',
        ]);

        $idUsuario = Auth::id();
        $idPlan = (int) $data['id_plan'];

        try {
            // 1. tarjeta: nueva (se encripta en el SP) o una guardada que sea del cliente
            if ($data['id_metodo'] === 'nueva') {
                $idMetodo = MetodoPagoController::registrar($idUsuario, $data['numero_tarjeta'], $data['fecha_vencimiento']);
            } else {
                $idMetodo = (int) $data['id_metodo'];
                $esSuya = collect(MetodoPagoController::delCliente($idUsuario))
                    ->contains(fn($m) => $m->id_metodo == $idMetodo && $m->estado === 'Activo');
                if (! $esSuya) {
                    return back()->withInput()->with('error', 'Elige una de tus tarjetas activas.');
                }
            }

            // 2. suscripcion
            $actual = self::delCliente($idUsuario);
            $vigente = $actual && $actual->estado !== 'Cancelada';

            if (! $vigente) {
                // la fecha de hoy la pone SQL Server (si se manda desde PHP puede venir en UTC
                // y quedar un dia adelantada, y entonces el cobro dice "esta al dia")
                $fila = DB::select(
                    'SET NOCOUNT ON;
                     DECLARE @id INT;
                     DECLARE @hoy DATE = CAST(SYSDATETIME() AS DATE);
                     EXEC sp_insert_suscripcion ?, ?, @hoy, @hoy, ?, @id OUTPUT;
                     SELECT @id AS id;',
                    [$idUsuario, $idPlan, 'Pendiente']
                );
                $idSuscripcion = (int) $fila[0]->id;
                $cobrar = true;
            } else {
                $idSuscripcion = $actual->id_suscripcion;
                if ($actual->id_plan == $idPlan && $actual->estado === 'Activa') {
                    return redirect()->route('suscripcion.mia')->with('error', 'Ya tienes ese plan activo.');
                }
                if ($actual->id_plan != $idPlan) {
                    DB::statement('EXEC sp_update_suscripcion ?, ?, ?, ?', [
                        $idSuscripcion, $idPlan, $actual->fecha_proximo_cobro, $actual->estado,
                    ]);
                }
                // si estaba Activa el nuevo precio se cobra en el proximo ciclo
                $cobrar = $actual->estado !== 'Activa';
            }

            // 3. cobro (el SP usa UPDLOCK para que no se cobre doble)
            if ($cobrar) {
                DB::statement('EXEC sp_procesar_cobro_suscripcion ?, ?', [$idSuscripcion, $idMetodo]);
            }
        } catch (\Throwable $e) {
            return back()->withInput($request->except('numero_tarjeta'))->with('error', $this->mensajeSql($e));
        }

        $estado = DB::table('suscripcion')->where('id_suscripcion', $idSuscripcion)->value('estado');

        if ($estado === 'Suspendida') {
            return redirect()->route('suscripcion.mia')
                ->with('error', 'No se pudo cobrar la tarjeta. Revisa tu método de pago.');
        }

        return redirect()->route('suscripcion.mia')->with('exito', $cobrar
            ? '¡Pago realizado! Ya puedes ver todo el catálogo.'
            : 'Cambiaste de plan. El nuevo precio se cobra en tu próximo cobro.');
    }

    public function cancelar()
    {
        // siempre la del cliente en sesion, nunca un id que venga del formulario
        $actual = self::delCliente(Auth::id());

        if (! $actual || $actual->estado === 'Cancelada') {
            return back()->with('error', 'No tienes una suscripción vigente.');
        }

        try {
            // el SP pone la fecha de cancelacion y el trigger lo registra en auditoria
            DB::statement('EXEC sp_update_suscripcion ?, ?, ?, ?', [
                $actual->id_suscripcion, $actual->id_plan, $actual->fecha_proximo_cobro, 'Cancelada',
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', $this->mensajeSql($e));
        }

        return redirect()->route('suscripcion.mia')->with('exito', 'Tu suscripción fue cancelada.');
    }
}