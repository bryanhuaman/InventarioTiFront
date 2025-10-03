<?php
require_once '../templates/header.php';

// --- LÓGICA PARA CONSTRUIR LA CONSULTA SQL DINÁMICAMENTE ---
$id_sucursal_usuario = $_SESSION['user_sucursal_id'];

require_once __DIR__.'/../api_clients/AsignacionApiClient.php';
$asignacionesapi = new AsignacionApiClient();
// Captura filtros desde GET
$filtros = [
        'sucursalId'      => $id_sucursal_usuario ?? ($_GET['sucursal'] ?? null), // 🔹 si el usuario tiene sucursal fija, se fuerza
        'empleadoId'          => $_GET['empleado'] ?? null,
        'equipoId'         => $_GET['equipo'] ?? null,
        'estado'          => $_GET['estado'] ?? null,
        'fechaDesde'      => $_GET['fecha_desde'] ?? null,
        'fechaHasta'      => $_GET['fecha_hasta'] ?? null
];

$filtro_sucursal       = $_GET['sucursal'] ?? '';
$filtro_empleado           = $_GET['empleado'] ?? '';
$filtro_equipo          = $_GET['equipo'] ?? '';
$filtro_estado         = $_GET['estado'] ?? '';
$filtro_fecha_desde    = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta    = $_GET['fecha_hasta'] ?? '';


// Filtra valores vacíos o nulos
$filtros = array_filter($filtros, fn($v) => !is_null($v) && $v !== '');

// Llamada a la API
try {
    $asignaciones = $asignacionesapi->filtrar($filtros);
} catch (Exception $e) {
    die("Error consumiendo API: " . $e->getMessage());
}

// Cargar catálogos para los dropdowns de filtros
require_once __DIR__.'/../api_clients/SucursalApiClient.php';
$sucursalApiClient = new SucursalApiClient();
$sucursales = $sucursalApiClient->listarSucursalesActivos();

require_once __DIR__ . '/../api_clients/EmpleadoApiClient.php';
$empleadoApiClient = new EmpleadoApiClient();
$empleados = $empleadoApiClient->listarEmpleadoActivos();

require_once __DIR__.'/../api_clients/EquipoApiClient.php';
$equipoApiClient = new EquipoApiClient();
$equipos = $equipoApiClient->listarEquiposActivos();

