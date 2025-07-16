<?php
/**
 * Página de registro
 * Archivo: views/auth/register.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/User.php';

// Si ya está autenticado, redirigir
if (estaAutenticado()) {
    header('Location: ' . getBaseUrl() . '/views/public/home.php');
    exit();
}

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $authController = new AuthController();
    $authController->registro();
}

// Obtener roles disponibles para registro (solo Estudiante y Colaborador)
$userModel = new User();
$roles = $userModel->obtenerRoles();
$rolesPermitidos = array_filter($roles, function($rol) {
    return in_array($rol['nombre'], ['Estudiante', 'Colaborador']);
});

$pageTitle = "Registro de Usuario";
$pageDescription = "Cree su cuenta en el sistema de tickets";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-8">
            <div class="card">
                <div class="card-header text-center">
                    <h2>Crear Cuenta</h2>
                    <p>Complete el formulario para registrarse en el sistema</p>
                </div>
                
                <div class="card-body">
                    <form method="POST" data-validate="true" enctype="multipart/form-data">
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                        
                        <div class="row">
                            <!-- Primer Nombre -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="primer_nombre" class="form-label">Primer Nombre: *</label>
                                    <input type="text" 
                                           id="primer_nombre" 
                                           name="primer_nombre" 
                                           class="form-control" 
                                           placeholder="Juan"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['primer_nombre'] ?? '') ?>"
                                           required>
                                </div>
                            </div>
                            
                            <!-- Segundo Nombre -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="segundo_nombre" class="form-label">Segundo Nombre:</label>
                                    <input type="text" 
                                           id="segundo_nombre" 
                                           name="segundo_nombre" 
                                           class="form-control" 
                                           placeholder="Carlos"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['segundo_nombre'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Primer Apellido -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="primer_apellido" class="form-label">Primer Apellido: *</label>
                                    <input type="text" 
                                           id="primer_apellido" 
                                           name="primer_apellido" 
                                           class="form-control" 
                                           placeholder="Pérez"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['primer_apellido'] ?? '') ?>"
                                           required>
                                </div>
                            </div>
                            
                            <!-- Segundo Apellido -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="segundo_apellido" class="form-label">Segundo Apellido:</label>
                                    <input type="text" 
                                           id="segundo_apellido" 
                                           name="segundo_apellido" 
                                           class="form-control" 
                                           placeholder="González"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['segundo_apellido'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Email -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email: *</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control" 
                                           placeholder="juan.perez@correo.com"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>"
                                           required>
                                </div>
                            </div>
                            
                            <!-- Identificación -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="identificacion" class="form-label">Identificación: *</label>
                                    <input type="text" 
                                           id="identificacion" 
                                           name="identificacion" 
                                           class="form-control" 
                                           placeholder="8-123-456"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['identificacion'] ?? '') ?>"
                                           required>
                                    <div class="form-text">Cédula, pasaporte o identificación estudiantil</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Sexo -->
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="sexo" class="form-label">Sexo: *</label>
                                    <select id="sexo" name="sexo" class="form-control form-select" required>
                                        <option value="">Seleccionar</option>
                                        <option value="M" <?= ($_SESSION['form_data']['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                        <option value="F" <?= ($_SESSION['form_data']['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Fecha de Nacimiento -->
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento: *</label>
                                    <input type="date" 
                                           id="fecha_nacimiento" 
                                           name="fecha_nacimiento" 
                                           class="form-control"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['fecha_nacimiento'] ?? '') ?>"
                                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                                           required>
                                    <div class="form-text">Debe ser mayor de 18 años</div>
                                </div>
                            </div>
                            
                            <!-- Rol -->
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="rol_id" class="form-label">Tipo de Usuario: *</label>
                                    <select id="rol_id" name="rol_id" class="form-control form-select" required>
                                        <option value="">Seleccionar</option>
                                        <?php foreach ($rolesPermitidos as $rol): ?>
                                            <option value="<?= $rol['id'] ?>" 
                                                    <?= ($_SESSION['form_data']['rol_id'] ?? '') == $rol['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rol['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Contraseña -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">Contraseña: *</label>
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           class="form-control" 
                                           placeholder="Mínimo 6 caracteres"
                                           minlength="6"
                                           required>
                                    <div class="form-text">Mínimo 6 caracteres</div>
                                </div>
                            </div>
                            
                            <!-- Confirmar Contraseña -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="confirmar_password" class="form-label">Confirmar Contraseña: *</label>
                                    <input type="password" 
                                           id="confirmar_password" 
                                           name="confirmar_password" 
                                           class="form-control" 
                                           placeholder="Repita la contraseña"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Foto de Perfil -->
                        <div class="form-group">
                            <label for="foto_perfil" class="form-label">Foto de Perfil (Opcional):</label>
                            <input type="file" 
                                   id="foto_perfil" 
                                   name="foto_perfil" 
                                   class="form-control" 
                                   accept="image/*"
                                   data-preview="#foto-preview">
                            <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB</div>
                            <div id="foto-preview" class="mt-2"></div>
                        </div>
                        
                        <!-- Términos y condiciones -->
                        <div class="form-group">
                            <label class="d-flex align-items-center">
                                <input type="checkbox" name="acepta_terminos" value="1" required style="margin-right: 8px;">
                                Acepto los <a href="<?= getBaseUrl() ?>/views/public/terms.php" target="_blank">términos y condiciones</a> 
                                y la <a href="<?= getBaseUrl() ?>/views/public/privacy.php" target="_blank">política de privacidad</a>
                            </label>
                        </div>
                        
                        <!-- Botón de submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                                ✅ Crear Cuenta
                            </button>
                        </div>
                    </form>
                    
                    <!-- Enlaces adicionales -->
                    <div class="text-center mt-3">
                        <hr>
                        <p>
                            ¿Ya tiene cuenta? 
                            <a href="<?= getBaseUrl() ?>/views/auth/login.php" class="btn btn-outline-primary">
                                Iniciar Sesión
                            </a>
                        </p>
                        <p>
                            <a href="<?= getBaseUrl() ?>/views/public/home.php">
                                🏠 Volver al inicio
                            </a>
                        </p>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="card-footer">
                    <small class="text-muted">
                        <strong>Nota:</strong> Todos los campos marcados con (*) son obligatorios. 
                        Su información será utilizada únicamente para la gestión de tickets y soporte técnico.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para la página de registro */
.main-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: calc(100vh - 140px);
    padding: 40px 20px;
}

.card {
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    border: none;
    border-radius: 15px;
}

.card-header {
    background: linear-gradient(135deg, var(--success-color), #1e7e34);
    color: white;
    border-radius: 15px 15px 0 0 !important;
}

.form-control:focus {
    box-shadow: 0 0 0 3px rgba(40,167,69,0.25);
    border-color: var(--success-color);
}

.form-text {
    font-size: 12px;
    color: #6c757d;
}

@media (max-width: 768px) {
    .col-8 {
        flex: 0 0 95%;
        max-width: 95%;
    }
    
    .col-6,
    .col-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

<script>
// Validación de contraseñas en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmar_password');
    
    function validatePasswords() {
        if (password.value && confirmPassword.value) {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
                confirmPassword.classList.add('error');
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.classList.remove('error');
            }
        }
    }
    
    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>