<?php
/**
 * Página para cambiar contraseña
 * Archivo: views/auth/cambiar_password.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

// Verificar autenticación
requerirAutenticacion();

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $authController->cambiarPassword();
}

$usuario = obtenerUsuarioActual();
$pageTitle = "Cambiar Contraseña";
$pageDescription = "Actualizar contraseña de acceso";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1>🔒 Cambiar Contraseña</h1>
                        <p>Actualiza tu contraseña de acceso de forma segura</p>
                    </div>
                    <div>
                        <a href="<?= getBaseUrl() ?>/views/auth/perfil.php" class="btn btn-secondary">
                            ← Volver al Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3>🔐 Nueva Contraseña</h3>
                </div>
                <div class="card-body">
                    <form method="POST" data-validate="true">
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                        
                        <!-- Información del usuario -->
                        <div class="alert alert-info">
                            <strong>Usuario:</strong> <?= htmlspecialchars($usuario['nombre']) ?><br>
                            <strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?>
                        </div>
                        
                        <!-- Contraseña actual -->
                        <div class="form-group">
                            <label for="password_anterior" class="form-label">Contraseña Actual: *</label>
                            <input type="password" 
                                   id="password_anterior" 
                                   name="password_anterior" 
                                   class="form-control" 
                                   placeholder="Ingrese su contraseña actual"
                                   required>
                            <div class="form-text">Para verificar su identidad</div>
                        </div>
                        
                        <!-- Nueva contraseña -->
                        <div class="form-group">
                            <label for="password_nuevo" class="form-label">Nueva Contraseña: *</label>
                            <input type="password" 
                                   id="password_nuevo" 
                                   name="password_nuevo" 
                                   class="form-control" 
                                   placeholder="Mínimo 6 caracteres"
                                   minlength="6"
                                   required>
                            <div class="password-strength mt-1">
                                <small>Fortaleza: <span id="strength-text" class="text-warning">Débil</span></small>
                            </div>
                        </div>
                        
                        <!-- Confirmar nueva contraseña -->
                        <div class="form-group">
                            <label for="confirmar_password" class="form-label">Confirmar Nueva Contraseña: *</label>
                            <input type="password" 
                                   id="confirmar_password" 
                                   name="confirmar_password" 
                                   class="form-control" 
                                   placeholder="Repita la nueva contraseña"
                                   required>
                        </div>
                        
                        <!-- Consejos de seguridad -->
                        <div class="alert alert-warning">
                            <strong>💡 Consejos para una contraseña segura:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Use al menos 8 caracteres</li>
                                <li>Combine letras mayúsculas y minúsculas</li>
                                <li>Incluya números y símbolos</li>
                                <li>Evite información personal</li>
                                <li>No reutilice contraseñas de otras cuentas</li>
                            </ul>
                        </div>
                        
                        <!-- Botones -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                                        🔒 Cambiar Contraseña
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="<?= getBaseUrl() ?>/views/auth/perfil.php" class="btn btn-secondary btn-lg" style="width: 100%;">
                                        ❌ Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Panel lateral con información -->
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h4>🛡️ Seguridad de la Cuenta</h4>
                </div>
                <div class="card-body">
                    <div class="security-item mb-3">
                        <h6>📅 Último Cambio</h6>
                        <p class="text-muted">Primera vez</p>
                    </div>
                    
                    <div class="security-item mb-3">
                        <h6>🌐 Último Acceso</h6>
                        <p class="text-muted"><?= date('d/m/Y H:i:s') ?></p>
                    </div>
                    
                    <div class="security-item mb-3">
                        <h6>📱 Dispositivo</h6>
                        <p class="text-muted">Navegador web</p>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <h6>🔐 ¿Olvidó su contraseña?</h6>
                        <p class="small">Si no recuerda su contraseña actual, cierre sesión y use la opción "¿Olvidó su contraseña?" en la página de login.</p>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h4>⚙️ Configuración</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= getBaseUrl() ?>/views/auth/perfil.php" class="btn btn-outline-primary">
                            👤 Editar Perfil
                        </a>
                        <a href="<?= getBaseUrl() ?>/views/tickets/list.php" class="btn btn-outline-info">
                            🎫 Mis Tickets
                        </a>
                        <a href="<?= getBaseUrl() ?>/views/public/help.php" class="btn btn-outline-secondary">
                            ❓ Centro de Ayuda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para cambiar contraseña */
.security-item h6 {
    color: var(--primary-color);
    margin-bottom: 5px;
}

.security-item p {
    margin-bottom: 0;
    font-size: 14px;
}

.password-strength {
    height: 20px;
}

#strength-text {
    font-weight: 600;
}

