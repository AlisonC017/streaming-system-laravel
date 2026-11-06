<?php

namespace App\Services\Reproductor;

use App\Models\Fuente;
use App\Models\Verificacion;
use Illuminate\Support\Facades\Http;
use Exception;

class VerificadorFuente
{
    public function verificar($fuenteId)
    {
        $fuente = Fuente::find($fuenteId);

        if (!$fuente) {
            return false;
        }

        $inicio = microtime(true);
        $fecha = now();
        $codigo = 0;
        $resultado = false;
        $concluyente = true;
        $mensaje = '';

        try {

            if (!$fuente->url || !filter_var($fuente->url, FILTER_VALIDATE_URL)) {
                throw new Exception('La URL de la fuente no es valida.');
            }

            if (!in_array(parse_url($fuente->url, PHP_URL_SCHEME), ['http', 'https'])) {
                throw new Exception('Protocolo de URL no permitido.');
            }

            if ($fuente->fecha_expiracion && $fuente->fecha_expiracion->isPast()) {
                throw new Exception('La fuente ha expirado.');
            }

            $this->validarDestino($fuente->url);

            $respuesta = $this->solicitar('head', $fuente->url);
            $codigo = $respuesta->status();

            // Algunos proveedores no permiten solicitudes HEAD
            // En fuentes embed se intenta una solicitud GET
            if (in_array($codigo, [405, 501]) && $fuente->tipo == 'embed') {
                $respuesta = $this->solicitar('get', $fuente->url);
                $codigo = $respuesta->status();
            }

            if ($codigo == 429) {
                $concluyente = false;
                $mensaje = 'El servidor limito las solicitudes (429). Verificacion no concluyente.';
            } elseif ($respuesta->successful()) {
                $resultado = true;
                $mensaje = 'La URL respondio correctamente. Esto no garantiza que el video se pueda reproducir.';
            } else {
                $mensaje = 'Error HTTP: ' . $codigo;
            }
        } catch (Exception $e) {

            $mensaje = $e->getMessage();
            $resultado = false;
        }

        $tiempo = round((microtime(true) - $inicio) * 1000);

        if ($concluyente) {
            $fuente->estado = $resultado ? 'disponible' : 'no_disponible';
        }

        $fuente->ultima_verificacion = $fecha;
        $fuente->tiempo_respuesta = $tiempo;
        $fuente->save();

        $verificacion = new Verificacion();

        $verificacion->fuente_id = $fuente->_id;
        $verificacion->fecha = $fecha;
        $verificacion->resultado = $resultado;
        $verificacion->codigo = $codigo;
        $verificacion->tiempo_respuesta = $tiempo;
        $verificacion->mensaje = $mensaje;

        $verificacion->save();

        return $resultado;
    }

    protected function solicitar($metodo, $url)
    {
        return Http::timeout(15)
            ->withOptions([
                'allow_redirects' => [
                    'max' => 3,
                    'protocols' => ['http', 'https'],
                    'on_redirect' => function ($request, $response, $uri) {
                        $this->validarDestino((string) $uri);
                    }
                ],
                'stream' => true
            ])
            ->{$metodo}($url);
    }

    protected function validarDestino($url)
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            throw new Exception('La URL no tiene un dominio valido.');
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : (gethostbynamel($host) ?: []);

        if (empty($ips)) {
            throw new Exception('No se pudo resolver el dominio de la fuente.');
        }

        foreach ($ips as $ip) {
            $publica = filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if (!$publica) {
                throw new Exception('Destino no permitido: apunta a una red interna.');
            }
        }
    }
}
