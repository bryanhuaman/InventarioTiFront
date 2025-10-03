<?php
require_once __DIR__ . '/../config/config.php';

class ModeloApiClient{

    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = API_BASE_URL . "/modelo";
    }

    // GET: Listar todos los modelos con su marca
    public function listarModelosConMarca()
    {
        $url = $this->baseUrl . "/listarConMarca";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }
}
