<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Control de Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-login {
            width: 100%;
            max-width: 450px;
            border-radius: 15px;
            border: none;
            overflow: hidden;
        }
        .nav-pills .nav-link {
            color: #6c757d;
            font-weight: bold;
            border-radius: 50px;
            padding: 10px 20px;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.3);
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="card card-login shadow-lg">
    
    <div class="card-header bg-white text-center pt-4 pb-0 border-0">
        <div class="mb-3">
            <i class="bi bi-shield-check text-primary display-1"></i>
        </div>
        <h4 class="fw-bold text-dark">Bienvenido</h4>
    </div>

    <div class="card-body p-4">
        
        <ul class="nav nav-pills nav-fill mb-4" id="loginTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin-pane" type="button">
                    <i class="bi bi-person-badge-fill me-1"></i> Admin
                </button>
            </li>
        
        </ul>

        <?php 
            // Capturar errores de la URL si vienen de redirección
            $err = isset($_GET['error']) ? $_GET['error'] : (isset($error) ? $error : '');
        ?>
        <?php if(!empty($err)): ?>
            <div class="alert alert-danger text-center p-2 mb-3 small rounded-3">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo $err; ?>
            </div>
        <?php endif; ?>

        <div class="tab-content" id="loginTabContent">
            
            <div class="tab-pane fade show active" id="admin-pane" role="tabpanel">
                <form action="?c=Auth&a=authenticate" method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" name="username" class="form-control" id="adminUser" placeholder="Usuario" required>
                        <label for="adminUser">Usuario (Ej: admin)</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" id="adminPass" placeholder="Contraseña" required>
                        <label for="adminPass">Contraseña</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">
                            Entrar al Sistema
                        </button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade" id="employee-pane" role="tabpanel">
                <form action="?c=Portal&a=authenticate" method="POST">
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="empEmail" placeholder="Correo" required>
                        <label for="empEmail">Correo Corporativo</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" id="empPass" placeholder="Contraseña" required>
                        <label for="empPass">Contraseña</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-dark btn-lg fw-bold">
                            Ingresar a Mi Portal
                        </button>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">¿Olvidaste tu clave? Contacta a RRHH.</small>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <div class="card-footer bg-light text-center py-3 border-0">
        <small class="text-muted">Sistema de Control de Asistencia V2.0</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>