<header class="site-header" id="siteHeader">
  <div class="wrap header-inner">
    <a class="brand" href="{{ route('inicio') }}" aria-label="Aetherix, inicio">
      <span class="brand__mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>
      </span>
      Aetherix<b> | an Universe of Stories </b>
    </a>

    <nav class="primary-nav" aria-label="Principal">
      <ul>
        <li><a href="{{ route('catalogo') }}" @class(['is-active' => request()->routeIs('inicio', 'catalogo', 'catalogo.buscar', 'catalogo.genero', 'catalogo.mostrar')])>Inicio</a></li>
        <li><a href="{{ route('catalogo.peliculas') }}" @class(['is-active' => request()->routeIs('catalogo.peliculas')])>Películas</a></li>
        <li><a href="{{ route('catalogo.series') }}" @class(['is-active' => request()->routeIs('catalogo.series')])>Series</a></li>
        <li><a href="{{ route('catalogo.anime') }}" @class(['is-active' => request()->routeIs('catalogo.anime')])>Anime</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <form class="header-search" action="{{ route('catalogo.buscar') }}" method="GET" role="search">
        <input type="search" name="nombre" placeholder="Buscar" aria-label="Buscar títulos" value="{{ request('nombre') }}">
      </form>

      @auth
        <span class="header-signin">{{ Auth::user()->nombre ?? Auth::user()->name ?? 'Mi cuenta' }}</span>
        <form method="POST" action="{{ url('/logout') }}" class="header-logout">@csrf<button class="btn-cta btn-primary header-cta" type="submit">Cerrar sesión</button></form>
      @else
        <a class="header-signin" href="{{ route('login') }}">Iniciar sesión</a>
        <a class="btn-cta btn-primary header-cta" href="{{ route('register') }}">Crear cuenta</a>
      @endauth

      <button class="icon-btn nav-toggle" id="navToggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobileNav">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>

  <div class="mobile-nav" id="mobileNav">
    <a href="{{ route('catalogo') }}">Inicio</a>
    <a href="{{ route('catalogo.peliculas') }}">Películas</a>
    <a href="{{ route('catalogo.series') }}">Series</a>
    <a href="{{ route('catalogo.anime') }}">Anime</a>
    @auth
      <form method="POST" action="{{ url('/logout') }}">@csrf<button class="btn-cta btn-primary btn-block" type="submit">Cerrar sesión</button></form>
    @else
      <a class="btn-cta btn-primary btn-block" href="{{ route('login') }}">Iniciar sesión</a>
      <a class="btn-cta btn-ghost btn-block" href="{{ route('register') }}">Crear cuenta</a>
    @endauth
  </div>
</header>
