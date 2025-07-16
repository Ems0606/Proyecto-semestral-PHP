<?php
/**
 * Configuración de conexión a la base de datos
 * Archivo: config/database.php
 * CONFIGURACIÓN PARA XAMPP - LOCALHOST/SISTEMA_TICKETS
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_tickets');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// CONFIGURACIÓN ESPECÍFICA PARA TU SETUP
define('APP_URL', 'http://localhost/sistema_tickets');
define('APP_NAME', 'Sistema de Tickets');
define('APP_VERSION', '1.0');

// Configuración de sesiones
define('SESSION_LIFETIME', 3600); // 1 hora en segundos

// Configuración de archivos
define('UPLOAD_PATH', 'assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt']);

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Configuración de email (para futuras implementaciones)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');

// Zona horaria
date_default_timezone_set('America/Panama');

// Configuración de errores (habilitar para desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Función para obtener la URL base de la aplicación
 */
function getBaseUrl() {
    return APP_URL;
}

/**
 * Función para obtener la ruta completa de un archivo
 */
function getPath($path) {
    return __DIR__ . '/../' . $path;
}

/**
 * Función para redirigir de forma segura
 */
function redirect($path) {
    $url = APP_URL . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit();
}

/**
 * Función para generar URLs de forma segura
 */
function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}
?>