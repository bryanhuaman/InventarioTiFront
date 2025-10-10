<?php
require_once '../templates/header.php';

//MOSTAR MENSAJE DESPUES DE AGREGAR
if (isset($_SESSION['alert_message'])) {
    $msg = $_SESSION['alert_message'];
    echo '
    <script>
        toastMixin.fire({
            position: "top-end",
            icon: "' . addslashes($msg['type']) . '",   // success, error, warning, info, question
            title: "' . addslashes($msg['text']) . '",
            showConfirmButton: false,
            timer: 3000,
            toast: true
        });
    </script>
    ';


    unset($_SESSION['alert_message']); // Eliminar después de mostrar
}


$id_asignacion = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_asignacion) {
    header("Location: asignaciones.php");
    exit();
}

// Cargar datos de la asignación para mostrarlos
Require_once '../api_clients/AsignacionApiClient.php';
$asignacionApiClient = new AsignacionApiClient();
try {
    $asignacion = $asignacionApiClient->obtenerAsignacionDetalle($id_asignacion);
    if (!$asignacion) {
        header("Location: asignaciones.php");
        exit();
    }
} catch (Exception $e) {
    header("Location: asignaciones.php");
    exit();
}
//$sql = "SELECT
//            e.codigo_inventario, ma.nombre as marca_nombre, mo.nombre as modelo_nombre,
//            emp.nombres, emp.apellidos
//        FROM asignaciones a
//        JOIN equipos e ON a.id_equipo = e.id
//        JOIN empleados emp ON a.id_empleado = emp.id
//        JOIN marcas ma ON e.id_marca = ma.id
//        JOIN modelos mo ON e.id_modelo = mo.id
//        WHERE a.id = ?";
//$stmt = $conexion->prepare($sql);
//$stmt->bind_param("i", $id_asignacion);
//$stmt->execute();
//$asignacion = $stmt->get_result()->fetch_assoc();
//$stmt->close();

?>

<h1 class="h2 mb-4">Registrar Devolución de Equipo</h1>

<div class="card mb-4">
    <div class="card-header">Detalles de la Asignación</div>
    <div class="card-body">
        <p><strong>Empleado:</strong> <?php echo htmlspecialchars($asignacion['apellidos'] . ', ' . $asignacion['nombres']); ?></p>
        <p><strong>Equipo:</strong> <?php echo htmlspecialchars($asignacion['codigoInventario'] . ' - ' . $asignacion['marcaNombre'] . ' ' . $asignacion['modeloNombre']); ?></p>
    </div>
</div>

<form id="devolucionForm" action="../includes/procesar_devolucion.php" method="POST" class="mt-4" enctype="multipart/form-data">
    <input type="hidden" name="id_asignacion" value="<?php echo $id_asignacion; ?>">
    
    <div class="mb-3">
        <label for="estado_equipo_recibido" class="form-label">Estado en que se recibe el equipo <span class="text-danger">*</span></label>
        <select class="form-select" name="estado_equipo_recibido" required>
            <option value="Bueno">Bueno</option>
            <option value="Regular">Regular (con detalles)</option>
            <option value="Dañado">Dañado</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="observaciones_devolucion" class="form-label">Observaciones de la Devolución (Motivo)</label>
        <textarea class="form-control" name="observaciones_devolucion" rows="3" placeholder="Ej: El equipo presenta rayones en la carcasa, pero es funcional."></textarea>
    </div>

    <div class="mb-3">
        <label for="estado_final_inventario" class="form-label">Estado final del equipo en inventario <span class="text-danger">*</span></label>
        <select class="form-select" name="estado_final_inventario" required>
            <option value="Disponible">Disponible (para reasignar)</option>
            <option value="En Reparacion">Enviar a Reparación</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="imagenes_devolucion" class="form-label">Imágenes del estado del equipo (Opcional)</label>
        <input class="form-control mb-2" type="file" name="imagen_devolucion_1" accept="image/jpeg, image/png, image/gif">
        <input class="form-control mb-2" type="file" name="imagen_devolucion_2" accept="image/jpeg, image/png, image/gif">
        <input class="form-control" type="file" name="imagen_devolucion_3" accept="image/jpeg, image/png, image/gif">
        <div class="form-text">Puedes subir hasta 3 imágenes en formato JPG, PNG o GIF.</div>
    </div>

    <hr class="my-4">
    <a href="asignaciones.php" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-danger"><i class="bi bi-check-lg"></i> Confirmar Devolución</button>
</form>
    <script>
        document.getElementById("devolucionForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Evita el envío automático del formulario

            Swal.fire({
                title: "¿Estás seguro?",
                text: "No podrás revertir esta acción.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, confirmar"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar confirmación y luego enviar el formulario
                    // Swal.fire({
                    //     title: "Asignado!",
                    //     text: "Se asigno correctamente.",
                    //     icon: "success",
                    //     timer: 2000,
                    //     showConfirmButton: false
                    // });

                    // Enviar el formulario realmente
                    setTimeout(() => {
                        event.target.submit();
                    }, 1500);
                }
            });
        });
    </script>


<!--    <script>-->
<!--        document.getElementById('devolucionForm').addEventListener('submit', function(e) {-->
<!--            // Abrir PDF en nueva pestaña al hacer click en el botón-->
<!--            var id_asignacion = this.querySelector('input[name="id_asignacion"]').value;-->
<!--            window.open('../public/generar_acta_devolucion.php?id_asignacion=' + id_asignacion, '_blank');-->
<!--        });-->
<!--    </script>-->


<?php require_once '../templates/footer.php'; ?>