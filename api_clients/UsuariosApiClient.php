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

    public function crearUsuario($payload)  {
        $url = $this->baseUrl . "/crear";

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($payload),
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        return json_decode($response, true);
    }

    public function obtenerUsuario(int $id){
        $url = $this->baseUrl . "/obtenerusuario/{$id}";

        $opciones = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n"
            ]
        ];

        $contexto = stream_context_create($opciones);
        $response = @file_get_contents($url, false, $contexto);

        if ($response === FALSE) {
            return [
                'success' => false,
                'message' => "Error al obtener el usuario con ID $id. Verifica que el ID exista."
            ];
        }


        return json_decode($response, true);
    }

    public function actualizarUsuario($id, $data) {
        $url = $this->baseUrl . "/actualizar/{$id}";

        $opciones = [
            'http' => [
                'method'  => 'PUT',
                'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => json_encode($data),
                'ignore_errors' => true // Para capturar respuestas de error HTTP
            ]
        ];

        $contexto = stream_context_create($opciones);
        $response = @file_get_contents($url, false, $contexto);

        if ($response === FALSE) {
            return [
                'success' => false,
                'message' => 'No se pudo conectar con la API o la API no devolvió respuesta.'
            ];
        }

        // Decodificar respuesta JSON de la API
        $result = json_decode($response, true);

        // Si no puede decodificar, devolver error básico
        if ($result === null) {
            return [
                'success' => false,
                'message' => 'La API devolvió una respuesta no válida.'
            ];
        }

        return $result;
    }

    public function cambiarPassword($id, $passwordActual, $passwordNueva, $passwordConfirmar) {
        $url = $this->baseUrl . "/cambiarpassword/{$id}";

        $data = [
            "passwordActual" => $passwordActual,
            "passwordNueva" => $passwordNueva,
            "passwordConfirmar" => $passwordConfirmar
        ];

        $opciones = [
            'http' => [
                'method' => 'PUT',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($opciones);
        $response = @file_get_contents($url, false, $context);

        if ($response === FALSE) {
            return ['success' => false, 'message' => 'No se pudo conectar con la API.'];
        }

        return json_decode($response, true);
    }


}
