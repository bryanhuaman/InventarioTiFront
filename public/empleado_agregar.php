<?php
require_once '../templates/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sucursal_post = ($_SESSION['user_sucursal_id'] === null) ? $_POST['id_sucursal'] : $_SESSION['user_sucursal_id'];
    $dni = $_POST['dni'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $id_cargo = $_POST['id_cargo'];
    $id_area = $_POST['id_area'];
    $sql_insert = "INSERT INTO empleados (id_sucursal, dni, nombres, apellidos, id_cargo, id_area) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql_insert);
    $stmt->bind_param("isssii", $id_sucursal_post, $dni, $nombres, $apellidos, $id_cargo, $id_area);
    if ($stmt->execute()) { header("Location: empleados.php?status=success_add"); exit(); }
    else { $error_message = "Error al agregar el empleado: " . $stmt->error; }
    $stmt->close();
}
$areas = $conexion->query("SELECT * FROM areas WHERE estado = 'Activo' ORDER BY nombre");
?>

<h1 class="h2 mb-4">Registrar Nuevo Empleado</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<form action="empleado_agregar.php" method="POST">
     <?php if ($_SESSION['user_sucursal_id'] === null): ?>
        <div class="row mb-3"><div class="col-md-12"><label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label><select class="form-select" name="id_sucursal" required><option value="">Seleccione...</option><?php $sucursales = $conexion->query("SELECT * FROM sucursales WHERE estado = 'Activo' ORDER BY nombre"); while($sucursal = $sucursales->fetch_assoc()): ?><option value="<?php echo $sucursal['id']; ?>"><?php echo htmlspecialchars($sucursal['nombre']); ?></option><?php endwhile; ?></select></div></div>
    <?php endif; ?>
    <div class="row mb-3"><div class="col-md-4"><label class="form-label">DNI <span class="text-danger">*</span></label><input type="text" class="form-control" name="dni" required></div><div class="col-md-4"><label class="form-label">Nombres <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombres" required></div><div class="col-md-4"><label class="form-label">Apellidos <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellidos" required></div></div>
    <div class="row mb-3"><div class="col-md-6"><label class="form-label">Área <span class="text-danger">*</span></label><select class="form-select" name="id_area" id="selectArea" required><option value="">Seleccione un área...</option><?php while($area = $areas->fetch_assoc()): ?><option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre']); ?></option><?php endwhile; ?></select></div><div class="col-md-6"><label class="form-label">Cargo <span class="text-danger">*</span></label><select class="form-select" name="id_cargo" id="selectCargo" required disabled><option value="">Seleccione un área primero</option></select></div></div>
    <hr class="my-4"><a href="empleados.php" class="btn btn-secondary">Cancelar</a><button type="submit" class="btn btn-primary">Registrar Empleado</button>
</form>

<script>
document.getElementById('selectArea').addEventListener('change', function() {
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
                        const option = new Option(cargo.nombre, cargo.id);
                        selectCargo.add(option);
                    });
                    selectCargo.disabled = false;
                } else {
                    selectCargo.innerHTML = '<option value="">No hay cargos</option>';
                }
            });
    } else {
        selectCargo.innerHTML = '<option value="">Seleccione un área</option>';
    }
});
</script>

<?php require_once '../templates/footer.php'; ?>