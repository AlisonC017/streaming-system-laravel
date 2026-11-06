<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Prueba catalogo MongoDB</title>
    <style>
        body {
            font-family: Arial;
            background: #f1f1f1;
            padding: 25px;
        }

        .contenedor {
            max-width: 1100px;
            margin: auto;
        }

        .contenido,
        .temporada {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        img {
            width: 120px;
        }

        input,
        select,
        button {
            padding: 10px;
            margin: 5px;
        }

        a {
            color: blue;
        }
    </style>
</head>

<body>

    <div class="contenedor">

        <h1>Catalogo de contenido</h1>

        <form method="GET" action="{{ url('/prueba-catalogo') }}">

            <input type="text" name="q" placeholder="Buscar contenido..." value="{{ request('q') }}">

            <select name="tipo">
                <option value="">Todos</option>
                <option value="anime" {{ request('tipo') == 'anime' ? 'selected' : '' }}>Anime</option>
                <option value="pelicula" {{ request('tipo') == 'pelicula' ? 'selected' : '' }}>Peliculas</option>
                <option value="serie" {{ request('tipo') == 'serie' ? 'selected' : '' }}>Series</option>
            </select>

            <button type="submit">Buscar</button>

            <a href="{{ url('/prueba-catalogo') }}">Limpiar</a>

        </form>

        <hr>

        <h2>Resultados: {{ $contenidos->count() }}</h2>

        @forelse($contenidos as $contenido)

            <div class="contenido">

                @if($contenido->imagen)
                    <img src="{{ $contenido->imagen }}" alt="Portada">
                @endif

                <h3>{{ $contenido->titulo }}</h3>

                <p>ID: {{ $contenido->_id }}</p>
                <p>Tipo: {{ $contenido->tipo }}</p>
                <p>Proveedor: {{ $contenido->proveedor_catalogo }}</p>
                <p>Proveedor ID: {{ $contenido->proveedor_id }}</p>
                <p>Estado: {{ $contenido->estado }}</p>
                <p>Puntuacion: {{ $contenido->puntuacion }}</p>

                <a href="{{ url('/prueba-catalogo') }}?contenido_id={{ $contenido->_id }}#detalles">
                    Seleccionar contenido
                </a>

                @if($contenido->tipo == 'pelicula')
                    <br><br>
                    <a href="{{ route('reproductor.mostrar', $contenido->_id) }}">
                        Probar reproductor
                    </a>
                @endif

            </div>

        @empty

            <p>No hay contenido registrado.</p>

        @endforelse

        @if($contenidoSeleccionado)

            <div id="detalles">

                <hr>

                <h2>Contenido seleccionado: {{ $contenidoSeleccionado->titulo }}</h2>

                <p>Tipo: {{ $contenidoSeleccionado->tipo }}</p>

                <p>{{ $contenidoSeleccionado->descripcion }}</p>

                @if($contenidoSeleccionado->tipo == 'pelicula')

                    <a href="{{ route('reproductor.mostrar', $contenidoSeleccionado->_id) }}">
                        Probar reproduccion de pelicula
                    </a>

                @else

                    <h3>Temporadas y episodios</h3>

                    @forelse($temporadas as $temporada)

                        <div class="temporada">

                            <h3>Temporada {{ $temporada->numero }}</h3>

                            @forelse($episodiosPorTemporada[(string) $temporada->_id] ?? [] as $episodio)

                                <p>
                                    Episodio {{ $episodio->numero }}:
                                    {{ $episodio->titulo }}
                                </p>

                                <a href="{{ route('reproductor.episodio', [
                                    'contenidoId' => $contenidoSeleccionado->_id,
                                    'episodioId' => $episodio->_id
                                ]) }}">
                                    Reproducir episodio
                                </a>

                                <hr>

                            @empty

                                <p>No hay episodios registrados.</p>

                            @endforelse

                        </div>

                    @empty

                        <p>No hay temporadas registradas.</p>

                    @endforelse

                @endif

            </div>

        @endif

    </div>

</body>

</html>