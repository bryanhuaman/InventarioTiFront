<?php
require_once '../templates/header.php';

// Validar el ID del equipo
$id_equipo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_equipo) {
    header("Location: equipos.php");
    exit();
}

// Lógica para procesar la ACTUALIZACIÓN del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sucursal_post = $_POST['id_sucursal'];
    $id_tipo_equipo = $_POST['id_tipo_equipo'];
    $id_marca = $_POST['id_marca'];
    $id_modelo = $_POST['id_modelo'];
    $codigo_inventario = $_POST['codigo_inventario'];
    $numero_serie = $_POST['numero_serie'];
    $tipo_adquisicion = $_POST['tipo_adquisicion'];
    $caracteristicas = $_POST['caracteristicas'];
    $observaciones = $_POST['observaciones'];
    $estado = $_POST['estado'];
    $fecha_adquisicion = !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null;
    $proveedor = $_POST['proveedor'];

    $sql_update = "UPDATE equipos SET 
                    id_sucursal = ?, codigo_inventario = ?, id_tipo_equipo = ?, id_marca = ?, 
                    id_modelo = ?, numero_serie = ?, tipo_adquisicion = ?, caracteristicas = ?, 
                    observaciones = ?, estado = ?, fecha_adquisicion = ?, proveedor = ?
                   WHERE id = ?";
    
    $stmt = $conexion->prepare($sql_update);
    $stmt->bind_param("isiiisssssssi", 
        $id_sucursal_post, $codigo_inventario, $id_tipo_equipo, $id_marca, $id_modelo, 
        $numero_serie, $tipo_adquisicion, $caracteristicas, $observaciones, $estado, 
        $fecha_adquisicion, $proveedor, $id_equipo);
    
    if ($stmt->execute()) {
        header("Location: equipos.php?status=success_edit");
        exit();
    } else {
        $error_message = "Error al actualizar el equipo: " . $stmt->error;
    }
    $stmt->close();
}

// Cargar datos actuales del equipo para pre-llenar el formulario
$stmt_select = $conexion->prepare("SELECT * FROM equipos WHERE id = ?");
$stmt_select->bind_param("i", $id_equipo);
$stmt_select->execute();
$equipo = $stmt_select->get_result()->fetch_assoc();
$stmt_select->close();

if (!$equipo) {
    header("Location: equipos.php");
    exit();
}

// Cargar catálogos
$sucursales = $conexion->query("SELECT * FROM sucursales WHERE estado = 'Activo' ORDER BY nombre");
$tipos = $conexion->query("SELECT * FROM tipos_equipo WHERE estado = 'Activo' ORDER BY nombre");
$marcas = $conexion->query("SELECT * FROM marcas WHERE estado = 'Activo' ORDER BY nombre");
// Cargar solo los modelos correspondientes a la marca actual del equipo
$modelos = $conexion->query("SELECT * FROM modelos WHERE id_marca = " . (int)$equipo['id_marca'] . " AND estado = 'Activo' ORDER BY nombre");
?>

