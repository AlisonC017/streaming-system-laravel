{{-- Vista base del detalle: Jime la reemplaza con el diseño final.
Variables que recibe: $contenido, $temporadas y $episodiosPorTemporada
(solo traen temporadas y episodios con fuentes reproducibles) --}}
@extends('app')

@section('contenido')

    <section class="section" aria-labelledby="titulo-contenido">
        <div class="wrap">

            <p><a href="{{ route('catalogo') }}">← Volver al catálogo</a></p>

            <div class="row g-4">

                @if($contenido->imagen)
                    <div class="col-12 col-md-3">
                        <img src="{{ $contenido->imagen }}" alt="Póster de {{ $contenido->titulo }}" class="img-fluid rounded">
                    </div>
                @endif

                <div class="col">
                    <h1 id="titulo-contenido">{{ $contenido->titulo }}</h1>

                    <p>
                        <span class="chip">{{ $contenido->tipo }}</span>

                        @if(is_array($contenido->generos))
                            @foreach($contenido->generos as $genero)
                                <a class="chip" href="{{ route('catalogo.genero', $genero) }}">{{ $genero }}</a>
                            @endforeach
                        @endif

                        @if($contenido->puntuacion)
                            <span class="chip chip--gold">{{ $contenido->puntuacion }}</span>
                        @endif
                    </p>

                    <p>{{ $contenido->descripcion }}</p>

                    @if($contenido->tipo == 'pelicula')

                        <a class="btn-cta btn-primary" href="{{ route('reproductor.mostrar', $contenido->_id) }}">
                            Reproducir
                        </a>

                    @endif
                </div>
            </div>

            @if($contenido->tipo != 'pelicula')

                <h2 class="mt-5">Temporadas y episodios</h2>

                @foreach($temporadas as $temporada)

                    <h3 class="h5 mt-4">Temporada {{ $temporada->numero }}</h3>

                    <ul class="list-unstyled">
                        @foreach($episodiosPorTemporada[(string) $temporada->_id] as $episodio)
                            <li class="mb-2">
                                Episodio {{ $episodio->numero }}: {{ $episodio->titulo }}
                                <a class="btn-cta btn-ghost ms-2"
                                    href="{{ route('reproductor.episodio', ['contenidoId' => $contenido->_id, 'episodioId' => $episodio->_id]) }}">
                                    Reproducir
                                </a>
                            </li>
                        @endforeach
                    </ul>

                @endforeach

            @endif

        </div>
    </section>

@endsection