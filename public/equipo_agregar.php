<?php

require_once '../templates/header.php';
require_once __DIR__ . '/../api_clients/EquipoApiClient.php';

$EquipoApiClient = new EquipoApiClient();
// Lógica para procesar el formulario de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sucursal_post = ($_SESSION['user_sucursal_id'] === null) ? $_POST['id_sucursal'] : $_SESSION['user_sucursal_id'];

// Capturar datos del formulario
    $payload = [
            "codigoInventario" => $_POST['codigo_inventario'],
            "numeroSerie" => $_POST['numero_serie'],
            "caracteristicas" => $_POST['caracteristicas'],
            "tipoAdquisicion" => $_POST['tipo_adquisicion'],
            "fechaAdquisicion" => !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null,
            "proveedor" => $_POST['proveedor'],
            "observaciones" => $_POST['observaciones'],
            "fechaRegistro" => date("Y-m-d"),
            "idSucursal" => (int)$id_sucursal_post,
            "idMarca" => (int)$_POST['id_marca'],
            "idModelo" => (int)$_POST['id_modelo'],
            "idTipoEquipo" => (int)$_POST['id_tipo_equipo']
    ];

    try {
        $response = $EquipoApiClient->agregarEquipo($payload);

            $_SESSION['alert_message'] = [
                    'type' => $response['status'] === 201 ? 'success' : 'error',
                    'text' => $response['mensaje']
            ];
            header("Location: equipos.php");
            exit();


    } catch (Exception $e) {
        header("Location: equipos.php");
        exit();
    }
}



// Cargar catálogos para los menús desplegables
require_once __DIR__ . '/../api_clients/TipoEquipoApiClient.php';
$tipoEquipoClient = new TipoEquipoApiClient();
$tipos = $tipoEquipoClient->listarTipoEquipoActivos();

require_once __DIR__ . '/../api_clients/MarcaApiClient.php';
$marcaClient = new MarcaApiClient();
$marcas = $marcaClient->listarMarcasActivos();

require_once __DIR__ . '/../api_clients/SucursalApiClient.php';
$sucursalClient = new SucursalApiClient();
$sucursales = $sucursalClient->listarSucursalesActivos();

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
                        <?php foreach ($sucursales as $sucursal): ?>
                            <option value="<?php echo $sucursal['id']; ?>"><?php echo htmlspecialchars($sucursal['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="codigo_inventario" class="form-label">Código de Inventario <span
                                class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="codigo_inventario" required>
                </div>
            <?php else: ?>
                <div class="col-md-6">
                    <label for="codigo_inventario" class="form-label">Código de Inventario <span
                                class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="codigo_inventario" required>
                </div>
                <div class="col-md-6">
                    <label for="numero_serie" class="form-label">Número de Serie <span
                                class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="numero_serie" required>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($_SESSION['user_sucursal_id'] === null): ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="numero_serie" class="form-label">Número de Serie <span
                                class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="numero_serie" required>
                </div>
            </div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tipo de Equipo <span class="text-danger">*</span></label>
                <select class="form-select" name="id_tipo_equipo" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?php echo $tipo['id']; ?>"><?php echo htmlspecialchars($tipo['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Marca <span class="text-danger">*</span></label>
                <select class="form-select" name="id_marca" id="selectMarca" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?php echo $marca['id']; ?>"><?php echo htmlspecialchars($marca['nombre']); ?></option>
                    <?php endforeach; ?>
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
        document.getElementById('selectMarca').addEventListener('change', function () {
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