<?php
require_once '../templates/header.php';
require_once __DIR__ . '/../api_clients/EquipoApiClient.php';
require_once __DIR__ . '/../api_clients/SucursalApiClient.php';
require_once __DIR__ . '/../api_clients/TipoEquipoApiClient.php';
require_once __DIR__ . '/../api_clients/MarcaApiClient.php';



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

// --- LÓGICA PARA CONSTRUIR LA CONSULTA SQL DINÁMICAMENTE ---
$id_sucursal_usuario = $_SESSION['user_sucursal_id'];

$equipoApi = new EquipoApiClient();
// Captura filtros desde GET
$filtros = [
        'sucursalId'      => $id_sucursal_usuario ?? ($_GET['sucursal'] ?? null), // 🔹 si el usuario tiene sucursal fija, se fuerza
        'tipoId'          => $_GET['tipo'] ?? null,
        'marcaId'         => $_GET['marca'] ?? null,
        'estado'          => $_GET['estado'] ?? null,
        'fechaDesde'      => $_GET['fecha_desde'] ?? null,
        'fechaHasta'      => $_GET['fecha_hasta'] ?? null,
        'codigoInventario'=> $_GET['codigo_inventario'] ?? null,
        'numeroSerie'     => $_GET['numero_serie'] ?? null,
];

$filtro_sucursal       = $_GET['sucursal'] ?? '';
$filtro_tipo           = $_GET['tipo'] ?? '';
$filtro_marca          = $_GET['marca'] ?? '';
$filtro_estado         = $_GET['estado'] ?? '';
$filtro_fecha_desde    = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta    = $_GET['fecha_hasta'] ?? '';
$filtro_codigo         = $_GET['codigo_inventario'] ?? '';
$filtro_serie          = $_GET['numero_serie'] ?? '';

// Filtra valores vacíos o nulos
$filtros = array_filter($filtros, fn($v) => !is_null($v) && $v !== '');

// Llamada a la API
try {
    $equipos = $equipoApi->filtrar($filtros);
} catch (Exception $e) {
    die("Error consumiendo API: " . $e->getMessage());
}

$sucursalApiClient = new SucursalApiClient();
$sucursales = $sucursalApiClient->listarSucursalesActivos();

$tipoEquipoApiClient = new TipoEquipoApiClient();
$tipos = $tipoEquipoApiClient->listarTipoEquipoActivos();

$marcaApiClient = new MarcaApiClient();
$marcas = $marcaApiClient->listarMarcasActivos();

?>

    <h1 class="h2 mb-3">Gestión de Equipos</h1>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel-fill"></i> Filtros y Reportes</div>
        <div class="card-body">
            <form action="equipos.php" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Código de Inventario</label>
                        <input type="text" class="form-control form-control-sm" name="codigo_inventario"
                               value="<?php echo htmlspecialchars($filtro_codigo); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Número de Serie</label>
                        <input type="text" class="form-control form-control-sm" name="numero_serie"
                               value="<?php echo htmlspecialchars($filtro_serie); ?>">
                    </div>
                    <?php if ($id_sucursal_usuario === null): ?>
                    <div class="col-md-3"><label class="form-label">Sucursal</label><select
                                class="form-select form-select-sm" name="sucursal">
                            <option value="">Todas</option><?php foreach ($sucursales as $s): {
                                echo "<option value='{$s['id']}' " . ($filtro_sucursal == $s['id'] ? 'selected' : '') . ">" . htmlspecialchars($s['nombre']) . "</option>";
                            } endforeach;?></select></div>
                    <?php endif; ?>
                    <div class="col-md-3"><label class="form-label">Tipo de Equipo</label><select
                                class="form-select form-select-sm" name="tipo">
                            <option value="">Todos</option><?php foreach ($tipos as $t): {
                                echo "<option value='{$t['id']}' " . ($filtro_tipo == $t['id'] ? 'selected' : '') . ">" . htmlspecialchars($t['nombre']) . "</option>";
                            } endforeach;?></select></div>
                    <div class="col-md-3"><label class="form-label">Marca</label><select
                                class="form-select form-select-sm" name="marca">
                            <option value="">Todas</option><?php foreach ($marcas as $m): {
                                echo "<option value='{$m['id']}' " . ($filtro_marca == $m['id'] ? 'selected' : '') . ">" . htmlspecialchars($m['nombre']) . "</option>";
                            } endforeach;?></select></div>
                    <div class="col-md-3"><label class="form-label">Estado</label><select
                                class="form-select form-select-sm" name="estado">
                            <option value="">Todos</option>
                            <option value="Disponible" <?php if ($filtro_estado == 'Disponible') echo 'selected'; ?>>
                                Disponible
                            </option>
                            <option value="Asignado" <?php if ($filtro_estado == 'Asignado') echo 'selected'; ?>>
                                Asignado
                            </option>
                            <option value="En Reparacion" <?php if ($filtro_estado == 'En Reparacion') echo 'selected'; ?>>
                                En Reparación
                            </option>
                            <option value="De Baja" <?php if ($filtro_estado == 'De Baja') echo 'selected'; ?>>De Baja
                            </option>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Fecha Adquisición (Desde)</label><input type="date"
                                                                                                            class="form-control form-control-sm"
                                                                                                            name="fecha_desde"
                                                                                                            value="<?php echo htmlspecialchars($filtro_fecha_desde); ?>">
                    </div>
                    <div class="col-md-3"><label class="form-label">Fecha Adquisición (Hasta)</label><input type="date"
                                                                                                            class="form-control form-control-sm"
                                                                                                            name="fecha_hasta"
                                                                                                            value="<?php echo htmlspecialchars($filtro_fecha_hasta); ?>">
                    </div>

                    <div class="col-md-12 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">Filtrar</button>
                        <a href="equipos.php" class="btn btn-secondary btn-sm">Limpiar</a>
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
        <h2 class="h4">Inventario Actual</h2>
        <a href="equipo_agregar.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo
            Equipo</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-equipos" class="table table-striped table-hover" style="width:100%">
                    <thead>
                    <tr>
                        <?php if ($id_sucursal_usuario === null) echo '<th>Sucursal</th>'; ?>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Marca / Modelo</th>
                        <th>N/S</th>
                        <th>Fecha Adquisición</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($equipos) && is_array($equipos)): ?>
                        <?php foreach ($equipos as $equipo): ?>
                            <tr>
                                <?php if ($id_sucursal_usuario === null) echo '<td>' . htmlspecialchars($equipo['sucursalNombre']) . '</td>'; ?>
                                <td><?php echo htmlspecialchars($equipo['codigoInventario']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['tipoNombre']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['marcaNombre'] . ' ' . $equipo['modeloNombre']); ?></td>
                                <td><?php echo htmlspecialchars($equipo['numeroSerie']); ?></td>
                                <td><?php echo $equipo['fechaAdquisicion'] ? date('d/m/Y', strtotime($equipo['fechaAdquisicion'])) : 'N/A'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo($equipo['estado'] == 'Disponible' ? 'success' : ($equipo['estado'] == 'Asignado' ? 'warning text-dark' : 'info text-dark')); ?>"><?php echo htmlspecialchars($equipo['estado']); ?></span>
                                </td>
                                <td><a href="equipo_editar.php?id=<?php echo $equipo['id']; ?>"
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
            var table = $('#tabla-equipos').DataTable({
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