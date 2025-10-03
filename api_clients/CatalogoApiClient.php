<?php
require_once __DIR__ . '/../config/config.php';

class CatalogoApiClient {
    private $baseUrl;

    public function __construct() {
        $this->baseUrl = API_BASE_URL . "/catalogo";
    }

    public function cambiarEstado($action, $id, $type) {
        $url = $this->baseUrl ."/EditEstado?action=" . urlencode($action)."&id=" . urlencode($id)."&type=" . urlencode($type) ;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // usamos PUT porque la API espera PUT
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception("Error en la petición: " . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        } else {
            throw new Exception("Error en la API (HTTP $httpCode): $response");
        }
    }

    public function agregarElemento($catalogo, $data) {
        $url = $this->baseUrl . "/agregar";

        $options = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json\r\n",
                "content" => json_encode([
                    "catalogo" => $catalogo,
                    "data" => $data
                ])
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        return json_decode($response, true);
    }

    //****************************************************************************************************//

    /**
     * Obtener un elemento por tipo e ID
     */
    public function obtenerElemento(string $type, int $id): array
    {
        $url = "{$this->baseUrl}/{$type}/{$id}";
        $response = file_get_contents($url);

        if ($response === false) {
            throw new Exception("Error al obtener {$type} con id {$id}");
        }

        return json_decode($response, true);
    }

    /**
     * Editar un elemento (PUT)
     */
    public function editarElemento(string $type, array $payload): array
    {
        if (!isset($payload["id"])) {
            throw new Exception("El payload debe incluir un ID");
        }

        $url = "{$this->baseUrl}/{$type}/{$payload['id']}";

        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "PUT",
                "content" => json_encode($payload),
                "ignore_errors" => true
            ]
        ];

        $context = stream_context_create($options);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new Exception("Error al editar {$type} con id {$payload['id']}");
        }

        return json_decode($response, true);
    }



}