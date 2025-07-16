<?php
/**
 * Página para listar tickets
 * Archivo: views/tickets/list.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/TicketController.php';
require_once __DIR__ . '/../../models/Ticket.php';

// Verificar autenticación
requerirAutenticacion();

$usuario = obtenerUsuarioActual();
$ticketController = new TicketController();
$ticketModel = new Ticket();

// Obtener tickets
$resultados = $ticketController->listar();
$tickets = $resultados['data'];
$totalPages = $resultados['total_pages'];
$currentPage = $resultados['page'];
$total = $resultados['total'];

// Obtener tipos de tickets para filtros
$tiposTickets = $ticketModel->obtenerTipos();

// Determinar título según el contexto
$esTodos = isset($_GET['todos']) && esAgente();
$pageTitle = $esTodos ? "Todos los Tickets" : "Mis Tickets";
$pageDescription = "Lista de tickets y solicitudes de soporte";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1><?= $esTodos ? '📋 Todos los Tickets' : '🎫 Mis Tickets' ?></h1>
                        <p><?= $esTodos ? 'Gestión de todos los tickets del sistema' : 'Sus solicitudes de soporte' ?></p>
                    </div>
                    <div>
                        <a href="<?= getBaseUrl() ?>/views/tickets/create.php" class="btn btn-primary">
                            ➕ Crear Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>🔍 Filtros de Búsqueda</h4>
                </div>
                <div class="card-body">
                    <form id="filtros-form" method="GET">
                        <?php if ($esTodos): ?>
                            <input type="hidden" name="todos" value="1">
                        <?php endif; ?>
                        
                        <div class="row">
                            <!-- Búsqueda general -->
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="busqueda" class="form-label">Buscar:</label>
                                    <input type="text" 
                                           id="busqueda" 
                                           name="busqueda" 
                                           class="form-control" 
                                           placeholder="Título o descripción"
                                           value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <!-- Estado -->
                            <div class="col-2">
                                <div class="form-group">
                                    <label for="estado" class="form-label">Estado:</label>
                                    <select id="estado" name="estado" class="form-control form-select">
                                        <option value="">Todos</option>
                                        <option value="abierto" <?= ($_GET['estado'] ?? '') === 'abierto' ? 'selected' : '' ?>>Abierto</option>
                                        <option value="en_proceso" <?= ($_GET['estado'] ?? '') === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="resuelto" <?= ($_GET['estado'] ?? '') === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                        <option value="cerrado" <?= ($_GET['estado'] ?? '') === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Prioridad -->
                            <div class="col-2">
                                <div class="form-group">
                                    <label for="prioridad" class="form-label">Prioridad:</label>
                                    <select id="prioridad" name="prioridad" class="form-control form-select">
                                        <option value="">Todas</option>
                                        <option value="baja" <?= ($_GET['prioridad'] ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
                                        <option value="media" <?= ($_GET['prioridad'] ?? '') === 'media' ? 'selected' : '' ?>>Media</option>
                                        <option value="alta" <?= ($_GET['prioridad'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                                        <option value="urgente" <?= ($_GET['prioridad'] ?? '') === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Tipo -->
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="tipo" class="form-label">Tipo:</label>
                                    <select id="tipo" name="tipo" class="form-control form-select">
                                        <option value="">Todos</option>
                                        <?php foreach ($tiposTickets as $tipo): ?>
                                            <option value="<?= $tipo['id'] ?>" <?= ($_GET['tipo'] ?? '') == $tipo['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tipo['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Botones -->
                            <div class="col-2">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            🔍 Filtrar
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="clearFilters('#filtros-form')">
                                            🗑️ Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <h4 class="text-primary"><?= $total ?></h4>
                            <p>Total de Tickets</p>
                        </div>
                        <div class="col-3">
                            <h4 class="text-info">
                                <?php
                                $abiertos = array_filter($tickets, fn($t) => $t['estado'] === 'abierto');
                                echo count($abiertos);
                                ?>
                            </h4>
                            <p>Abiertos</p>
                        </div>
                        <div class="col-3">
                            <h4 class="text-warning">
                                <?php
                                $enProceso = array_filter($tickets, fn($t) => $t['estado'] === 'en_proceso');
                                echo count($enProceso);
                                ?>
                            </h4>
                            <p>En Proceso</p>
                        </div>
                        <div class="col-3">
                            <h4 class="text-success">
                                <?php
                                $resueltos = array_filter($tickets, fn($t) => in_array($t['estado'], ['resuelto', 'cerrado']));
                                echo count($resueltos);
                                ?>
                            </h4>
                            <p>Resueltos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de tickets -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>📄 Lista de Tickets</h3>
                    <div>
                        <?php if (esAgente()): ?>
                            <?php if ($esTodos): ?>
                                <a href="<?= getBaseUrl() ?>/views/tickets/list.php" class="btn btn-sm btn-outline-primary">
                                    👤 Mis Tickets
                                </a>
                            <?php else: ?>
                                <a href="<?= getBaseUrl() ?>/views/tickets/list.php?todos=1" class="btn btn-sm btn-outline-primary">
                                    📋 Todos los Tickets
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <div class="text-center py-5">
                            <h4>📭 No hay tickets</h4>
                            <p>No se encontraron tickets con los criterios seleccionados.</p>
                            <a href="<?= getBaseUrl() ?>/views/tickets/create.php" class="btn btn-primary">
                                ➕ Crear mi primer ticket
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <?php if ($esTodos): ?>
                                            <th>Usuario</th>
                                        <?php endif; ?>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Prioridad</th>
                                        <th>Fecha</th>
                                        <?php if (esAgente()): ?>
                                            <th>Agente</th>
                                        <?php endif; ?>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= $ticket['id'] ?></strong>
                                        </td>
                                        <td>
                                            <a href="<?= getBaseUrl() ?>/views/tickets/view.php?id=<?= $ticket['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($ticket['titulo']) ?>
                                            </a>
                                        </td>
                                        <?php if ($esTodos): ?>
                                        <td>
                                            <small>
                                                <?= htmlspecialchars($ticket['usuario_nombre'] . ' ' . $ticket['usuario_apellido']) ?>
                                            </small>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($ticket['tipo_nombre']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= 
                                                $ticket['estado'] === 'abierto' ? 'info' : 
                                                ($ticket['estado'] === 'en_proceso' ? 'warning' : 
                                                ($ticket['estado'] === 'resuelto' ? 'success' : 'secondary')) 
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $ticket['estado'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="prioridad-<?= $ticket['prioridad'] ?>">
                                                <?php
                                                $prioridadIcons = [
                                                    'baja' => '🟢',
                                                    'media' => '🟡',
                                                    'alta' => '🟠',
                                                    'urgente' => '🔴'
                                                ];
                                                echo $prioridadIcons[$ticket['prioridad']] . ' ' . ucfirst($ticket['prioridad']);
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('d/m/Y', strtotime($ticket['fecha_creacion'])) ?><br>
                                                <?= date('H:i', strtotime($ticket['fecha_creacion'])) ?>
                                            </small>
                                        </td>
                                        <?php if (esAgente()): ?>
                                        <td>
                                            <small>
                                                <?= $ticket['agente_nombre'] ? 
                                                    htmlspecialchars($ticket['agente_nombre'] . ' ' . $ticket['agente_apellido']) : 
                                                    '<em>Sin asignar</em>' ?>
                                            </small>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= getBaseUrl() ?>/views/tickets/view.php?id=<?= $ticket['id'] ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    👁️ Ver
                                                </a>
                                                
                                                <?php if (esAgente() && $ticket['estado'] !== 'cerrado'): ?>
                                                    <button class="btn btn-outline-warning btn-sm" 
                                                            onclick="openModal('asignar-modal-<?= $ticket['id'] ?>')">
                                                        👤 Asignar
                                                    </button>
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

<!-- Modales para asignar tickets (solo para agentes) -->
<?php if (esAgente()): ?>
    <?php
    require_once __DIR__ . '/../../controllers/UserController.php';
    $userController = new UserController();
    $agentes = $userController->obtenerAgentes();
    ?>
    
    <?php foreach ($tickets as $ticket): ?>
        <?php if ($ticket['estado'] !== 'cerrado'): ?>
        <div id="asignar-modal-<?= $ticket['id'] ?>" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>👤 Asignar Ticket #<?= $ticket['id'] ?></h3>
                    <button type="button" class="btn-close" onclick="closeModal('asignar-modal-<?= $ticket['id'] ?>')">&times;</button>
                </div>
                <form method="POST" action="<?= getBaseUrl() ?>/controllers/TicketController.php?action=asignar&id=<?= $ticket['id'] ?>">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                        
                        <p><strong>Ticket:</strong> <?= htmlspecialchars($ticket['titulo']) ?></p>
                        
                        <div class="form-group">
                            <label for="agente_id_<?= $ticket['id'] ?>" class="form-label">Asignar a:</label>
                            <select id="agente_id_<?= $ticket['id'] ?>" name="agente_id" class="form-control form-select" required>
                                <option value="">Seleccionar agente</option>
                                <?php foreach ($agentes as $agente): ?>
                                    <option value="<?= $agente['id'] ?>" 
                                            <?= $ticket['agente_id'] == $agente['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($agente['primer_nombre'] . ' ' . $agente['primer_apellido']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('asignar-modal-<?= $ticket['id'] ?>')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            ✅ Asignar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<style>
/* Estilos específicos para lista de tickets */
.btn-group-sm .btn {
    padding: 4px 8px;
    font-size: 12px;
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
    .col-3,
    .col-2 {
        flex: 0 0 50%;
        max-width: 50%;
        margin-bottom: 10px;
    }
    
    .table {
        font-size: 14px;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 5px;
    }
}
</style>

<script>
// Función para limpiar filtros
function clearFilters(formSelector) {
    const form = document.querySelector(formSelector);
    if (form) {
        // Mantener campos hidden (como 'todos')
        const hiddenInputs = form.querySelectorAll('input[type="hidden"]');
        const hiddenValues = {};
        hiddenInputs.forEach(input => {
            hiddenValues[input.name] = input.value;
        });
        
        form.reset();
        
        // Restaurar campos hidden
        Object.keys(hiddenValues).forEach(name => {
            const input = form.querySelector(`input[name="${name}"]`);
            if (input) {
                input.value = hiddenValues[name];
            }
        });
        
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>