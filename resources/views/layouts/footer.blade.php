<footer class="site-footer">
  <div class="wrap">
    <div class="footer-top">
      <div class="footer-brand">
        <a class="brand" href="{{ route('inicio') }}" aria-label="Stream Line, inicio">
          <span class="brand__mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg></span>
          Stream<b>line</b>
        </a>
        <p>Películas, series y anime en un solo catálogo, con reproductor y seguimiento de lo que ya viste.</p>
      </div>

      <nav class="footer-col" aria-label="Explorar">
        <h4>Explorar</h4>
        <a href="{{ route('catalogo') }}">Todo el catálogo</a>
        <a href="{{ route('catalogo.peliculas') }}">Películas</a>
        <a href="{{ route('catalogo.series') }}">Series</a>
        <a href="{{ route('catalogo.anime') }}">Anime</a>
      </nav>
    </div>

    <div class="footer-bottom">
      <p class="credit">© {{ date('Y') }} Proyecto final IF0010 Bases de datos II, UCR. Plantilla Streamline de <b>uiCookies</b>.</p>
    </div>
  </div>
</footer>
