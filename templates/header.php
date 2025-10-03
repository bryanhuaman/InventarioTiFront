<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once __DIR__ . '/../config/database.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="css/style.css?v=1.0">

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head>
<body>

<header class="mobile-header d-lg-none">
    <button class="btn text-white" type="button" id="menu-toggle">
        <i class="bi bi-list fs-4"></i>
    </button>
    <a href="index.php" class="text-white text-decoration-none">
        <span class="fs-4 ms-3">Inventario TI</span>
    </a>
</header>

<div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white" id="sidebar" >
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="bi bi-box-seam-fill me-2" style="font-size: 2rem;"></i>
        <span class="fs-4">Inventario TI</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item"><a href="index.php" class="nav-link text-white <?php if($current_page == 'index.php') echo 'active'; ?>"><i class="bi bi-house-door me-2"></i> Dashboard</a></li>
        <li><a href="equipos.php" class="nav-link text-white <?php if(in_array($current_page, ['equipos.php', 'equipo_agregar.php', 'equipo_editar.php'])) echo 'active'; ?>"><i class="bi bi-laptop me-2"></i> Equipos</a></li>
        <li><a href="empleados.php" class="nav-link text-white <?php if(in_array($current_page, ['empleados.php', 'empleado_agregar.php', 'empleado_editar.php'])) echo 'active'; ?>"><i class="bi bi-people me-2"></i> Empleados</a></li>
        <li><a href="asignaciones.php" class="nav-link text-white <?php if(str_starts_with($current_page, 'asignacion')) echo 'active'; ?>"><i class="bi bi-card-list me-2"></i> Asignaciones</a></li>
        <li><a href="gestion_catalogos.php" class="nav-link text-white <?php if($current_page == 'gestion_catalogos.php') echo 'active'; ?>"><i class="bi bi-tags me-2"></i> Catálogos</a></li>
        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'Administrador'): ?>
        <li><a href="gestion_usuarios.php" class="nav-link text-white <?php if(str_starts_with($current_page, 'usuario')) echo 'active'; ?>"><i class="bi bi-person-badge me-2"></i> Usuarios y Roles</a></li>
        <?php endif; ?>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-2" style="font-size: 2rem;"></i>
            <strong><?php echo isset($_SESSION['user_nombre']) ? htmlspecialchars($_SESSION['user_nombre']) : 'Usuario'; ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li><span class="dropdown-item-text"><?php echo isset($_SESSION['user_rol']) ? htmlspecialchars($_SESSION['user_rol']) : 'Rol'; ?></span></li>
            <li><a class="dropdown-item" href="cambiar_password.php"><i class="bi bi-key me-2"></i> Restablecer Contraseña</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
        </ul>
    </div>
</div>

<main class="main-content">