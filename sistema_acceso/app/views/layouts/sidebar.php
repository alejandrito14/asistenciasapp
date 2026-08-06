<?php
// =========================================================
// 1. LÓGICA DEL MENÚ (Función para no repetir código)
// =========================================================

// Capturamos controlador y acción para marcar "active"
$c = isset($_GET['c']) ? $_GET['c'] : 'Dashboard';
$a = isset($_GET['a']) ? $_GET['a'] : 'index';

function renderMenu($c, $a) {
?>
    <ul class="list-unstyled">
        
        <li>
            <a href="?c=Dashboard" class="<?php echo ($c=='Dashboard') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <li class="text-muted small fw-bold px-3 mt-3 mb-1">ADMINISTRACIÓN</li>
            
            <li>
                <a href="?c=Employee" class="<?php echo ($c=='Employee') ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i> Maestros
                </a>
            </li>
            <li>
                <a href="?c=Group" class="<?php echo ($c=='Group') ? 'active' : ''; ?>">
                    <i class="bi bi-diagram-3-fill"></i> Grupos
                </a>
            </li>
            <li>
                <a href="?c=Subject" class="<?php echo ($c=='Subject') ? 'active' : ''; ?>">
                    <i class="bi bi-journal-bookmark-fill"></i> Materias
                </a>
            </li>
            <li>
                <a href="?c=GroupSubject" class="<?php echo ($c=='GroupSubject') ? 'active' : ''; ?>">
                    <i class="bi bi-diagram-3"></i> Grupo-Materias
                </a>
            </li>
            <li>
                <a href="?c=GroupMateriaMaestro" class="<?php echo ($c=='GroupMateriaMaestro') ? 'active' : ''; ?>">
                    <i class="bi bi-person-badge-fill"></i> Asignación Maestro
                </a>
            </li>
            <li>
                <a href="?c=Schedule" class="<?php echo ($c=='Schedule') ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-week-fill"></i> Horarios
                </a>
            </li>
            <li>
                <a href="?c=User" class="<?php echo ($c=='User') ? 'active' : ''; ?>">
                    <i class="bi bi-person-gear"></i> Usuarios Sistema
                </a>
            </li>
            <li>
                <a href="?c=Setting" class="<?php echo ($c=='Setting') ? 'active' : ''; ?>">
                    <i class="bi bi-gear-fill"></i> Configuración
                </a>
            </li>
            <li>
                <a href="?c=SchoolCalendar" class="<?php echo ($c=='SchoolCalendar') ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-event"></i> Calendario Escolar
                </a>
            </li>
            
            <li class="text-muted small fw-bold px-3 mt-3 mb-1">REPORTES</li>
            <li>
                <a href="?c=Report" class="<?php echo ($c=='Report' && $a!='history') ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                </a>
            </li>
        <?php endif; ?>

        <li class="text-muted small fw-bold px-3 mt-3 mb-1">SEGURIDAD</li>

        <li>
            <a href="#" data-bs-toggle="modal" data-bs-target="#sysPassModal" class="text-info">
                <i class="bi bi-key"></i> Cambiar mi Clave
            </a>
        </li>
        
        <li>
            <a href="?c=Auth&a=logout" class="text-danger">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </a>
        </li>
    </ul>

    <div class="modal fade text-dark" id="sysPassModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title"><i class="bi bi-shield-lock"></i> Cambiar Mi Contraseña</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <form action="?c=User&a=change_own_password" method="POST">
              <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nueva Contraseña</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar Contraseña</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
              </div>
          </form>
        </div>
      </div>
    </div>
<?php
}
?>

<div class="sidebar d-none d-md-block" style="width: 260px; min-height: 100vh; background-color: #212529; color: white;">
    
    <div class="py-4 px-3 mb-4 bg-black bg-gradient d-flex align-items-center">
        <i class="bi bi-shield-lock-fill fs-3 me-2 text-warning"></i>
        <div>
            <h5 class="m-0 fw-bold">Control de Asistencias</h5>
            <small class="text-white-50 text-uppercase" style="font-size: 0.7rem;">
                Rol: <?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Invitado'; ?>
            </small>
        </div>
    </div>
    
    <?php renderMenu($c, $a); ?>
</div>

<div class="d-md-none w-100">
    <nav class="navbar navbar-dark bg-dark mb-3 shadow p-3">
        <div class="container-fluid p-0">
            <button class="btn btn-outline-secondary border-0 text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                <i class="bi bi-list fs-1"></i>
            </button>
            
            <span class="navbar-brand m-0 fw-bold text-white">
                <i class="bi bi-shield-check text-warning"></i> App V2.0
            </span>

            <div class="d-flex">
               <a href="?c=Auth&a=logout" class="text-danger fs-2">
                   <i class="bi bi-box-arrow-right"></i>
               </a>
            </div>
        </div>
    </nav>
</div>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar" style="width: 280px;">
  <div class="offcanvas-header bg-black bg-gradient border-bottom border-secondary">
    <h5 class="offcanvas-title fw-bold">
        <i class="bi bi-person-circle me-2"></i> 
        <?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Usuario'; ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0 sidebar">
    <?php renderMenu($c, $a); ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .sidebar {
        min-height: 100vh;
        background-color: #212529;
        color: white;
    }

    .sidebar a {
        color: #adb5bd;
        text-decoration: none;
        padding: 12px 20px;
        display: block;
        border-left: 3px solid transparent;
        transition: 0.3s;
    }

    .sidebar a:hover {
        background-color: #343a40;
        color: white;
    }

    .sidebar a.active {
        background-color: #0d6efd;
        color: white;
        border-left-color: white;
    }

    .sidebar i {
        width: 25px;
    }

    .sidebar .menu-section {
        color: #adb5bd;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
    }

    .sidebar .brand-box {
        background: linear-gradient(135deg, #000 0%, #111827 100%);
    }
</style>
<script>
    const urlParamsSidebar = new URLSearchParams(window.location.search);
    if(urlParamsSidebar.get('msg') === 'pass_ok') Swal.fire('¡Éxito!', 'Tu contraseña ha sido actualizada.', 'success');
    if(urlParamsSidebar.get('err') === 'pass_mismatch') Swal.fire('Error', 'Las contraseñas no coinciden.', 'error');
</script>
