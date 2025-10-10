<?php
require_once '../config/database.php';
require_once __DIR__.'/../api_clients/ModeloApiClient.php';
require_once __DIR__.'/../api_clients/CargoApiClient.php';
require_once __DIR__.'/../api_clients/EmpleadoApiClient.php';
require_once __DIR__.'/../api_clients/EquipoApiClient.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

// Maneja la petición para obtener los modelos de una marca
if ($action == 'getModelos' && isset($_GET['id_marca'])) {
    $id_marca = (int)$_GET['id_marca'];
    $modeloApi = new ModeloApiClient();

    try {
        $modelos = $modeloApi->obtenerModelosPorMarca($id_marca);
        echo json_encode($modelos);
    }catch (Exception $e){
        echo json_encode(["error" => $e->getMessage()]);
    }

} 
// Maneja la petición para obtener los cargos de un área
else if ($action == 'getCargos' && isset($_GET['id_area'])) {
    $id_area = (int)$_GET['id_area'];
    $cargoApi = new CargoApiClient();

    try {
        $modelos = $cargoApi->obtenerCargosPorArea($id_area);
        echo json_encode($modelos);
    }catch (Exception $e){
        echo json_encode(["error" => $e->getMessage()]);
    }

}
// Obtener Empleados por Sucursal
else if ($action == 'getEmpleadosPorSucursal' && isset($_GET['id_sucursal'])) {
    $id_sucursal = (int)$_GET['id_sucursal'];
    $empleadoApiClient = new EmpleadoApiClient();

    try {
        $empleados = $empleadoApiClient->obtenerEmpleadosPorSucursal($id_sucursal);
        echo json_encode($empleados);
    }catch (Exception $e){
        echo json_encode(["error" => $e->getMessage()]);
    }
}
// Obtener Equipos por Sucursal
else if ($action == 'getEquiposPorSucursal' && isset($_GET['id_sucursal'])) {
    $id_sucursal = (int)$_GET['id_sucursal'];
    $equipoApiClient = new EquipoApiClient();

    try {
        $equipos = $equipoApiClient->obtenerEquipoPorSucursal($id_sucursal);
        echo json_encode($equipos);
    }catch (Exception $e){
        echo json_encode(["error" => $e->getMessage()]);
    }

}
else {
    echo json_encode(["error" => "Acción no válida o parámetros faltantes."]);
}
