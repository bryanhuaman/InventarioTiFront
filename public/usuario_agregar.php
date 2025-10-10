<?php
require_once '../templates/header.php';

// Solo los administradores pueden acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'Administrador') {
    echo "<div class='alert alert-danger'>Acceso denegado.</div>";
    require_once '../templates/footer.php';
    exit();
}

$password_plano = $_POST['password'] ?? '';
// Lógica para crear un nuevo usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($password_plano)) {
        $error_message = "La contraseña no puede estar vacía.";
    } else {
        require_once '../api_clients/UsuariosApiClient.php';
        $usuarioApi = new UsuariosApiClient();

        $payload = [
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => $password_plano,
                'sucursalId' => (int) !empty($_POST['id_sucursal']) ? $_POST['id_sucursal'] : null,
                'rolId' => (int)$_POST['id_rol']
        ];


        try {
            $resultado = $usuarioApi->crearUsuario($payload);

            $_SESSION['alert_message'] = [
                    'type' => $resultado['status'] === 201 ? 'success' : 'error',
                    'text' => $resultado['mensaje']
            ];
            header("Location: gestion_usuarios.php");
            exit();
        } catch (Exception $e) {
            header("Location: gestion_usuarios.php");
            exit();
        }
    }
}

// Cargar catálogos para los menús
Require_once __DIR__ . '/../api_clients/RolApiClient.php';
$rolApiClient = new RolApiClient();
$roles = $rolApiClient->listar();
Require_once __DIR__ . '/../api_clients/SucursalApiClient.php';
$sucursalApiClient = new SucursalApiClient();
$sucursales = $sucursalApiClient->listarSucursalesActivos();
?>

<h1 class="h2 mb-4">Registrar Nuevo Usuario</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<form id="miForm" method="POST">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <div class="col-md-4 mb-3">
            <label for="id_rol" class="form-label">Rol <span class="text-danger">*</span></label>
            <select class="form-select" name="id_rol" required>
                <option value="">Seleccione un rol...</option>
                <?php foreach ($roles as $rol): ?>
                    <option value="<?php echo $rol['id']; ?>"><?php echo htmlspecialchars($rol['nombreRol']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="id_sucursal" class="form-label">Sucursal</label>
            <select class="form-select" name="id_sucursal">
                <option value="">General (Todas las sucursales)</option>
                <?php foreach ($sucursales as $sucursal): ?>
                    <option value="<?php echo $sucursal['id']; ?>"><?php echo htmlspecialchars($sucursal['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <hr class="my-4">
    <a href="gestion_usuarios.php" class="btn btn-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">Crear Usuario</button>
</form>

    <script>
        document.getElementById("miForm").addEventListener("submit", function(event) {
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

<?php require_once '../templates/footer.php'; ?>