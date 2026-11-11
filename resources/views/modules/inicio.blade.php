{{-- Portada. Recibe: $destacado (un Contenido o null), $peliculas, $series y $anime (colecciones) --}}
@extends('app')
@section('titulo', 'Stream Line · Películas, series y anime')

@section('contenido')

@php
    $destacado = $destacado ?? null;
    $filas = [
        ['titulo' => 'Películas', 'items' => $peliculas ?? collect(), 'enlace' => route('catalogo.peliculas')],
        ['titulo' => 'Series',    'items' => $series ?? collect(),    'enlace' => route('catalogo.series')],
        ['titulo' => 'Anime',     'items' => $anime ?? collect(),     'enlace' => route('catalogo.anime')],
    ];
    $iconoPlay = '<svg class="ico" viewBox="0 0 24 24"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>';
@endphp

{{-- ===== Destacado ===== --}}
@if ($destacado)
    @php
        $generosDest = is_array($destacado->generos ?? null) ? $destacado->generos : [];
        $anioDest = $destacado->fecha_estreno?->format('Y');
    @endphp
    <section class="feat" aria-labelledby="destacado-titulo">
        <div class="feat-bg">
            @if ($destacado->imagen)
                <img src="{{ $destacado->imagen }}" alt="">
            @endif
        </div>
        <div class="wrap">
            <div class="feat-body">
                <span class="tag">Destacado</span>
                <h1 id="destacado-titulo">{{ $destacado->titulo }}</h1>
                <div class="meta">
                    <span class="chip">HD</span>
                    @if ($anioDest)<span>{{ $anioDest }}</span>@endif
                    @if ($generosDest)<span>{{ implode(' · ', array_slice($generosDest, 0, 3)) }}</span>@endif
                </div>
                <p class="desc">{{ \Illuminate\Support\Str::limit($destacado->descripcion, 200) }}</p>
                <div class="actions">
                    <a class="btn btn-primary btn-lg" href="{{ route('catalogo.mostrar', $destacado->_id) }}">{!! $iconoPlay !!} Reproducir</a>
                    <a class="btn btn-ghost btn-lg" href="{{ route('catalogo') }}">Más títulos</a>
                </div>
            </div>
        </div>
    </section>
@endif

{{-- ===== Filas ===== --}}
<div class="wrap">
    @foreach ($filas as $fila)
        @if ($fila['items']->count())
            <section class="shelf">
                <div class="shelf-head">
                    <h2>{{ $fila['titulo'] }}</h2>
                    <a href="{{ $fila['enlace'] }}">Ver todo ›</a>
                </div>

                <div class="track-box">
                    <button class="arrow prev" type="button" aria-label="Anterior"
                        onclick="this.parentElement.querySelector('.track').scrollBy({left:-600,behavior:'smooth'})">‹</button>

                    <div class="track">
                        @foreach ($fila['items'] as $item)
                            @php($gens = is_array($item->generos ?? null) ? $item->generos : [])
                            <a class="poster-card" href="{{ route('catalogo.mostrar', $item->_id) }}">
                                <div class="poster" style="background:linear-gradient(160deg,#6d28d9,#1a0f2e 80%)">
                                    @if ($item->imagen)
                                        <img src="{{ $item->imagen }}" alt="Póster de {{ $item->titulo }}" loading="lazy">
                                    @else
                                        <span class="poster-title">{{ $item->titulo }}</span>
                                    @endif
                                    <span class="poster-play"><i>{!! $iconoPlay !!}</i></span>
                                </div>
                                <div class="poster-info">
                                    <h3>{{ $item->titulo }}</h3>
                                    <p>{{ $gens ? implode(' · ', array_slice($gens, 0, 2)) : ucfirst($item->tipo ?? '') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <button class="arrow next" type="button" aria-label="Siguiente"
                        onclick="this.parentElement.querySelector('.track').scrollBy({left:600,behavior:'smooth'})">›</button>
                </div>
            </section>
        @endif
    @endforeach
</div>

@endsection