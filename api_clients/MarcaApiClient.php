<?php
require_once __DIR__ . '/../config/config.php';

class MarcaApiClient{

    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = API_BASE_URL . "/marca";
    }

    // GET: Listar todas las marcas
    public function listarMarca()
    {
        $url = $this->baseUrl . "/listar";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    // GET: Obtener una marca por estado
    public function listarMarcasActivos(){
        $url = $this->baseUrl . "/listarActivas";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }
}
