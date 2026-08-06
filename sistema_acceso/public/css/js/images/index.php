<?php
// public/index.php

// 1. Habilitar visualización de errores (Solo para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Cargar la configuración de la base de datos
// Nota: Ajusta la ruta si es necesario. ".." significa subir un nivel.
require_once '../app/config/db.php';

// 3. Definir el controlador y acción por defecto
$controller = isset($_GET['c']) ? $_GET['c'] : 'Dashboard';
$action = isset($_GET['a']) ? $_GET['a'] : 'index';

// 4. Construir el nombre del archivo y la clase
$controllerName = ucfirst($controller) . 'Controller'; // Ej: AttendanceController
$controllerFile = '../app/controllers/' . $controllerName . '.php';

// 5. Verificar si el archivo del controlador existe
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
    // Si el controlador no existe, mostramos error o redirigimos
    echo "<h3>Error 404</h3>";
    echo "No se encontró el controlador: <code>$controllerFile</code><br>";
    echo "Verifica que el archivo exista en la carpeta <b>app/controllers</b>.";
}
?>