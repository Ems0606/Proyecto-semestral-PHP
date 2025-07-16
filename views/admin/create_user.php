<?php
/**
 * Página para crear usuarios (solo administradores)
 * Archivo: views/admin/create_user.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/UserController.php';

// Verificar permisos de administrador
requerirPermiso('usuarios', 'create');

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController = new UserController();
    $userController->crear();
}

// Obtener roles
$userController = new UserController();
$roles = $userController->obtenerRoles();

$pageTitle = "Crear Usuario";
$pageDescription = "Crear nuevo usuario en el sistema";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1>➕ Crear Nuevo Usuario</h1>
                        <p>Complete la información para crear un nuevo usuario</p>
                    </div>
                    <div>
                        <a href="<?= getBaseUrl() ?>/views/admin/manage_users.php" class="btn btn-secondary">
                            ← Volver a la lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formulario de creación -->
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3>📝 Información del Usuario</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" data-validate="true">
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
                            <div class="col-3">
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
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento: *</label>
                                    <input type="date" 
                                           id="fecha_nacimiento" 
                                           name="fecha_nacimiento" 
                                           class="form-control"
                                           value="<?= htmlspecialchars($_SESSION['form_data']['fecha_nacimiento'] ?? '') ?>"
                                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                                           required>
                                </div>
                            </div>
                            
                            <!-- Rol -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="rol_id" class="form-label">Rol del Usuario: *</label>
                                    <select id="rol_id" name="rol_id" class="form-control form-select" required>
                                        <option value="">Seleccionar rol</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= $rol['id'] ?>" 
                                                    <?= ($_SESSION['form_data']['rol_id'] ?? '') == $rol['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rol['nombre']) ?> - <?= htmlspecialchars($rol['descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Seleccione el rol apropiado según las responsabilidades del usuario</div>
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
                                    <div class="form-text">Mínimo 6 caracteres. Incluya números y letras para mayor seguridad</div>
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
                        
                        <!-- Estado inicial -->
                        <div class="form-group">
                            <label class="d-flex align-items-center">
                                <input type="checkbox" name="activo" value="1" checked style="margin-right: 8px;">
                                Usuario activo (puede iniciar sesión inmediatamente)
                            </label>
                        </div>
                        
                        <!-- Botones -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                                        ✅ Crear Usuario
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="<?= getBaseUrl() ?>/views/admin/manage_users.php" class="btn btn-secondary btn-lg" style="width: 100%;">
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
            <!-- Información sobre roles -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>🔐 Roles del Sistema</h4>
                </div>
                <div class="card-body">
                    <?php foreach ($roles as $rol): ?>
                        <div class="role-info mb-3">
                            <strong><?= htmlspecialchars($rol['nombre']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($rol['descripcion']) ?></small>
                            
                            <?php
                            $permisos = json_decode($rol['permisos'], true);
                            if ($permisos):
                            ?>
                            <div class="mt-2">
                                <strong>Permisos:</strong>
                                <ul class="list-sm">
                                    <?php foreach ($permisos as $modulo => $acciones): ?>
                                        <li><?= ucfirst($modulo) ?>: <?= implode(', ', $acciones) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($rol !== end($roles)): ?><hr><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Consejos de seguridad -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>🔒 Consejos de Seguridad</h4>
                </div>
                <div class="card-body">
                    <ul class="list-sm">
                        <li><strong>Contraseñas:</strong> Use combinaciones de letras, números y símbolos</li>
                        <li><strong>Roles:</strong> Asigne solo los permisos necesarios</li>
                        <li><strong>Emails:</strong> Verifique que sean válidos y únicos</li>
                        <li><strong>Información:</strong> Mantenga los datos actualizados</li>
                        <li><strong>Activación:</strong> Active usuarios solo cuando sea necesario</li>
                    </ul>
                </div>
            </div>
            
            <!-- Estadísticas rápidas -->
            <div class="card">
                <div class="card-header">
                    <h4>📊 Estadísticas</h4>
                </div>
                <div class="card-body">
                    <?php
                    $estadisticas = $userController->estadisticas();
                    ?>
                    <p><strong>Total usuarios:</strong> <?= $estadisticas['total_activos'] ?></p>
                    <p><strong>Registros este mes:</strong> <?= $estadisticas['este_mes'] ?></p>
                    
                    <div class="mt-3">
                        <strong>Por rol:</strong>
                        <?php foreach ($estadisticas['por_rol'] as $rolStat): ?>
                            <div class="d-flex justify-content-between">
                                <span><?= $rolStat['rol'] ?>:</span>
                                <span><?= $rolStat['total'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para crear usuario */
