{{-- Vista de prueba del catalogo --}}
@extends('app')

@section('contenido')

  <section class="section" aria-labelledby="catalogo-title">
    <div class="wrap">

      <div class="section-head">
        <div>
          <p class="eyebrow">Catálogo</p>
          <h2 id="catalogo-title">
            Explorar
            @isset($tipo) · {{ $tipo }} @endisset
            @isset($genero) · {{ $genero }} @endisset
          </h2>
        </div>
      </div>

      <form method="GET" action="{{ route('catalogo.buscar') }}" class="row g-2 mb-4">
        <div class="col-12 col-md-4">
          <input type="text" name="nombre" class="form-control" placeholder="Buscar por nombre..."
            value="{{ request('nombre') }}">
        </div>
        <div class="col-6 col-md-3">
          <input type="text" name="genero" class="form-control" placeholder="Género" value="{{ request('genero') }}">
        </div>
        <div class="col-6 col-md-3">
          <select name="tipo" class="form-select">
            <option value="">Todos</option>
            <option value="pelicula" {{ request('tipo') == 'pelicula' ? 'selected' : '' }}>Películas</option>
            <option value="serie" {{ request('tipo') == 'serie' ? 'selected' : '' }}>Series</option>
            <option value="anime" {{ request('tipo') == 'anime' ? 'selected' : '' }}>Anime</option>
          </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
          <button type="submit" class="btn-cta btn-primary">Buscar</button>
          <a href="{{ route('catalogo') }}" class="btn-cta btn-ghost">Limpiar</a>
        </div>
      </form>

      <p>
        <a href="{{ route('catalogo') }}">Todos</a> ·
        <a href="{{ route('catalogo.peliculas') }}">Películas</a> ·
        <a href="{{ route('catalogo.series') }}">Series</a> ·
        <a href="{{ route('catalogo.anime') }}">Anime</a>
      </p>

      <p>{{ $contenidos->total() }} resultados</p>

      <div class="row g-4">
        @forelse($contenidos as $contenido)

          <div class="col-6 col-md-3 col-lg-2">
            <a href="{{ route('catalogo.mostrar', $contenido->_id) }}" class="text-decoration-none">
              @if($contenido->imagen)
                <img src="{{ $contenido->imagen }}" alt="Póster de {{ $contenido->titulo }}" class="img-fluid rounded"
                  loading="lazy">
              @endif
              <h3 class="h6 mt-2">{{ $contenido->titulo }}</h3>
              <p class="small">{{ $contenido->tipo }}</p>
            </a>
          </div>

        @empty

          <p>No hay contenido disponible.</p>

        @endforelse
      </div>

      <div class="mt-4">
        {{ $contenidos->links('pagination::bootstrap-5') }}
      </div>

    </div>
  </section>

@endsection