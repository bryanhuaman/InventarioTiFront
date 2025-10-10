<?php
require_once __DIR__ . '/../config/config.php';

class RolApiClient {

    private $baseUrl;

    public function __construct() {
        $this->baseUrl = API_BASE_URL . "/roles";
    }

    // GET: Listar todas las áreas
    public function listar() {
        $url = $this->baseUrl . "/listar";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }
}
