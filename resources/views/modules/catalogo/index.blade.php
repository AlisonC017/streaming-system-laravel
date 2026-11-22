{{-- Inicio + catalogo en una sola vista.
     Siempre recibe: $contenidos (paginado). Opcionales: $tipo, $genero.
     Solo en el inicio (primera pagina, sin filtros): $destacado, $peliculas, $series, $anime
     y $masDestacados.

     TODO (companero): $masDestacados es una coleccion de Contenido (maximo 10, ya
     ordenada de mas a menos destacado). Mientras no exista, la fila muestra tarjetas vacias. --}}
@extends('layouts.app')
@section('titulo', 'Aetherix · Explora películas, series y anime')

@section('contenido')

    @php
        $iconoPlay =
            '<svg class="ico" viewBox="0 0 24 24"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>';
        $iconoBuscar =
            '<svg class="ico" viewBox="0 0 24 24"><path d="M10 2a8 8 0 0 1 6.32 12.9l5.39 5.39-1.42 1.42-5.39-5.39A8 8 0 1 1 10 2Zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z"/></svg>';

        // Inicio = sin filtros, sin busqueda y en la primera pagina
        $esInicio = !isset($tipo) && !isset($genero) && !request()->filled('nombre') && (int) request('page', 1) === 1;

        $masDestacados = $masDestacados ?? collect();

        $filas = [
            ['titulo' => 'Películas', 'items' => $peliculas ?? collect(), 'enlace' => route('catalogo.peliculas')],
            ['titulo' => 'Series', 'items' => $series ?? collect(), 'enlace' => route('catalogo.series')],
            ['titulo' => 'Anime', 'items' => $anime ?? collect(), 'enlace' => route('catalogo.anime')],
        ];
    @endphp

    {{-- ===== 1. Destacado (inicio) o encabezado ===== --}}
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
                        @if ($anioDest)
                            <span>{{ $anioDest }}</span>
                        @endif
                        @if ($generosDest)
                            <span>{{ implode(' · ', array_slice($generosDest, 0, 3)) }}</span>
                        @endif
                    </div>
                    <p class="desc">{{ \Illuminate\Support\Str::limit($destacado->descripcion, 200) }}</p>
                    <div class="actions">
                        <a class="btn btn-primary btn-lg"
                            href="{{ route('catalogo.mostrar', $destacado->_id) }}">{!! $iconoPlay !!} Reproducir</a>
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
    @if ($esInicio)
        <div class="wrap">

            {{-- ---- Lo más destacado (pendiente: lo llena $masDestacados) ---- --}}
            <section class="shelf shelf--top" aria-labelledby="top-titulo">
                <div class="shelf-head">
                    <div>
                        <span class="tag">Ranking</span>
                        <h2 id="top-titulo">Lo más destacado</h2>
                    </div>
                </div>

                <div class="track-box">
                    @if ($masDestacados->isNotEmpty())
                        <button class="arrow prev" type="button" aria-label="Anterior"
                            onclick="this.parentElement.querySelector('.track').scrollBy({left:-600,behavior:'smooth'})">‹</button>
                    @endif

                    <div class="track">
                        @forelse ($masDestacados as $item)
                            @php($gens = is_array($item->generos ?? null) ? $item->generos : [])
                            <a class="poster-card" href="{{ route('catalogo.mostrar', $item->_id) }}">
                                <div class="poster" style="background:linear-gradient(160deg,#6d28d9,#1a0f2e 80%)">
                                    @if ($item->imagen)
                                        <img src="{{ $item->imagen }}" alt="Póster de {{ $item->titulo }}" loading="lazy">
                                    @else
                                        <span class="poster-title">{{ $item->titulo }}</span>
                                    @endif
                                    <span class="poster-rank">{{ $loop->iteration }}</span>
                                    <span class="poster-play"><i>{!! $iconoPlay !!}</i></span>
                                </div>
                                <div class="poster-info">
                                    <h3>{{ $item->titulo }}</h3>
                                    <p>{{ $gens ? implode(' · ', array_slice($gens, 0, 2)) : ucfirst($item->tipo ?? '') }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            @for ($i = 1; $i <= 6; $i++)
                                <div class="poster-card is-skeleton" aria-hidden="true">
                                    <div class="poster">
                                        <span class="poster-rank">{{ $i }}</span>
                                    </div>
                                    <div class="poster-info">
                                        <h3>Próximamente</h3>
                                        <p>Título destacado</p>
                                    </div>
                                </div>
                            @endfor
                        @endforelse
                    </div>

                    @if ($masDestacados->isNotEmpty())
                        <button class="arrow next" type="button" aria-label="Siguiente"
                            onclick="this.parentElement.querySelector('.track').scrollBy({left:600,behavior:'smooth'})">›</button>
                    @endif
                </div>
            </section>

            {{-- ---- Películas, Series y Anime ---- --}}
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
                                                <img src="{{ $item->imagen }}" alt="Póster de {{ $item->titulo }}"
                                                    loading="lazy">
                                            @else
                                                <span class="poster-title">{{ $item->titulo }}</span>
                                            @endif
                                            <span class="poster-play"><i>{!! $iconoPlay !!}</i></span>
                                        </div>
                                        <div class="poster-info">
                                            <h3>{{ $item->titulo }}</h3>
                                            <p>{{ $gens ? implode(' · ', array_slice($gens, 0, 2)) : ucfirst($item->tipo ?? '') }}
                                            </p>
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

    {{-- ===== 3. Catalogo completo: un solo buscador, cuadricula y paginacion ===== --}}
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
            {!! $iconoBuscar !!}
            <input name="nombre" value="{{ request('nombre') }}" placeholder="Buscar por título..."
                aria-label="Buscar por título">
            <select name="tipo" aria-label="Tipo">
                <option value="">Todos</option>
                <option value="pelicula" @selected(request('tipo') === 'pelicula')>Películas</option>
                <option value="serie" @selected(request('tipo') === 'serie')>Series</option>
                <option value="anime" @selected(request('tipo') === 'anime')>Anime</option>
            </select>
            <button class="btn btn-primary">Buscar</button>
        </form>

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
                        <p>{{ is_array($contenido->generos ?? null) ? implode(' · ', $contenido->generos) : 'Disponible para reproducir' }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="empty-state">No hay títulos disponibles.</div>
            @endforelse
        </div>

        <div class="pagination-wrap">{{ $contenidos->links('pagination::bootstrap-5') }}</div>
    </section>

@endsection
