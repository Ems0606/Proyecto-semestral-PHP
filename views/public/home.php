<?php
/**
 * Página principal pública
 * Archivo: views/public/home.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

// Obtener usuario actual si está autenticado
$usuario = obtenerUsuarioActual();
$esAuth = estaAutenticado();

// Si está autenticado, obtener estadísticas básicas
$estadisticas = null;
if ($esAuth) {
    require_once __DIR__ . '/../../models/Ticket.php';
    $ticketModel = new Ticket();
    
    // Obtener tickets del usuario actual
    $misTickets = $ticketModel->obtenerPorUsuario($usuario['id'], 1);
    
    // Estadísticas básicas del usuario
    $estadisticas = [
        'total_tickets' => $misTickets['total'],
        'tickets_abiertos' => 0,
        'tickets_resueltos' => 0,
        'tickets_recientes' => array_slice($misTickets['data'], 0, 5)
    ];
    
    // Contar por estado
    foreach ($misTickets['data'] as $ticket) {
        switch ($ticket['estado']) {
            case 'abierto':
            case 'en_proceso':
                $estadisticas['tickets_abiertos']++;
                break;
            case 'resuelto':
            case 'cerrado':
                $estadisticas['tickets_resueltos']++;
                break;
        }
    }
}

$pageTitle = $esAuth ? "Bienvenido, " . $usuario['nombre'] : "Sistema de Tickets";
$pageDescription = "Sistema de gestión de tickets y soporte técnico";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <?php if ($esAuth): ?>
        <!-- Dashboard para usuarios autenticados -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h1>👋 Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h1>
                        <p>Panel de control personal - <?= htmlspecialchars($usuario['rol']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas del usuario -->
        <div class="row mb-4">
            <div class="col-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-primary"><?= $estadisticas['total_tickets'] ?></h3>
                        <p>Tickets Totales</p>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-warning"><?= $estadisticas['tickets_abiertos'] ?></h3>
                        <p>Tickets Pendientes</p>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-success"><?= $estadisticas['tickets_resueltos'] ?></h3>
                        <p>Tickets Resueltos</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones rápidas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>🚀 Acciones Rápidas</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <a href="<?= getBaseUrl() ?>/views/tickets/create.php" class="btn btn-primary btn-lg d-block">
                                    ➕ Crear Ticket
                                </a>
                            </div>
                            <div class="col-3">
                                <a href="<?= getBaseUrl() ?>/views/tickets/list.php" class="btn btn-info btn-lg d-block">
                                    📋 Mis Tickets
                                </a>
                            </div>
                            <div class="col-3">
                                <a href="<?= getBaseUrl() ?>/views/auth/perfil.php" class="btn btn-secondary btn-lg d-block">
                                    👤 Mi Perfil
                                </a>
                            </div>
                            <div class="col-3">
                                <a href="<?= getBaseUrl() ?>/views/public/help.php" class="btn btn-warning btn-lg d-block">
                                    ❓ Ayuda
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tickets recientes -->
        <?php if (!empty($estadisticas['tickets_recientes'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>📝 Tickets Recientes</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Estado</th>
                                        <th>Prioridad</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estadisticas['tickets_recientes'] as $ticket): ?>
                                    <tr>
                                        <td>#<?= $ticket['id'] ?></td>
                                        <td><?= htmlspecialchars($ticket['titulo']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $ticket['estado'] === 'abierto' ? 'info' : ($ticket['estado'] === 'resuelto' ? 'success' : 'warning') ?>">
                                                <?= ucfirst(str_replace('_', ' ', $ticket['estado'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="prioridad-<?= $ticket['prioridad'] ?>">
                                                <?= ucfirst($ticket['prioridad']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></td>
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
                        <div class="text-center">
                            <a href="<?= getBaseUrl() ?>/views/tickets/list.php" class="btn btn-primary">
                                Ver Todos los Tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Página de bienvenida para usuarios no autenticados -->
        <div class="text-center mb-5">
            <h1 class="display-4">📧 Sistema de Tickets</h1>
            <p class="lead">Gestión eficiente de tickets y soporte técnico</p>
        </div>
        
        <!-- Características del sistema -->
        <div class="row mb-5">
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>🎫</h2>
                        <h4>Gestión de Tickets</h4>
                        <p>Cree, gestione y haga seguimiento a sus solicitudes de soporte de manera fácil y organizada.</p>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>👥</h2>
                        <h4>Soporte Especializado</h4>
                        <p>Nuestro equipo de agentes especializados está listo para ayudarle con sus consultas.</p>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>📊</h2>
                        <h4>Seguimiento en Tiempo Real</h4>
                        <p>Consulte el estado de sus tickets y reciba notificaciones sobre actualizaciones.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Llamada a la acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h3>¿Listo para comenzar?</h3>
                        <p class="lead">Únase a nuestro sistema para gestionar sus solicitudes de soporte</p>
                        <div class="row justify-content-center">
                            <div class="col-3">
                                <a href="<?= getBaseUrl() ?>/views/auth/register.php" class="btn btn-success btn-lg d-block">
                                    ✅ Registrarse
                                </a>
                            </div>
                            <div class="col-3">
                                <a href="<?= getBaseUrl() ?>/views/auth/login.php" class="btn btn-primary btn-lg d-block">
                                    🔐 Iniciar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información adicional -->
        <div class="row mt-5">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h4>📋 Tipos de Soporte</h4>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li><strong>Soporte Técnico:</strong> Problemas con sistemas y aplicaciones</li>
                            <li><strong>Consultas Académicas:</strong> Información sobre créditos y programas</li>
                            <li><strong>Solicitudes de Acceso:</strong> Permisos y accesos a servicios</li>
                            <li><strong>Reclamos:</strong> Quejas y sugerencias</li>
                            <li><strong>Información General:</strong> Consultas diversas</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h4>⏰ Horarios de Atención</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Lunes a Viernes:</strong> 8:00 AM - 6:00 PM</p>
                        <p><strong>Sábados:</strong> 9:00 AM - 2:00 PM</p>
                        <p><strong>Domingos:</strong> Cerrado</p>
                        <hr>
                        <p><strong>Soporte en línea:</strong> 24/7 a través del sistema de tickets</p>
                        <p><strong>Tiempo de respuesta:</strong> Máximo 24 horas</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Estilos específicos para la página principal */
.display-4 {
    font-size: 3.5rem;
    font-weight: 300;
    line-height: 1.2;
}

.lead {
    font-size: 1.25rem;
    font-weight: 300;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
}

.text-center h2 {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.prioridad-baja { color: var(--success-color); font-weight: 600; }
.prioridad-media { color: var(--warning-color); font-weight: 600; }
.prioridad-alta { color: #fd7e14; font-weight: 600; }
.prioridad-urgente { color: var(--danger-color); font-weight: 600; }

@media (max-width: 768px) {
    .col-4,
    .col-3,
    .col-6 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
    
    .display-4 {
        font-size: 2.5rem;
    }
    
    .btn-lg {
        margin-bottom: 10px;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>