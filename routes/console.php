<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Actualiza el catalogo y sincroniza fuentes
Schedule::command('catalogo:ciclo-diario')
    ->dailyAt('00:00')
    ->timezone('America/Costa_Rica')
    ->withoutOverlapping(600)
    ->appendOutputTo(storage_path('logs/ciclo-diario.log'));
