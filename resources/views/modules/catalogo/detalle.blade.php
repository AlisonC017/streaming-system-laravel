{{-- Detalle. Recibe: $contenido, $temporadas y $episodiosPorTemporada
     (solo temporadas y episodios con fuentes reproducibles) --}}
@extends('app')
@section('titulo', ($contenido->titulo ?? 'Detalle') . ' · Stream Line')

@section('contenido')

    @php($generos = is_array($contenido->generos ?? null) ? $contenido->generos : [])

    <section class="detail-hero" aria-labelledby="titulo-contenido">
        @if ($contenido->imagen)
            <div class="detail-backdrop" style="background-image:url('{{ $contenido->imagen }}')"></div>
        @endif
        <div class="detail-shade"></div>

        <div class="wrap detail-layout">
            <div class="detail-poster">
                @if ($contenido->imagen)
                    <img src="{{ $contenido->imagen }}" alt="Póster de {{ $contenido->titulo }}">
                @else
                    <div class="poster-placeholder">{{ $contenido->titulo }}</div>
                @endif
            </div>

            <div class="detail-copy">
                <a class="back-link" href="{{ route('catalogo') }}">← Volver al catálogo</a>
                <h1 id="titulo-contenido">{{ $contenido->titulo }}</h1>

                <div class="detail-meta">
                    <span class="chip">{{ ucfirst($contenido->tipo ?? 'Contenido') }}</span>
                    @if ($contenido->puntuacion)
                        <span class="chip chip--gold">★ {{ $contenido->puntuacion }}</span>
                    @endif
                    @foreach ($generos as $genero)
                        <a class="chip" href="{{ route('catalogo.genero', $genero) }}">{{ $genero }}</a>
                    @endforeach
                </div>

                <p class="detail-description">{{ $contenido->descripcion }}</p>

                @if (($contenido->tipo ?? '') === 'pelicula')
                    <a class="btn btn-primary btn-lg" href="{{ route('reproductor.mostrar', $contenido->_id) }}">
                        ▶ Reproducir ahora
                    </a>
                @else
                    <a class="btn btn-primary btn-lg" href="#episodios">Ver episodios</a>
                @endif
            </div>
        </div>
    </section>

    @if (($contenido->tipo ?? '') !== 'pelicula')
        <section class="wrap episode-section" id="episodios">
            <h2>Temporadas y episodios</h2>

            @forelse ($temporadas as $temporada)
                <div class="season-block">
                    <h3>Temporada {{ $temporada->numero }}</h3>

                    <div class="episode-grid">
                        @foreach ($episodiosPorTemporada[(string) $temporada->_id] ?? [] as $episodio)
                            <article class="episode-card">
                                <strong class="episode-number">{{ $episodio->numero }}</strong>
                                <div class="episode-copy">
                                    <h4>{{ $episodio->titulo }}</h4>
                                    <p>Episodio {{ $episodio->numero }}</p>
                                </div>
                                <a class="btn btn-ghost btn-sm"
                                    href="{{ route('reproductor.episodio', ['contenidoId' => $contenido->_id, 'episodioId' => $episodio->_id]) }}">
                                    Reproducir
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="empty-state">No hay episodios reproducibles.</p>
            @endforelse
        </section>
    @endif

@endsection