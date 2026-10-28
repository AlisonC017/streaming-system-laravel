<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('modules.index');
});

Route::get('/lol', function () {
    return view('modules.series.show');
});
