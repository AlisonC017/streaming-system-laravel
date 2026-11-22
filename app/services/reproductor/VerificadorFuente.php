<?php

namespace App\Services\Reproductor;

use App\Models\Fuente;
use App\Models\Verificacion;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Pool as GuzzlePool;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Http;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use Throwable;

class VerificadorFuente
{
    // Dominios cuyo destino ya se valido en esta ejecucion (evita repetir la consulta DNS)
    protected $hostsValidados = [];

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

    // Verifica muchas fuentes con una ventana deslizante: cuando una termina, entra otra.
    // Guarda los resultados en grupos para no escribir en la base fuente por fuente.
    // Devuelve [correctas, otras]
    public function verificarVentana(array $ids, int $concurrencia, ?callable $progreso = null, int $tamanoEscritura = 50)
    {
        $correctas = 0;
        $otras = 0;
        $hechas = 0;
        $buffer = [];
        $pendientes = [];
        $tiempos = [];

        $volcar = function () use (&$buffer, &$correctas, &$otras, &$hechas, $progreso) {
            if (empty($buffer)) {
                return;
            }

            $this->registrarLote($buffer);

            foreach ($buffer as $r) {
                $r[3] ? $correctas++ : $otras++;
            }

            $hechas += count($buffer);
            $buffer = [];

            if ($progreso) {
                $progreso($hechas);
            }
        };

        $cliente = new Client([
            'timeout' => 5,
            'connect_timeout' => 3,
            'http_errors' => false,
            'stream' => true,
            'headers' => ['Accept' => 'application/json'],
            'allow_redirects' => [
                'max' => 3,
                'protocols' => ['http', 'https'],
                'on_redirect' => function ($request, $response, $uri) {
                    $this->validarDestinoCacheado((string) $uri);
                }
            ]
        ]);

        // Carga las fuentes de 500 en 500 y entrega una peticion cada vez que hay un espacio libre
        $peticiones = function () use ($ids, $cliente, &$buffer, &$pendientes, &$tiempos, $volcar, $tamanoEscritura) {
            foreach (array_chunk($ids, 500) as $grupo) {
                foreach (Fuente::whereIn('_id', $grupo)->get() as $fuente) {
                    $id = (string) $fuente->_id;
                    $error = $this->validarFuente($fuente);

                    if ($error) {
                        $buffer[] = [$fuente, now(), 0, false, true, $error, 0];
                        continue;
                    }

                    $pendientes[$id] = $fuente;

                    yield $id => function () use ($cliente, $fuente, $id, &$tiempos) {
                        $opciones = [
                            'on_stats' => function (TransferStats $stats) use ($id, &$tiempos) {
                                $tiempos[$id] = round(($stats->getTransferTime() ?? 0) * 1000);
                            }
                        ];

                        $url = $fuente->url;

                        // HEAD y, si un embed no lo acepta, GET, sin liberar el espacio entre ambas
                        return $cliente->requestAsync('HEAD', $url, $opciones)
                            ->then(function ($respuesta) use ($cliente, $fuente, $url, $opciones) {
                                if (in_array($respuesta->getStatusCode(), [405, 501]) && $fuente->tipo == 'embed') {
                                    return $cliente->requestAsync('GET', $url, $opciones);
                                }

                                return $respuesta;
                            });
                    };
                }

                if (count($buffer) >= $tamanoEscritura) {
                    $volcar();
                }
            }
        };

        $alTerminar = function ($respuesta, $id) use (&$pendientes, &$tiempos, &$buffer, $volcar, $tamanoEscritura) {
            $fuente = $pendientes[$id];
            unset($pendientes[$id]);

            $codigo = $respuesta->getStatusCode();
            $respuesta->getBody()->close();
            $resultado = false;
            $concluyente = true;

            if ($codigo == 429) {
                $concluyente = false;
                $mensaje = 'El servidor limito las solicitudes (429). Verificacion no concluyente.';
            } elseif ($codigo >= 200 && $codigo < 300) {
                $resultado = true;
                $mensaje = 'La URL respondio correctamente. Esto no garantiza que el video se pueda reproducir.';
            } else {
                $mensaje = 'Error HTTP: ' . $codigo;
            }

            $buffer[] = [$fuente, now(), $codigo, $resultado, $concluyente, $mensaje, $tiempos[$id] ?? 0];
            unset($tiempos[$id]);

            if (count($buffer) >= $tamanoEscritura) {
                $volcar();
            }
        };

        $alFallar = function ($motivo, $id) use (&$pendientes, &$tiempos, &$buffer, $volcar, $tamanoEscritura) {
            $fuente = $pendientes[$id];
            unset($pendientes[$id]);

            $mensaje = $motivo instanceof Throwable ? $motivo->getMessage() : 'Sin respuesta del servidor.';

            $buffer[] = [$fuente, now(), 0, false, true, $mensaje, $tiempos[$id] ?? 0];
            unset($tiempos[$id]);

            if (count($buffer) >= $tamanoEscritura) {
                $volcar();
            }
        };

        $pool = new GuzzlePool($cliente, $peticiones(), [
            'concurrency' => $concurrencia,
            'fulfilled' => $alTerminar,
            'rejected' => $alFallar
        ]);

        $pool->promise()->wait();

        // Lo que quedo sin guardar
        $volcar();

        return [$correctas, $otras];
    }

