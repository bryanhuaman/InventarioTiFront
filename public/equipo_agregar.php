<?php
require_once '../templates/header.php';

// Lógica para procesar el formulario de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sucursal_post = ($_SESSION['user_sucursal_id'] === null) ? $_POST['id_sucursal'] : $_SESSION['user_sucursal_id'];
    $id_tipo_equipo = $_POST['id_tipo_equipo'];
    $id_marca = $_POST['id_marca'];
    $id_modelo = $_POST['id_modelo'];
    $codigo_inventario = $_POST['codigo_inventario'];
    $numero_serie = $_POST['numero_serie'];
    $tipo_adquisicion = $_POST['tipo_adquisicion'];
    $caracteristicas = $_POST['caracteristicas'];
    $observaciones = $_POST['observaciones'];
    $fecha_adquisicion = !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null;
    $proveedor = $_POST['proveedor'];

    $sql_insert = "INSERT INTO equipos (id_sucursal, codigo_inventario, id_tipo_equipo, id_marca, id_modelo, numero_serie, tipo_adquisicion, caracteristicas, observaciones, fecha_adquisicion, proveedor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql_insert);
    $stmt->bind_param("isiiissssss", $id_sucursal_post, $codigo_inventario, $id_tipo_equipo, $id_marca, $id_modelo, $numero_serie, $tipo_adquisicion, $caracteristicas, $observaciones, $fecha_adquisicion, $proveedor);
    
    if ($stmt->execute()) {
        header("Location: equipos.php?status=success_add");
        exit();
    } else {
        $error_message = "Error al agregar el equipo: " . $stmt->error;
    }
    $stmt->close();
}

// Cargar catálogos para los menús desplegables
$tipos = $conexion->query("SELECT * FROM tipos_equipo WHERE estado = 'Activo' ORDER BY nombre");
$marcas = $conexion->query("SELECT * FROM marcas WHERE estado = 'Activo' ORDER BY nombre");
?>

<h1 class="h2 mb-4">Registrar Nuevo Equipo</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<form action="equipo_agregar.php" method="POST">
    <div class="row mb-3">
        <?php if ($_SESSION['user_sucursal_id'] === null): ?>
            <div class="col-md-6">
                <label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label>
                <select class="form-select" name="id_sucursal" required>
                    <option value="">Seleccione...</option>
                    <?php 
                    $sucursales = $conexion->query("SELECT * FROM sucursales WHERE estado = 'Activo' ORDER BY nombre");
                    while($sucursal = $sucursales->fetch_assoc()): ?>
                        <option value="<?php echo $sucursal['id']; ?>"><?php echo htmlspecialchars($sucursal['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="codigo_inventario" class="form-label">Código de Inventario <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="codigo_inventario" required>
            </div>
        <?php else: ?>
            <div class="col-md-6">
                <label for="codigo_inventario" class="form-label">Código de Inventario <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="codigo_inventario" required>
            </div>
            <div class="col-md-6">
                <label for="numero_serie" class="form-label">Número de Serie <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="numero_serie" required>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($_SESSION['user_sucursal_id'] === null): ?>
         <div class="row mb-3">
            <div class="col-md-6">
                <label for="numero_serie" class="form-label">Número de Serie <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="numero_serie" required>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Tipo de Equipo <span class="text-danger">*</span></label>
            <select class="form-select" name="id_tipo_equipo" required>
                <option value="">Seleccione...</option>
                <?php while($tipo = $tipos->fetch_assoc()): ?>
                    <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Marca <span class="text-danger">*</span></label>
            <select class="form-select" name="id_marca" id="selectMarca" required>
                <option value="">Seleccione...</option>
                <?php while($marca = $marcas->fetch_assoc()): ?>
                    <option value="<?php echo $marca['id']; ?>"><?php echo htmlspecialchars($marca['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Modelo <span class="text-danger">*</span></label>
            <select class="form-select" name="id_modelo" id="selectModelo" required disabled>
                <option value="">Seleccione una marca</option>
            </select>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Tipo de Adquisición <span class="text-danger">*</span></label>
            <select class="form-select" name="tipo_adquisicion" required>
                <option value="Propio">Propio</option>
                <option value="Arrendado">Arrendado</option>
                <option value="Prestamo">Préstamo</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Características</label>
            <textarea class="form-control" name="caracteristicas" rows="1"></textarea>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
            <input type="date" class="form-control" name="fecha_adquisicion">
        </div>
        <div class="col-md-6">
            <label for="proveedor" class="form-label">Proveedor</label>
            <input type="text" class="form-control" name="proveedor">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Observaciones</label>
        <textarea class="form-control" name="observaciones" rows="3"></textarea>
    </div>
    
    <hr class="my-4">
    <a href="equipos.php" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">Registrar Equipo</button>
</form>

<script>
document.getElementById('selectMarca').addEventListener('change', function() {
    const idMarca = this.value;
    const selectModelo = document.getElementById('selectModelo');
    
    selectModelo.innerHTML = '<option value="">Cargando...</option>';
    selectModelo.disabled = true;

    if (idMarca) {
        fetch(`../includes/api.php?action=getModelos&id_marca=${idMarca}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error de red o del servidor.');
                }
                return response.json();
            })
            .then(data => {
                selectModelo.innerHTML = '<option value="">Seleccione un modelo...</option>';
                if (data.length > 0) {
                    data.forEach(modelo => {
                        const option = new Option(modelo.nombre, modelo.id);
                        selectModelo.add(option);
                    });
                    selectModelo.disabled = false;
                } else {
                    selectModelo.innerHTML = '<option value="">No hay modelos activos para esta marca</option>';
                }
            })
            .catch(error => {
                console.error('Error al cargar los modelos:', error);
                selectModelo.innerHTML = '<option value="">Error al cargar modelos</option>';
            });
    } else {
        selectModelo.innerHTML = '<option value="">Seleccione una marca primero</option>';
    }
});
</script>

<?php require_once '../templates/footer.php'; ?>