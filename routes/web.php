<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ControllerProgreso;
use App\Http\Controllers\ReproductorController;
use App\Http\Controllers\ContenidoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\AuditoriaController;

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

// Planes: publico (se ve sin iniciar sesion)
Route::get('/planes', [PlanController::class, 'index'])->name('planes');

// Cuenta del cliente: requiere sesion
Route::middleware('auth')->group(function () {
    Route::get('/cuenta', [CuentaController::class, 'panel'])->name('cuenta.panel');
    Route::get('/cuenta/historial', [CuentaController::class, 'historial'])->name('cuenta.historial');
});

// Rutas del dashboard 
Route::view('/admin', 'modules.admin.dashboard')->name('admin');

Route::view('/admin/cobros',   'modules.admin.cobros')->name('admin.cobros');
Route::view('/admin/reportes', 'modules.admin.reportes')->name('admin.reportes');
Route::view('/admin/usuarios', 'modules.admin.usuarios')->name('admin.usuarios');
Route::get('/admin/auditoria', [AuditoriaController::class, 'index'])->name('admin.auditoria');

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



// Prueba
Route::get('/demo/salir', function () {
    session()->forget('nivel');
    return redirect('/');
});

Route::get('/demo/auditoria', function () {
    session(['nivel' => 'Soporte']);
    return view('modules.admin.auditoria', ['registros' => []]);
});

// papas
//Prueba
Route::view('/admin', 'modules.admin.dashboard', [
    'suscripcionesActivas' => 0,
    'ingresosMes' => 0,
    'cobrosFallidos' => 0,
    'cancelaciones' => 0,
    'ultimosCobros' => [],
])->name('admin');

Route::view('/admin/planes',   'modules.admin.planes',   ['planes' => []])->name('admin.planes');
Route::view('/admin/cobros',   'modules.admin.cobros',   ['suscripciones' => [], 'cobros' => []])->name('admin.cobros');
Route::view('/admin/reportes', 'modules.admin.reportes', ['rendimiento' => []])->name('admin.reportes');
Route::view('/admin/usuarios', 'modules.admin.usuarios', ['clientes' => []])->name('admin.usuarios');
// Rutas demo integrante 2 (planes y suscripcion): datos falsos para ver las vistas sin backend
// ?estado=Activa|Pendiente|Suspendida|Cancelada  /  ?sin=1 para cliente sin suscripcion
Route::prefix('demo')->group(function () {
    $planes = fn() => [
        (object) ['id_plan' => 1, 'nombre_plan' => 'Basico',   'precio' => 6.99,  'calidad' => 'SD'],
        (object) ['id_plan' => 2, 'nombre_plan' => 'Estandar', 'precio' => 10.99, 'calidad' => 'HD'],
        (object) ['id_plan' => 4, 'nombre_plan' => 'Familiar', 'precio' => 12.99, 'calidad' => 'FullHD'],
        (object) ['id_plan' => 3, 'nombre_plan' => 'Premium',  'precio' => 15.99, 'calidad' => '4K'],
    ];
    $suscripcion = fn() => request('sin') ? null : (object) [
        'id_suscripcion' => 1,
        'id_plan' => 2,
        'nombre_plan' => 'Estandar',
        'precio' => 10.99,
        'calidad' => 'HD',
        'fecha_inicio' => '2026-09-06',
        'fecha_proximo_cobro' => '2026-12-04',
        'estado' => request('estado', 'Activa'),
        'fecha_cancelacion' => request('estado') === 'Cancelada' ? '2026-11-20' : null,
        'ultimos4' => '1111',
    ];
    $metodos = fn() => [
        (object) ['id_metodo' => 1, 'ultimos4' => '1111', 'fecha_vencimiento' => '2028-05-31', 'estado' => 'Activo'],
        (object) ['id_metodo' => 7, 'ultimos4' => '4242', 'fecha_vencimiento' => '2026-01-31', 'estado' => 'Inactivo'],
    ];

    Route::get('/planes', function () use ($planes, $suscripcion) {
        return view('modules.planes.index', ['planes' => $planes(), 'suscripcion' => request('con') ? $suscripcion() : null]);
    });
    Route::get('/suscripcion', function () use ($planes, $suscripcion) {
        session()->forget('nivel');
        return view('modules.suscripcion.mi-suscripcion', ['suscripcion' => $suscripcion(), 'planes' => $planes()]);
    });
    Route::get('/checkout/{id?}', function ($id = 3) use ($planes, $suscripcion, $metodos) {
        session()->forget('nivel');
        $plan = collect($planes())->firstWhere('id_plan', (int) $id) ?? $planes()[0];
        return view('modules.suscripcion.checkout', [
            'plan' => $plan,
            'suscripcion' => $suscripcion(),
            'metodos' => request('sin') ? [] : array_filter($metodos(), fn($m) => $m->estado === 'Activo'),
        ]);
    });
    Route::get('/metodos', function () use ($metodos) {
        session()->forget('nivel');
        return view('modules.pagos.metodos', ['metodos' => request('sin') ? [] : $metodos()]);
    });
});
