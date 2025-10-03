<?php
require_once '../templates/header.php';
require_once __DIR__ . '/../api_clients/CatalogoApiClient.php';

$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_UNSAFE_RAW);

$table_map = [
        'sucursal' => 'Sucursales',
        'tipo'     => 'Tipos de Equipo',
        'marca'    => 'Marcas',
        'modelo'   => 'Modelos',
        'area'     => 'Áreas',
        'cargo'    => 'Cargos'
];

if (!$id || !array_key_exists($type, $table_map)) {
    header("Location: gestion_catalogos.php");
    exit();
}

$catalogoApi = new CatalogoApiClient();

// --- Si hay POST (guardar cambios) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
            "id"     => $id,
            "nombre" => $_POST['nombre'] ?? '',
            "estado" => $_POST['estado'] ?? 'Activo'
    ];

    if ($type === "sucursal") {
        $payload["direccion"] = $_POST['direccion'] ?? '';
    } elseif ($type === "cargo") {
        $payload["id_area"] = $_POST['id_area'] ?? null;
    } elseif ($type === "modelo") {
        $payload["id_marca"] = $_POST['id_marca'] ?? null;
    }

    try {
        $catalogoApi->editarElemento($type, $payload);
        header("Location: gestion_catalogos.php?status=success_edit");
        exit();
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
        if (isset($_GET['action'])) {
            echo "<pre>";
            print_r($_GET);  // Muestra action, id, type, etc.
            echo "</pre>";
        }
    }
}

// --- Obtener datos de la entidad ---
try {
    $item = $catalogoApi->obtenerElemento($type, $id);
} catch (Exception $e) {
    // Si no se encuentra el elemento, redirigir
    header("Location: gestion_catalogos.php");
    exit();
}

// Para combos dinámicos
//Listar areas
require_once __DIR__ . '/../api_clients/AreaApiClient.php';
$areaApiClient = new AreaApiClient();
$areas  = ($type === "cargo")  ? $areaApiClient->listar()  : [];
require_once __DIR__ . '/../api_clients/MarcaApiClient.php';
$marcaApiClient = new MarcaApiClient();
$marcas = ($type === "modelo") ? $marcas = $marcaApiClient->listarMarca() : [];
?>

<h1 class="h2 mb-4">Editar <?php echo $table_map[$type]; ?></h1>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nombre"
                       value="<?php echo htmlspecialchars($item['nombre']); ?>" required>
            </div>

            <?php if ($type === "sucursal"): ?>
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" name="direccion"
                           value="<?php echo htmlspecialchars($item['direccion']); ?>">
                </div>
            <?php endif; ?>

            <?php if ($type === "cargo"): ?>
                <div class="mb-3">
                    <label for="id_area" class="form-label">Área</label>
                    <select class="form-select" name="id_area" required>
                        <?php
                        $idAreaSeleccionada = $item['id_area'] ?? ($item['area']['id'] ?? null);
                        ?>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?php echo $area['id']; ?>"
                                    <?php if($idAreaSeleccionada  == $area['id']) echo "selected"; ?>>
                                <?php echo htmlspecialchars($area['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if ($type === "modelo"): ?>
                <div class="mb-3">
                    <label for="id_marca" class="form-label">Marca</label>
                    <select class="form-select" name="id_marca" required>
                        <?php
                        $idMarcaSeleccionada = $item['id_marca'] ?? ($item['marca']['id'] ?? null);
                        ?>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?php echo $marca['id']; ?>"
                                    <?php if($idMarcaSeleccionada == $marca['id']) echo "selected"; ?>>
                                <?php echo htmlspecialchars($marca['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" name="estado" required>
                    <option value="Activo"   <?php if($item['estado'] === 'Activo') echo 'selected'; ?>>Activo</option>
                    <option value="Inactivo" <?php if($item['estado'] === 'Inactivo') echo 'selected'; ?>>Inactivo</option>
                </select>
            </div>

            <hr class="my-4">
            <a href="gestion_catalogos.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger mt-3"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>
