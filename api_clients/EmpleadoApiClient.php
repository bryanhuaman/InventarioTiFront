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

    public function agregarEmpleado($payload) {
        $url = $this->baseUrl . "/insertar";

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

    public function obtenerEmpleado(int $id){
        $url = $this->baseUrl . "/obtenerEmpleado/{$id}";

        $opciones = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n"
            ]
        ];

        $contexto = stream_context_create($opciones);
        $response = @file_get_contents($url, false, $contexto);

        if ($response === FALSE) {
            return [
                'success' => false,
                'message' => "Error al obtener el empleado con ID $id. Verifica la conexión o que el ID exista."
            ];
        }


        return json_decode($response, true);
    }

    public function actualizarEmpleado($id, $payload) {
        $url = $this->baseUrl . "/actualizar/{$id}";

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

    public function obtenerEmpleadosPorSucursal($id_sucursal)
    {
        $url = $this->baseUrl . "/sucursal/" . $id_sucursal;

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("Error al obtener empleados de la area ID {$id_sucursal}");
        }

        return json_decode($response, true);
    }

}
