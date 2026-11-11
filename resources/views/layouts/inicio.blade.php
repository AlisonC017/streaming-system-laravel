{{-- Inicio. Recibe: $destacado (un contenido), $peliculas y $series (colecciones) --}}
@extends('app')
@section('titulo', 'Stream Line')

@section('contenido')

    @php
        $destacado = $destacado ?? null;
        $peliculas = $peliculas ?? collect();
        $series = $series ?? collect();
        $generosDest = is_array($destacado->generos ?? null) ? $destacado->generos : [];
    @endphp

    @if ($destacado)
        <section class="hero">
            @if ($destacado->imagen)
                <div class="hero__bg" style="background-image:url('{{ $destacado->imagen }}')"></div>
            @endif

            <div class="wrap">
                <div class="hero__copy">
                    <p class="eyebrow">Destacado</p>
                    <h1>{{ $destacado->titulo }}</h1>

                    <div class="hero__meta">
                        <span class="tag-hd">HD</span>
                        @if (!empty($destacado->anio))<span>{{ $destacado->anio }}</span>@endif
                        @if ($generosDest)<span>{{ implode(' · ', $generosDest) }}</span>@endif
                    </div>

                    <p class="hero__text">{{ \Illuminate\Support\Str::limit($destacado->descripcion, 180) }}</p>

                    <div class="hero__actions">
                        <a class="btn btn-primary btn-lg" href="{{ route('catalogo.mostrar', $destacado->_id) }}">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>
                            Reproducir
                        </a>
                        <a class="btn btn-ghost btn-lg" href="{{ route('catalogo') }}">Más títulos</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @foreach ([['Películas', $peliculas], ['Series', $series]] as [$titulo, $lista])
        @if ($lista->count())
            <section class="wrap rail-section">
                <div class="rail-head">
                    <h2>{{ $titulo }}</h2>
                    <a href="{{ route('catalogo') }}">Ver todo ›</a>
                </div>
                <div class="rail">
                    @foreach ($lista as $item)
                        @include('partials.tarjeta', ['item' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

@endsection
