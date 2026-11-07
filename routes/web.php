<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ControllerProgreso;
use App\Http\Controllers\ReproductorController;
use App\Http\Controllers\ContenidoController;
use Illuminate\Support\Facades\Schedule;

// Inicio
Route::get('/', function () {
    return view('modules.index');
});

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

// prueba de catalogo
Route::get('/prueba-catalogo', [ContenidoController::class, 'pruebaCatalogo']);
