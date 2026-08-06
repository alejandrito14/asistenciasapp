<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kiosco de Control</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0d6efd"> <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/9502/9502362.png"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Fondo oscuro para ahorrar batería y verse moderno */
        body { background-color: #212529; overflow-x: hidden; }
        
        /* Ajustes de la cámara */
        #reader { width: 100%; border-radius: 10px; overflow: hidden; background-color: #000; }
        #reader video { object-fit: cover; width: 100% !important; border-radius: 10px; }
        
        /* Botones táctiles más grandes */
        .nav-link { font-size: 1.1rem; }
    </style>
</head>
<body>

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-3">
    
    <div class="col-12 col-md-8 col-lg-5 col-xl-4">
        
        <div class="text-center text-white mb-4">
            <h3><i class="bi bi-shield-check text-primary"></i> Control de Acceso</h3>
            <p class="text-muted small">App Móvil V2.0</p>
        </div>

        <?php 
            $msg = isset($_GET['msg']) ? $_GET['msg'] : (isset($message) ? $message : '');
            $err = isset($_GET['err']) ? $_GET['err'] : (isset($error) ? $error : '');
        ?>
        
        <?php if(!empty($msg)): ?>
            <div class="alert alert-success fw-bold text-center shadow mb-3">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $msg; ?>
            </div>
        <?php endif; ?>
        <?php if(!empty($err)): ?>
            <div class="alert alert-danger fw-bold text-center shadow mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $err; ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            
            <div class="card-header bg-white p-0 border-bottom-0">
                <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3 fw-bold border-0 border-bottom border-3 border-primary rounded-0" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee-pane" type="button">
                            <i class="bi bi-qr-code-scan"></i> EMPLEADOS
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 fw-bold border-0 text-secondary" id="visitor-tab" data-bs-toggle="tab" data-bs-target="#visitor-pane" type="button">
                            <i class="bi bi-person-vcard"></i> VISITANTES
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="tab-content" id="myTabContent">
                    
                    <div class="tab-pane fade show active" id="employee-pane">
                        
                        <div id="reader" class="mb-3 shadow-sm"></div>
                        
                        <form action="?c=Attendance&a=register" method="POST" id="attendanceForm">
                            <div class="form-floating mb-3">
                                <input type="text" name="employee_code" id="employee_code" 
                                       class="form-control text-center fw-bold fs-4" 
                                       placeholder="Código" autofocus>
                                <label>Código QR (Ej: EMP001)</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg py-3 fw-bold shadow">
                                Registrar Asistencia
                            </button>
                        </form>
                        <p class="text-center text-muted small mt-3 mb-0">
                            <i class="bi bi-info-circle"></i> Enfoca el código con la cámara
                        </p>
                    </div>

                    <div class="tab-pane fade" id="visitor-pane">
                        <form action="?c=Visitor&a=register" method="POST">
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="action_type" id="v_in" value="entrada" checked onchange="toggleVisitorFields()">
                                <label class="btn btn-outline-success" for="v_in">🟢 Entrada</label>
                                <input type="radio" class="btn-check" name="action_type" id="v_out" value="salida" onchange="toggleVisitorFields()">
                                <label class="btn btn-outline-danger" for="v_out">🔴 Salida</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">IDENTIFICACIÓN</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                    <input type="number" name="dni" class="form-control form-control-lg" required placeholder="DNI / Cédula">
                                </div>
                            </div>

                            <div id="visitorDetails">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted small">NOMBRE COMPLETO</label>
                                    <input type="text" name="full_name" class="form-control" placeholder="Nombre del visitante">
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-3">
                                        <label class="form-label fw-bold text-muted small">EMPRESA</label>
                                        <input type="text" name="company" class="form-control" placeholder="Particular">
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label class="form-label fw-bold text-muted small">MOTIVO</label>
                                        <input type="text" name="reason" class="form-control" placeholder="Reunión...">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold shadow mt-2">
                                Procesar Registro
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <small class="text-muted">¿Eres administrador? <a href="?c=Auth&a=login" class="text-decoration-none fw-bold">Ingresa aquí</a></small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // === 2. REGISTRAR SERVICE WORKER (PWA) ===
    // Esto permite instalar la app en el celular
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js')
                .then(reg => console.log('Service Worker registrado', reg))
                .catch(err => console.log('Service Worker error', err));
        });
    }

    // === 3. LÓGICA DEL ESCÁNER ===
    let isScanning = false;

    function toggleVisitorFields() {
        const isOut = document.getElementById('v_out').checked;
        const detailsDiv = document.getElementById('visitorDetails');
        const inputs = detailsDiv.getElementsByTagName('input');
        if (isOut) {
            detailsDiv.style.display = 'none';
            for(let input of inputs) input.required = false;
        } else {
            detailsDiv.style.display = 'block';
            for(let input of inputs) if(input.name !== 'company') input.required = true;
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isScanning) return; // Bloquear lecturas múltiples
        isScanning = true;

        html5QrcodeScanner.pause(); // Congelar cámara (Feedback visual)
        
        document.getElementById('employee_code').value = decodedText;
        document.getElementById('attendanceForm').submit();
    }

    // Configuración optimizada
    let config = { 
        fps: 10, 
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    if (window.innerWidth < 600) {
        config.qrbox = { width: 200, height: 200 };
    }

    let html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
    html5QrcodeScanner.render(onScanSuccess);
</script>

</body>
</html>