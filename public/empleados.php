<?php
require_once '../templates/header.php';

// --- LÓGICA PARA CONSTRUIR LA CONSULTA SQL DINÁMICAMENTE ---
$id_sucursal_usuario = $_SESSION['user_sucursal_id'];

require_once __DIR__ . '/../api_clients/EmpleadoApiClient.php';
$empleadoapi = new EmpleadoApiClient();

// Captura filtros desde GET
$filtros = [
        'sucursalId'      => $id_sucursal_usuario ?? ($_GET['sucursal'] ?? null), // 🔹 si el usuario tiene sucursal fija, se fuerza
        'areaId'         => $_GET['area'] ?? null,
        'cargoId'        => $_GET['cargo'] ?? null,
        'estado'          => $_GET['estado'] ?? null,
        'texto'           => $_GET['texto'] ?? null,
];

$filtro_texto       = $_GET['sucursal'] ?? '';
$filtro_sucursal   = $_GET['sucursal'] ?? ($id_sucursal_usuario ?? '');
$filtro_area      = $_GET['area'] ?? '';
$filtro_cargo     = $_GET['cargo'] ?? '';
$filtro_estado      = $_GET['estado'] ?? '';

// Filtra valores vacíos o nulos
$filtros = array_filter($filtros, fn($v) => !is_null($v) && $v !== '');

// Llamada a la API
try {
    $empleados = $empleadoapi->filtrar($filtros);
} catch (Exception $e) {
    die("Error consumiendo API: " . $e->getMessage());
}

require_once __DIR__ . '/../api_clients/SucursalApiClient.php';
$sucursalApiClient = new SucursalApiClient();
$sucursales = $sucursalApiClient->listarSucursalesActivos();

require_once __DIR__.'/../api_clients/AreaApiClient.php';
$areaApiClient = new AreaApiClient();
$areas = $areaApiClient->listarActivas();

require_once __DIR__ . '/../api_clients/CargoApiClient.php';
$cargoApiClient = new CargoApiClient();
$cargos = $cargoApiClient->listarActivos();
?>

    <h1 class="h2 mb-3">Gestión de Empleados</h1>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel-fill"></i> Filtros y Reportes</div>
        <div class="card-body">
            <form action="empleados.php" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Buscar por DNI o Nombre</label>
                        <input type="text" class="form-control form-control-sm" name="texto"
                               value="<?php echo htmlspecialchars($filtro_texto); ?>">
                    </div>
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
                        <label class="form-label">Área</label>
                        <select class="form-select form-select-sm" name="area">
                            <option value="">Todas</option>
                            <?php foreach ($areas as $a): {
                                echo "<option value='{$a['id']}' " . ($filtro_area == $a['id'] ? 'selected' : '') . ">" . htmlspecialchars($a['nombre']) . "</option>";
                            } endforeach;?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cargo</label>
                        <select class="form-select form-select-sm" name="cargo">
                            <option value="">Todos</option>
                            <?php foreach ($cargos as $c): {
                                echo "<option value='{$c['id']}' " . ($filtro_cargo == $c['id'] ? 'selected' : '') . ">" . htmlspecialchars($c['nombre']) . "</option>";
                            } endforeach;?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select class="form-select form-select-sm" name="estado">
                            <option value="">Todos</option>
                            <option value="Activo" <?php if ($filtro_estado == 'Activo') echo 'selected'; ?>>Activo
                            </option>
                            <option value="Inactivo" <?php if ($filtro_estado == 'Inactivo') echo 'selected'; ?>>
                                Inactivo
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">Filtrar</button>
                        <a href="empleados.php" class="btn btn-secondary btn-sm">Limpiar</a>
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
        <h2 class="h4">Listado de Empleados</h2>
        <a href="empleado_agregar.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo
            Empleado</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-empleados" class="table table-striped table-hover" style="width:100%">
                    <thead>
                    <tr>
                        <?php if ($id_sucursal_usuario === null) echo '<th>Sucursal</th>'; ?>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Cargo</th>
                        <th>Área</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($empleados) && is_array($empleados)): ?>
                        <?php foreach ($empleados as $empleado): ?>
                            <tr>
                                <?php if ($id_sucursal_usuario === null) echo '<td>' . htmlspecialchars($empleado['sucursalNombre']) . '</td>'; ?>
                                <td><?php echo htmlspecialchars($empleado['dni']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['apellidos'] . ', ' . $empleado['nombres']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['cargoNombre'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['areaNombre'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge <?php echo $empleado['estado'] === 'Activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo htmlspecialchars($empleado['estado']); ?></span>
                                </td>
                                <td><a href="empleado_editar.php?id=<?php echo $empleado['id']; ?>"
                                       class="btn btn-warning btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
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
            var table = $('#tabla-empleados').DataTable({
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