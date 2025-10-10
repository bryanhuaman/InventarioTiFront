<?php
require_once '../templates/header.php';

$id_asignacion = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_asignacion) {
    header("Location: asignaciones.php");
    exit();
}

// Cargar todos los datos de la asignación y devolución
require_once __DIR__ . '/../api_clients/AsignacionApiClient.php';
$asignacionApi = new AsignacionApiClient();
try {
    $devolucion = $asignacionApi->obtenerAsingacionFinalizada($id_asignacion);
} catch (Exception $e) {
    // Manejar el error apropiadamente
    header("Location: asignaciones.php");
    exit();
}

if (!$devolucion) {
    // Si no se encuentra o no está finalizada, redirigir
    header("Location: asignaciones.php");
    exit();
}

$imagenes_adjuntas = array_filter([
    $devolucion['imagenDevolucion1'],
    $devolucion['imagenDevolucion2'],
    $devolucion['imagenDevolucion3']
]);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Detalle de Devolución</h1>
    <a href="asignaciones.php" class="btn btn-secondary">Volver al Historial</a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Información de la Devolución</div>
            <div class="card-body">
                <p><strong>Empleado:</strong> <?php echo htmlspecialchars($devolucion['apellidosEmpleado'] . ', ' . $devolucion['nombresEmpleado']); ?></p>
                <p><strong>Equipo:</strong> <?php echo htmlspecialchars($devolucion['codigoInventario'] . ' - ' . $devolucion['marcaNombre'] . ' ' . $devolucion['modeloNombre']); ?></p>
                <p><strong>Fecha de Devolución:</strong> <?php echo date('d/m/Y H:i', strtotime($devolucion['fechaDevolucion'])); ?></p>
                <hr>
                <p class="mb-1"><strong>Observaciones registradas:</strong></p>
                <blockquote class="blockquote">
                    <p class="mb-0 fst-italic">"<?php echo !empty($devolucion['observacionesDevolucion']) ? htmlspecialchars($devolucion['observacionesDevolucion']) : 'No se registraron observaciones.'; ?>"</p>
                </blockquote>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mt-4 mt-lg-0">
        <div class="card">
            <div class="card-header">Evidencia Fotográfica</div>
            <div class="card-body">
                <?php if (!empty($imagenes_adjuntas)): ?>
                    <div class="row">
                        <?php foreach ($imagenes_adjuntas as $imagen): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <a href="../uploads/devoluciones/<?php echo $id_asignacion . '/' . htmlspecialchars($imagen); ?>" target="_blank">
                                    <img src="../uploads/devoluciones/<?php echo $id_asignacion . '/' . htmlspecialchars($imagen); ?>" class="img-fluid rounded" alt="Evidencia de devolución">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No se adjuntaron imágenes para esta devolución.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>