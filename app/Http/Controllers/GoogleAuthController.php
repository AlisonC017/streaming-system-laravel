<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    private const SESION_PENDIENTE = 'google_pendiente';

    public function redirigir()
    {
        return Socialite::driver('google')->redirect();
    }

    // Google devuelve al usuario aqui
    public function callback(Request $request)
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')
                ->with('error', 'No se pudo iniciar sesión con Google. Intenta de nuevo.');
        }

        $correo = $google->getEmail();

        // Solo se confia en correos que Google ya verifico
        if (!$correo || !($google->user['email_verified'] ?? false)) {
            return redirect()->route('login')
                ->with('error', 'Tu cuenta de Google no tiene un correo verificado.');
        }

        $usuario = DB::table('usuario')->where('correo', $correo)->first();

        if ($usuario) {
            if ($usuario->estado !== 'Activo') {
                return redirect()->route('login')->with('error', 'Tu cuenta está inactiva.');
            }

            return $this->iniciarSesion($request, $usuario->id_usuario, route('inicio'));
        }

        // Usuario nuevo: faltan el pais y la fecha de nacimiento que pide sp_insert_cliente
        $request->session()->put(self::SESION_PENDIENTE, [
            'nombre' => $google->user['given_name'] ?? $google->getName(),
            'apellidos' => $google->user['family_name'] ?? '',
            'correo' => $correo,
        ]);

        return redirect()->route('google.completar');
    }

    // Formulario para pedir los datos que Google no entrega
    public function formularioCompletar(Request $request)
    {
        $pendiente = $request->session()->get(self::SESION_PENDIENTE);

        if (!$pendiente) {
            return redirect()->route('login');
        }

        return view('modules.auth.google-completar', ['pendiente' => $pendiente]);
    }

    // Crea el cliente con los datos de Google mas el pais y la fecha de nacimiento
    public function completar(Request $request)
    {
        $pendiente = $request->session()->get(self::SESION_PENDIENTE);

        if (!$pendiente) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:80',
            'pais' => 'required|string|max:50',
            'fecha_nacimiento' => 'required|date|before:today',
        ]);

        try {
            // El correo sale de la sesion (lo verifico Google), no del formulario.
            // La contrasena es aleatoria: este usuario entra siempre con Google.
            $resultado = DB::select(
                'SET NOCOUNT ON;
                DECLARE @id INT;
                EXEC sp_insert_cliente ?,?,?,?,?,?, @id OUTPUT;
                SELECT @id AS id;',
                [
                    $data['nombre'],
                    $data['apellidos'],
                    $pendiente['correo'],
                    Hash::make(Str::random(40)),
                    $data['pais'],
                    $data['fecha_nacimiento'],
                ]
            );
        } catch (QueryException $e) {
            return back()->withInput()->with('error', $this->mensajeSql($e));
        }

        $request->session()->forget(self::SESION_PENDIENTE);

        // Cuenta nueva: se manda a los planes para que elija a que suscribirse
        return $this->iniciarSesion($request, $resultado[0]->id, route('planes'));
    }

    private function iniciarSesion(Request $request, $idUsuario, string $destinoPorDefecto)
    {
        Auth::loginUsingId($idUsuario);
        $request->session()->regenerate();

        $nivel = DB::table('administrador')->where('id_usuario', $idUsuario)->value('nivel_acceso');

        if ($nivel) {
            session(['nivel' => $nivel]);
            return redirect()->route('admin');
        }

        // Si venia de una pagina protegida (por ejemplo el pago) vuelve a ella
        return redirect()->intended($destinoPorDefecto);
    }

    private function mensajeSql(QueryException $e): string
    {
        if (preg_match('/\[SQL Server\](.*?)\s*\(Connection:/s', $e->getMessage(), $m)) {
            return trim($m[1]);
        }

        return 'No se pudo completar la operación.';
    }
}
