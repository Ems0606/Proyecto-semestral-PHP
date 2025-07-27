<?php
/**
 * Página de login
 * Archivo: views/auth/login.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

// Si ya está autenticado, redirigir
if (estaAutenticado()) {
    $usuario = obtenerUsuarioActual();
    switch ($usuario['rol']) {
        case 'Admin':
            header('Location: ' . getBaseUrl() . '/views/admin/dashboard.php');
            break;
        case 'Agente':
            header('Location: ' . getBaseUrl() . '/views/tickets/list.php');
            break;
        default:
            header('Location: ' . getBaseUrl() . '/views/public/home.php');
            break;
    }
    exit();
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $authController->login();
}

$pageTitle = "Iniciar Sesión";
$pageDescription = "Acceda a su cuenta en el sistema de tickets";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="justify-content-center">
        <div class="col-18">
            <div class="card">
                <div class="card-header text-center" style="flex-direction: column;">
                    <h2>Iniciar Sesión</h2> 
                    <p>Acceda a su cuenta para gestionar tickets</p>
                </div>
                
                <div class="card-body">
                    <form method="POST" data-validate="true">
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                        
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   placeholder="ejemplo@correo.com"
                                   value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>"
                                   required>
                        </div>
                        
                        <!-- Password -->
                        <div class="form-group">
                            <label for="password" class="form-label">Contraseña:</label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Ingrese su contraseña"
                                   required>
                        </div>
                        
                        <!-- Recordar sesión -->
                        <div class="form-group">
                            <label class="d-flex align-items-center">
                                <input type="checkbox" name="recordar" value="1" style="margin-right: 8px;">
                                Recordar mi sesión
                            </label>
                        </div>
                        
                        <!-- Botón de submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                🔐 Iniciar Sesión
                            </button>
                        </div>
                    </form>
                    
                    <!-- Enlaces adicionales -->
                    <div class="text-center mt-3">
                        <p>
                            <a href="<?= getBaseUrl() ?>/views/auth/recuperar_password.php">
                                ¿Olvidó su contraseña?
                            </a>
                        </p>
                        <hr>
                        <p>
                            ¿No tiene cuenta? 
                            <a href="<?= getBaseUrl() ?>/views/auth/register.php" class="btn btn-outline-primary">
                                Registrarse
                            </a>
                        </p>
                        <p>
                            <a href="<?= getBaseUrl() ?>/views/public/home.php">
                                🏠 Volver al inicio
                            </a>
                        </p>
                    </div>
                </div>
                
                <!-- Información del sistema -->
                <div class="card-footer text-center">
                    <small class="text-muted">
                        <strong>Credenciales de prueba:</strong><br>
                        Admin: admin@sistema.com / password<br>
                        <em>Sistema de Tickets v<?= APP_VERSION ?></em>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para la página de login */
.main-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: calc(100vh - 140px);
    justify-content: center;
    align-items: center;
}

.card {
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border: none;
    border-radius: 15px;
}

.card-header {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: white;
    border-radius: 15px 15px 0 0 !important;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
    border-color: var(--primary-color);
}

@media (max-width: 768px) {
    .col-6 {
        flex: 0 0 95%;
        max-width: 95%;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>