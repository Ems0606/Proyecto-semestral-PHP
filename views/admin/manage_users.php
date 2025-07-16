<?php
/**
 * Gestión de usuarios para administradores
 * Archivo: views/admin/manage_users.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/UserController.php';

// Verificar permisos de administrador
requerirPermiso('usuarios', 'read');

$userController = new UserController();

// Obtener usuarios
$resultados = $userController->listar();
$usuarios = $resultados['data'];
$totalPages = $resultados['total_pages'];
$currentPage = $resultados['page'];
$total = $resultados['total'];

// Obtener roles para filtros
$roles = $userController->obtenerRoles();

$pageTitle = "Gestión de Usuarios";
$pageDescription = "Administración de usuarios del sistema";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1>👥 Gestión de Usuarios</h1>
                        <p>Administración y control de usuarios del sistema</p>
                    </div>
                    <div>
                        <a href="<?= getBaseUrl() ?>/views/admin/create_user.php" class="btn btn-success">
                            ➕ Crear Usuario
                        </a>
                        <a href="<?= getBaseUrl() ?>/controllers/UserController.php?action=exportar_csv" class="btn btn-secondary">
                            📥 Exportar CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros de búsqueda -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>🔍 Filtros de Búsqueda</h4>
                </div>
                <div class="card-body">
                    <form id="filtros-form" method="GET">
                        <div class="row">
                            <!-- Buscar por nombre -->
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="nombre" class="form-label">Buscar por nombre:</label>
                                    <input type="text" 
                                           id="nombre" 
                                           name="nombre" 
                                           class="form-control" 
                                           placeholder="Nombre o apellido"
                                           value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <!-- Filtrar por rol -->
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="rol_id" class="form-label">Filtrar por rol:</label>
                                    <select id="rol_id" name="rol_id" class="form-control form-select">
                                        <option value="">Todos los roles</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?= $rol['id'] ?>" <?= ($_GET['rol_id'] ?? '') == $rol['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rol['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Botones -->
                            <div class="col-5">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            🔍 Buscar
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="clearFilters('#filtros-form')">
                                            🗑️ Limpiar
                                        </button>
                                        <div class="ms-auto">
                                            <span class="badge badge-info">Total: <?= $total ?> usuarios</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de usuarios -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>📄 Lista de Usuarios</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($usuarios)): ?>
                        <div class="text-center py-5">
                            <h4>👤 No hay usuarios</h4>
                            <p>No se encontraron usuarios con los criterios seleccionados.</p>
                            <a href="<?= getBaseUrl() ?>/views/admin/create_user.php" class="btn btn-success">
                                ➕ Crear primer usuario
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Foto</th>
                                        <th>Nombre Completo</th>
                                        <th>Email</th>
                                        <th>Identificación</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><strong><?= $usuario['id'] ?></strong></td>
                                        <td>
                                            <?php if (!empty($usuario['foto_perfil'])): ?>
                                                <img src="<?= getBaseUrl() ?>/assets/uploads/<?= htmlspecialchars($usuario['foto_perfil']) ?>" 
                                                     alt="Foto" 
                                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; 
                                                            display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                    <?= strtoupper(substr($usuario['primer_nombre'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>
                                                    <?= htmlspecialchars($usuario['primer_nombre'] . ' ' . ($usuario['segundo_nombre'] ? $usuario['segundo_nombre'] . ' ' : '')) ?>
                                                    <?= htmlspecialchars($usuario['primer_apellido'] . ' ' . ($usuario['segundo_apellido'] ?? '')) ?>
                                                </strong>
                                            </div>
                                            <small class="text-muted">
                                                Edad: <?= date_diff(date_create($usuario['fecha_nacimiento']), date_create())->y ?> años
                                            </small>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($usuario['email']) ?>">
                                                <?= htmlspecialchars($usuario['email']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($usuario['identificacion']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= 
                                                $usuario['rol_nombre'] === 'Admin' ? 'danger' : 
                                                ($usuario['rol_nombre'] === 'Agente' ? 'warning' : 'primary') 
                                            ?>">
                                                <?= htmlspecialchars($usuario['rol_nombre']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $usuario['activo'] ? 'success' : 'secondary' ?>">
                                                <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('d/m/Y', strtotime($usuario['created_at'])) ?><br>
                                                <?= date('H:i', strtotime($usuario['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= getBaseUrl() ?>/views/admin/edit_user.php?id=<?= $usuario['id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    ✏️ Editar
                                                </a>
                                                
                                                <?php if ($usuario['id'] != obtenerUsuarioActual()['id']): ?>
                                                    <button class="btn btn-outline-warning btn-sm" 
                                                            onclick="toggleUserStatus(<?= $usuario['id'] ?>, '<?= $usuario['activo'] ? 'desactivar' : 'activar' ?>')">
                                                        <?= $usuario['activo'] ? '🚫 Desactivar' : '✅ Activar' ?>
                                                    </button>
                                                    
                                                    <button class="btn btn-outline-danger btn-sm" 
                                                            onclick="confirmDeleteUser(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['primer_nombre'] . ' ' . $usuario['primer_apellido']) ?>')">
                                                        🗑️ Eliminar
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge badge-info">Usuario actual</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $currentURL = $_SERVER['REQUEST_URI'];
                            $baseURL = strtok($currentURL, '?');
                            $params = $_GET;
                            ?>
                            
                            <?php if ($currentPage > 1): ?>
                                <?php
                                $params['page'] = $currentPage - 1;
                                $prevURL = $baseURL . '?' . http_build_query($params);
                                ?>
                                <a href="<?= $prevURL ?>">« Anterior</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <?php
                                    $params['page'] = $i;
                                    $pageURL = $baseURL . '?' . http_build_query($params);
                                    ?>
                                    <a href="<?= $pageURL ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <?php
                                $params['page'] = $currentPage + 1;
                                $nextURL = $baseURL . '?' . http_build_query($params);
                                ?>
                                <a href="<?= $nextURL ?>">Siguiente »</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Modal de confirmación para eliminar usuario -->
<div id="delete-user-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>🗑️ Confirmar Eliminación</h3>
            <button type="button" class="btn-close" onclick="closeModal('delete-user-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>¿Está seguro que desea eliminar al usuario <strong id="delete-user-name"></strong>?</p>
            <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('delete-user-modal')">
                Cancelar
            </button>
            <form id="delete-user-form" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                <button type="submit" class="btn btn-danger">
                    🗑️ Eliminar Usuario
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación para cambiar estado -->
<div id="toggle-status-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="toggle-status-title">Cambiar Estado</h3>
            <button type="button" class="btn-close" onclick="closeModal('toggle-status-modal')">&times;</button>
        </div>
        <div class="modal-body">
            <p id="toggle-status-message"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('toggle-status-modal')">
                Cancelar
            </button>
            <form id="toggle-status-form" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                <button type="submit" class="btn btn-warning" id="toggle-status-btn">
                    Confirmar
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para gestión de usuarios */
.btn-group-sm .btn {
    padding: 4px 8px;
    font-size: 12px;
    margin-right: 2px;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: var(--border-radius);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6c757d;
}

