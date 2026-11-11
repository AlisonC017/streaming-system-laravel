<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ControllerProgreso;
use App\Http\Controllers\ReproductorController;
use App\Http\Controllers\ContenidoController;


// Inicio
//Route::get('/', [ContenidoController::class, 'obtenerTodos'])->name('inicio');
// Solo para ver el diseño de la portada (borrar después)
Route::get('/vista-inicio', function () {
    $falso = fn ($i, $tipo, $generos) => (object) [
        '_id'           => (string) $i,
        'titulo'        => ucfirst($tipo) . ' de ejemplo ' . $i,
        'imagen'        => null,
        'tipo'          => $tipo,
        'generos'       => $generos,
        'descripcion'   => 'Una ciudad que nunca duerme guarda un secreto que podría cambiarlo todo.',
        'fecha_estreno' => now(),
    ];

    return view('modules.inicio', [
        'destacado' => $falso(0, 'pelicula', ['Drama', 'Misterio']),
        'peliculas' => collect(range(1, 10))->map(fn ($i) => $falso($i, 'pelicula', ['Acción', 'Terror'])),
        'series'    => collect(range(11, 20))->map(fn ($i) => $falso($i, 'serie', ['Drama', 'Comedia'])),
        'anime'     => collect(range(21, 30))->map(fn ($i) => $falso($i, 'anime', ['Aventura'])),
    ]);
});
Route::get('/', [ContenidoController::class, 'obtenerTodos'])
    ->name('inicio');

// Prueba
Route::get('/lol', function () {
    return view('modules.series.show');
});

//Rutas de reproduccion

Route::get('/reproductor', function () {
    return view('reproductor');
})->name('reproductor.index');

Route::get('/reproductor/{contenidoId}', [ReproductorController::class, 'mostrar'])
    ->name('reproductor.mostrar');

Route::get('/reproductor/{contenidoId}/episodio/{episodioId}', [ReproductorController::class, 'mostrarEpisodio'])
    ->name('reproductor.episodio');

Route::post('/reproductor/fuentes', [ReproductorController::class, 'obtenerFuentes'])
    ->name('reproductor.fuentes');

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


    // Rutas Jimena
Route::get('/login', function () { return view('modules.auth.login'); })->name('login');
Route::get('/registro', function () { return view('modules.auth.register'); })->name('register');
