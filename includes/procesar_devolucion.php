<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_asignacion = $_POST['id_asignacion'];
    $observaciones_devolucion = $_POST['observaciones_devolucion'];
    $estado_final_inventario = $_POST['estado_final_inventario'];
    $fecha_devolucion = date("Y-m-d");
    $nombres_imagenes = []; // Array para guardar los nombres de los archivos

    $conexion->begin_transaction();
    try {
        // --- Lógica para procesar las imágenes subidas ---
        $upload_dir = '../uploads/devoluciones/';
        $devolucion_dir = $upload_dir . $id_asignacion . '/';
        if (!is_dir($devolucion_dir)) {
            mkdir($devolucion_dir, 0777, true);
        }

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        for ($i = 1; $i <= 3; $i++) {
            $file_input_name = 'imagen_devolucion_' . $i;
            if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {

                $file_tmp_path = $_FILES[$file_input_name]['tmp_name'];
                $file_name = basename($_FILES[$file_input_name]['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (in_array($file_ext, $allowed_types)) {
                    $new_file_name = 'devolucion_' . $i . '_' . time() . '.' . $file_ext;
                    $dest_path = $devolucion_dir . $new_file_name;
                    if (move_uploaded_file($file_tmp_path, $dest_path)) {
                        $nombres_imagenes[$i] = $new_file_name;
                    }
                }
            }
        }

        // Obtener el id_equipo
        Require_once '../api_clients/AsignacionApiClient.php';
        $apiClient = new AsignacionApiClient();
        $asignacionData = $apiClient->obtenerEquipoId($id_asignacion);
        $id_equipo = $asignacionData['equipoId'];

//        $stmt_get = $conexion->prepare("SELECT id_equipo FROM asignaciones WHERE id = ?");
//        $stmt_get->bind_param("i", $id_asignacion);
//        $stmt_get->execute();
//        $id_equipo = $stmt_get->get_result()->fetch_assoc()['id_equipo'];
//        $stmt_get->close();

        // Se asignan los valores a variables antes de usarlas en bind_param
        $img1 = $nombres_imagenes[1] ?? null;
        $img2 = $nombres_imagenes[2] ?? null;
        $img3 = $nombres_imagenes[3] ?? null;

        // Actualizar la asignación con los detalles y las rutas de las imágenes
        Require_once '../api_clients/AsignacionApiClient.php';
        $apiClient = new AsignacionApiClient();
        $payload = [
            'fechaDevolucion' => $fecha_devolucion,
            'observacionesDevolucion' => $observaciones_devolucion,
            'imagenDevolucion1' => $img1,
            'imagenDevolucion2' => $img2,
            'imagenDevolucion3' => $img3
        ];
        $responseA =$apiClient->realizarDevolucion($id_asignacion, $payload);
//        $sql_update_asignacion = "UPDATE asignaciones SET
//                                    fecha_devolucion = ?,
//                                    estado_asignacion = 'Finalizada',
//                                    observaciones_devolucion = ?,
//                                    imagen_devolucion_1 = ?,
//                                    imagen_devolucion_2 = ?,
//                                    imagen_devolucion_3 = ?
//                                  WHERE id = ?";
//        $stmt_asignacion = $conexion->prepare($sql_update_asignacion);
//        $stmt_asignacion->bind_param("sssssi",
//            $fecha_devolucion,
//            $observaciones_devolucion,
//            $img1,
//            $img2,
//            $img3,
//            $id_asignacion
//        );
//        $stmt_asignacion->execute();
//        $stmt_asignacion->close();

        // Actualizar el estado del equipo en el inventario
        Require_once '../api_clients/EquipoApiClient.php';
        $equipoApiClient = new EquipoApiClient();
        $payloadEquipo = [
            'estado' => $estado_final_inventario
        ];
        $responseE = $equipoApiClient->actualizarEstadoEquipo($id_equipo, $payloadEquipo);
//        $sql_update_equipo = "UPDATE equipos SET estado = ? WHERE id = ?";
//        $stmt_equipo = $conexion->prepare($sql_update_equipo);
//        $stmt_equipo->bind_param("si", $estado_final_inventario, $id_equipo);
//        $stmt_equipo->execute();
//        $stmt_equipo->close();
//
//        $conexion->commit();



        $_SESSION['alert_message'] = [
            'type' => $responseA['status'] === 200 && $responseE['status'] === 200 ? 'success' : 'error',
            'text' => $responseA['mensaje']
        ];

        //header("Location: ../public/asignaciones.php?status=success_return&return_id=" . $id_asignacion);

        // Generar JS para abrir el PDF y redirigir la página
        $pdf_url = "../public/generar_acta_devolucion.php?id_asignacion=" . $id_asignacion;
        $redirigir_url = "../public/asignaciones.php?status=success_return&return_id=" . $id_asignacion;

        echo "<script>
        // Abrir PDF en nueva pestaña
        window.open('$pdf_url', '_blank');

        // Redirigir la página actual después de un breve retraso (ej. 200 milisegundos)
        setTimeout(function() {
            window.location.href = '$redirigir_url';
        }, 200); 
    </script>";
        exit();

    } catch (mysqli_sql_exception $exception) {
        $conexion->rollback();
        header("Location: ../public/asignaciones.php?status=devolucion_error&msg=" . urlencode($exception->getMessage()));
        exit();
    } catch (Exception $exception) {
        header("Location: ../public/asignaciones.php?status=devolucion_error&msg=" . urlencode($exception->getMessage()));
        exit();
    }
}
?>