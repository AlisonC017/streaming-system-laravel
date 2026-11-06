<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Reproductor\Proveedores\ProveedorInterface;
use App\Services\Reproductor\Proveedores\VidSrcProvider;
use App\Services\Reproductor\Proveedores\AnimeDocProvider;
use App\Services\Reproductor\ProveedorReproduccionService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            [VidSrcProvider::class, AnimeDocProvider::class],
            'proveedores.reproduccion'
        );

        $this->app->when(ProveedorReproduccionService::class)
            ->needs('$proveedores')
            ->giveTagged('proveedores.reproduccion');
    }

    public function boot(): void
    {
        //
    }
}
