<?php
require_once __DIR__ . '/../config/config.php';

class CargoApiClient{

    private $baseUrl;

    public function __construct(){
        $this->baseUrl = API_BASE_URL . "/cargo";
    }

    // GET: Listar todos los cargos con sus áreas
    public function listarconCargos(){
        $url = $this->baseUrl . "/listarConArea";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    public function listarActivos(){
        $url = $this->baseUrl . "/listarActivos";
        $response = file_get_contents($url);
        return json_decode($response, true);

    }

    public function obtenerCargosPorArea($id_area)
    {
        $url = $this->baseUrl . "/area/" . $id_area;

        $response = @file_get_contents($url);
        if ($response === false) {
            throw new Exception("Error al obtener cargos de la area ID {$id_area}");
        }

        return json_decode($response, true);
    }

}
