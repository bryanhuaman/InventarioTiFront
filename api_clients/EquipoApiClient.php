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

}
