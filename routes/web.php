<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ControllerProgreso;
use App\Http\Controllers\ReproductorController;
use App\Http\Controllers\ContenidoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\GoogleAuthController;
use App\http\Controllers\AdministradorController;
use App\Http\Controllers\AdminClientesController;
use App\Http\Controllers\AdminPanelController;
// Inicio real 
Route::get('/', [ContenidoController::class, 'obtenerTodos'])
    ->name('inicio');

//Rutas de reproduccion

Route::get('/reproductor', function () {
    return view('reproductor');
})->name('reproductor.index');

Route::middleware(['auth', 'pago.activo'])->group(function () {
    Route::get('/reproductor/{contenidoId}', [ReproductorController::class, 'mostrar'])
        ->name('reproductor.mostrar');

    Route::get('/reproductor/{contenidoId}/episodio/{episodioId}', [ReproductorController::class, 'mostrarEpisodio'])
        ->name('reproductor.episodio');

    Route::post('/reproductor/fuentes', [ReproductorController::class, 'obtenerFuentes'])
        ->name('reproductor.fuentes');
});

// Administracion de fuentes: protegidas hasta que exista autenticacion
// Cuando haya login, cambiar 'solo.local' por ['auth', 'rol:admin']
Route::middleware('solo.local')->group(function () {

    Route::post('/reproductor/fuentes/guardar', [ReproductorController::class, 'guardarFuente'])
        ->name('reproductor.fuentes.guardar');

    Route::delete('/reproductor/fuentes/{id}', [ReproductorController::class, 'eliminarFuente'])
        ->name('reproductor.fuentes.eliminar');
});

//Rutas de progreso de reproduccion
Route::post('/progreso', [ControllerProgreso::class, 'guardar'])
    ->name('progreso.guardar');

Route::get('/progreso/{contenidoId}', [ControllerProgreso::class, 'obtener'])
    ->name('progreso.obtener');

Route::get('/continuar-viendo', [ControllerProgreso::class, 'continuar'])
    ->name('progreso.continuar');

// rutas de catalogo
Route::get('/catalogo', [ContenidoController::class, 'obtenerTodos'])
    ->name('catalogo');

Route::get('/catalogo/peliculas', [ContenidoController::class, 'obtenerPeliculas'])
    ->name('catalogo.peliculas');

Route::get('/catalogo/series', [ContenidoController::class, 'obtenerSeries'])
    ->name('catalogo.series');

Route::get('/catalogo/anime', [ContenidoController::class, 'obtenerAnime'])
    ->name('catalogo.anime');

Route::get('/catalogo/genero/{genero}', [ContenidoController::class, 'porGenero'])
    ->name('catalogo.genero');

Route::get('/catalogo/buscar', [ContenidoController::class, 'buscar'])
    ->name('catalogo.buscar');

Route::get('/catalogo/{id}', [ContenidoController::class, 'mostrar'])
    ->name('catalogo.mostrar');


