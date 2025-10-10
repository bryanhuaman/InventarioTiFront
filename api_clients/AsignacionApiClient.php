<?php
require_once __DIR__ . '/../config/config.php';

class AsignacionApiClient{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = API_BASE_URL . "/asignacion";
    }


    public function filtrar($filtros = []){

        $url = $this->baseUrl . "/filtro";

        if (!empty($filtros)) {
            $url .= '?' . http_build_query($filtros);
        }

        $options = [
            "http" => [
                "method" => "GET",
                "header" => "Accept: application/json\r\n"
            ]
        ];

        $context = stream_context_create($options);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception("Error API: " . $error['message']);
        }

        return json_decode($response, true);
    }

    public function crearAsignacion($payload) {
        $url = $this->baseUrl . "/crear";

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($payload),
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);




        return json_decode($response, true);
    }

    public function obtenerAsingacionFinalizada($id_asignacion)
    {
        $url = $this->baseUrl . "/obtenerfinaasignacion/" . $id_asignacion;

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("Error al obtener asignacion de la area ID {$id_asignacion}");
        }

        return json_decode($response, true);
    }

    public function obtenerAsignacionDetalle($id_asignacion)
    {
        $url = $this->baseUrl . "/asignaciondetalle/" . $id_asignacion;

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("Error al obtener asignacion de la area ID {$id_asignacion}");
        }

        return json_decode($response, true);
    }

    public function obtenerAsignacionDevolucion($id_asignacion)
    {
        $url = $this->baseUrl . "/asignacionDevolucion/" . $id_asignacion;

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("Error al obtener asignacion de la area ID {$id_asignacion}");
        }

        return json_decode($response, true);
    }

    public function obtenerImagenesAsignacion($id_asignacion)
    {
        $url = $this->baseUrl . "/imagenes/" . $id_asignacion;

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("Error al obtener asignacion de la area ID {$id_asignacion}");
        }

        return json_decode($response, true);
    }

    public function obtenerEquipoId($id_asignacion)
    {
        $url = $this->baseUrl . "/buscarequipoid/" . $id_asignacion;

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("Error al obtener asignacion de la area ID {$id_asignacion}");
        }

        return json_decode($response, true);
    }

    public function realizarDevolucion($id, $payload) {
        $url = $this->baseUrl . "/devolucion/{$id}";

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'PUT',
                'content' => json_encode($payload),
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);




        return json_decode($response, true);
    }

    public function actualizarActas($id, $payload) {
        $url = $this->baseUrl . "/actualizaractas/{$id}";

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'PUT',
                'content' => json_encode($payload),
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);




        return json_decode($response, true);
    }

}

