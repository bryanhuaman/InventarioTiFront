<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Inventario TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
        }
    </style>
</head>
<body>



<!-- Login 8 - Bootstrap Brain Component -->
<section class="bg-light p-3 p-md-4 p-xl-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12 col-md-6">
                            <img class="img-fluid rounded-start w-100 h-100 object-fit-cover" loading="lazy" src="./img/Gestion-de-Inventario-de-Equipo-de-Computo.png" alt="Welcome back you've been missed!">
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-center justify-content-center">
                            <div class="col-12 col-lg-11 col-xl-10">
                                <div class="card-body p-3 p-md-4 p-xl-5">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-5">
                                                <div class="text-center mb-4">
                                                    <a href="#!">
                                                        <img src="./img/inventario2.png" alt="BootstrapBrain Logo" width="160" height="100">
                                                    </a>
                                                </div>
                                                <h4 class="text-center">Bienvenido!</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (isset($_GET['error'])): ?>
                                                       <div class="alert alert-danger">Credenciales incorrectas.</div>
                                    <?php endif; ?>
                                    <form action="../includes/procesar_login.php" method="POST">
                                        <div class="row gy-3 overflow-hidden">
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="email" class="form-control" name="email" id="email" placeholder="nombre@ejemplo.com" required>
                                                    <label for="email" class="form-label">Correo Electrónico</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating mb-3">
                                                    <input type="password" class="form-control" name="password" id="password" value="" placeholder="Contrasña" required>
                                                    <label for="password" class="form-label">Contraseña</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button class="btn btn-dark btn-lg" type="submit">Ingresar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>




<!--    <div class="login-container">-->
<!--        <div class="text-center mb-4">-->
<!--            <h1 class="h3 mb-3 fw-normal text-muted">Sistema de Inventario de TI</h1>-->
<!--        </div>-->
<!--        -->
<!--        <div class="card shadow-sm">-->
<!--            <div class="card-body p-4">-->
<!--                <h3 class="card-title text-center mb-4">Iniciar Sesión</h3>-->
<!--                -->
<!--                --><?php //if (isset($_GET['error'])): ?>
<!--                    <div class="alert alert-danger">Credenciales incorrectas.</div>-->
<!--                --><?php //endif; ?>
<!---->
<!--                <form action="../includes/procesar_login.php" method="POST">-->
<!--                    <div class="mb-3">-->
<!--                        <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>-->
<!--                        <input type="email" class="form-control" id="email" name="email" required>-->
<!--                    </div>-->
<!--                    <div class="mb-3">-->
<!--                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>-->
<!--                        <input type="password" class="form-control" id="password" name="password" required>-->
<!--                    </div>-->
<!--                    <div class="d-grid">-->
<!--                        <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>-->
<!--                    </div>-->
<!--                </form>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->

</body>
</html>