.btn-close:hover {
    color: #000;
}

@media (max-width: 768px) {
    .col-4,
    .col-3,
    .col-5 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 10px;
    }
    
    .table {
        font-size: 12px;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 5px;
        margin-right: 0;
    }
}
</style>

<script>
// Función para limpiar filtros
function clearFilters(formSelector) {
    const form = document.querySelector(formSelector);
    if (form) {
        form.reset();
        form.submit();
    }
}

// Función para confirmar eliminación de usuario
function confirmDeleteUser(userId, userName) {
    document.getElementById('delete-user-name').textContent = userName;
    document.getElementById('delete-user-form').action = 
        '<?= getBaseUrl() ?>/controllers/UserController.php?action=eliminar&id=' + userId;
    openModal('delete-user-modal');
}

// Función para cambiar estado de usuario
function toggleUserStatus(userId, action) {
    const title = action === 'activar' ? '✅ Activar Usuario' : '🚫 Desactivar Usuario';
    const message = action === 'activar' ? 
        '¿Está seguro que desea activar este usuario?' : 
        '¿Está seguro que desea desactivar este usuario?';
    const btnText = action === 'activar' ? '✅ Activar' : '🚫 Desactivar';
    
    document.getElementById('toggle-status-title').textContent = title;
    document.getElementById('toggle-status-message').textContent = message;
    document.getElementById('toggle-status-btn').textContent = btnText;
    document.getElementById('toggle-status-form').action = 
        '<?= getBaseUrl() ?>/controllers/UserController.php?action=toggle_estado&id=' + userId;
    
    openModal('toggle-status-modal');
}

// Función para búsqueda en tiempo real
document.getElementById('nombre').addEventListener('input', function() {
    const query = this.value.trim();
    if (query.length >= 2) {
        // Implementar búsqueda AJAX si se desea
        console.log('Buscando:', query);
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>