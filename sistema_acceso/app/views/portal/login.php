<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Empleados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card-login { max-width: 400px; width: 100%; border: none; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="card card-login shadow-lg p-4">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Portal del Empleado</h3>
            <p class="text-muted">Consulta tus asistencias</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger text-center p-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="?c=Portal&a=authenticate" method="POST">
            <div class="form-floating mb-3">
                <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                <label for="email">Correo Corporativo</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password">Contraseña</label>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fs-5 fw-bold">Ingresar</button>
        </form>
        <div class="text-center mt-3">
            <small><a href="?c=Auth&a=login" class="text-decoration-none text-muted">Soy Administrador</a></small>
        </div>
    </div>
</body>
</html>