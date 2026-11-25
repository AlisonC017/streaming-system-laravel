<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function registrar(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'correo' => 'required|email|max:100',
            'password' => 'required|string|min:8',
            'pais' => 'required|string|max:50',
            'fecha_nacimiento' => 'required|date',
        ]);

        try {
            DB::select(
                'SET NOCOUNT ON;
                DECLARE @id INT;
                EXEC sp_insert_cliente ?,?,?,?,?,?, @id OUTPUT;
                SELECT @id AS id;',
                [
                    $data['nombre'],
                    $data['apellidos'],
                    $data['correo'],
                    Hash::make($data['password']),
                    $data['pais'],
                    $data['fecha_nacimiento'],
                ]
            );
        } catch (QueryException $e) {
            return back()->withInput($request->except('password'))
                ->with('error', $this->mensajeSql($e));
        }

        return redirect()->route('login')->with('exito', 'Cuenta Creada!');
    }

    private function mensajeSql(QueryException $e): string
    {
        if (preg_match('/\[SQL Server\](.*?)\s*\(Connection:/s', $e->getMessage(), $m)) {
            return trim($m[1]);
        }
        return 'No se pudo completar la operación.';
    }

    //  FUNCIONES DE LOGIN

    public function login(Request $request)
    {
        $data = $request->validate([
            'correo' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $ok = Auth::attempt([
                'correo'  => $data['correo'],
                'password' => $data['password'],
                'estado' => 'Activo',
            ]);
        } catch (\RuntimeException $e) {
            $ok = false;
        }

        if (! $ok) {
            return back()->withInput($request->only('correo'))
                ->with('error', 'Correo o contraseña incorrectos');
        }

        $request->session()->regenerate();

        $nivel = DB::table('administrador')
            ->where('id_usuario', Auth::id())
            ->value('nivel_acceso');

        if ($nivel) {
            session(['nivel' => $nivel]);
            return redirect()->route('admin');
        }

        return redirect()->intended(route('inicio'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio');
    }
}
