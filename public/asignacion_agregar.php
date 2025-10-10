<?php
require_once '../templates/header.php';

$id_sucursal_usuario = $_SESSION['user_sucursal_id'];
$es_admin_general = ($id_sucursal_usuario === null);

$empleados = [];
$equipos = [];
if (!$es_admin_general) {
    Require_once '../api_clients/EmpleadoApiClient.php';
    $empleadoApiClient = new EmpleadoApiClient();
    Require_once '../api_clients/EquipoApiClient.php';
    $equipoApiClient = new EquipoApiClient();
    try {
        $empleados = $empleadoApiClient->obtenerEmpleadosPorSucursal($id_sucursal_usuario);
        $equipos = $equipoApiClient->obtenerEquipoPorSucursal($id_sucursal_usuario);
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error al cargar datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
Require_once '../api_clients/SucursalApiClient.php';
$sucursalApiClient = new SucursalApiClient();
$sucursales = $sucursalApiClient->listarSucursalesActivos();
?>

<h1 class="h2 mb-4">Asignar Equipo a Empleado</h1>

<form id="miFormulario" action="../includes/procesar_asignacion.php" method="POST">

    <?php if ($es_admin_general): ?>
    <div class="mb-3">
        <label for="selectSucursal" class="form-label">Seleccionar Sucursal <span class="text-danger">*</span></label>
        <select class="form-select" id="selectSucursal" name="id_sucursal" required>
            <option value="" selected>-- Primero selecciona una sucursal --</option>
            <?php foreach ($sucursales as $sucursal): ?>
                <option value="<?php echo $sucursal['id']; ?>"><?php echo htmlspecialchars($sucursal['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="selectEmpleado" class="form-label">Seleccionar Empleado <span class="text-danger">*</span></label>
        <select class="form-select" id="selectEmpleado" name="id_empleado" required <?php if ($es_admin_general) echo 'disabled'; ?>>
            <option value="" selected>-- <?php echo $es_admin_general ? 'Selecciona una sucursal' : 'Empleados en tu sucursal'; ?> --</option>
            <?php foreach ($empleados as $empleado): ?>
                <option value="<?php echo $empleado['id']; ?>"><?php echo htmlspecialchars($empleado['apellidos'] . ', ' . $empleado['nombres']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="selectEquipo" class="form-label">Seleccionar Equipo <span class="text-danger">*</span></label>
        <select class="form-select" id="selectEquipo" name="id_equipo" required <?php if ($es_admin_general) echo 'disabled'; ?>>
            <option value="" selected>-- <?php echo $es_admin_general ? 'Selecciona una sucursal' : 'Equipos en tu sucursal'; ?> --</option>
             <?php foreach ($equipos as $equipo): ?>
                <option value="<?php echo $equipo['id']; ?>"><?php echo htmlspecialchars($equipo['codigo_inventario'] . ' - ' . $equipo['marca_nombre'] . ' ' . $equipo['modelo_nombre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="observaciones_entrega" class="form-label">Observaciones de la Entrega</label>
        <textarea class="form-control" name="observaciones_entrega" rows="3" placeholder="Ej: Se entrega con cargador y maletín."></textarea>
    </div>

    <hr class="my-4">
    <a href="asignaciones.php" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Asignar Equipo</button>
</form>

    <script>
        document.getElementById("miFormulario").addEventListener("submit", function(event) {
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


<?php if ($es_admin_general): ?>
<script>
document.getElementById('selectSucursal').addEventListener('change', function() {
    const idSucursal = this.value;
    const selectEmpleado = document.getElementById('selectEmpleado');
    const selectEquipo = document.getElementById('selectEquipo');

    selectEmpleado.innerHTML = '<option value="">Cargando...</option>';
    selectEquipo.innerHTML = '<option value="">Cargando...</option>';
    selectEmpleado.disabled = true;
    selectEquipo.disabled = true;

    if (!idSucursal) {
        selectEmpleado.innerHTML = '<option value="">-- Selecciona una sucursal --</option>';
        selectEquipo.innerHTML = '<option value="">-- Selecciona una sucursal --</option>';
        return;
    }

    // Cargar Empleados
    fetch(`../includes/api.php?action=getEmpleadosPorSucursal&id_sucursal=${idSucursal}`)
        .then(response => response.json())
        .then(data => {
            selectEmpleado.innerHTML = '<option value="">-- Empleados en la sucursal --</option>';
            if (data.length > 0) {
                data.forEach(empleado => {
                    const option = new Option(`${empleado.apellidos}, ${empleado.nombres}`, empleado.id);
                    selectEmpleado.add(option);
                });
                selectEmpleado.disabled = false;
            } else {
                selectEmpleado.innerHTML = '<option value="">-- No hay empleados --</option>';
            }
        });

    // Cargar Equipos
    fetch(`../includes/api.php?action=getEquiposPorSucursal&id_sucursal=${idSucursal}`)
        .then(response => response.json())
        .then(data => {
            selectEquipo.innerHTML = '<option value="">-- Equipos en la sucursal --</option>';
            if (data.length > 0) {
                data.forEach(equipo => {
                    const option = new Option(`${equipo.codigo_inventario} - ${equipo.marca_nombre} ${equipo.modelo_nombre}`, equipo.id);
                    selectEquipo.add(option);
                });
                selectEquipo.disabled = false;
            } else {
                selectEquipo.innerHTML = '<option value="">-- No hay equipos --</option>';
            }
        });
});
</script>
<?php endif; ?>

<?php require_once '../templates/footer.php'; ?>