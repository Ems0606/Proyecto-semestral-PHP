<?php
/**
 * Header común para todas las páginas
 * Archivo: views/layouts/header.php
 */

// Incluir configuración si no está incluida
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../config/session.php';
}

// Obtener información del usuario actual
$usuario = obtenerUsuarioActual();
$esAuth = estaAutenticado();

// Obtener página actual para marcar navegación activa
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= APP_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= getBaseUrl() ?>/assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= getBaseUrl() ?>/assets/images/favicon.ico">
    
    <!-- Meta tags adicionales -->
    <meta name="description" content="<?= isset($pageDescription) ? $pageDescription : 'Sistema de gestión de tickets y soporte técnico' ?>">
    <meta name="author" content="<?= APP_NAME ?>">
</head>
<body>
    <!-- Contenedor principal -->
    <div class="wrapper">
        
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <!-- Logo -->
                <a href="<?= getBaseUrl() ?>/index.php" class="logo">
                    📧 <?= APP_NAME ?>
                </a>
                
                <!-- Información del usuario -->
                <div class="user-info">
                    <?php if ($esAuth): ?>
                        <!-- Avatar del usuario -->
                        <div class="user-avatar">
                            <?php if (!empty($usuario['foto_perfil'])): ?>
                                <img src="<?= getBaseUrl() ?>/assets/uploads/<?= htmlspecialchars($usuario['foto_perfil']) ?>" 
                                     alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Información del usuario -->
                        <div class="user-details">
                            <div style="font-weight: bold;"><?= htmlspecialchars($usuario['nombre']) ?></div>
                            <div style="font-size: 0.9em; opacity: 0.8;"><?= htmlspecialchars($usuario['rol']) ?></div>
                        </div>
                        
                        <!-- Menú de usuario -->
                        <div class="user-menu">
                            <a href="<?= getBaseUrl() ?>/views/auth/perfil.php" class="btn btn-sm btn-outline-primary">
                                Perfil
                            </a>
                            <a href="<?= getBaseUrl() ?>/controllers/AuthController.php?action=logout" class="btn btn-sm btn-danger">
                                Salir
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Enlaces para usuarios no autenticados -->
                        <div class="guest-links">
                            <a href="<?= getBaseUrl() ?>/views/auth/login.php" class="btn btn-sm btn-outline-primary">
                                Iniciar Sesión
                            </a>
                            <a href="<?= getBaseUrl() ?>/views/auth/register.php" class="btn btn-sm btn-primary">
                                Registrarse
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <!-- Navegación -->
        <?php if ($esAuth): ?>
        <nav class="navbar">
            <div class="nav-content">
                <ul class="nav-menu">
                    <!-- Menú para todos los usuarios autenticados -->
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>/views/public/home.php" 
                           class="<?= $paginaActual === 'home.php' ? 'active' : '' ?>">
                            🏠 Inicio
                        </a>
                    </li>
                    
                    <!-- Menú de tickets -->
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>/views/tickets/list.php" 
                           class="<?= strpos($paginaActual, 'tickets') !== false ? 'active' : '' ?>">
                            🎫 Mis Tickets
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>/views/tickets/create.php" 
                           class="<?= $paginaActual === 'create.php' ? 'active' : '' ?>">
                            ➕ Crear Ticket
                        </a>
                    </li>
                    
                    <!-- Menú para agentes -->
                    <?php if (esAgente()): ?>
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>/views/tickets/list.php?todos=1" 
                           class="<?= isset($_GET['todos']) ? 'active' : '' ?>">
                            📋 Todos los Tickets
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Menú para administradores -->
                    <?php if (esAdministrador()): ?>
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>/views/admin/dashboard.php" 
                           class="<?= $paginaActual === 'dashboard.php' ? 'active' : '' ?>">
                            📊 Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>/views/admin/manage_users.php" 
                           class="<?= $paginaActual === 'manage_users.php' ? 'active' : '' ?>">
                            👥 Usuarios
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>/views/admin/reports.php" 
                           class="<?= $paginaActual === 'reports.php' ? 'active' : '' ?>">
                            📈 Reportes
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Mesa de ayuda -->
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>/views/public/help.php" 
                           class="<?= $paginaActual === 'help.php' ? 'active' : '' ?>">
                            ❓ Ayuda
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <?php endif; ?>
        
        <!-- Contenedor para alertas -->
        <div id="alert-container"></div>
        
        <!-- Mostrar mensajes de sesión -->
        <?php if (isset($_SESSION['exito'])): ?>
            <div class="container">
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['exito']) ?>
                </div>
            </div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="container">
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['info'])): ?>
            <div class="container">
                <div class="alert alert-info">
                    <?= htmlspecialchars($_SESSION['info']) ?>
                </div>
            </div>
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['warning'])): ?>
            <div class="container">
                <div class="alert alert-warning">
                    <?= htmlspecialchars($_SESSION['warning']) ?>
                </div>
            </div>
            <?php unset($_SESSION['warning']); ?>
        <?php endif; ?>
        
        <!-- Contenido principal -->
        <main class="main-content"><?php
// El contenido de la página se insertará aquí
?>