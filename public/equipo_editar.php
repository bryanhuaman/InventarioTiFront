<?php
require_once '../templates/header.php';

// Validar el ID del equipo
$id_equipo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_equipo) {
    header("Location: equipos.php");
    exit();
}

require_once '../api_clients/EquipoApiClient.php';
$equipoApiClient = new EquipoApiClient();
// Lógica para procesar la ACTUALIZACIÓN del formulario

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Capturar datos del formulario
    $payload = [
            "codigoInventario" => $_POST['codigo_inventario'],
            "numeroSerie" => $_POST['numero_serie'],
            "caracteristicas" => $_POST['caracteristicas'],
            "tipoAdquisicion" => $_POST['tipo_adquisicion'],
            "fechaAdquisicion" => !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null,
            "proveedor" => $_POST['proveedor'],
            "observaciones" => $_POST['observaciones'],
            "estado" => $_POST['estado'],
            "fechaRegistro" => date("Y-m-d"),
            "idSucursal" => $_POST['id_sucursal'],
            "idMarca" => (int)$_POST['id_marca'],
            "idModelo" => (int)$_POST['id_modelo'],
            "idTipoEquipo" => (int)$_POST['id_tipo_equipo']
    ];

    try {
        $response = $equipoApiClient->actualizarEquipo($id_equipo, $payload);

        $_SESSION['alert_message'] = [
                'type' => $response['status'] === 200 ? 'success' : 'error',
                'text' => $response['mensaje']
        ];
        header("Location: equipos.php");
        exit();


    } catch (Exception $e) {
        header("Location: equipos.php");
        exit();

    }
}


$equipo = $equipoApiClient->obtenerEquipo($id_equipo);

// Cargar catálogos
//$sucursales = $conexion->query("SELECT * FROM sucursales WHERE estado = 'Activo' ORDER BY nombre");
require_once '../api_clients/SucursalApiClient.php';
$sucursalApiClient = new SucursalApiClient();
$sucursales = $sucursalApiClient->listarSucursalesActivos();
//$tipos = $conexion->query("SELECT * FROM tipos_equipo WHERE estado = 'Activo' ORDER BY nombre");
require_once '../api_clients/TipoEquipoApiClient.php';
$tipoEquipoApiClient = new TipoEquipoApiClient();
$tipos = $tipoEquipoApiClient->listarTipoEquipoActivos();
//$marcas = $conexion->query("SELECT * FROM marcas WHERE estado = 'Activo' ORDER BY nombre");
require_once '../api_clients/MarcaApiClient.php';
$marcaApiClient = new MarcaApiClient();
$marcas = $marcaApiClient->listarMarcasActivos();
// Cargar solo los modelos correspondientes a la marca actual del equipo
//$modelos = $conexion->query("SELECT * FROM modelos WHERE id_marca = " . (int)$equipo['id_marca'] . " AND estado = 'Activo' ORDER BY nombre");
require_once '../api_clients/ModeloApiClient.php';
$modeloApiClient = new ModeloApiClient();

try {
    $modelos = $modeloApiClient->obtenerModelosPorMarca((int)$equipo['idMarca']);
} catch (Exception $e) {
    header("Location: equipos.php");
    exit();
}

