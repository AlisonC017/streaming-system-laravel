<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Reproductor\Proveedores\VidSrcProvider;
use App\Services\Reproductor\ProveedorReproduccionService;
use App\Services\Reproductor\Proveedores\YouTubeProveedor;
use App\Services\Reproductor\Proveedores\SuperEmbedProvider;
use App\Services\Reproductor\Proveedores\CineSrcProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            [
                VidSrcProvider::class,
                YouTubeProveedor::class,
                SuperEmbedProvider::class,
                CineSrcProvider::class
            ],
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
