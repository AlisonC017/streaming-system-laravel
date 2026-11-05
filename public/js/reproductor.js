const token = document.querySelector('meta[name="csrf-token"]').content;

async function enviarSolicitud(url, metodo, datos) {
    const respuesta = await fetch(url, {
        method: metodo,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify(datos)
    });

    const resultado = await respuesta.json();

    if (!respuesta.ok) {
        throw new Error(resultado.message || resultado.error || 'Error en la solicitud.');
    }

    return resultado;
}

async function registrarFuente() {
    const datos = {
        tipo: document.getElementById('tipoFuente').value,
        id_contenido: document.getElementById('idContenido').value,
        proveedor: document.getElementById('proveedor').value,
        url: document.getElementById('urlFuente').value,
        formato: document.getElementById('formato').value,
        activo: true
    };

    try {
        const resultado = await enviarSolicitud(
            '/reproductor/fuentes',
            'POST',
            datos
        );
        document.getElementById('mensajeFuente').textContent = resultado.message;
    } catch (error) {
        document.getElementById('mensajeFuente').textContent = error.message;
    }
}

// --- FUNCIÓN MODIFICADA PARA INTERCEPTAR CLICKS EN EMBEDS ---
function reproducirFuente(url, tipo, proveedor = '') {
    player.pause();
    player.el().style.display = 'none';
    iframe.style.display = 'none';
    iframe.src = '';

    // Remover cualquier overlay previo para evitar duplicados
    const overlayPrevio = document.getElementById('player-overlay');
    if (overlayPrevio) overlayPrevio.remove();

    if (tipo === 'embed') {
        iframe.src = url;
        iframe.style.display = 'block';
        estadoReproductor.textContent = '● Embed cargado';

        // Si el proveedor es VidSrc, creamos la capa protectora invisible
        if (proveedor.toLowerCase().includes('vidsrc')) {
            crearCapadeProteccion();
        }
        return;
    }

    player.el().style.display = 'block';

    let formato = 'video/mp4';
    if (tipo === 'hls') {
        formato = 'application/x-mpegURL';
    }

    player.src({
        src: url,
        type: formato
    });

    player.load();

    player.play().catch(function () {
        estadoReproductor.textContent = '● Presione reproducir';
    });

    estadoReproductor.textContent = '● Fuente cargada';
}

// Función auxiliar para inicializar el interceptor transparente
function crearCapadeProteccion() {
    // Buscamos el contenedor padre del iframe para posicionar el div encima
    const contenedorIframe = iframe.parentElement;
    if (!contenedorIframe) return;

    // Asegurar que el contenedor use posición relativa
    contenedorIframe.style.position = 'relative';

    const overlay = document.createElement('div');
    overlay.id = 'player-overlay';

    // Estilos para cubrir perfectamente el iframe de forma transparente
    Object.assign(overlay.style, {
        position: 'absolute',
        top: '0',
        left: '0',
        width: '100%',
        height: '100%',
        zIndex: '9999',
        cursor: 'pointer',
        background: 'rgba(255, 0, 0, 0.4)' // Esto pintará la capa de rojo transparente
    });

    // Interceptamos el primer click destructivo del usuario
    overlay.addEventListener('click', function (e) {
        console.log('Click interceptado antes de que VidSrc abra ventanas emergentes.');

        // Opcional: Aquí puedes registrar métricas o lanzar un evento propio de tu app

        // Desactivar temporalmente los eventos del mouse para permitir que el segundo click interactúe con el reproductor real
        this.style.pointerEvents = 'none';

        // Reactivar la protección tras 8 segundos en caso de que pausen o interactúen con los controles del iframe más tarde
        setTimeout(() => {
            this.style.pointerEvents = 'auto';
        }, 8000);
    });

    contenedorIframe.appendChild(overlay);
}

let ultimoSegundoGuardado = -1;
let guardandoProgreso = false;

async function guardarProgreso() {
    if (!window.tipoActual || !window.idContenidoActual) {
        return;
    }

    if (guardandoProgreso) {
        return;
    }

    const segundo = Math.floor(player.currentTime());

    if (segundo < 1 || segundo == ultimoSegundoGuardado) {
        return;
    }

    guardandoProgreso = true;

    try {
        await enviarSolicitud('/progreso', 'POST', {
            tipo: window.tipoActual,
            id_contenido: window.idContenidoActual,
            segundo: segundo
        });

        ultimoSegundoGuardado = segundo;
    } catch (error) {
        console.error('Error guardando progreso:', error);
    } finally {
        guardandoProgreso = false;
    }
}
