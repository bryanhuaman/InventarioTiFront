<?php require_once '../templates/header.php'; ?>

    <h1 class="h2">Dashboard</h1>

<?php
require_once __DIR__ . '/../api_clients/EquipoApiClient.php';
require_once __DIR__ . '/../api_clients/SucursalApiClient.php';

// Revisa si existe un mensaje flash en la sesión
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];

    // Genera el script de SweetAlert2
    $saludo = htmlspecialchars($alert['title'] ?? '');
    $nombreUsuario = htmlspecialchars($_SESSION['user_nombre'] ?? '');
    $fullTitle = "{$saludo} <b>{$nombreUsuario}</b>";

    echo "
    <script>
        Swal.fire({ // <-- CAMBIO AQUÍ: de mixin.fire a Swal.fire
            toast: " . ($alert['toast'] ? 'true' : 'false') . ",
            position: 'top',
            icon: '" . htmlspecialchars($alert['type']) . "',
            title: " . json_encode(trim($fullTitle)) . ",
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => { // Agregado para mejor UX en toasts
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    </script>";

    // Borra el mensaje de la sesión
    unset($_SESSION['alert']);
}

// --- FILTRO POR SUCURSAL PARA LAS ESTADÍSTICAS ---
$id_sucursal_usuario = $_SESSION['user_sucursal_id'];
$es_admin_general = ($id_sucursal_usuario === null);

$equipoApiClient = new EquipoApiClient();
$sucursalApiClient = new SucursalApiClient();

// --- DATOS PARA GRÁFICO 1 ---
$equipos_tipo_labels = [];
$equipos_tipo_data = [];

try {
    $dataEquiposTipo = $equipoApiClient->equiposPorTipo($es_admin_general ? null : $id_sucursal_usuario);

    foreach ($dataEquiposTipo as $item) {
        $equipos_tipo_labels[] = $item['tipoEquipo'];
        $equipos_tipo_data[] = $item['cantidad'];
    }
} catch (Exception $e) {
    error_log("Error obteniendo equipos por tipo: " . $e->getMessage());
}


// --- DATOS PARA GRÁFICO 2: EQUIPOS Y EMPLEADOS POR SUCURSAL (SOLO ADMIN GENERAL) ---

$sucursal_chart_labels = [];
$sucursal_equipos_data = [];
$sucursal_empleados_data = [];

try {
    //es admin general
    if ($es_admin_general) {
        // Llamada al endpoint de la API
        $dataSucursales = $sucursalApiClient->resumenPorSucursal();

        foreach ($dataSucursales as $item) {
            $sucursal_chart_labels[] = $item['sucursalnombre'];
            $sucursal_equipos_data[] = $item['totalEquipos'];
            $sucursal_empleados_data[] = $item['totalEmpleados'];
        }
    }
} catch (Exception $e) {
    error_log("Error obteniendo resumen por sucursal: " . $e->getMessage());
}

// --- TARJETAS DE ESTADÍSTICAS ---
//Total de equipos
try {
    // Si es admin general, idSucursal es null
    $idSucursal = $es_admin_general ? null : $id_sucursal_usuario;
    //Total equipos
    $total_equipos = $equipoApiClient->totalEquipos($idSucursal)['total'] ?? 0;
    //Total asignados
    $total_asignados = $equipoApiClient->totalEquiposAsignados($idSucursal)['total'] ?? 0;
    //Total disponibles
    $total_disponibles = $equipoApiClient->totalEquiposDisponibles($idSucursal)['total'] ?? 0;
} catch (Exception $e) {
    error_log("Error obteniendo total de equipos: " . $e->getMessage());
    $total_equipos = 0;
}

?>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h5 class="card-title">Total de
                                Equipos</h5>
                            <p class="card-text fs-2 fw-bold"><?php echo $total_equipos;?></p></div>
                        <i class="bi bi-hdd-stack display-4 opacity-50"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card text-dark bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h5 class="card-title">Equipos
                                Asignados</h5>
                            <p class="card-text fs-2 fw-bold"><?php echo $total_asignados; ?></p></div>
                        <i class="bi bi-person-check display-4 opacity-50"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h5 class="card-title">Equipos
                                Disponibles</h5>
                            <p class="card-text fs-2 fw-bold"><?php echo $total_disponibles; ?></p></div>
                        <i class="bi bi-box-seam display-4 opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header">Equipos Disponibles por Tipo</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <?php if (!empty($equipos_tipo_data)): ?>
                        <canvas id="equiposPorTipoChart"></canvas>
                    <?php else: ?>
                        <p class="text-muted">No hay datos para mostrar en el gráfico.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($es_admin_general): ?>
            <div class="col-lg-7 mb-4">
                <div class="card h-100">
                    <div class="card-header">Equipos y Empleados por Sucursal</div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <?php if (!empty($sucursal_chart_labels)): ?>
                            <canvas id="sucursalChart"></canvas>
                        <?php else: ?>
                            <p class="text-muted">No hay datos de sucursales para mostrar.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gráfico de Dona: Equipos por Tipo
            const ctxEquiposTipo = document.getElementById('equiposPorTipoChart');
            if (ctxEquiposTipo) {
                new Chart(ctxEquiposTipo, {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($equipos_tipo_labels); ?>,
                        datasets: [{
                            label: 'Cantidad',
                            data: <?php echo json_encode($equipos_tipo_data); ?>,
                            backgroundColor: ['rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)', 'rgba(75, 192, 192, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)'],
                            borderWidth: 2
                        }]
                    },
                    options: {responsive: true, maintainAspectRatio: false, plugins: {legend: {position: 'top'}}}
                });
            }

            // Gráfico de Barras: Equipos y Empleados por Sucursal
            const ctxSucursal = document.getElementById('sucursalChart');
            if (ctxSucursal) {
                new Chart(ctxSucursal, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($sucursal_chart_labels); ?>,
                        datasets: [
                            {
                                label: 'Nº de Equipos',
                                data: <?php echo json_encode($sucursal_equipos_data); ?>,
                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Nº de Empleados',
                                data: <?php echo json_encode($sucursal_empleados_data); ?>,
                                backgroundColor: 'rgba(255, 159, 64, 0.7)',
                                borderColor: 'rgba(255, 159, 64, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {precision: 0}
                            }
                        },
                        plugins: {
                            legend: {position: 'top'}
                        }
                    }
                });
            }
        });
    </script>

<?php require_once '../templates/footer.php'; ?>