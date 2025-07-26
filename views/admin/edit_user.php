<?php
/**
 * Página para editar usuarios (solo administradores)
 * Archivo: views/admin/edit_user.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/UserController.php';

// Verificar permisos de administrador
requerirPermiso('usuarios', 'update');

// Obtener ID del usuario
$userId = intval($_GET['id'] ?? 0);

if (!$userId) {
    $_SESSION['error'] = "ID de usuario no válido";
    header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
    exit();
}

$userController = new UserController();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController->actualizar($userId);
}

// Obtener datos del usuario
$usuario = $userController->perfil($userId);

if (!$usuario) {
    $_SESSION['error'] = "Usuario no encontrado";
    header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
    exit();
}

// Obtener roles
$roles = $userController->obtenerRoles();

$pageTitle = "Editar Usuario - " . $usuario['primer_nombre'];
$pageDescription = "Editar información del usuario";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1>✏️ Editar Usuario</h1>
                        <p>Modificar información de: <strong><?= htmlspecialchars($usuario['primer_nombre'] . ' ' . $usuario['primer_apellido']) ?></strong></p>
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
    
    <!-- Formulario de edición -->
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
                        
                        <!-- Foto de perfil actual -->
                        <div class="form-group text-center mb-4">
                            <div class="profile-photo-container">
                                <?php if (!empty($usuario['foto_perfil'])): ?>
                                    <img src="<?= getBaseUrl() ?>/assets/uploads/<?= htmlspecialchars($usuario['foto_perfil']) ?>" 
                                         alt="Foto actual" 
                                         class="profile-photo"
                                         id="current-photo">
                                <?php else: ?>
                                    <div class="profile-photo-placeholder" id="current-photo">
                                        <span class="profile-initial">
                                            <?= strtoupper(substr($usuario['primer_nombre'], 0, 1)) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-3">
                                <label for="foto_perfil" class="btn btn-outline-primary">
                                    📷 Cambiar Foto
                                </label>
                                <input type="file" 
                                       id="foto_perfil" 
                                       name="foto_perfil" 
                                       class="form-control d-none" 
                                       accept="image/*"
                                       data-preview="#foto-preview">
                                <div class="form-text">JPG, PNG o GIF. Máximo 2MB</div>
                                <div id="foto-preview" class="mt-2"></div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Primer Nombre -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="primer_nombre" class="form-label">Primer Nombre: *</label>
                                    <input type="text" 
                                           id="primer_nombre" 
                                           name="primer_nombre" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($usuario['primer_nombre']) ?>"
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
                                           value="<?= htmlspecialchars($usuario['segundo_nombre'] ?? '') ?>">
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
                                           value="<?= htmlspecialchars($usuario['primer_apellido']) ?>"
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
                                           value="<?= htmlspecialchars($usuario['segundo_apellido'] ?? '') ?>">
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
                                           value="<?= htmlspecialchars($usuario['email']) ?>"
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
                                           value="<?= htmlspecialchars($usuario['identificacion']) ?>"
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Sexo -->
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="sexo" class="form-label">Sexo: *</label>
                                    <select id="sexo" name="sexo" class="form-control form-select" required>
                                        <option value="M" <?= $usuario['sexo'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                                        <option value="F" <?= $usuario['sexo'] === 'F' ? 'selected' : '' ?>>Femenino</option>
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
                                           value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>"
                                           required>
                                </div>
                            </div>
                            
                            <!-- Rol -->
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="rol_id" class="form-label">Rol del Usuario: *</label>
                                    <select id="rol_id" name="rol_id" class="form-control form-select" required>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= $rol['id'] ?>" 
                                                    <?= $usuario['rol_id'] == $rol['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rol['nombre']) ?> - <?= htmlspecialchars($rol['descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Estado del usuario -->
                        <div class="form-group">
                            <label class="d-flex align-items-center">
                                <input type="checkbox" name="activo" value="1" 
                                       <?= $usuario['activo'] ? 'checked' : '' ?> style="margin-right: 8px;">
                                Usuario activo (puede iniciar sesión)
                            </label>
                        </div>
                        
                        <!-- Botones -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                                        ✅ Guardar Cambios
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
            <!-- Información del usuario -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>📊 Información Actual</h4>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> <?= $usuario['id'] ?></p>
                    <p><strong>Estado:</strong> 
                        <span class="badge badge-<?= $usuario['activo'] ? 'success' : 'danger' ?>">
                            <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </p>
                    <p><strong>Rol Actual:</strong> <?= htmlspecialchars($usuario['rol_nombre']) ?></p>
                    <p><strong>Registro:</strong> <?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?></p>
                    <?php if ($usuario['updated_at'] && $usuario['updated_at'] != $usuario['created_at']): ?>
                    <p><strong>Última Act.:</strong> <?= date('d/m/Y H:i', strtotime($usuario['updated_at'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Acciones adicionales -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>⚙️ Acciones</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($usuario['id'] != obtenerUsuarioActual()['id']): ?>
                            <button class="btn btn-warning btn-sm" 
                                    onclick="resetPassword(<?= $usuario['id'] ?>)">
                                🔒 Cambiar Contraseña
                            </button>
                            
                            <button class="btn btn-<?= $usuario['activo'] ? 'danger' : 'success' ?> btn-sm" 
                                    onclick="toggleUserStatus(<?= $usuario['id'] ?>, '<?= $usuario['activo'] ? 'desactivar' : 'activar' ?>')">
                                <?= $usuario['activo'] ? '🚫 Desactivar' : '✅ Activar' ?>
                            </button>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <small>No puede modificar su propio estado</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Historial (si aplica) -->
            <div class="card">
                <div class="card-header">
                    <h4>📝 Notas</h4>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        • Los cambios se aplicarán inmediatamente<br>
                        • El usuario recibirá notificación por email<br>
                        • El historial se guarda automáticamente
                    </small>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para editar usuario */
