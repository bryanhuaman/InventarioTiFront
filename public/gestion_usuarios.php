<?php
require_once '../templates/header.php';

// Solo los administradores pueden acceder
if ($_SESSION['user_rol'] !== 'Administrador') {
    echo "<div class='alert alert-danger'>Acceso denegado.</div>";
    require_once '../templates/footer.php';
    exit();
}

//MOSTAR MENSAJE DESPUES DE AGREGAR
if (isset($_SESSION['alert_message'])) {
    $msg = $_SESSION['alert_message'];


    echo '
    <script>
        toastMixin.fire({
            position: "top-end",
            icon: "' . addslashes($msg['type']) . '",   // success, error, warning, info, question
            title: "' . addslashes($msg['text']) . '",
            showConfirmButton: false,
            timer: 3000,
            toast: true
        });
    </script>
    ';


    unset($_SESSION['alert_message']); // Eliminar después de mostrar
}

// Ususarios API Client
require_once __DIR__ . '/../api_clients/UsuariosApiClient.php';
$apiClient = new UsuariosApiClient();
$usuarios = $apiClient->listarUsuConRolYSucursal();

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h2">Gestión de Usuarios y Roles</h1>
    <a href="usuario_agregar.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Usuario</a>
</div>

<div class="card">
    <div class="card-header">Usuarios del Sistema</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Sucursal</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($usuario['nombre_rol'] ?? 'Sin rol'); ?></span></td>
                            <td><?php echo htmlspecialchars($usuario['nombre_sucursal'] ?? 'Todas'); ?></td>
                            <td>
                                <a href="usuario_editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>