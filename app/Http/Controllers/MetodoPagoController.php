<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ErroresSql;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Metodos de pago del cliente (rutas con middleware auth + solo.cliente) */
class MetodoPagoController extends Controller
{
    use ErroresSql;

    /** Tarjetas del cliente, nunca el numero completo */
    public static function delCliente(int $idUsuario): array
    {
        return DB::select('EXEC sp_listar_metodos_cliente ?', [$idUsuario]);
    }

    public function index()
    {
        try {
            $metodos = self::delCliente(Auth::id());
        } catch (\Throwable $e) {
            $metodos = [];
            session()->now('error', $this->mensajeSql($e));
        }

        return view('modules.pagos.metodos', ['metodos' => $metodos]);
    }

    public function guardar(Request $request)
    {
        $data = $request->validate([
            'numero_tarjeta' => ['required', 'string', 'regex:/^[0-9 -]{13,23}$/'],
            'fecha_vencimiento' => ['required', 'date_format:Y-m'],
        ], [
            'numero_tarjeta.regex' => 'El número de tarjeta debe tener de 13 a 19 dígitos.',
        ]);

        try {
            self::registrar(Auth::id(), $data['numero_tarjeta'], $data['fecha_vencimiento']);
        } catch (\Throwable $e) {
            return back()->with('error', $this->mensajeSql($e));
        }

        return back()->with('exito', 'Tarjeta guardada.');
    }

    public function eliminar(int $id)
    {
        // solo puede borrar SUS tarjetas
        $esSuya = collect(self::delCliente(Auth::id()))->contains('id_metodo', $id);
        abort_unless($esSuya, 404);

        try {
            // el trigger tr_metodo_pago_borrado_logico la inactiva si ya tiene pagos
            DB::statement('EXEC sp_delete_metodo_pago ?', [$id]);
        } catch (\Throwable $e) {
            return back()->with('error', $this->mensajeSql($e));
        }

        return back()->with('exito', 'Tarjeta eliminada.');
    }

    /**
     * Registra una tarjeta con sp_registrar_tarjeta_encriptada (el SP la encripta con AES_256)
     * $mes viene del input type="month" (2028-05) y se guarda como ultimo dia del mes
     */
    public static function registrar(int $idUsuario, string $numero, string $mes): int
    {
        $vence = Carbon::createFromFormat('!Y-m', $mes)->endOfMonth()->toDateString();

        $fila = DB::select(
            'SET NOCOUNT ON;
             DECLARE @id INT;
             EXEC sp_registrar_tarjeta_encriptada ?, ?, ?, @id OUTPUT;
             SELECT @id AS id;',
            [$idUsuario, preg_replace('/\D/', '', $numero), $vence]
        );

        return (int) $fila[0]->id;
    }
}
