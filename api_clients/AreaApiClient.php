<?php
require_once __DIR__ . '/../config/config.php';

class AreaApiClient {

    private $baseUrl;

    public function __construct() {
        $this->baseUrl = API_BASE_URL . "/area";
    }

    // GET: Listar todas las áreas
    public function listar() {
        $url = $this->baseUrl . "/listar";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    // GET: Obtener un área activas
    public function listarActivas() {
        $url = $this->baseUrl . "/listarActivos";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

//    // POST: Crear nueva área
//    public function crear($nombre, $estado) {
//        $url = $this->baseUrl . "/crear";
//
//        $data = [
//            "nombre" => $nombre,
//            "estado" => $estado
//        ];
//
//        $options = [
//            "http" => [
//                "header"  => "Content-type: application/json\r\n",
//                "method"  => "POST",
//                "content" => json_encode($data),
//            ],
//        ];
//
//        $context  = stream_context_create($options);
//        $result = file_get_contents($url, false, $context);
//        return json_decode($result, true);
//    }
//
//    // PUT: Actualizar un área
//    public function actualizar($id, $nombre, $estado) {
//        $url = $this->baseUrl . "/actualizar/" . $id;
//
//        $data = [
//            "nombre" => $nombre,
//            "estado" => $estado
//        ];
//
//        $options = [
//            "http" => [
//                "header"  => "Content-type: application/json\r\n",
//                "method"  => "PUT",
//                "content" => json_encode($data),
//            ],
//        ];
//
//        $context  = stream_context_create($options);
//        $result = file_get_contents($url, false, $context);
//        return json_decode($result, true);
//    }
//
//    // DELETE: Eliminar un área
//    public function eliminar($id) {
//        $url = $this->baseUrl . "/eliminar/" . $id;
//
//        $options = [
//            "http" => [
//                "method" => "DELETE",
//            ],
//        ];
//
//        $context  = stream_context_create($options);
//        $result = file_get_contents($url, false, $context);
//        return json_decode($result, true);
//    }
}