<?php
session_start();
require_once '../config/database.php';
require_once '../api_clients/AsignacionApiClient.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $fecha_entrega = date("Y-m-d");

    try {
        $asignacionApiClient = new AsignacionApiClient();
        // 1. Insertar el nuevo registro en la tabla de asignaciones
        $payload = [
            "idEquipo" => $_POST['id_equipo'],
            "idEmpleado" => $_POST['id_empleado'],
            "fechaEntrega" => $fecha_entrega,
            "observacionesEntrega" => $_POST['observaciones_entrega']
        ];

        $response = $asignacionApiClient->crearAsignacion($payload);

        // --- CAMBIO: Redirigir de vuelta al listado con el ID de la nueva asignación ---



            $_SESSION['alert_message'] = [
                'type' => $response['status'] === 201 ? 'success' : 'error',
                'text' => $response['mensaje']
            ];
            header("Location: ../public/asignaciones.php?new_id=". $response['idAsignacion']);
            exit();





        //header("Location: ../public/asignaciones.php?status=success_add&new_id=" . $response['idAsignacion']);
        //xit();

    } catch (Exception $e) {
        // Si ocurre una excepción (error de conexión, timeout, etc.)
        $_SESSION['alert_message'] = [
            'type' => 'error',
            'text' => 'Ocurrió un error inesperado: ' . $e->getMessage()
        ];
        header("Location: asignaciones.php");
        exit();
    }
}