.alert ul {
    margin-bottom: 0;
    padding-left: 20px;
}

.alert ul li {
    margin-bottom: 5px;
}

@media (max-width: 768px) {
    .col-6,
    .col-4 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
    
    .btn-lg {
        margin-bottom: 10px;
    }
}
</style>

<script>
// JavaScript específico para cambiar contraseña
document.addEventListener('DOMContentLoaded', function() {
    const passwordAnterior = document.getElementById('password_anterior');
    const passwordNuevo = document.getElementById('password_nuevo');
    const confirmarPassword = document.getElementById('confirmar_password');
    const strengthText = document.getElementById('strength-text');
    
    // Validación de contraseñas en tiempo real
    function validatePasswords() {
        if (passwordNuevo.value && confirmarPassword.value) {
            if (passwordNuevo.value !== confirmarPassword.value) {
                confirmarPassword.setCustomValidity('Las contraseñas no coinciden');
                confirmarPassword.classList.add('error');
            } else {
                confirmarPassword.setCustomValidity('');
                confirmarPassword.classList.remove('error');
            }
        }
    }
    
    // Indicador de fortaleza de contraseña
    function calculatePasswordStrength(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        switch (score) {
            case 0:
            case 1:
                return { text: 'Muy débil', class: 'text-danger' };
            case 2:
                return { text: 'Débil', class: 'text-warning' };
            case 3:
                return { text: 'Regular', class: 'text-info' };
            case 4:
                return { text: 'Fuerte', class: 'text-success' };
            case 5:
                return { text: 'Muy fuerte', class: 'text-success' };
            default:
                return { text: 'Débil', class: 'text-warning' };
        }
    }
    
    // Event listeners
    passwordNuevo.addEventListener('input', function() {
        const strength = calculatePasswordStrength(this.value);
        strengthText.textContent = strength.text;
        strengthText.className = strength.class;
        validatePasswords();
    });
    
    confirmarPassword.addEventListener('input', validatePasswords);
    
    // Mostrar/ocultar contraseña
    function addTogglePassword(inputId) {
        const input = document.getElementById(inputId);
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'btn btn-outline-secondary btn-sm position-absolute';
        toggleBtn.style.cssText = 'right: 10px; top: 50%; transform: translateY(-50%); z-index: 5;';
        toggleBtn.innerHTML = '👁️';
        toggleBtn.onclick = function() {
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '🙈';
            } else {
                input.type = 'password';
                this.innerHTML = '👁️';
            }
        };
        
        input.parentNode.style.position = 'relative';
        input.style.paddingRight = '45px';
        input.parentNode.appendChild(toggleBtn);
    }
    
    // Agregar botones para mostrar/ocultar contraseñas
    addTogglePassword('password_anterior');
    addTogglePassword('password_nuevo');
    addTogglePassword('confirmar_password');
    
    // Validación adicional al enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        const passwordAnteriorVal = passwordAnterior.value;
        const passwordNuevoVal = passwordNuevo.value;
        const confirmarPasswordVal = confirmarPassword.value;
        
        if (!passwordAnteriorVal || !passwordNuevoVal || !confirmarPasswordVal) {
            e.preventDefault();
            alert('Todos los campos son obligatorios');
            return;
        }
        
        if (passwordNuevoVal !== confirmarPasswordVal) {
            e.preventDefault();
            alert('Las contraseñas nuevas no coinciden');
            return;
        }
        
        if (passwordNuevoVal.length < 6) {
            e.preventDefault();
            alert('La nueva contraseña debe tener al menos 6 caracteres');
            return;
        }
        
        if (passwordAnteriorVal === passwordNuevoVal) {
            if (!confirm('La nueva contraseña es igual a la actual. ¿Está seguro de continuar?')) {
                e.preventDefault();
                return;
            }
        }
    });
    
    // Generar contraseña segura
    const generatePasswordBtn = document.createElement('button');
    generatePasswordBtn.type = 'button';
    generatePasswordBtn.className = 'btn btn-sm btn-outline-info mt-2';
    generatePasswordBtn.textContent = '🎲 Generar Contraseña Segura';
    generatePasswordBtn.onclick = function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let result = '';
        for (let i = 0; i < 12; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        passwordNuevo.value = result;
        confirmarPassword.value = result;
        
        // Actualizar indicador de fortaleza
        const strength = calculatePasswordStrength(result);
        strengthText.textContent = strength.text;
        strengthText.className = strength.class;
        
        validatePasswords();
        
        alert('Contraseña generada: ' + result + '\n\nAsegúrese de guardarla en un lugar seguro.');
    };
    
    passwordNuevo.parentNode.appendChild(generatePasswordBtn);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>