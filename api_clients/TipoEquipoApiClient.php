<?php
require_once __DIR__ . '/../config/config.php';

class TipoEquipoApiClient{

    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = API_BASE_URL . "/tipoequipo";
    }

    // GET: Listar todos los tipos de equipo
    public function listarTipoEquipo()
    {
        $url = $this->baseUrl . "/listar";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    // GET: Obtener un tipo de equipo por estado
    public function listarTipoEquipoActivos() {
        $url = $this->baseUrl . "/listarActivos";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }
}