    // Devuelve el motivo si la fuente no se puede verificar, o null si esta bien
    protected function validarFuente($fuente)
    {
        if (!$fuente->url || !filter_var($fuente->url, FILTER_VALIDATE_URL)) {
            return 'La URL de la fuente no es valida.';
        }

        if (!in_array(parse_url($fuente->url, PHP_URL_SCHEME), ['http', 'https'])) {
            return 'Protocolo de URL no permitido.';
        }

        if ($fuente->fecha_expiracion && $fuente->fecha_expiracion->isPast()) {
            return 'La fuente ha expirado.';
        }

        try {
            $this->validarDestinoCacheado($fuente->url);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return null;
    }

    // Igual que validarDestino, pero recuerda los dominios ya aprobados
    protected function validarDestinoCacheado($url)
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host && isset($this->hostsValidados[$host])) {
            return;
        }

        $this->validarDestino($url);

        if ($host) {
            $this->hostsValidados[$host] = true;
        }
    }

    // Guarda todo el lote con 2 operaciones: una actualiza las fuentes y otra inserta el historial
    protected function registrarLote(array $registros)
    {
        if (empty($registros)) {
            return;
        }

        $operaciones = [];
        $documentos = [];

        foreach ($registros as [$fuente, $fecha, $codigo, $resultado, $concluyente, $mensaje, $tiempo]) {
            $momento = new UTCDateTime($fecha);

            $campos = [
                'ultima_verificacion' => $momento,
                'tiempo_respuesta' => $tiempo,
                'updated_at' => $momento
            ];

            if ($concluyente) {
                $campos['estado'] = $resultado ? 'disponible' : 'no_disponible';
            }

            $idFuente = $fuente->_id instanceof ObjectId
                ? $fuente->_id
                : new ObjectId((string) $fuente->_id);

            $operaciones[] = ['updateOne' => [['_id' => $idFuente], ['$set' => $campos]]];

            $documentos[] = [
                'fuente_id' => $fuente->_id,
                'fecha' => $momento,
                'resultado' => $resultado,
                'codigo' => $codigo,
                'tiempo_respuesta' => $tiempo,
                'mensaje' => $mensaje,
                'created_at' => $momento,
                'updated_at' => $momento
            ];
        }

        Fuente::raw(fn($coleccion) => $coleccion->bulkWrite($operaciones));
        Verificacion::raw(fn($coleccion) => $coleccion->insertMany($documentos));
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
