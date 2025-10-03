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
}
