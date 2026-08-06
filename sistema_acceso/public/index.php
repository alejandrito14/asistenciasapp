<?php
// ==========================================
// 1. CONFIGURACIÓN GLOBAL
// ==========================================

// Ajuste de Zona Horaria (Perú -05:00)
// Esto arregla que la hora de entrada salga 5 horas adelantada.
date_default_timezone_set('America/Mexico_City');

// Habilitar visualización de errores (Solo para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================
// 2. SISTEMA DE ENRUTAMIENTO
// ==========================================

// Cargar conexión a BD
require_once '../app/config/db.php';

// Definir controlador y acción por defecto
$controller = isset($_GET['c']) ? $_GET['c'] : 'Dashboard';
$action = isset($_GET['a']) ? $_GET['a'] : 'index';

// Construir nombres de archivo y clase
$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = '../app/controllers/' . $controllerName . '.php';

// Verificar existencia y cargar
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controllerObject = new $controllerName();
        
        if (method_exists($controllerObject, $action)) {
            $controllerObject->$action();
        } else {
            echo "Error: La acción '$action' no existe en el controlador '$controllerName'.";
        }
    } else {
        echo "Error: La clase '$controllerName' no se encontró.";
    }
} else {
    // Si la página no existe, mandar al Login
    header("Location: ?c=Auth&a=login");
    exit;
}
?>