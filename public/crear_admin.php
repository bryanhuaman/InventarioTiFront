<?php
require_once '../api_clients/UsuariosApiClient.php';

$nombre = 'Admin';
$email = 'admin@tuempresa.com';
$password_plano = '123'; // Cambia esta contraseña
$id_rol = 1; // 1 = Administrador


try {
        $usuarioApi = new UsuariosApiClient();

        $payload = [
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password_plano,
            'sucursalId' =>  null,
            'rolId' => $id_rol
        ];

            $resultado = $usuarioApi->crearUsuario($payload);

            $_SESSION['alert_message'] = [
                'type' => $resultado['status'] === 201 ? 'success' : 'error',
                'text' => $resultado['mensaje']
            ];

    echo "¡Usuario administrador creado con éxito! <br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Password: " . htmlspecialchars($password_plano) . "<br>";
    echo "<b>YA PUEDES BORRAR ESTE ARCHIVO.</b>";

}catch (Exception $exception) {
//    $conexion->rollback();
    echo "Error al crear el usuario: " . $exception->getMessage();
}
