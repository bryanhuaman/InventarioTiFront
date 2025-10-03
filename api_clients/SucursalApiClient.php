<?php
require_once __DIR__ . '/../config/config.php';

class SucursalApiClient{

    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = API_BASE_URL . "/sucursal";
    }

    // GET: Listar todos las sucursales
    public function listarSucursales()
    {
        $url = $this->baseUrl . "/listar";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    // GET: Obtener una sucursal activos
    public function listarSucursalesActivos()
    {
        $url = $this->baseUrl . "/listarPorEstado";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    // Gráfico 2: Equipos y empleados por sucursal (solo admin general)
    public function resumenPorSucursal()
    {
        $url = $this->baseUrl . "/sucursalesDashboard";



        $response = @file_get_contents($url);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception("Error API: " . $error['message']);
        }

        return json_decode($response, true);
    }
}
