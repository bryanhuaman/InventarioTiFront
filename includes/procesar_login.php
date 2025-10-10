<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/../api_clients/UsuariosApiClient.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $apiClient = new UsuariosApiClient();
    $usuario = $apiClient->login($email, $password);

    
    if ($usuario) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_rol'] = $usuario['nombre_rol'];
            $_SESSION['user_sucursal_id'] = $usuario['id_sucursal']; // ¡Importante!



        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => '¡Bienvenido de nuevo!, ',
            'toast' => true
        ];

        header("Location: ../public/index.php");
            exit();
    }


    header("Location: ../public/login.php?error=1");
    //header("Location: ../public/login.php");
    exit();
}