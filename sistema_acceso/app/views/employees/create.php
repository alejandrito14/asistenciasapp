<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4>Agregar Nuevo Empleado</h4>
                </div>
                <div class="card-body">
                    <form action="?c=Employee&a=store" method="POST">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código de Empleado (Para QR)</label>
                                <input type="text" name="employee_code" class="form-control" required placeholder="Ej: EMP005">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombres</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellidos</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Departamento</label>
                                <select name="department_id" class="form-select">
                                    <?php foreach($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>">
                                            <?php echo $dept['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cargo / Puesto</label>
                                <input type="text" name="position" class="form-control" required placeholder="Ej: Analista">
                            </div>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-success">Guardar Empleado</button>
                        <a href="?c=Employee" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>