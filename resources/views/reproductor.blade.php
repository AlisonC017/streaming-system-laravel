<!DOCTYPE html>
<html lang="es">
{{-- Vistas solo de prueba :p --}}

<head>
    <meta charset="UTF-8">
    <title>Prueba reproductor</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial;
            background: #181818;
            color: white;
            padding: 25px;
        }

        .contenedor {
            max-width: 1000px;
            margin: auto;
        }

        .fuente {
            background: #292929;
            padding: 15px;
            margin: 10px 0;
        }

        button,
        select {
            padding: 10px;
            margin: 5px;
        }

        video {
            width: 100%;
        }

        iframe {
            width: 100%;
            height: 500px;
            border: none;
        }
    </style>
</head>

<body>

    <div class="contenedor">

        <h1>Prueba del reproductor</h1>

        @if(isset($contenido))

            <h2>{{ $contenido->titulo }}</h2>

            <input type="hidden" id="contenido_id" value="{{ $contenido->_id }}">

            <input type="hidden" id="episodio_id" value="{{ $episodio?->_id }}">

            @if($episodio)
                <h3>{{ $episodio->titulo }}</h3>
            @endif

        @else

            <input type="text" id="contenido_id" placeholder="ID del contenido">

            <input type="text" id="episodio_id" placeholder="ID del episodio">

        @endif

        <button onclick="consultarFuentes()">Consultar fuentes</button>

        <hr>

        <h2>Fuentes disponibles</h2>

        <div id="fuentes">
            <p>Sin consultar.</p>
        </div>

        <hr>

        <h2>Reproductor</h2>

        <video id="video" class="video-js vjs-default-skin" controls preload="auto"></video>

        <iframe id="embed" allowfullscreen style="display:none;"></iframe>

        <p id="estado"></p>

        <hr>

        <h2>Progreso</h2>

        <p>Segundo actual: <span id="segundo">0</span></p>

        <p>Porcentaje: <span id="porcentaje">0</span>%</p>

        <button onclick="guardarProgreso()">Guardar progreso</button>

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
                alert('Ingrese el ID del contenido');
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
                    contenedor.innerHTML = '<p>No hay fuentes activas.</p>';
                    return;
                }

                datos.fuentes.forEach(fuente => {

                    let elemento = document.createElement('div');

                    elemento.className = 'fuente';

                    elemento.innerHTML = `
                    <p>Proveedor: ${fuente.proveedor}</p>
                    <p>Tipo: ${fuente.tipo}</p>
                    <p>Idioma: ${fuente.idioma}</p>
                    <p>Estado: ${fuente.estado}</p>
                    <button>Reproducir</button>
                `;

                    elemento.querySelector('button').onclick = function () {
                        reproducir(fuente);
                    };

                    contenedor.appendChild(elemento);

                });

            } catch (error) {
                alert(error.message);
            }
        }

        function reproducir(fuente) {
            fuenteActual = fuente;

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
                'Fuente seleccionada: ' + fuente.proveedor;
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
                if (!automatico) alert('Primero selecciona una fuente.');
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
                if (!automatico) alert('No hay una duracion valida para guardar.');
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
                    alert(datos.mensaje);
                }

            } catch (error) {
                console.error(error);

                if (!automatico) {
                    alert(error.message);
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

    </script>

</body>

</html>