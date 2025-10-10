<?php
require_once __DIR__ . '/../config/config.php';

class EquipoApiClient
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = API_BASE_URL . "/equipo";
    }

    public function listarEquiposActivos(){
        $url = $this->baseUrl . "/listarEquiposActivosCodigos";
        $response = file_get_contents($url);
        return json_decode($response, true);


    }

    public function filtrar($filtros = [])
    {

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

    // Gráfico 1: Equipos disponibles por tipo
    public function equiposPorTipo($sucursalId = null)
    {
        $url = $this->baseUrl . "/equiposPorTipo";
        if ($sucursalId !== null) {
            $url .= "?sucursalId=" . (int)$sucursalId;
        }

        $response = @file_get_contents($url);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception("Error API (equiposPorTipo): " . $error['message']);
        }

        return json_decode($response, true);
    }

//    public function totalEquipos(){
//
//
//        $url = $this->baseUrl . "/total";
//        $response = file_get_contents($url);
//        return json_decode($response, true);
//
//
//    }

    public function totalEquipos($idSucursal = null)
    {
        $url = $this->baseUrl . "/total";

        // Si hay idSucursal, agregamos como parámetro
        if ($idSucursal !== null) {
            $url .= '?idSucursal=' . urlencode($idSucursal);
        }

        $response = @file_get_contents($url);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception("Error API totalEquipos: " . $error['message']);
        }

        return json_decode($response, true);
    }

    public function totalEquiposAsignados($idSucursal = null)
    {
        $url = $this->baseUrl . "/totalAsignados";

        // Si hay idSucursal, agregamos como parámetro
        if ($idSucursal !== null) {
            $url .= '?idSucursal=' . urlencode($idSucursal);
        }

        $response = @file_get_contents($url);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception("Error API totalEquipos: " . $error['message']);
        }

        return json_decode($response, true);
    }

    public function totalEquiposDisponibles($idSucursal = null)
    {
        $url = $this->baseUrl . "/totalDisponibles";

        // Si hay idSucursal, agregamos como parámetro
        if ($idSucursal !== null) {
            $url .= '?idSucursal=' . urlencode($idSucursal);
        }

        $response = @file_get_contents($url);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception("Error API totalEquipos: " . $error['message']);
        }

        return json_decode($response, true);
    }

    //Agregar nuevo equipo
    public function agregarEquipo($payload) {
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

    public function obtenerEquipo(int $id){
        $url = $this->baseUrl . "/obtenerEquipo/{$id}";

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
                'message' => "Error al obtener el equipo con ID $id. Verifica la conexión o que el ID exista."
            ];
        }


        return json_decode($response, true);
    }

    public function actualizarEquipo($id, $payload) {
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

    public function obtenerEquipoPorSucursal($id_sucursal)
    {
        $url = $this->baseUrl . "/sucursal/" . $id_sucursal;

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("Error al obtener cargos de la area ID {$id_sucursal}");
        }

        return json_decode($response, true);
    }

    public function actualizarEstadoEquipo($id, $payload) {
        $url = $this->baseUrl . "/actualizarestadoequipo/{$id}";

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