.profile-photo-container {
    position: relative;
    display: inline-block;
}

.profile-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #007bff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.profile-photo-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #007bff, #0056b3);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid #007bff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.profile-initial {
    font-size: 3rem;
    font-weight: bold;
    color: white;
}

@media (max-width: 768px) {
    .col-8,
    .col-4,
    .col-6 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
    
    .profile-photo,
    .profile-photo-placeholder {
        width: 100px;
        height: 100px;
    }
    
    .profile-initial {
        font-size: 2.5rem;
    }
}
</style>

<script>
// JavaScript específico para editar usuario
document.addEventListener('DOMContentLoaded', function() {
    // Preview de foto de perfil
    const fotoInput = document.getElementById('foto_perfil');
    const currentPhoto = document.getElementById('current-photo');
    
    if (fotoInput && currentPhoto) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tipo de archivo
                if (!file.type.startsWith('image/')) {
                    alert('Por favor seleccione un archivo de imagen válido');
                    this.value = '';
                    return;
                }
                
                // Validar tamaño (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen es demasiado grande. Máximo 2MB permitido.');
                    this.value = '';
                    return;
                }
                
                // Mostrar preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (currentPhoto.tagName === 'IMG') {
                        currentPhoto.src = e.target.result;
                    } else {
                        // Reemplazar placeholder con imagen
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'profile-photo';
                        img.id = 'current-photo';
                        currentPhoto.parentNode.replaceChild(img, currentPhoto);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
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
    
    // Formateo de identificación
    const identificacionInput = document.getElementById('identificacion');
    if (identificacionInput) {
        identificacionInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9-]/g, '');
        });
    }
});

// Función para cambiar contraseña
function resetPassword(userId) {
    const newPassword = prompt('Ingrese la nueva contraseña (mínimo 6 caracteres):');
    if (newPassword && newPassword.length >= 6) {
        if (confirm('¿Está seguro que desea cambiar la contraseña de este usuario?')) {
            // Aquí se implementaría la llamada AJAX para cambiar contraseña
            alert('Funcionalidad de cambio de contraseña por implementar');
        }
    } else if (newPassword !== null) {
        alert('La contraseña debe tener al menos 6 caracteres');
    }
}

// Función para cambiar estado de usuario
function toggleUserStatus(userId, action) {
    const message = action === 'activar' ? 
        '¿Está seguro que desea activar este usuario?' : 
        '¿Está seguro que desea desactivar este usuario?';
    
    if (confirm(message)) {
        // Crear formulario para enviar solicitud
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= getBaseUrl() ?>/controllers/UserController.php?action=toggle_estado&id=' + userId;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= generarTokenCSRF() ?>';
        
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>