?>

    <h1 class="h2 mb-3">Historial de Asignaciones</h1>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel-fill"></i> Filtros y Reportes</div>
        <div class="card-body">
            <form action="asignaciones.php" method="GET">
                <div class="row g-3">
                    <?php if ($id_sucursal_usuario === null): ?>
                        <div class="col-md-4">
                            <label class="form-label">Sucursal</label>
                            <select class="form-select form-select-sm" name="sucursal">
                                <option value="">Todas</option>
                                <?php foreach ($sucursales as $s): {
                                    echo "<option value='{$s['id']}' " . ($filtro_sucursal == $s['id'] ? 'selected' : '') . ">" . htmlspecialchars($s['nombre']) . "</option>";
                                } endforeach;?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <label class="form-label">Empleado</label>
                        <select class="form-select form-select-sm" name="empleado">
                            <option value="">Todos</option>
                            <?php foreach ($empleados as $e): {
                                echo "<option value='{$e['id']}' " . ($filtro_empleado == $e['id'] ? 'selected' : '') . ">" . htmlspecialchars($e['apellidos'] . ', ' . $e['nombres']) . "</option>";
                            } endforeach;?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Equipo (Código)</label>
                        <select class="form-select form-select-sm" name="equipo">
                            <option value="">Todos</option>
                            <?php foreach ($equipos as $eq): {
                                echo "<option value='{$eq['id']}' " . ($filtro_equipo == $eq['id'] ? 'selected' : '') . ">" . htmlspecialchars($eq['codigoInventario']) . "</option>";
                            } endforeach;?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado Asignación</label>
                        <select class="form-select form-select-sm" name="estado">
                            <option value="">Todos</option>
                            <option value="Activa" <?php if ($filtro_estado == 'Activa') echo 'selected'; ?>>Activa
                            </option>
                            <option value="Finalizada" <?php if ($filtro_estado == 'Finalizada') echo 'selected'; ?>>
                                Finalizada
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Entrega (Desde)</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_desde"
                               value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Entrega (Hasta)</label>
                        <input type="date" class="form-control form-control-sm" name="fecha_hasta"
                               value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">Filtrar</button>
                        <a href="asignaciones.php" class="btn btn-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>
            <hr>
            <div class="d-flex gap-2">
                <button id="export-excel" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Excel
                </button>
                <button id="export-pdf" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
                <button id="export-print" class="btn btn-info"><i class="bi bi-printer"></i> Imprimir</button>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4">Listado de Asignaciones</h2>
        <a href="asignacion_agregar.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Nueva Asignación</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-asignaciones" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Equipo</th>
                        <th>Fecha Entrega</th>
                        <th>Fecha Devolución</th>
                        <th>Estado</th>
                        <th>Acta Entrega</th>
                        <th>Acta Devolución</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($asignaciones) && is_array($asignaciones)): ?>
                        <?php foreach ($asignaciones as $asignacion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($asignacion['apellidosEmpleado'] . ', ' . $asignacion['nombresEmpleado']); ?></td>
                                <td><?php echo htmlspecialchars($asignacion['codigoInventario'] . ' (' . $asignacion['marcaNombre'] . ')'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($asignacion['fechaEntrega'])); ?></td>
                                <td><?php echo $asignacion['fechaDevolucion'] ? date('d/m/Y', strtotime($asignacion['fechaDevolucion'])) : '<span class="text-muted">---</span>'; ?></td>
                                <td>
                                    <span class="badge <?php echo $asignacion['estadoAsignacion'] === 'Activa' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo htmlspecialchars($asignacion['estadoAsignacion']); ?></span>
                                </td>
                                <td>
                                    <?php if ($asignacion['actaFirmadaPath']): ?>
                                        <a href="../uploads/actas/<?php echo htmlspecialchars($asignacion['actaFirmadaPath']); ?>"
                                           target="_blank" class="btn btn-info btn-sm" title="Ver Acta"><i
                                                    class="bi bi-file-earmark-pdf-fill"></i></a>
                                    <?php else: ?>
                                        <a href="asignacion_subir_acta.php?id=<?php echo $asignacion['idAsignacion']; ?>"
                                           class="btn btn-outline-primary btn-sm" title="Subir Acta"><i
                                                    class="bi bi-upload"></i></a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($asignacion['estadoAsignacion'] === 'Finalizada'): ?>
                                        <?php if ($asignacion['actaDevolucionPath']): ?>
                                            <a href="../uploads/actas_devolucion/<?php echo htmlspecialchars($asignacion['actaDevolucionPath']); ?>"
                                               target="_blank" class="btn btn-info btn-sm" title="Ver Acta"><i
                                                        class="bi bi-file-earmark-pdf-fill"></i></a>
                                        <?php else: ?>
                                            <a href="asignacion_subir_acta_devolucion.php?id=<?php echo $asignacion['idAsignacion']; ?>"
                                               class="btn btn-outline-danger btn-sm" title="Subir Acta"><i
                                                        class="bi bi-upload"></i></a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">---</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="generar_acta.php?id_asignacion=<?php echo $asignacion['idAsignacion']; ?>"
                                           target="_blank" class="btn btn-secondary btn-sm"
                                           title="Imprimir Acta Entrega"><i class="bi bi-printer"></i></a>
                                        <?php if ($asignacion['estadoAsignacion'] === 'Activa'): ?>
                                            <a href="asignacion_devolver.php?id=<?php echo $asignacion['idAsignacion']; ?>"
                                               class="btn btn-danger btn-sm" title="Registrar Devolución"><i
                                                        class="bi bi-arrow-return-left"></i></a>
                                        <?php else: ?>
                                            <a href="asignacion_detalle_devolucion.php?id=<?php echo $asignacion['idAsignacion']; ?>"
                                               class="btn btn-primary btn-sm" title="Ver Detalle"><i
                                                        class="bi bi-eye"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            var table = $('#tabla-asignaciones').DataTable({
                "language": {"url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"},
                "dom": 'rt<"d-flex justify-content-between"ip>',
                "buttons": ['excelHtml5', 'pdfHtml5', 'print']
            });
            $('#export-excel').on('click', function () {
                table.button('.buttons-excel').trigger();
            });
            $('#export-pdf').on('click', function () {
                table.button('.buttons-pdf').trigger();
            });
            $('#export-print').on('click', function () {
                table.button('.buttons-print').trigger();
            });
        });
    </script>

<?php require_once '../templates/footer.php'; ?>