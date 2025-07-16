<?php
/**
 * Configuración y manejo de sesiones
 * Archivo: config/session.php
 */

// Incluir configuración de base de datos
require_once 'database.php';

/**
 * Inicializar sesión si no está iniciada
 */
function iniciarSesion() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verificar si el usuario está autenticado
 */
function estaAutenticado() {
    iniciarSesion();
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Obtener información del usuario actual
 */
function obtenerUsuarioActual() {
    iniciarSesion();
    if (estaAutenticado()) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'email' => $_SESSION['usuario_email'],
            'rol' => $_SESSION['usuario_rol'],
            'rol_id' => $_SESSION['usuario_rol_id'],
            'permisos' => json_decode($_SESSION['usuario_permisos'] ?? '{}', true)
        ];
    }
    return null;
}

/**
 * Establecer datos de sesión del usuario
 */
function establecerSesion($usuario) {
    iniciarSesion();
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['primer_nombre'] . ' ' . $usuario['primer_apellido'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_rol'] = $usuario['rol_nombre'];
    $_SESSION['usuario_rol_id'] = $usuario['rol_id'];
    $_SESSION['usuario_permisos'] = $usuario['permisos'];
    $_SESSION['login_time'] = time();
}

/**
 * Destruir sesión del usuario
 */
function cerrarSesion() {
    iniciarSesion();
    session_destroy();
    header('Location: ' . getBaseUrl() . '/index.php');
    exit();
}

/**
 * Verificar si la sesión ha expirado
 */
function sesionExpirada() {
    iniciarSesion();
    if (isset($_SESSION['login_time'])) {
        return (time() - $_SESSION['login_time']) > SESSION_LIFETIME;
    }
    return true;
}

/**
 * Renovar tiempo de sesión
 */
function renovarSesion() {
    iniciarSesion();
    $_SESSION['login_time'] = time();
}

/**
 * Verificar permisos del usuario
 */
function tienePermiso($modulo, $accion) {
    $usuario = obtenerUsuarioActual();
    if (!$usuario) return false;
    
    $permisos = $usuario['permisos'];
    return isset($permisos[$modulo]) && in_array($accion, $permisos[$modulo]);
}

/**
 * Verificar si el usuario es administrador
 */
function esAdministrador() {
    $usuario = obtenerUsuarioActual();
    return $usuario && $usuario['rol'] === 'Admin';
}

/**
 * Verificar si el usuario es agente
 */
function esAgente() {
    $usuario = obtenerUsuarioActual();
    return $usuario && ($usuario['rol'] === 'Agente' || $usuario['rol'] === 'Admin');
}

/**
 * Redirigir si no está autenticado
 */
function requerirAutenticacion() {
    if (!estaAutenticado() || sesionExpirada()) {
        header('Location: ' . getBaseUrl() . '/views/auth/login.php');
        exit();
    }
    renovarSesion();
}

/**
 * Redirigir si no tiene permisos
 */
function requerirPermiso($modulo, $accion) {
    requerirAutenticacion();
    if (!tienePermiso($modulo, $accion)) {
        header('Location: ' . getBaseUrl() . '/views/public/home.php?error=sin_permisos');
        exit();
    }
}

/**
 * Generar token CSRF
 */
function generarTokenCSRF() {
    iniciarSesion();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verificarTokenCSRF($token) {
    iniciarSesion();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Inicializar sesión automáticamente
iniciarSesion();
?>