<?php
/**
 * Página principal del sistema
 * Archivo: index.php
 */

// Incluir configuración
require_once 'config/database.php';
require_once 'config/session.php';

// Verificar si el usuario está autenticado
if (estaAutenticado() && !sesionExpirada()) {
    $usuario = obtenerUsuarioActual();
    
    // Redirigir según el rol
    switch ($usuario['rol']) {
        case 'Admin':
            header('Location: ' . APP_URL . '/views/admin/dashboard.php');
            break;
        case 'Agente':
            header('Location: ' . APP_URL . '/views/tickets/list.php');
            break;
        case 'Estudiante':
        case 'Colaborador':
        default:
            header('Location: ' . APP_URL . '/views/public/home.php');
            break;
    }
    exit();
} else {
    // Redirigir a la página de inicio público
    header('Location: ' . APP_URL . '/views/public/home.php');
    exit();
}
?>