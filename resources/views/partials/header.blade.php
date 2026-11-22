<header class="site-header" id="siteHeader">
    <div class="wrap header-inner">

        <a class="brand" href="{{ route('inicio') }}" aria-label="Aetherix, inicio">
            <span class="brand__mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z" />
                </svg>
            </span>
            Aetherix<b> | an Universe of Stories </b>
        </a>

        <nav class="primary-nav" aria-label="Principal">
            <ul>
                <li><a href="{{ route('catalogo') }}" @class([
                    'is-active' => request()->routeIs(
                        'inicio',
                        'catalogo',
                        'catalogo.buscar',
                        'catalogo.genero',
                        'catalogo.mostrar'),
                ])>Inicio</a></li>
                <li><a href="{{ route('catalogo.peliculas') }}" @class(['is-active' => request()->routeIs('catalogo.peliculas')])>Películas</a></li>
                <li><a href="{{ route('catalogo.series') }}" @class(['is-active' => request()->routeIs('catalogo.series')])>Series</a></li>
                <li><a href="{{ route('catalogo.anime') }}" @class(['is-active' => request()->routeIs('catalogo.anime')])>Anime</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <form @class([
                'search-big',
                'is-collapsible',
                'is-open' => request()->filled('nombre'),
            ]) action="{{ route('catalogo.buscar') }}" method="GET" role="search">
                <button class="search-big__toggle" type="button" aria-label="Buscar"
                    aria-expanded="{{ request()->filled('nombre') ? 'true' : 'false' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3.5-3.5" />
                    </svg>
                </button>
                <div class="search-big__fields">
                    <input name="nombre" value="{{ request('nombre') }}" placeholder="Buscar por título..."
                        aria-label="Buscar por título">
                    <select name="tipo" aria-label="Tipo">
                        <option value="">Todos</option>
                        <option value="pelicula" @selected(request('tipo') === 'pelicula')>Películas</option>
                        <option value="serie" @selected(request('tipo') === 'serie')>Series</option>
                        <option value="anime" @selected(request('tipo') === 'anime')>Anime</option>
                    </select>
                    <button class="btn btn-primary">Buscar</button>
                </div>
            </form>

            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const form = document.querySelector('.search-big.is-collapsible');
                        if (!form) return;
                        const toggle = form.querySelector('.search-big__toggle');
                        const input = form.querySelector('input[name="nombre"]');

                        const abrir = () => {
                            form.classList.add('is-open');
                            toggle.setAttribute('aria-expanded', 'true');
                            setTimeout(() => input.focus(), 150);
                        };
                        const cerrar = () => {
                            form.classList.remove('is-open');
                            toggle.setAttribute('aria-expanded', 'false');
                        };

                        toggle.addEventListener('click', () => form.classList.contains('is-open') ? cerrar() : abrir());
                        document.addEventListener('click', e => {
                            if (!form.contains(e.target) && !input.value.trim()) cerrar();
                        });
                        input.addEventListener('keydown', e => {
                            if (e.key === 'Escape' && !input.value.trim()) cerrar();
                        });
                    });
                </script>
            @endpush


            @auth
                <span class="header-signin">{{ Auth::user()->nombre ?? (Auth::user()->name ?? 'Mi cuenta') }}</span>
                <form method="POST" action="{{ url('/logout') }}" class="header-logout">@csrf<button
                        class="btn-cta btn-primary header-cta" type="submit">Cerrar sesión</button></form>
            @else
                <a class="header-signin" href="{{ route('login') }}">Iniciar sesión</a>
                <a class="btn-cta btn-primary header-cta" href="{{ route('register') }}">Crear cuenta</a>
            @endauth

            <button class="icon-btn nav-toggle" id="navToggle" type="button" aria-label="Abrir menú"
                aria-expanded="false" aria-controls="mobileNav">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <div class="mobile-nav" id="mobileNav">
        <a href="{{ route('catalogo') }}">Inicio</a>
        <a href="{{ route('catalogo.peliculas') }}">Películas</a>
        <a href="{{ route('catalogo.series') }}">Series</a>
        <a href="{{ route('catalogo.anime') }}">Anime</a>
        @auth
            <form method="POST" action="{{ url('/logout') }}">@csrf<button class="btn-cta btn-primary btn-block"
                    type="submit">Cerrar sesión</button></form>
        @else
            <a class="btn-cta btn-primary btn-block" href="{{ route('login') }}">Iniciar sesión</a>
            <a class="btn-cta btn-ghost btn-block" href="{{ route('register') }}">Crear cuenta</a>
        @endauth
    </div>
</header>