<h1 class="h2 mb-4">Editar Equipo</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<form action="equipo_editar.php?id=<?php echo $id_equipo; ?>" method="POST">
    <div class="row mb-3">
        <div class="col-md-6"><label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label><select class="form-select" name="id_sucursal" required><?php while($sucursal = $sucursales->fetch_assoc()): ?><option value="<?php echo $sucursal['id']; ?>" <?php echo ($sucursal['id'] == $equipo['id_sucursal']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sucursal['nombre']); ?></option><?php endwhile; ?></select></div>
        <div class="col-md-6"><label for="codigo_inventario" class="form-label">Código de Inventario <span class="text-danger">*</span></label><input type="text" class="form-control" name="codigo_inventario" required value="<?php echo htmlspecialchars($equipo['codigo_inventario']); ?>"></div>
    </div>
    <div class="row mb-3"><div class="col-md-6"><label for="numero_serie" class="form-label">Número de Serie <span class="text-danger">*</span></label><input type="text" class="form-control" name="numero_serie" required value="<?php echo htmlspecialchars($equipo['numero_serie']); ?>"></div><div class="col-md-6"><label for="estado" class="form-label">Estado del Equipo <span class="text-danger">*</span></label><select class="form-select" name="estado" required <?php echo ($equipo['estado'] == 'Asignado') ? 'disabled' : ''; ?>><option value="Disponible" <?php echo ($equipo['estado'] == 'Disponible') ? 'selected' : ''; ?>>Disponible</option><option value="En Reparacion" <?php echo ($equipo['estado'] == 'En Reparacion') ? 'selected' : ''; ?>>En Reparación</option><option value="De Baja" <?php echo ($equipo['estado'] == 'De Baja') ? 'selected' : ''; ?>>De Baja</option><?php if ($equipo['estado'] == 'Asignado'): ?><option value="Asignado" selected>Asignado (no se puede cambiar)</option><?php endif; ?></select></div></div>
    <div class="row mb-3"><div class="col-md-4"><label class="form-label">Tipo de Equipo <span class="text-danger">*</span></label><select class="form-select" name="id_tipo_equipo" required><?php while($tipo = $tipos->fetch_assoc()): ?><option value="<?php echo $tipo['id']; ?>" <?php echo ($tipo['id'] == $equipo['id_tipo_equipo']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($tipo['nombre']); ?></option><?php endwhile; ?></select></div><div class="col-md-4"><label class="form-label">Marca <span class="text-danger">*</span></label><select class="form-select" name="id_marca" id="selectMarca" required><?php while($marca = $marcas->fetch_assoc()): ?><option value="<?php echo $marca['id']; ?>" <?php echo ($marca['id'] == $equipo['id_marca']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($marca['nombre']); ?></option><?php endwhile; ?></select></div><div class="col-md-4"><label class="form-label">Modelo <span class="text-danger">*</span></label><select class="form-select" name="id_modelo" id="selectModelo" required><?php while($modelo = $modelos->fetch_assoc()): ?><option value="<?php echo $modelo['id']; ?>" <?php echo ($modelo['id'] == $equipo['id_modelo']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($modelo['nombre']); ?></option><?php endwhile; ?></select></div></div>
    <div class="row mb-3"><div class="col-md-6"><label class="form-label">Tipo de Adquisición <span class="text-danger">*</span></label><select class="form-select" name="tipo_adquisicion" required><option value="Propio" <?php echo ($equipo['tipo_adquisicion'] == 'Propio') ? 'selected' : ''; ?>>Propio</option><option value="Arrendado" <?php echo ($equipo['tipo_adquisicion'] == 'Arrendado') ? 'selected' : ''; ?>>Arrendado</option><option value="Prestamo" <?php echo ($equipo['tipo_adquisicion'] == 'Prestamo') ? 'selected' : ''; ?>>Préstamo</option></select></div><div class="col-md-6"><label class="form-label">Características</label><textarea class="form-control" name="caracteristicas" rows="1"><?php echo htmlspecialchars($equipo['caracteristicas'] ?? ''); ?></textarea></div></div>
    <div class="row mb-3"><div class="col-md-6"><label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label><input type="date" class="form-control" name="fecha_adquisicion" value="<?php echo htmlspecialchars($equipo['fecha_adquisicion'] ?? ''); ?>"></div><div class="col-md-6"><label for="proveedor" class="form-label">Proveedor</label><input type="text" class="form-control" name="proveedor" value="<?php echo htmlspecialchars($equipo['proveedor'] ?? ''); ?>"></div></div>
    <div class="mb-3"><label class="form-label">Observaciones</label><textarea class="form-control" name="observaciones" rows="3"><?php echo htmlspecialchars($equipo['observaciones'] ?? ''); ?></textarea></div>
    
    <hr class="my-4">
    <a href="equipos.php" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
</form>

<script>
document.getElementById('selectMarca').addEventListener('change', function() {
    const idMarca = this.value;
    const selectModelo = document.getElementById('selectModelo');
    selectModelo.innerHTML = '<option value="">Cargando...</option>';
    selectModelo.disabled = true;

    if (idMarca) {
        fetch(`../includes/api.php?action=getModelos&id_marca=${idMarca}`)
            .then(response => response.json())
            .then(data => {
                selectModelo.innerHTML = '<option value="">Seleccione un modelo...</option>';
                if (data.length > 0) {
                    data.forEach(modelo => {
                        const option = new Option(modelo.nombre, modelo.id);
                        selectModelo.add(option);
                    });
                    selectModelo.disabled = false;
                } else {
                    selectModelo.innerHTML = '<option value="">No hay modelos activos</option>';
                }
            });
    } else {
        selectModelo.innerHTML = '<option value="">Seleccione una marca</option>';
    }
});
</script>

<?php require_once '../templates/footer.php'; ?>