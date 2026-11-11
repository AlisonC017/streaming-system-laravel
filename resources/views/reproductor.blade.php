{{-- Reproductor. Recibe (opcional): $contenido, $episodio, $temporada, $episodios.
     La logica del script (fuentes, progreso, endpoints) es de Albert y no cambia:
     solo se cambio como se dibujan las fuentes y los avisos. --}}
@extends('app')
@section('titulo', (isset($contenido) ? $contenido->titulo . ' · ' : '') . 'Reproductor · Stream Line')

@section('contenido')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet">

<div class="wrap player-wrap">

    @if (isset($contenido))
        <a class="back" href="{{ route('catalogo.mostrar', $contenido->_id) }}">← Volver</a>
    @else
        <a class="back" href="{{ route('catalogo') }}">← Volver</a>
    @endif

    <div class="player-layout">

        <div class="player-main">
            <div class="stage">
                <video id="video" class="video-js vjs-default-skin vjs-fluid" controls preload="auto"></video>
                <iframe id="embed" allowfullscreen style="display:none;"></iframe>

                <div class="stage-vacio" id="vacio">
                    <span class="stage-vacio__play">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.53.85l10.5-6.5a1 1 0 0 0 0-1.7L9.53 4.65A1 1 0 0 0 8 5.5Z"/></svg>
                    </span>
                    <strong>Elige una fuente</strong>
                    <span>Selecciona una opción del panel para empezar a reproducir.</span>
                </div>

                <div class="toast" id="aviso" role="status" aria-live="polite"></div>
            </div>

            <div class="player-titulo">
                <p id="estado" class="player-estado">Listo para reproducir</p>

                @if (isset($contenido))
                    <h1>{{ $contenido->titulo }}</h1>
                    @if ($episodio)
                        <p class="player-sub">Episodio {{ $episodio->numero }}: {{ $episodio->titulo }}</p>
                    @endif
                    <input type="hidden" id="contenido_id" value="{{ $contenido->_id }}">
                    <input type="hidden" id="episodio_id" value="{{ $episodio?->_id }}">
                @else
                    <h1>Reproductor</h1>
                    <input class="campo-id" type="text" id="contenido_id" placeholder="ID del contenido">
                    <input class="campo-id" type="text" id="episodio_id" placeholder="ID del episodio (opcional)">
                @endif
            </div>
        </div>

        <aside class="player-side">
            <div class="panel">
                <div class="panel-head">
                    <h2>Fuentes disponibles</h2>
                    <button class="btn btn-ghost btn-sm" type="button" onclick="consultarFuentes()">Consultar</button>
                </div>
                <div id="fuentes" class="src-list"><p class="muted">Sin consultar.</p></div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Progreso</h2></div>
                <div class="progreso-datos">
                    <div><span>Segundo actual</span><b><span id="segundo">0</span> s</b></div>
                    <div class="der"><span>Porcentaje</span><b><span id="porcentaje">0</span>%</b></div>
                </div>
                <div class="progreso-barra"><i id="barra"></i></div>
                <button class="btn btn-ghost btn-block" type="button" onclick="guardarProgreso()">Guardar progreso</button>
                @guest
                    <p class="nota-sesion">Para que tu avance quede guardado, <a href="{{ route('login') }}">inicia sesión</a>.</p>
                @endguest
            </div>

            @if (isset($contenido) && isset($episodios) && $episodios->count() > 1)
                <div class="panel">
                    <div class="panel-head"><h2>Episodios{{ $temporada ? ' · Temp. ' . $temporada->numero : '' }}</h2></div>
                    <div class="up-next">
                        @foreach ($episodios as $ep)
                            <a class="up {{ $episodio && (string) $ep->_id === (string) $episodio->_id ? 'on' : '' }}"
                                href="{{ route('reproductor.episodio', ['contenidoId' => $contenido->_id, 'episodioId' => $ep->_id]) }}">
                                <strong class="episode-number">{{ $ep->numero }}</strong>
                                <div><h3>{{ $ep->titulo }}</h3></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>

    </div>
</div>

<script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>

