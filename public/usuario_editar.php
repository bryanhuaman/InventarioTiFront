<?php
require_once '../templates/header.php';
require_once '../api_clients/UsuariosApiClient.php';

// Solo los administradores pueden acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'Administrador') {
    echo "<div class='alert alert-danger'>Acceso denegado.</div>";
    require_once '../templates/footer.php';
    exit();
}

$id_usuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_usuario) {
    header("Location: gestion_usuarios.php");
    exit();
}

$usuarioApiClient = new UsuariosApiClient();
// Lógica para ACTUALIZAR el usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $id_rol = $_POST['id_rol'];
    $id_sucursal = !empty($_POST['id_sucursal']) ? $_POST['id_sucursal'] : null;
    $activo = isset($_POST['activo']) ? filter_var($_POST['activo'], FILTER_VALIDATE_BOOLEAN) : false;

    // Datos a enviar en JSON
    $data = [
            'nombre' => $nombre,
            'email' => $email,
            'rolId' => (int)$id_rol,
            'sucursalId' => $id_sucursal ? (int)$id_sucursal : null,
            'activo' => $activo
    ];

    // Verificar respuesta
    try {
        // Llamada a la API
        $response = $usuarioApiClient->actualizarUsuario($id_usuario, $data);
        $_SESSION['alert_message'] = [
                'type' => $response['status'] === 200 ? 'success' : 'error',
                'text' => $response['mensaje']
        ];
        header("Location: gestion_usuarios.php");
        exit();

    } catch (Exception $e) {
        header("Location: gestion_usuarios.php");
        exit();

    }
}

// Cargar datos del usuario a editar
$usuario = $usuarioApiClient->obtenerUsuario($id_usuario);

// Cargar catálogos
Require_once '../api_clients/RolApiClient.php';
$rolApiClient = new RolApiClient();
$roles = $rolApiClient->listar();
Require_once '../api_clients/SucursalApiClient.php';
$sucursalApiClient = new SucursalApiClient();
$sucursales = $sucursalApiClient->listarSucursalesActivos();
?>

<h1 class="h2 mb-4">Editar Usuario</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<form method="POST">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="id_rol" class="form-label">Rol <span class="text-danger">*</span></label>
            <select class="form-select" name="id_rol" required>
                <?php foreach ($roles as $rol): ?>
                    <option value="<?php echo $rol['id']; ?>" <?php if($rol['id'] == $usuario['rolId']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($rol['nombreRol']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="id_sucursal" class="form-label">Sucursal</label>
            <select class="form-select" name="id_sucursal">
                <option value="">General (Todas las sucursales)</option>
                <?php foreach ($sucursales as $sucursal): ?>
                    <option value="<?php echo $sucursal['id']; ?>" <?php if($sucursal['id'] == $usuario['sucursalId']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($sucursal['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="activo" class="form-label">Estado <span class="text-danger">*</span></label>
            <select class="form-select" name="activo" required>
                <option value="1" <?php if($usuario['activo'] == 1) echo 'selected'; ?>>Activo</option>
                <option value="0" <?php if($usuario['activo'] == 0) echo 'selected'; ?>>Inactivo</option>
            </select>
        </div>
    </div>
    <div class="alert alert-info mt-3">
        La contraseña solo puede ser cambiada por el propio usuario o mediante una función de "recuperar contraseña".
    </div>

    <hr class="my-4">
    <a href="gestion_usuarios.php" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
</form>

<?php require_once '../templates/footer.php'; ?>