<?php
require_once '../templates/header.php';

// Validar el ID
$id_empleado = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_empleado) {
    header("Location: empleados.php");
    exit();
}

// Lógica para procesar la ACTUALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sucursal_post = $_POST['id_sucursal'];
    $dni = $_POST['dni'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $id_cargo = $_POST['id_cargo'];
    $id_area = $_POST['id_area'];
    $estado = $_POST['estado'];

    $sql_update = "UPDATE empleados SET 
                    id_sucursal = ?, dni = ?, nombres = ?, apellidos = ?, 
                    id_cargo = ?, id_area = ?, estado = ?
                   WHERE id = ?";
    
    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("isssiisi", 
        $id_sucursal_post, $dni, $nombres, $apellidos, $id_cargo, $id_area, $estado, $id_empleado);
    
    if ($stmt->execute()) {
        header("Location: empleados.php?status=success_edit");
        exit();
    } else {
        $error_message = "Error al actualizar el empleado: " . $stmt->error;
    }
    $stmt->close();
}

// Cargar datos del empleado a editar
$stmt_select = $conexion->prepare("SELECT * FROM empleados WHERE id = ?");
$stmt_select->bind_param("i", $id_empleado);
$stmt_select->execute();
$empleado = $stmt_select->get_result()->fetch_assoc();
$stmt_select->close();

if (!$empleado) {
    header("Location: empleados.php");
    exit();
}

// Cargar catálogos
$sucursales = $conexion->query("SELECT * FROM sucursales ORDER BY nombre");
$areas = $conexion->query("SELECT * FROM areas ORDER BY nombre");
$cargos = $conexion->query("SELECT * FROM cargos WHERE id_area = " . (int)$empleado['id_area'] . " ORDER BY nombre");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Editar Empleado</h1>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<form action="empleado_editar.php?id=<?php echo $id_empleado; ?>" method="POST">
    <div class="row mb-3">
        <div class="col-md-12">
            <label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label>
            <select class="form-select" name="id_sucursal" required>
                <?php while($sucursal = $sucursales->fetch_assoc()): ?>
                    <option value="<?php echo $sucursal['id']; ?>" <?php echo ($sucursal['id'] == $empleado['id_sucursal']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sucursal['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4"><label class="form-label">DNI <span class="text-danger">*</span></label><input type="text" class="form-control" name="dni" required value="<?php echo htmlspecialchars($empleado['dni']); ?>"></div>
        <div class="col-md-4"><label class="form-label">Nombres <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombres" required value="<?php echo htmlspecialchars($empleado['nombres']); ?>"></div>
        <div class="col-md-4"><label class="form-label">Apellidos <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellidos" required value="<?php echo htmlspecialchars($empleado['apellidos']); ?>"></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Área <span class="text-danger">*</span></label>
            <select class="form-select" name="id_area" id="selectArea" required>
                <?php while($area = $areas->fetch_assoc()): ?>
                    <option value="<?php echo $area['id']; ?>" <?php echo ($area['id'] == $empleado['id_area']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($area['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Cargo <span class="text-danger">*</span></label>
            <select class="form-select" name="id_cargo" id="selectCargo" required>
                <?php while($cargo = $cargos->fetch_assoc()): ?>
                    <option value="<?php echo $cargo['id']; ?>" <?php echo ($cargo['id'] == $empleado['id_cargo']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cargo['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Estado <span class="text-danger">*</span></label>
            <select class="form-select" name="estado" required>
                <option value="Activo" <?php echo ($empleado['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="Inactivo" <?php echo ($empleado['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
    </div>
    
    <hr class="my-4">
    <a href="empleados.php" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
</form>

<script>
document.getElementById('selectArea').addEventListener('change', function() {
    // La misma lógica de script que en empleado_agregar.php
    const idArea = this.value;
    const selectCargo = document.getElementById('selectCargo');
    selectCargo.innerHTML = '<option value="">Cargando...</option>';
    selectCargo.disabled = true;

    if (idArea) {
        fetch(`../includes/api.php?action=getCargos&id_area=${idArea}`)
            .then(response => response.json())
            .then(data => {
                selectCargo.innerHTML = '<option value="">Seleccione...</option>';
                if (data.length > 0) {
                    data.forEach(cargo => {
                        const option = document.createElement('option');
                        option.value = cargo.id;
                        option.textContent = cargo.nombre;
                        selectCargo.appendChild(option);
                    });
                    selectCargo.disabled = false;
                } else {
                    selectCargo.innerHTML = '<option value="">No hay cargos para esta área</option>';
                }
            });
    } else {
        selectCargo.innerHTML = '<option value="">Seleccione un área primero</option>';
    }
});
</script>

<?php require_once '../templates/footer.php'; ?>