// Rutas de login 
Route::view('/login', 'modules.auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::view('/registro', 'modules.auth.register')->name('register');
Route::post('/registro', [AuthController::class, 'registrar'])->name('register.post');

// Login con Google
Route::get('/auth/google', [GoogleAuthController::class, 'redirigir'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::get('/auth/google/completar', [GoogleAuthController::class, 'formularioCompletar'])->name('google.completar');
Route::post('/auth/google/completar', [GoogleAuthController::class, 'completar'])->name('google.completar.post');

// Planes: publico (se ve sin iniciar sesion)
Route::get('/planes', [PlanController::class, 'index'])->name('planes');

// Cuenta del cliente: requiere sesion
Route::middleware('auth')->group(function () {
    Route::get('/cuenta', [CuentaController::class, 'panel'])->name('cuenta.panel');
    Route::get('/cuenta/historial', [CuentaController::class, 'historial'])->name('cuenta.historial');
});

// Suscripcion y metodos de pago: solo clientes (un admin se manda a /admin)
Route::middleware(['auth', 'solo.cliente'])->group(function () {
    Route::get('/suscripcion', [SuscripcionController::class, 'mia'])->name('suscripcion.mia');
    Route::get('/suscripcion/checkout/{plan}', [SuscripcionController::class, 'checkout'])
        ->whereNumber('plan')->name('suscripcion.checkout');
    Route::post('/suscripcion/confirmar', [SuscripcionController::class, 'confirmar'])->name('suscripcion.confirmar');
    Route::post('/suscripcion/cancelar', [SuscripcionController::class, 'cancelar'])->name('suscripcion.cancelar');

    Route::get('/pagos/metodos', [MetodoPagoController::class, 'index'])->name('pagos.metodos');
    Route::post('/pagos/metodos', [MetodoPagoController::class, 'guardar'])->name('pagos.metodos.guardar');
    Route::delete('/pagos/metodos/{id}', [MetodoPagoController::class, 'eliminar'])
        ->whereNumber('id')->name('pagos.metodos.eliminar');
});

// Rutas del dashboard (solo administradores con sesion)
// Rutas del dashboard (solo administradores con sesion)
Route::middleware(['auth', 'solo.admin'])->group(function () {
    Route::get('/admin', [AdminPanelController::class, 'dashboard'])->name('admin');

    // Clientes
    Route::get('/admin/usuarios', [AdminPanelController::class, 'usuarios'])->name('admin.usuarios');
    Route::get('/admin/usuarios/{id}', [AdminPanelController::class, 'usuarioShow'])->whereNumber('id')->name('admin.usuarios.show');
    Route::get('/admin/usuarios/{id}/editar', [AdminPanelController::class, 'usuarioEdit'])->whereNumber('id')->name('admin.usuarios.edit');
    Route::put('/admin/usuarios/{id}', [AdminPanelController::class, 'usuarioUpdate'])->whereNumber('id')->name('admin.usuarios.update');
    Route::post('/admin/usuarios/{id}/cancelar', [AdminPanelController::class, 'cancelarCuenta'])->whereNumber('id')->name('admin.usuarios.cancelar');

    // Administradores
    Route::get('/admin/administradores', [AdminPanelController::class, 'administradores'])->name('admin.administradores');
    Route::get('/admin/administradores/{id}/editar', [AdminPanelController::class, 'administradorEdit'])->whereNumber('id')->name('admin.administradores.edit');
    Route::put('/admin/administradores/{id}', [AdminPanelController::class, 'administradorUpdate'])->whereNumber('id')->name('admin.administradores.update');

    // Planes
    Route::get('/admin/planes', [AdminPanelController::class, 'planes'])->name('admin.planes');
    Route::post('/admin/planes', [AdminPanelController::class, 'planStore'])->name('admin.planes.store');
    Route::get('/admin/planes/{id}/editar', [AdminPanelController::class, 'planEdit'])->whereNumber('id')->name('admin.planes.edit');
    Route::put('/admin/planes/{id}', [AdminPanelController::class, 'planUpdate'])->whereNumber('id')->name('admin.planes.update');

    // Cobros, reportes, auditoria
    Route::get('/admin/cobros', [AdminPanelController::class, 'cobros'])->name('admin.cobros');
    Route::get('/admin/reportes', [AdminPanelController::class, 'reportes'])->name('admin.reportes');
    Route::get('/admin/auditoria', [AuditoriaController::class, 'index'])->name('admin.auditoria');
});

// Rutas prueba logins
// Rutas demo de CLIENTE: sin nivel
Route::get('/demo/panel', function () {
    session()->forget('nivel');
    session(['nombre' => 'Ali']);
    return view('modules.cuenta-cliente.panel', [
        'usuario' => (object) ['nombre' => 'Ali'],
        'suscripcion' => request('con') ? (object) [
            'plan' => 'Premium',
            'precio' => 12.99,
            'proximo_cobro' => '2026-12-22',
            'estado' => 'Activa',
        ] : null,
        'planes' => [
            (object) ['id_plan' => 1, 'nombre_plan' => 'Básico', 'calidad' => 'HD', 'precio' => 7.99],
            (object) ['id_plan' => 2, 'nombre_plan' => 'Premium', 'calidad' => '4K', 'precio' => 12.99],
        ],
    ]);
});

Route::get('/demo/historial', function () {
    session()->forget('nivel');
    session(['nombre' => 'Ali']);
    return view('modules.cuenta-cliente.historial', ['pagos' => []]);
});

// Rutas demo de ADMIN: con nivel
Route::get('/demo/admin', function () {
    session(['nivel' => 'Total', 'nombre' => 'Ali']);
    return view('modules.admin.dashboard');
});

Route::get('/demo/salir', function () {
    session()->forget('nivel');
    return redirect('/');
});

Route::get('/demo/auditoria', function () {
    session(['nivel' => 'Soporte']);
    return view('modules.admin.auditoria', ['registros' => []]);
});
