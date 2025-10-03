<?php
require_once __DIR__ . '/../config/config.php';

class UsuariosApiClient{

    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = API_BASE_URL . "/usuarios";
    }

    // GET: Listar usuarios con su rol y sucursal
    public function listarUsuConRolYSucursal()
    {
        $url = $this->baseUrl . "/listarConRolYSucursal";
        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    // POST: Login de usuario
    public function login($email, $password) {
        $url = $this->baseUrl . "/login";

        $data = [
            "email" => $email,
            "password" => $password
        ];

        $options = [
            "http" => [
                "header"  => "Content-Type: application/json",
                "method"  => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }

}
