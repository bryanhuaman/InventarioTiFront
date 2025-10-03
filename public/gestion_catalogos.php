<?php
require_once '../templates/header.php';
require_once __DIR__ . '/../api_clients/CatalogoApiClient.php';

// --- Lógica para CAMBIAR ESTADO ---
$catalogoApiClient = new CatalogoApiClient();
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['type'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    $type = $_GET['type'];


    try {
        $catalogoApiClient->cambiarEstado($action, $id, $type);
        header("Location: gestion_catalogos.php?msg=ok");
        exit();
    } catch (Exception $e) {
        error_log("Error cambiando estado: " . $e->getMessage());
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

//Mensaje de éxito al cambiar estado
if (isset($_GET['msg']) && $_GET['msg'] === 'ok') {
    echo "
        <div id='alerta' 
             class='alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow' 
             role='alert'
             style='z-index: 9999;'>
            Estado actualizado correctamente
        </div>
        <script>
            setTimeout(() => {
                const alerta = document.getElementById('alerta');
                if (alerta) {
                    alerta.classList.remove('show');
                    alerta.classList.add('hide');
                }
            }, 3000);
        </script>
    ";
}


// --- Lógica para AÑADIR NUEVOS ELEMENTOS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['catalogo'])) {
        $catalogo = $_POST['catalogo'];
    } elseif (isset($_POST['catalogo_sucursal'])) {
        $catalogo = "sucursal";
    } else {
        $catalogo = null;
    }

    if ($catalogo) {
        $data = $_POST;
        unset($data['catalogo'], $data['catalogo_sucursal']); // limpiar claves extras

        try {
            $response = $catalogoApiClient->agregarElemento($catalogo, $data);
            $msg = $response['message'] ?? ("Error: " . json_encode($response));
            //echo "<div class='alert alert-success mt-3'>{$msg}</div>";
            echo "
            <div id='alerta' 
                 class='alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow' 
                 role='alert'
                 style='z-index: 9999;'>
                {$msg}
            </div>
            <script>
                setTimeout(() => {
                    const alerta = document.getElementById('alerta');
                    if (alerta) {
                        alerta.classList.remove('show');
                        alerta.classList.add('hide');
                    }
                }, 3000);
            </script>
        ";

        } catch (Exception $e) {
            echo "<div class='alert alert-danger mt-3'>Excepción: " . $e->getMessage() . "</div>";
        }
    }
}

//Mostrar mensaje de exito al editar
try {
    if (isset($_GET['status'])) {
        if ($_GET['status'] === 'success_edit') {
            echo "
            <div id='alerta' 
                 class='alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow' 
                 role='alert'
                 style='z-index: 9999;'>
                Elemento actualizado exitosamente
            </div>
            <script>
                setTimeout(() => {
                    const alerta = document.getElementById('alerta');
                    if (alerta) {
                        alerta.classList.remove('show');
                        alerta.classList.add('hide');
                    }
                }, 3000);
            </script>
        ";
        }
    }
} catch (Exception $e) {
    error_log("Error mostrando mensaje de exito: " . $e->getMessage());
}


//--- Api Area ---
//Listar areas
require_once __DIR__ . '/../api_clients/AreaApiClient.php';
$areaApiClient = new AreaApiClient();
$areas = $areaApiClient->listar();

//--- Api Cargo ---
//Listar cargos con su area
require_once __DIR__ . '/../api_clients/CargoApiClient.php';
$cargoApiClient = new CargoApiClient();
$cargos = $cargoApiClient->listarconCargos();

//--- Api Tipo Equipo ---
//Listar tipos de equipo
require_once __DIR__ . '/../api_clients/TipoEquipoApiClient.php';
$tipoEquipoApiClient = new TipoEquipoApiClient();
$tipos = $tipoEquipoApiClient->listarTipoEquipo();

//--- Api Marca ---
//Listar marcas
require_once __DIR__ . '/../api_clients/MarcaApiClient.php';
$marcaApiClient = new MarcaApiClient();
$marcas = $marcaApiClient->listarMarca();

//--- Api Modelo ---
//Listar modelos con su marca
require_once __DIR__ . '/../api_clients/ModeloApiClient.php';
$modeloApiClient = new ModeloApiClient();
$modelos = $modeloApiClient->listarModelosConMarca();

//--- Api Sucursal ---
//Listar sucursales
require_once __DIR__ . '/../api_clients/SucursalApiClient.php';
$sucursalApiClient = new SucursalApiClient();
$sucursales = $sucursalApiClient->listarSucursales();

