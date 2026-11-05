<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ControllerProgreso;
use App\Http\Controllers\ReproductorController;
use App\Http\Controllers\ContenidoController;

//imports solo para prubas

use Illuminate\Http\Request;
use App\Models\Contenido;
use App\Models\Temporada;
use App\Models\Episodio;

// Inicio
Route::get('/', function () {
    return view('modules.index');
});

// Prueba
Route::get('/lol', function () {
    return view('modules.series.show');
});

Route::get('/reproductor', function () {
    return view('reproductor');
})->name('reproductor.index');

Route::get('/reproductor/{contenidoId}', [ReproductorController::class, 'mostrar'])
    ->name('reproductor.mostrar');

Route::get('/reproductor/{contenidoId}/episodio/{episodioId}', [ReproductorController::class, 'mostrarEpisodio'])
    ->name('reproductor.episodio');

Route::post('/reproductor/fuentes', [ReproductorController::class, 'obtenerFuentes'])
    ->name('reproductor.fuentes');

Route::post('/reproductor/fuentes/guardar', [ReproductorController::class, 'guardarFuente'])
    ->name('reproductor.fuentes.guardar');

Route::post('/reproductor/fuentes/{id}/verificar', [ReproductorController::class, 'verificarFuente'])
    ->name('reproductor.fuentes.verificar');

Route::delete('/reproductor/fuentes/{id}', [ReproductorController::class, 'eliminarFuente'])
    ->name('reproductor.fuentes.eliminar');

Route::get('/reproductor/consultar-pelicula/{id}', [ReproductorController::class, 'consultarPelicula'])
    ->name('reproductor.consultar.pelicula');

Route::post('/progreso', [ControllerProgreso::class, 'guardar'])
    ->name('progreso.guardar');

Route::get('/progreso/{contenidoId}', [ControllerProgreso::class, 'obtener'])
    ->name('progreso.obtener');

Route::get('/continuar-viendo', [ControllerProgreso::class, 'continuar'])
    ->name('progreso.continuar');

Route::get('/anime/buscar', [ContenidoController::class, 'buscarAnime']);

Route::post('/anime/importar/{id}', [ContenidoController::class, 'importarAnime']);

Route::get('/peliculas/buscar', [ContenidoController::class, 'buscarPeliculas']);

Route::get('/series/buscar', [ContenidoController::class, 'buscarSeries']);

Route::post('/contenido/importar/{tipo}/{id}', [ContenidoController::class, 'importarContenido']);

Route::get('/tvmaze/series/buscar', [ContenidoController::class, 'buscarSeriesTVmaze']);

Route::post('/tvmaze/importar/{contenidoId}/{tvmazeId}', [ContenidoController::class, 'importarTemporadasYEpisodios']);

Route::get('/catalogo', [ContenidoController::class, 'catalogo'])
    ->name('catalogo');

Route::get('/catalogo/buscar', [ContenidoController::class, 'buscar'])
    ->name('catalogo.buscar');

Route::get('/catalogo/{id}', [ContenidoController::class, 'mostrar'])
    ->name('catalogo.mostrar');

//Este desastre es para probar el catalogo, no es parte del proyecto final, solo es para pruebas

Route::get('/prueba-catalogo', function (Request $request) {

    $consulta = Contenido::query();

    if ($request->tipo) {
        $consulta->where('tipo', $request->tipo);
    }

    $contenidos = $consulta->orderBy('titulo')->get();

    if ($request->q) {
        $contenidos = $contenidos->filter(function ($contenido) use ($request) {
            return stripos($contenido->titulo, $request->q) !== false;
        })->values();
    }

    $contenidoSeleccionado = null;
    $temporadas = collect();
    $episodiosPorTemporada = [];

    if ($request->contenido_id) {

        $contenidoSeleccionado = Contenido::find($request->contenido_id);

        if ($contenidoSeleccionado) {

            $temporadas = Temporada::where(
                'contenido_id',
                $contenidoSeleccionado->_id
            )->orderBy('numero')->get();

            foreach ($temporadas as $temporada) {

                $episodiosPorTemporada[(string) $temporada->_id] = Episodio::where(
                    'temporada_id',
                    $temporada->_id
                )->orderBy('numero')->get();
            }
        }
    }

    return view('prueba-catalogo', compact(
        'contenidos',
        'contenidoSeleccionado',
        'temporadas',
        'episodiosPorTemporada'
    ));
});