<script>

    const reproductor = videojs('video');
    let fuenteActual = null;
    let segundoActual = 0;
    let duracionActual = 0;

    function obtenerToken() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }

    async function consultarFuentes() {

        let contenidoId = document.getElementById('contenido_id').value;
        let episodioId = document.getElementById('episodio_id').value;

        if (!contenidoId) {
            aviso('Ingrese el ID del contenido.', 'warn');
            return;
        }

        try {

            let respuesta = await fetch('/reproductor/fuentes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': obtenerToken()
                },
                body: JSON.stringify({
                    contenido_id: contenidoId,
                    episodio_id: episodioId || null
                })
            });

            let datos = await respuesta.json();

            let contenedor = document.getElementById('fuentes');

            contenedor.innerHTML = '';

            if (!respuesta.ok) {
                throw new Error('No se pudieron consultar las fuentes');
            }

            if (datos.fuentes.length === 0) {
                contenedor.innerHTML = '<p class="muted">No hay fuentes activas.</p>';
                return;
            }

            datos.fuentes.forEach(fuente => {

                let tarjeta = document.createElement('div');
                tarjeta.className = 'src-card';
                tarjeta._fuente = fuente;

                let arriba = document.createElement('div');
                arriba.className = 'src-card__top';

                let nombre = document.createElement('strong');
                nombre.textContent = fuente.proveedor;

                let tipo = document.createElement('em');
                tipo.textContent = (fuente.tipo || '').toUpperCase();

                arriba.append(nombre, tipo);

                let meta = document.createElement('p');
                meta.className = 'src-card__meta';

                let idioma = document.createElement('span');
                idioma.textContent = 'Idioma: ' + fuente.idioma;

                let estado = document.createElement('span');
                estado.textContent = 'Estado: ' + fuente.estado;

                meta.append(idioma, estado);

                let boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'btn btn-primary btn-sm';
                boton.textContent = 'Reproducir';
                boton.onclick = function () {
                    reproducir(fuente);
                };

                tarjeta.append(arriba, meta, boton);
                contenedor.appendChild(tarjeta);

            });

        } catch (error) {
            aviso(error.message, 'warn');
        }
    }

    function reproducir(fuente) {
        fuenteActual = fuente;

        document.querySelectorAll('#fuentes .src-card').forEach(c => {
            let activa = c._fuente === fuente;
            c.classList.toggle('active', activa);
            c.querySelector('button').textContent = activa ? 'Reproduciendo' : 'Reproducir';
        });
        document.getElementById('vacio')?.classList.add('oculto');

        let video = document.getElementById('video');
        let iframe = document.getElementById('embed');

        if (fuente.tipo === 'embed') {
            reproductor.pause();
            reproductor.reset();
            video.style.display = 'none';
            iframe.style.display = 'block';
            iframe.src = fuente.url;
        } else {
            iframe.style.display = 'none';
            iframe.src = '';
            video.style.display = 'block';

            let tipo = 'video/mp4';

            if (fuente.tipo === 'hls') {
                tipo = 'application/x-mpegURL';
            }

            reproductor.src({
                src: fuente.url,
                type: tipo
            });

            reproductor.play();
        }

        document.getElementById('estado').textContent =
            'Reproduciendo · ' + fuente.proveedor;
    }

    window.addEventListener('message', function (event) {
        let iframe = document.getElementById('embed');

        if (event.source !== iframe.contentWindow) {
            return;
        }

        if (event.origin !== new URL(iframe.src).origin) {
            return;
        }

        let mensaje = event.data;

        if (!mensaje || mensaje.type !== 'PLAYER_EVENT') {
            return;
        }

        let datos = mensaje.data;

        if (Number.isFinite(datos.player_progress)) {
            segundoActual = Math.floor(datos.player_progress);
        }

        if (Number.isFinite(datos.player_duration) && datos.player_duration > 0) {
            duracionActual = Math.floor(datos.player_duration);
        }

        document.getElementById('segundo').textContent = segundoActual;

        if (duracionActual > 0) {
            let porcentaje = (segundoActual / duracionActual) * 100;

            document.getElementById('porcentaje').textContent =
                porcentaje.toFixed(2);
        }

        if (['playing', 'paused', 'seeked', 'completed'].includes(datos.player_status)) {
            guardarProgreso(true);
        }
    });


    async function guardarProgreso(automatico = false) {
        let contenidoId = document.getElementById('contenido_id').value;
        let episodioId = document.getElementById('episodio_id').value;

        let segundo;
        let duracion;

        if (!fuenteActual) {
            if (!automatico) aviso('Primero selecciona una fuente.', 'warn');
            return;
        }

        if (fuenteActual.tipo === 'embed') {
            segundo = segundoActual;
            duracion = duracionActual;
        } else {
            segundo = Math.floor(reproductor.currentTime() || 0);
            duracion = Math.floor(reproductor.duration() || 0);
        }

        if (!Number.isFinite(duracion) || duracion <= 0) {
            if (!automatico) aviso('Aún no hay una duración válida para guardar.', 'warn');
            return;
        }

        try {
            let respuesta = await fetch('/progreso', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': obtenerToken()
                },
                body: JSON.stringify({
                    contenido_id: contenidoId,
                    episodio_id: episodioId || null,
                    segundo_actual: segundo,
                    duracion_total: duracion
                })
            });

            let datos = await respuesta.json();

            if (!respuesta.ok) {
                throw new Error(datos.message || 'Error guardando el progreso');
            }

            if (!automatico) {
                aviso(datos.mensaje, 'ok');
            }

        } catch (error) {
            console.error(error);

            if (!automatico) {
                aviso(error.message, 'warn');
            }
        }
    }

    reproductor.on('timeupdate', function () {

        let segundo = Math.floor(reproductor.currentTime() || 0);
        let duracion = reproductor.duration() || 0;

        document.getElementById('segundo').textContent = segundo;

        if (duracion > 0) {

            let porcentaje = (segundo / duracion) * 100;

            document.getElementById('porcentaje').textContent =
                porcentaje.toFixed(2);

        }

    });

    @if(isset($contenido))
        consultarFuentes();
    @endif

    // ---------- Solo presentacion: avisos en pantalla y barra de progreso ----------
    var avisoTimer;
    function aviso(texto, tipo = 'ok') {
        const el = document.getElementById('aviso');
        if (!el) return;
        el.textContent = texto;
        el.classList.toggle('warn', tipo === 'warn');
        el.classList.add('show');
        clearTimeout(avisoTimer);
        avisoTimer = setTimeout(() => el.classList.remove('show'), 3500);
    }

    (function () {
        const pct = document.getElementById('porcentaje');
        const barra = document.getElementById('barra');
        if (!pct || !barra) return;
        new MutationObserver(() => {
            barra.style.width = Math.min(100, parseFloat(pct.textContent) || 0) + '%';
        }).observe(pct, { childList: true, characterData: true, subtree: true });
    })();

</script>
@endsection