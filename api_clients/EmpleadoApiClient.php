<?php
require_once __DIR__ . '/../config/config.php';

class EmpleadoApiClient{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = API_BASE_URL . "/empleados";
    }


    public function listarEmpleadoActivos() {
        $url = $this->baseUrl . "/listarActivos";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }


    public function filtrar($filtros = []){

        $url = $this->baseUrl . "/filtrar";

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
}