?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h2">Gestión de Catálogos</h1>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Sucursales</div>
                <div class="card-body d-flex flex-column">
                    <form method="POST" class="mb-3"><input type="hidden" name="catalogo_sucursal" value="1">
                        <div class="mb-2"><label class="form-label">Nombre <span
                                        class="text-danger">*</span></label><input type="text" name="nombre_sucursal"
                                                                                   class="form-control" required></div>
                        <div class="mb-2"><label class="form-label">Dirección</label><textarea name="direccion_sucursal"
                                                                                               class="form-control"
                                                                                               rows="1"></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-plus"></i> Agregar</button>
                    </form>
                    <hr>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm table-hover">
                            <tbody><?php foreach ($sucursales as $item): ?>
                                <tr>
                                <td><strong><?php echo htmlspecialchars($item['nombre']); ?></strong><br><small
                                            class="text-muted"><?php echo htmlspecialchars($item['direccion']); ?></small><span
                                            class="badge float-end <?php echo $item['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo $item['estado']; ?></span>
                                </td>
                                <td class="text-end align-middle">
                                    <div class="btn-group"><a
                                                href="catalogo_editar.php?id=<?php echo $item['id']; ?>&type=sucursal"
                                                class="btn btn-warning btn-sm" title="Editar"><i
                                                    class="bi bi-pencil"></i></a><?php if ($item['estado'] == 'Activo'): ?>
                                        <a href="?action=desactivate&id=<?php echo $item['id']; ?>&type=sucursal"
                                           class="btn btn-danger btn-sm" title="Desactivar"><i
                                                        class="bi bi-trash"></i></i></a><?php else: ?><a
                                            href="?action=activate&id=<?php echo $item['id']; ?>&type=sucursal"
                                            class="btn btn-success btn-sm" title="Activar"><i
                                                        class="bi bi-check-circle"></i></a><?php endif; ?></div>
                                </td></tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Áreas</div>
                <div class="card-body d-flex flex-column">
                    <form method="POST" class="mb-3"><input type="hidden" name="catalogo" value="area">
                        <div class="input-group"><input type="text" name="nombre" class="form-control"
                                                        placeholder="Nueva área... *" required>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                    <hr>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm table-hover">
                            <tbody>
                            <?php foreach ($areas as $item): ?>
                                <tr>
                                <td><?php echo htmlspecialchars($item['nombre']); ?><span
                                            class="badge float-end
                        <?php echo $item['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $item['estado']; ?></span>
                                </td>
                                <td class="text-end align-middle">
                                    <div class="btn-group"><a
                                                href="catalogo_editar.php?id=
                        <?php echo $item['id']; ?>&type=area"
                                                class="btn btn-warning btn-sm"><i
                                                    class="bi bi-pencil"></i></a><?php if ($item['estado'] == 'Activo'): ?>
                                        <a href="?action=desactivate&id=<?php echo $item['id']; ?>&type=area"
                                           class="btn btn-danger btn-sm"><i class="bi bi-trash"></i>
                                            </a><?php else: ?><a
                                            href="?action=activate&id=<?php echo $item['id']; ?>&type=area"
                                            class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i>
                                            </a><?php endif; ?></div>
                                </td></tr><?php endforeach; ?></tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Cargos (por Área)</div>
                <div class="card-body d-flex flex-column">
                    <form method="POST" class="mb-3"><input type="hidden" name="catalogo" value="cargo">
                        <div class="mb-2"><select name="id_area" class="form-select" required>
                                <option value="">Selecciona un área *</option>
                                <?php foreach ($areas as $item): ?>
                                    <option
                                    value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['nombre']); ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="input-group"><input type="text" name="nombre" class="form-control"
                                                        placeholder="Nuevo cargo... *" required>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                    <hr>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm table-hover">
                            <tbody><?php foreach ($cargos as $item): ?>
                                <tr>
                                <td><strong><?php echo htmlspecialchars($item['area_nombre']); ?></strong>
                                    - <?php echo htmlspecialchars($item['nombre']); ?><span
                                            class="badge float-end <?php echo $item['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo $item['estado']; ?></span>
                                </td>
                                <td class="text-end align-middle">
                                    <div class="btn-group"><a
                                                href="catalogo_editar.php?id=<?php echo $item['id']; ?>&type=cargo"
                                                class="btn btn-warning btn-sm"><i
                                                    class="bi bi-pencil"></i></a><?php if ($item['estado'] == 'Activo'): ?>
                                        <a href="?action=desactivate&id=<?php echo $item['id']; ?>&type=cargo"
                                           class="btn btn-danger btn-sm"><i class="bi bi-trash"></i>
                                            </a><?php else: ?><a
                                            href="?action=activate&id=<?php echo $item['id']; ?>&type=cargo"
                                            class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i>
                                            </a><?php endif; ?></div>
                                </td></tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Tipos de Equipo</div>
                <div class="card-body d-flex flex-column">
                    <form method="POST" class="mb-3"><input type="hidden" name="catalogo" value="tipo">
                        <div class="input-group"><input type="text" name="nombre" class="form-control"
                                                        placeholder="Nuevo tipo... *" required>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                    <hr>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm table-hover">
                            <tbody><?php foreach ($tipos as $item): ?>
                                <tr>
                                <td><?php echo htmlspecialchars($item['nombre']); ?><span
                                            class="badge float-end <?php echo $item['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo $item['estado']; ?></span>
                                </td>
                                <td class="text-end align-middle">
                                    <div class="btn-group"><a
                                                href="catalogo_editar.php?id=<?php echo $item['id']; ?>&type=tipo"
                                                class="btn btn-warning btn-sm"><i
                                                    class="bi bi-pencil"></i></a><?php if ($item['estado'] == 'Activo'): ?>
                                        <a href="?action=desactivate&id=<?php echo $item['id']; ?>&type=tipo"
                                           class="btn btn-danger btn-sm"><i class="bi bi-trash"></i>
                                            </a><?php else: ?><a
                                            href="?action=activate&id=<?php echo $item['id']; ?>&type=tipo"
                                            class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i>
                                            </a><?php endif; ?></div>
                                </td></tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Marcas</div>
                <div class="card-body d-flex flex-column">
                    <form method="POST" class="mb-3"><input type="hidden" name="catalogo" value="marca">
                        <div class="input-group"><input type="text" name="nombre" class="form-control"
                                                        placeholder="Nueva marca... *" required>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                    <hr>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm table-hover">
                            <tbody><?php foreach ($marcas as $item): ?>
                                <tr>
                                <td><?php echo htmlspecialchars($item['nombre']); ?><span
                                            class="badge float-end <?php echo $item['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo $item['estado']; ?></span>
                                </td>
                                <td class="text-end align-middle">
                                    <div class="btn-group"><a
                                                href="catalogo_editar.php?id=<?php echo $item['id']; ?>&type=marca"
                                                class="btn btn-warning btn-sm"><i
                                                    class="bi bi-pencil"></i></a><?php if ($item['estado'] == 'Activo'): ?>
                                        <a href="?action=desactivate&id=<?php echo $item['id']; ?>&type=marca"
                                           class="btn btn-danger btn-sm"><i class="bi bi-trash"></i>
                                            </a><?php else: ?><a
                                            href="?action=activate&id=<?php echo $item['id']; ?>&type=marca"
                                            class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i>
                                            </a><?php endif; ?></div>
                                </td></tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">Modelos</div>
                <div class="card-body d-flex flex-column">
                    <form method="POST" class="mb-3"><input type="hidden" name="catalogo" value="modelo">
                        <div class="mb-2"><select name="id_marca" class="form-select" required>
                                <option value="">Selecciona una marca *</option>
                                <?php foreach ($marcas as $item): ?>
                                    <option
                                    value="<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['nombre']); ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="input-group"><input type="text" name="nombre" class="form-control"
                                                        placeholder="Nuevo modelo... *" required>
                            <button class="btn btn-primary" type="submit"><i class="bi bi-plus"></i></button>
                        </div>
                    </form>
                    <hr>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm table-hover">
                            <tbody><?php foreach ($modelos as $item): ?>
                                <tr>
                                <td><strong><?php echo htmlspecialchars($item['marca_nombre']); ?></strong>
                                    - <?php echo htmlspecialchars($item['nombre']); ?><span
                                            class="badge float-end <?php echo $item['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>"><?php echo $item['estado']; ?></span>
                                </td>
                                <td class="text-end align-middle">
                                    <div class="btn-group"><a
                                                href="catalogo_editar.php?id=<?php echo $item['id']; ?>&type=modelo"
                                                class="btn btn-warning btn-sm"><i
                                                    class="bi bi-pencil"></i></a><?php if ($item['estado'] == 'Activo'): ?>
                                        <a href="?action=desactivate&id=<?php echo $item['id']; ?>&type=modelo"
                                           class="btn btn-danger btn-sm"><i class="bi bi-trash"></i>
                                            </a><?php else: ?><a
                                            href="?action=activate&id=<?php echo $item['id']; ?>&type=modelo"
                                            class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i>
                                            </a><?php endif; ?></div>
                                </td></tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once '../templates/footer.php'; ?>