?>

    <h1 class="h2 mb-4">Editar Equipo</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

    <form action="equipo_editar.php?id=<?php echo $id_equipo; ?>" method="POST">
        <div class="row mb-3">
            <div class="col-md-6"><label for="id_sucursal" class="form-label">Sucursal <span
                            class="text-danger">*</span></label><select class="form-select" name="id_sucursal"
                                                                        required><?php foreach ($sucursales as $sucursal): ?>
                        <option
                        value="<?php echo $sucursal['id']; ?>" <?php echo ($sucursal['id'] == $equipo['idSucursal']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sucursal['nombre']); ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-6"><label for="codigo_inventario" class="form-label">Código de Inventario <span
                            class="text-danger">*</span></label><input type="text" class="form-control"
                                                                       name="codigo_inventario" required
                                                                       value="<?php echo htmlspecialchars($equipo['codigoInventario']); ?>">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><label for="numero_serie" class="form-label">Número de Serie <span
                            class="text-danger">*</span></label><input type="text" class="form-control"
                                                                       name="numero_serie" required
                                                                       value="<?php echo htmlspecialchars($equipo['numeroSerie']); ?>">
            </div>
            <div class="col-md-6"><label for="estado" class="form-label">Estado del Equipo <span
                            class="text-danger">*</span></label><select class="form-select" name="estado"
                                                                        required <?php echo ($equipo['estado'] == 'Asignado') ? 'disabled' : ''; ?>>
                    <option value="Disponible" <?php echo ($equipo['estado'] == 'Disponible') ? 'selected' : ''; ?>>
                        Disponible
                    </option>
                    <option value="En Reparacion" <?php echo ($equipo['estado'] == 'En Reparacion') ? 'selected' : ''; ?>>
                        En Reparación
                    </option>
                    <option value="De Baja" <?php echo ($equipo['estado'] == 'De Baja') ? 'selected' : ''; ?>>De Baja
                    </option><?php if ($equipo['estado'] == 'Asignado'): ?>
                        <option value="Asignado" selected>Asignado (no se puede cambiar)</option><?php endif; ?>
                </select></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4"><label class="form-label">Tipo de Equipo <span
                            class="text-danger">*</span></label><select class="form-select" name="id_tipo_equipo"
                                                                        required><?php foreach ($tipos as $tipo): ?>
                        <option
                        value="<?php echo $tipo['id']; ?>" <?php echo ($tipo['id'] == $equipo['idTipoEquipo']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($tipo['nombre']); ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-4"><label class="form-label">Marca <span class="text-danger">*</span></label><select
                        class="form-select" name="id_marca" id="selectMarca"
                        required><?php foreach ($marcas as $marca): ?>
                        <option
                        value="<?php echo $marca['id']; ?>" <?php echo ($marca['id'] == $equipo['idMarca']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($marca['nombre']); ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-4"><label class="form-label">Modelo <span class="text-danger">*</span></label><select
                        class="form-select" name="id_modelo" id="selectModelo"
                        required><?php foreach ($modelos as $modelo): ?>
                        <option
                        value="<?php echo $modelo['id']; ?>" <?php echo ($modelo['id'] == $equipo['idModelo']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($modelo['nombre']); ?></option><?php endforeach; ?>
                </select></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label">Tipo de Adquisición <span
                            class="text-danger">*</span></label><select class="form-select" name="tipo_adquisicion"
                                                                        required>
                    <option value="Propio" <?php echo ($equipo['tipoAdquisicion'] == 'Propio') ? 'selected' : ''; ?>>
                        Propio
                    </option>
                    <option value="Arrendado" <?php echo ($equipo['tipoAdquisicion'] == 'Arrendado') ? 'selected' : ''; ?>>
                        Arrendado
                    </option>
                    <option value="Prestamo" <?php echo ($equipo['tipoAdquisicion'] == 'Prestamo') ? 'selected' : ''; ?>>
                        Préstamo
                    </option>
                </select></div>
            <div class="col-md-6"><label class="form-label">Características</label><textarea class="form-control"
                                                                                             name="caracteristicas"
                                                                                             rows="1"><?php echo htmlspecialchars($equipo['caracteristicas'] ?? ''); ?></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label><input
                        type="date" class="form-control" name="fecha_adquisicion"
                        value="<?php echo htmlspecialchars($equipo['fechaAdquisicion'] ?? ''); ?>"></div>
            <div class="col-md-6"><label for="proveedor" class="form-label">Proveedor</label><input type="text"
                                                                                                    class="form-control"
                                                                                                    name="proveedor"
                                                                                                    value="<?php echo htmlspecialchars($equipo['proveedor'] ?? ''); ?>">
            </div>
        </div>
        <div class="mb-3"><label class="form-label">Observaciones</label><textarea class="form-control"
                                                                                   name="observaciones"
                                                                                   rows="3"><?php echo htmlspecialchars($equipo['observaciones'] ?? ''); ?></textarea>
        </div>

        <hr class="my-4">
        <a href="equipos.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>

    <script>
        document.getElementById('selectMarca').addEventListener('change', function () {
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