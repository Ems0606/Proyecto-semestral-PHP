<?php
/**
 * Página de perfil de usuario - VERSIÓN CORREGIDA
 * Archivo: views/auth/perfil.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../models/Ticket.php';

// Verificar autenticación
requerirAutenticacion();

$userController = new UserController();
$ticketModel = new Ticket();

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioActual = obtenerUsuarioActual();
    $userController->actualizar($usuarioActual['id']);
}

// Obtener datos del usuario actual
$usuarioActual = obtenerUsuarioActual();
$perfil = $userController->obtenerDatosPrincipales($usuarioActual['id']);


// Verificar que el usuario existe
if (!$perfil) {
    $_SESSION['error'] = "Error al cargar el perfil del usuario";
    header('Location: ' . getBaseUrl() . '/views/public/home.php');
    exit();
}

// Obtener estadísticas de tickets del usuario
$estadisticasTickets = $ticketModel->obtenerPorUsuario($usuarioActual['id'], 1);
$misTickets = $estadisticasTickets['data'] ?? [];

// Calcular estadísticas
$totalTickets = $estadisticasTickets['total'] ?? 0;
$ticketsAbiertos = count(array_filter($misTickets, function($t) { return $t['estado'] === 'abierto'; }));
$ticketsEnProceso = count(array_filter($misTickets, function($t) { return $t['estado'] === 'en_proceso'; }));
$ticketsResueltos = count(array_filter($misTickets, function($t) { return in_array($t['estado'], ['resuelto', 'cerrado']); }));
$ticketsCerrados = count(array_filter($misTickets, function($t) { return $t['estado'] === 'cerrado'; }));

// Tickets recientes (últimos 5)
$ticketsRecientes = array_slice($misTickets, 0, 5);

$pageTitle = "Mi Perfil";
$pageDescription = "Información personal y configuración de cuenta";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h1>👤 Mi Perfil</h1>
                    <p>Información personal y configuración de cuenta</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Información personal -->
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3>📝 Información Personal</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" data-validate="true">
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                        
                        <!-- Foto de perfil -->
                        <div class="form-group text-center mb-4">
                            <div class="profile-photo-container">
                                <?php if (!empty($perfil['foto_perfil'])): ?>
                                    <img src="<?= getBaseUrl() ?>/assets/uploads/<?= htmlspecialchars($perfil['foto_perfil']) ?>" 
                                         alt="Foto de perfil" 
                                         class="profile-photo"
                                         id="current-photo">
                                <?php else: ?>
                                    <div class="profile-photo-placeholder" id="current-photo">
                                        <span class="profile-initial">
                                            <?= strtoupper(substr($perfil['primer_nombre'] ?? 'U', 0, 1)) ?>
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
                                           value="<?= htmlspecialchars($perfil['primer_nombre'] ?? '') ?>"
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
                                           value="<?= htmlspecialchars($perfil['segundo_nombre'] ?? '') ?>">
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
                                           value="<?= htmlspecialchars($perfil['primer_apellido'] ?? '') ?>"
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
                                           value="<?= htmlspecialchars($perfil['segundo_apellido'] ?? '') ?>">
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
                                           value="<?= htmlspecialchars($perfil['email'] ?? '') ?>"
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
                                           value="<?= htmlspecialchars($perfil['identificacion'] ?? '') ?>"
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
                                        <option value="M" <?= ($perfil['sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                        <option value="F" <?= ($perfil['sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
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
                                           value="<?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '') ?>"
                                           required>
                                </div>
                            </div>
                            
                            <!-- Rol (solo lectura) -->
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="rol" class="form-label">Rol:</label>
                                    <input type="text" 
                                           id="rol" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($perfil['rol_nombre'] ?? 'Sin rol') ?>"
                                           readonly>
                                    <div class="form-text">El rol solo puede ser cambiado por un administrador</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información adicional (solo lectura) -->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Fecha de Registro:</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="<?= isset($perfil['created_at']) ? date('d/m/Y H:i', strtotime($perfil['created_at'])) : 'No disponible' ?>"
                                           readonly>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Última Actualización:</label>
                                    <input type="text" 
                                           class="form-control" 
                                           value="<?= isset($perfil['updated_at']) ? date('d/m/Y H:i', strtotime($perfil['updated_at'])) : 'No disponible' ?>"
                                           readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-success btn-lg" style="width: 100%;">
                                        ✅ Actualizar Perfil
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="<?= getBaseUrl() ?>/views/auth/cambiar_password.php" class="btn btn-warning btn-lg" style="width: 100%;">
                                        🔒 Cambiar Contraseña
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Panel lateral con estadísticas -->
        <div class="col-4">
            <!-- Estadísticas -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>📊 Estadísticas</h4>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <div class="stat-number text-primary"><?= $totalTickets ?></div>
                        <div class="stat-label">Tickets Totales</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="stat-item-small">
                                <div class="stat-number-small text-info"><?= $ticketsAbiertos ?></div>
                                <div class="stat-label-small">Abiertos</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item-small">
                                <div class="stat-number-small text-warning"><?= $ticketsEnProceso ?></div>
                                <div class="stat-label-small">En Proceso</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="stat-item-small">
                                <div class="stat-number-small text-success"><?= $ticketsResueltos ?></div>
                                <div class="stat-label-small">Resueltos</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item-small">
                                <div class="stat-number-small text-secondary"><?= $ticketsCerrados ?></div>
                                <div class="stat-label-small">Cerrados</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tickets recientes -->
            <div class="card">
                <div class="card-header">
                    <h4>🎫 Tickets Recientes</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($ticketsRecientes)): ?>
                        <div class="text-center">
                            <p class="text-muted">No hay tickets aún</p>
                            <a href="<?= getBaseUrl() ?>/views/tickets/create.php" class="btn btn-primary btn-sm">
                                Crear Primer Ticket
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ticketsRecientes as $ticket): ?>
                            <div class="ticket-item mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="ticket-title">
                                            <a href="<?= getBaseUrl() ?>/views/tickets/view.php?id=<?= $ticket['id'] ?>">
                                                #<?= $ticket['id'] ?> - <?= htmlspecialchars(substr($ticket['titulo'] ?? '', 0, 30)) ?>
                                                <?= strlen($ticket['titulo'] ?? '') > 30 ? '...' : '' ?>
                                            </a>
                                        </div>
                                        <div class="ticket-meta">
                                            <small class="text-muted">
                                                <?= isset($ticket['fecha_creacion']) ? date('d/m/Y', strtotime($ticket['fecha_creacion'])) : 'Sin fecha' ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="ticket-status">
                                        <span class="badge badge-<?= 
                                            ($ticket['estado'] ?? '') === 'abierto' ? 'info' : 
                                            (($ticket['estado'] ?? '') === 'en_proceso' ? 'warning' : 
                                            (($ticket['estado'] ?? '') === 'resuelto' ? 'success' : 'secondary')) 
                                        ?> badge-sm">
                                            <?= ucfirst($ticket['estado'] ?? 'Desconocido') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="<?= getBaseUrl() ?>/views/tickets/list.php" class="btn btn-outline-primary btn-sm">
                                Ver Todos los Tickets
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para el perfil */
.profile-photo-container {
    position: relative;
    display: inline-block;
}

.profile-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #007bff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.profile-photo-placeholder {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #007bff, #0056b3);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid #007bff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.profile-initial {
    font-size: 4rem;
    font-weight: bold;
    color: white;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    color: #6c757d;
    margin-top: 5px;
}

.stat-item-small {
    text-align: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 10px;
}

.stat-number-small {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.stat-label-small {
    font-size: 12px;
    color: #6c757d;
    margin-top: 2px;
}

.ticket-item {
    padding: 10px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: #f8f9fa;
}

.ticket-title a {
    text-decoration: none;
    color: #007bff;
    font-weight: 500;
    font-size: 13px;
}

.ticket-title a:hover {
    text-decoration: underline;
}

.ticket-meta {
    margin-top: 2px;
}

.badge-sm {
    font-size: 10px;
    padding: 2px 6px;
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
        width: 120px;
        height: 120px;
    }
    
    .profile-initial {
        font-size: 3rem;
    }
}
</style>

<script>
// JavaScript específico para el perfil
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
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>