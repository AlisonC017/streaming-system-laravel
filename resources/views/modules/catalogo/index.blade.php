{{-- Inicio + catalogo en una sola vista.
     Siempre recibe: $contenidos (paginado). Opcionales: $tipo, $genero,
     y solo en la primera pagina del inicio: $destacado, $peliculas, $series, $anime --}}
@extends('app')
@section('titulo', 'Stream Line · Explora películas, series y anime')

@section('contenido')

@php
    $iconoPlay = '<svg class="ico" viewBox="0 0 24 24"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>';
    $filas = [
        ['titulo' => 'Películas', 'items' => $peliculas ?? collect(), 'enlace' => route('catalogo.peliculas')],
        ['titulo' => 'Series',    'items' => $series ?? collect(),    'enlace' => route('catalogo.series')],
        ['titulo' => 'Anime',     'items' => $anime ?? collect(),     'enlace' => route('catalogo.anime')],
    ];
@endphp

{{-- ===== 1. Destacado (solo en el inicio) o encabezado con buscador (en filtros) ===== --}}
@if (isset($destacado) && $destacado)
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
                    <a class="btn btn-ghost btn-lg" href="#catalogo">Más títulos</a>
                </div>
            </div>
        </div>
    </section>
@else
    <section class="catalog-hero">
        <div class="wrap">
            <span class="eyebrow">Tu próximo maratón comienza aquí</span>
            <h1>Explora un universo de <span class="accent">historias</span></h1>
            <p>Películas, series y anime en un solo lugar.</p>
        </div>
    </section>
@endif

{{-- ===== 2. Filas horizontales (solo en el inicio) ===== --}}
@if (isset($destacado))
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
@endif

{{-- ===== 3. Catalogo completo: buscador, filtros, cuadricula y paginacion ===== --}}
<section class="wrap catalog-section" id="catalogo">
    <div class="section-head">
        <div>
            <p class="eyebrow">Catálogo</p>
            <h2>
                @isset($tipo)
                    {{ ucfirst($tipo) }}s
                @elseif(isset($genero))
                    {{ $genero }}
                @else
                    Todos los títulos
                @endisset
            </h2>
        </div>
        <span>{{ $contenidos->total() }} resultados</span>
    </div>

    <form class="search-big" action="{{ route('catalogo.buscar') }}" method="GET" role="search">
        <input name="nombre" value="{{ request('nombre') }}" placeholder="Buscar por título...">
        <select name="tipo" aria-label="Tipo">
            <option value="">Todos</option>
            <option value="pelicula" @selected(request('tipo') === 'pelicula')>Películas</option>
            <option value="serie" @selected(request('tipo') === 'serie')>Series</option>
            <option value="anime" @selected(request('tipo') === 'anime')>Anime</option>
        </select>
        <button class="btn btn-primary">Buscar</button>
    </form>

    <nav class="chips">
        <a href="{{ route('catalogo') }}">Todos</a>
        <a href="{{ route('catalogo.peliculas') }}">Películas</a>
        <a href="{{ route('catalogo.series') }}">Series</a>
        <a href="{{ route('catalogo.anime') }}">Anime</a>
    </nav>

    <div class="content-grid">
        @forelse($contenidos as $contenido)
            <a class="content-card" href="{{ route('catalogo.mostrar', $contenido->_id) }}">
                <div class="content-poster">
                    @if ($contenido->imagen)
                        <img src="{{ $contenido->imagen }}" alt="{{ $contenido->titulo }}" loading="lazy">
                    @else
                        <div class="poster-placeholder">{{ $contenido->titulo }}</div>
                    @endif
                    <span class="type-pill">{{ ucfirst($contenido->tipo ?? 'Contenido') }}</span>
                    <span class="poster-play">▶</span>
                </div>
                <div class="content-card-info">
                    <h3>{{ $contenido->titulo }}</h3>
                    <p>{{ is_array($contenido->generos ?? null) ? implode(' · ', $contenido->generos) : 'Disponible para reproducir' }}</p>
                </div>
            </a>
        @empty
            <div class="empty-state">No hay títulos disponibles.</div>
        @endforelse
    </div>

    <div class="pagination-wrap">{{ $contenidos->links('pagination::bootstrap-5') }}</div>
</section>

@endsection