.role-info {
    padding: 10px;
    border-left: 3px solid var(--primary-color);
    background: #f8f9fa;
    border-radius: 4px;
}

.list-sm {
    font-size: 13px;
    margin-bottom: 0;
    padding-left: 15px;
}

.list-sm li {
    margin-bottom: 5px;
}

.form-text {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

#foto-preview img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    border: 2px solid #dee2e6;
}

.file-preview {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.file-info {
    flex: 1;
}

.file-icon {
    font-size: 2rem;
}

@media (max-width: 768px) {
    .col-8,
    .col-4,
    .col-6,
    .col-3 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 15px;
    }
    
    .btn-lg {
        margin-bottom: 10px;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
    }
}
</style>

<script>
// JavaScript específico para crear usuario
document.addEventListener('DOMContentLoaded', function() {
    // Validación de contraseñas en tiempo real
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
    
    // Generar contraseña automática
    const generatePasswordBtn = document.createElement('button');
    generatePasswordBtn.type = 'button';
    generatePasswordBtn.className = 'btn btn-sm btn-outline-secondary mt-2';
    generatePasswordBtn.textContent = '🎲 Generar Contraseña';
    generatePasswordBtn.onclick = generateRandomPassword;
    
    password.parentNode.appendChild(generatePasswordBtn);
    
    function generateRandomPassword() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let result = '';
        for (let i = 0; i < 12; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        password.value = result;
        confirmPassword.value = result;
        validatePasswords();
        
        // Mostrar la contraseña generada
        alert('Contraseña generada: ' + result + '\n\nAsegúrese de compartirla de forma segura con el usuario.');
    }
    
    // Validación de email único (simulada)
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            // Aquí se podría implementar una validación AJAX para verificar si el email ya existe
            console.log('Validando email:', email);
        }
    });
    
    // Formateo automático de identificación
    const identificacionInput = document.getElementById('identificacion');
    identificacionInput.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9-]/g, ''); // Solo números y guiones
        this.value = value;
    });
    
    // Mostrar información del rol seleccionado
    const rolSelect = document.getElementById('rol_id');
    rolSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const rolName = selectedOption.text.split(' - ')[0];
            console.log('Rol seleccionado:', rolName);
            
            // Mostrar información adicional sobre el rol
            if (rolName === 'Admin') {
                showAlert('⚠️ El rol de Administrador tiene acceso completo al sistema', 'warning');
            } else if (rolName === 'Agente') {
                showAlert('ℹ️ Los Agentes pueden gestionar tickets y responder consultas', 'info');
            }
        }
    });
    
    // Validación de edad
    const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
    fechaNacimientoInput.addEventListener('change', function() {
        const fechaNacimiento = new Date(this.value);
        const hoy = new Date();
        const edad = Math.floor((hoy - fechaNacimiento) / (365.25 * 24 * 60 * 60 * 1000));
        
        if (edad < 18) {
            showAlert('⚠️ El usuario debe ser mayor de 18 años', 'warning');
            this.value = '';
        } else if (edad > 100) {
            showAlert('⚠️ Verifique la fecha de nacimiento', 'warning');
        }
    });
    
    // Auto-capitalizar nombres
    const nameFields = ['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'];
    nameFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
            });
        }
    });
    
    // Indicador de fortaleza de contraseña
    const strengthIndicator = document.createElement('div');
    strengthIndicator.className = 'password-strength mt-1';
    strengthIndicator.innerHTML = '<small>Fortaleza: <span id="strength-text">Débil</span></small>';
    password.parentNode.appendChild(strengthIndicator);
    
    password.addEventListener('input', function() {
        const strength = calculatePasswordStrength(this.value);
        const strengthText = document.getElementById('strength-text');
        
        strengthText.textContent = strength.text;
        strengthText.className = strength.class;
    });
    
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
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>