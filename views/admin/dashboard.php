<?php
/**
 * Dashboard de administración - ACTUALIZADO CON ESTADÍSTICAS DE IP
 * Archivo: views/admin/dashboard.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Ticket.php';

// Verificar que sea administrador
requerirPermiso('usuarios', 'read');

$userModel = new User();
$ticketModel = new Ticket();

// Obtener estadísticas
$estadisticasUsuarios = $userModel->obtenerEstadisticas();
$estadisticasTickets = $ticketModel->obtenerEstadisticas();

// Tickets recientes (últimos 10)
$ticketsRecientes = $ticketModel->obtenerTodos(1, [])['data'];
$ticketsRecientes = array_slice($ticketsRecientes, 0, 10);

$pageTitle = "Dashboard Administrativo";
$pageDescription = "Panel de control y estadísticas del sistema";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header del dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h1>📊 Dashboard Administrativo</h1>
                    <p>Panel de control y estadísticas del sistema de tickets</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas principales -->
    <div class="row mb-4">
        <!-- Usuarios -->
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-primary"><?= $estadisticasUsuarios['total_activos'] ?></h2>
                    <p>Usuarios Activos</p>
                    <small class="text-muted">
                        +<?= $estadisticasUsuarios['este_mes'] ?> este mes
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Tickets totales -->
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-info"><?= $estadisticasTickets['total'] ?></h2>
                    <p>Tickets Totales</p>
                    <small class="text-muted">
                        +<?= $estadisticasTickets['hoy'] ?> hoy
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Tickets pendientes -->
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-warning"><?= $estadisticasTickets['pendientes'] ?></h2>
                    <p>Tickets Pendientes</p>
                    <small class="text-muted">
                        Requieren atención
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Tiempo promedio -->
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-success"><?= $estadisticasTickets['tiempo_promedio_resolucion'] ?>h</h2>
                    <p>Tiempo Promedio</p>
                    <small class="text-muted">
                        Resolución de tickets
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos y estadísticas detalladas -->
    <div class="row mb-4">
        <!-- Tickets por estado -->
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h4>📈 Tickets por Estado</h4>
                </div>
                <div class="card-body">
                    <?php foreach ($estadisticasTickets['por_estado'] as $estado): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span><?= ucfirst(str_replace('_', ' ', $estado['estado'])) ?></span>
                                <span><strong><?= $estado['total'] ?></strong></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar 
                                    <?= $estado['estado'] === 'abierto' ? 'bg-info' : 
                                        ($estado['estado'] === 'en_proceso' ? 'bg-warning' : 
                                        ($estado['estado'] === 'resuelto' ? 'bg-success' : 'bg-secondary')) ?>" 
                                     style="width: <?= $estadisticasTickets['total'] > 0 ? ($estado['total'] / $estadisticasTickets['total']) * 100 : 0 ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Usuarios por rol -->
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h4>👥 Usuarios por Rol</h4>
                </div>
                <div class="card-body">
                    <?php foreach ($estadisticasUsuarios['por_rol'] as $rol): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span><?= htmlspecialchars($rol['rol']) ?></span>
                                <span><strong><?= $rol['total'] ?></strong></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" 
                                     style="width: <?= $estadisticasUsuarios['total_activos'] > 0 ? ($rol['total'] / $estadisticasUsuarios['total_activos']) * 100 : 0 ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- TOP IPs que más tickets crean -->
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h4>🌐 TOP IPs Creadoras</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($estadisticasTickets['por_ip'])): ?>
                        <?php foreach ($estadisticasTickets['por_ip'] as $ipStat): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <code class="ip-address"><?= htmlspecialchars($ipStat['ip_origen']) ?></code>
                                    </div>
                                    <span class="badge badge-info"><?= $ipStat['total_tickets'] ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" 
                                         style="width: <?= count($estadisticasTickets['por_ip']) > 0 ? ($ipStat['total_tickets'] / $estadisticasTickets['por_ip'][0]['total_tickets']) * 100 : 0 ?>%">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No hay datos de IP disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tickets por tipo y prioridad -->
    <div class="row mb-4">
        <!-- Tickets por tipo -->
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>🏷️ Tickets por Tipo</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estadisticasTickets['por_tipo'] as $tipo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tipo['tipo']) ?></td>
                                    <td class="text-center"><?= $tipo['total'] ?></td>
                                    <td class="text-center">
                                        <?= $estadisticasTickets['total'] > 0 ? round(($tipo['total'] / $estadisticasTickets['total']) * 100, 1) : 0 ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tickets por prioridad -->
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>⚡ Tickets por Prioridad</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Prioridad</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estadisticasTickets['por_prioridad'] as $prioridad): ?>
                                <tr>
                                    <td>
                                        <span class="prioridad-<?= $prioridad['prioridad'] ?>">
                                            <?php
                                            $icons = ['baja' => '🟢', 'media' => '🟡', 'alta' => '🟠', 'urgente' => '🔴'];
                                            echo $icons[$prioridad['prioridad']] . ' ' . ucfirst($prioridad['prioridad']);
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= $prioridad['total'] ?></td>
                                    <td class="text-center">
                                        <?= $estadisticasTickets['total'] > 0 ? round(($prioridad['total'] / $estadisticasTickets['total']) * 100, 1) : 0 ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tickets recientes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>📋 Tickets Recientes</h4>
                    <a href="<?= getBaseUrl() ?>/views/tickets/list.php?todos=1" class="btn btn-sm btn-primary">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($ticketsRecientes)): ?>
                        <p class="text-center text-muted">No hay tickets recientes</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Usuario</th>
                                        <th>Estado</th>
                                        <th>Prioridad</th>
                                        <th>IP Origen</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ticketsRecientes as $ticket): ?>
                                    <tr>
                                        <td><strong>#<?= $ticket['id'] ?></strong></td>
                                        <td>
                                            <a href="<?= getBaseUrl() ?>/views/tickets/view.php?id=<?= $ticket['id'] ?>">
                                                <?= htmlspecialchars(substr($ticket['titulo'], 0, 40)) ?>
                                                <?= strlen($ticket['titulo']) > 40 ? '...' : '' ?>
                                            </a>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($ticket['usuario_nombre'] . ' ' . $ticket['usuario_apellido']) ?></small>
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
                                                $icons = ['baja' => '🟢', 'media' => '🟡', 'alta' => '🟠', 'urgente' => '🔴'];
                                                echo $icons[$ticket['prioridad']];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($ticket['ip_origen'])): ?>
                                                <code class="ip-small"><?= htmlspecialchars($ticket['ip_origen']) ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('d/m/Y', strtotime($ticket['fecha_creacion'])) ?><br>
                                                <?= date('H:i', strtotime($ticket['fecha_creacion'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="<?= getBaseUrl() ?>/views/tickets/view.php?id=<?= $ticket['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Acciones rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>🚀 Acciones Rápidas</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-3">
                            <a href="<?= getBaseUrl() ?>/views/admin/manage_users.php" class="btn btn-primary btn-lg d-block">
                                👥 Gestionar Usuarios
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="<?= getBaseUrl() ?>/views/tickets/list.php?todos=1" class="btn btn-info btn-lg d-block">
                                📋 Ver Todos los Tickets
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="<?= getBaseUrl() ?>/views/admin/reports.php" class="btn btn-success btn-lg d-block">
                                📊 Reportes Avanzados
                            </a>
                        </div>
                        <div class="col-3">
                            <a href="<?= getBaseUrl() ?>/views/admin/settings.php" class="btn btn-secondary btn-lg d-block">
                                ⚙️ Configuración
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para el dashboard */
.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.table-sm th,
.table-sm td {
    padding: 8px;
    font-size: 14px;
}

.ip-address {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
}

.ip-small {
    background: #f8f9fa;
    color: #495057;
    padding: 1px 4px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 11px;
}

.prioridad-baja { color: var(--success-color); font-weight: 600; }
.prioridad-media { color: var(--warning-color); font-weight: 600; }
.prioridad-alta { color: #fd7e14; font-weight: 600; }
.prioridad-urgente { color: var(--danger-color); font-weight: 600; }

@media (max-width: 768px) {
    .col-3,
    .col-4,
    .col-6 {
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
// Auto-actualizar estadísticas cada 5 minutos
setInterval(function() {
    location.reload();
}, 300000); // 5 minutos

// Mostrar fecha y hora actual
function updateDateTime() {
    const now = new Date();
    const dateTime = now.toLocaleString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    // Si existe un elemento para mostrar la fecha
    const dateElement = document.getElementById('current-date');
    if (dateElement) {
        dateElement.textContent = dateTime;
    }
}

// Actualizar cada segundo
setInterval(updateDateTime, 1000);
updateDateTime();

// Tooltip para IPs
document.addEventListener('DOMContentLoaded', function() {
    const ipElements = document.querySelectorAll('.ip-address, .ip-small');
    ipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.title = 'IP desde donde se creó el ticket';
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>