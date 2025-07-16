<?php
/**
 * Página de reportes y estadísticas avanzadas
 * Archivo: views/admin/reports.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Ticket.php';
require_once __DIR__ . '/../../models/Database.php';

// Verificar permisos
requerirPermiso('reportes', 'read');

$userModel = new User();
$ticketModel = new Ticket();
$db = new Database();

// Obtener parámetros de fecha
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d'); // Hoy

// Estadísticas generales
$estadisticasUsuarios = $userModel->obtenerEstadisticas();
$estadisticasTickets = $ticketModel->obtenerEstadisticas();

// Reportes específicos por fechas
$ticketsPorDia = $db->select("
    SELECT DATE(fecha_creacion) as fecha, COUNT(*) as total 
    FROM tickets 
    WHERE DATE(fecha_creacion) BETWEEN ? AND ? 
    GROUP BY DATE(fecha_creacion) 
    ORDER BY fecha DESC
", [$fechaInicio, $fechaFin]);

$ticketsResueltosPorDia = $db->select("
    SELECT DATE(fecha_cierre) as fecha, COUNT(*) as total 
    FROM tickets 
    WHERE DATE(fecha_cierre) BETWEEN ? AND ? 
    AND estado IN ('resuelto', 'cerrado')
    GROUP BY DATE(fecha_cierre) 
    ORDER BY fecha DESC
", [$fechaInicio, $fechaFin]);

// Top usuarios con más tickets
$topUsuarios = $db->select("
    SELECT u.primer_nombre, u.primer_apellido, u.email, COUNT(t.id) as total_tickets
    FROM usuarios u 
    LEFT JOIN tickets t ON u.id = t.usuario_id 
    WHERE t.fecha_creacion BETWEEN ? AND ?
    GROUP BY u.id 
    ORDER BY total_tickets DESC 
    LIMIT 10
", [$fechaInicio, $fechaFin]);

// Rendimiento de agentes
$rendimientoAgentes = $db->select("
    SELECT 
        u.primer_nombre, u.primer_apellido, u.email,
        COUNT(t.id) as tickets_asignados,
        COUNT(CASE WHEN t.estado IN ('resuelto', 'cerrado') THEN 1 END) as tickets_resueltos,
        AVG(CASE 
            WHEN t.fecha_cierre IS NOT NULL 
            THEN TIMESTAMPDIFF(HOUR, t.fecha_creacion, t.fecha_cierre) 
        END) as tiempo_promedio_resolucion
    FROM usuarios u 
    INNER JOIN roles r ON u.rol_id = r.id 
    LEFT JOIN tickets t ON u.id = t.agente_id 
    WHERE r.nombre IN ('Admin', 'Agente') 
    AND (t.fecha_creacion BETWEEN ? AND ? OR t.fecha_creacion IS NULL)
    GROUP BY u.id 
    ORDER BY tickets_resueltos DESC
", [$fechaInicio, $fechaFin]);

// Satisfacción promedio
$satisfaccionPromedio = $db->selectOne("
    SELECT 
        AVG(calificacion) as promedio,
        COUNT(*) as total_encuestas
    FROM encuestas_satisfaccion es
    INNER JOIN tickets t ON es.ticket_id = t.id
    WHERE t.fecha_cierre BETWEEN ? AND ?
", [$fechaInicio, $fechaFin]);

$pageTitle = "Reportes y Estadísticas";
$pageDescription = "Análisis detallado del sistema de tickets";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h1>📊 Reportes y Estadísticas</h1>
                    <p>Análisis detallado del rendimiento del sistema de tickets</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros de fecha -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>📅 Filtros de Período</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                            <input type="date" 
                                   id="fecha_inicio" 
                                   name="fecha_inicio" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($fechaInicio) ?>">
                        </div>
                        <div class="col-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                            <input type="date" 
                                   id="fecha_fin" 
                                   name="fecha_fin" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($fechaFin) ?>">
                        </div>
                        <div class="col-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">
                                🔍 Aplicar Filtros
                            </button>
                        </div>
                        <div class="col-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-success d-block" onclick="exportReport()">
                                📥 Exportar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Métricas principales -->
    <div class="row mb-4">
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-primary"><?= count($ticketsPorDia) > 0 ? array_sum(array_column($ticketsPorDia, 'total')) : 0 ?></h2>
                    <p>Tickets Creados</p>
                    <small class="text-muted">En el período seleccionado</small>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-success"><?= count($ticketsResueltosPorDia) > 0 ? array_sum(array_column($ticketsResueltosPorDia, 'total')) : 0 ?></h2>
                    <p>Tickets Resueltos</p>
                    <small class="text-muted">En el período seleccionado</small>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-warning"><?= round($estadisticasTickets['tiempo_promedio_resolucion'], 1) ?>h</h2>
                    <p>Tiempo Promedio</p>
                    <small class="text-muted">Resolución de tickets</small>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-info">
                        <?= $satisfaccionPromedio['promedio'] ? round($satisfaccionPromedio['promedio'], 1) : 'N/A' ?>⭐
                    </h2>
                    <p>Satisfacción Promedio</p>
                    <small class="text-muted"><?= $satisfaccionPromedio['total_encuestas'] ?? 0 ?> evaluaciones</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos de tendencias -->
    <div class="row mb-4">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>📈 Tickets Creados por Día</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($ticketsPorDia)): ?>
                        <p class="text-center text-muted">No hay datos para mostrar en el período seleccionado</p>
                    <?php else: ?>
                        <div class="chart-container">
                            <?php foreach (array_slice($ticketsPorDia, 0, 10) as $dia): ?>
                                <div class="chart-bar mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span><?= date('d/m', strtotime($dia['fecha'])) ?></span>
                                        <span><strong><?= $dia['total'] ?></strong></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" 
                                             style="width: <?= max($ticketsPorDia) ? ($dia['total'] / max(array_column($ticketsPorDia, 'total'))) * 100 : 0 ?>%">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>✅ Tickets Resueltos por Día</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($ticketsResueltosPorDia)): ?>
                        <p class="text-center text-muted">No hay datos para mostrar en el período seleccionado</p>
                    <?php else: ?>
                        <div class="chart-container">
                            <?php foreach (array_slice($ticketsResueltosPorDia, 0, 10) as $dia): ?>
                                <div class="chart-bar mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span><?= date('d/m', strtotime($dia['fecha'])) ?></span>
                                        <span><strong><?= $dia['total'] ?></strong></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: <?= max($ticketsResueltosPorDia) ? ($dia['total'] / max(array_column($ticketsResueltosPorDia, 'total'))) * 100 : 0 ?>%">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tablas de datos -->
    <div class="row mb-4">
        <!-- Top usuarios -->
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>👥 Usuarios con Más Tickets</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($topUsuarios)): ?>
                        <p class="text-center text-muted">No hay datos para mostrar</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th class="text-center">Tickets</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topUsuarios as $usuario): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($usuario['primer_nombre'] . ' ' . $usuario['primer_apellido']) ?></td>
                                        <td><small><?= htmlspecialchars($usuario['email']) ?></small></td>
                                        <td class="text-center">
                                            <span class="badge badge-primary"><?= $usuario['total_tickets'] ?></span>
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
        
        <!-- Rendimiento de agentes -->
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>🎯 Rendimiento de Agentes</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($rendimientoAgentes)): ?>
                        <p class="text-center text-muted">No hay datos para mostrar</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Agente</th>
                                        <th class="text-center">Asignados</th>
                                        <th class="text-center">Resueltos</th>
                                        <th class="text-center">Tiempo Prom.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rendimientoAgentes as $agente): ?>
                                    <tr>
                                        <td>
                                            <small><?= htmlspecialchars($agente['primer_nombre'] . ' ' . $agente['primer_apellido']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info"><?= $agente['tickets_asignados'] ?? 0 ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success"><?= $agente['tickets_resueltos'] ?? 0 ?></span>
                                        </td>
                                        <td class="text-center">
                                            <small><?= $agente['tiempo_promedio_resolucion'] ? round($agente['tiempo_promedio_resolucion'], 1) . 'h' : 'N/A' ?></small>
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
    
    <!-- Estadísticas detalladas -->
    <div class="row mb-4">
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h4>📋 Estados de Tickets</h4>
                </div>
                <div class="card-body">
                    <?php foreach ($estadisticasTickets['por_estado'] as $estado): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span><?= ucfirst(str_replace('_', ' ', $estado['estado'])) ?></span>
                                <span><strong><?= $estado['total'] ?></strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
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
        
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h4>⚡ Prioridades</h4>
                </div>
                <div class="card-body">
                    <?php foreach ($estadisticasTickets['por_prioridad'] as $prioridad): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="prioridad-<?= $prioridad['prioridad'] ?>">
                                    <?php
                                    $icons = ['baja' => '🟢', 'media' => '🟡', 'alta' => '🟠', 'urgente' => '🔴'];
                                    echo $icons[$prioridad['prioridad']] . ' ' . ucfirst($prioridad['prioridad']);
                                    ?>
                                </span>
                                <span><strong><?= $prioridad['total'] ?></strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" 
                                     style="width: <?= $estadisticasTickets['total'] > 0 ? ($prioridad['total'] / $estadisticasTickets['total']) * 100 : 0 ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h4>🏷️ Tipos de Tickets</h4>
                </div>
                <div class="card-body">
                    <?php foreach ($estadisticasTickets['por_tipo'] as $tipo): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span><small><?= htmlspecialchars($tipo['tipo']) ?></small></span>
                                <span><strong><?= $tipo['total'] ?></strong></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-secondary" 
                                     style="width: <?= $estadisticasTickets['total'] > 0 ? ($tipo['total'] / $estadisticasTickets['total']) * 100 : 0 ?>%">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Resumen ejecutivo -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>📝 Resumen Ejecutivo</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h5>🎯 Indicadores Clave</h5>
                            <ul>
                                <li><strong>Tasa de Resolución:</strong> 
                                    <?php
                                    $totalCreados = count($ticketsPorDia) > 0 ? array_sum(array_column($ticketsPorDia, 'total')) : 0;
                                    $totalResueltos = count($ticketsResueltosPorDia) > 0 ? array_sum(array_column($ticketsResueltosPorDia, 'total')) : 0;
                                    $tasaResolucion = $totalCreados > 0 ? round(($totalResueltos / $totalCreados) * 100, 1) : 0;
                                    ?>
                                    <?= $tasaResolucion ?>% (<?= $totalResueltos ?>/<?= $totalCreados ?>)
                                </li>
                                <li><strong>Tiempo Promedio de Resolución:</strong> <?= round($estadisticasTickets['tiempo_promedio_resolucion'], 1) ?> horas</li>
                                <li><strong>Tickets Pendientes:</strong> <?= $estadisticasTickets['pendientes'] ?></li>
                                <li><strong>Satisfacción del Cliente:</strong> 
                                    <?= $satisfaccionPromedio['promedio'] ? round($satisfaccionPromedio['promedio'], 1) . '/5 ⭐' : 'Sin datos' ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h5>💡 Recomendaciones</h5>
                            <ul>
                                <?php if ($tasaResolucion < 80): ?>
                                    <li class="text-warning">⚠️ La tasa de resolución está por debajo del 80%. Considere asignar más recursos.</li>
                                <?php endif; ?>
                                
                                <?php if ($estadisticasTickets['tiempo_promedio_resolucion'] > 48): ?>
                                    <li class="text-warning">⚠️ El tiempo promedio de resolución excede las 48 horas. Revise el proceso.</li>
                                <?php endif; ?>
                                
                                <?php if ($estadisticasTickets['pendientes'] > 50): ?>
                                    <li class="text-danger">🚨 Hay muchos tickets pendientes. Priorice la atención.</li>
                                <?php endif; ?>
                                
                                <?php if (($satisfaccionPromedio['promedio'] ?? 0) < 4): ?>
                                    <li class="text-warning">⚠️ La satisfacción está por debajo de 4/5. Mejore la calidad del servicio.</li>
                                <?php endif; ?>
                                
                                <?php if ($tasaResolucion >= 80 && $estadisticasTickets['tiempo_promedio_resolucion'] <= 24 && ($satisfaccionPromedio['promedio'] ?? 0) >= 4): ?>
                                    <li class="text-success">✅ Excelente rendimiento general del sistema.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            Reporte generado el <?= date('d/m/Y H:i:s') ?> | 
                            Período: <?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para reportes */
.chart-container {
    max-height: 300px;
    overflow-y: auto;
}

.chart-bar {
    margin-bottom: 8px;
}

.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.table-sm th,
.table-sm td {
    padding: 6px 8px;
    font-size: 13px;
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
}

@media print {
    .btn,
    .form-control,
    .card-header {
        print-color-adjust: exact;
    }
    
    .no-print {
        display: none !important;
    }
}
</style>

<script>
// Función para exportar reporte
function exportReport() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    
    // Crear ventana de impresión
    const printWindow = window.open('', '_blank');
    const reportContent = document.documentElement.outerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reporte de Sistema de Tickets</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .no-print { display: none !important; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .card { border: 1px solid #ddd; margin-bottom: 20px; padding: 15px; }
                .progress { height: 20px; background-color: #e9ecef; }
                .progress-bar { height: 100%; background-color: #007bff; }
            </style>
        </head>
        <body>
            <h1>📊 Reporte del Sistema de Tickets</h1>
            <p><strong>Período:</strong> ${fechaInicio} - ${fechaFin}</p>
            <p><strong>Generado:</strong> ${new Date().toLocaleString('es-ES')}</p>
            <hr>
            ${document.querySelector('.container').innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Auto-actualizar gráficos cada 30 segundos
setInterval(function() {
    // Solo actualizar si la página está visible
    if (!document.hidden) {
        console.log('Actualizando reportes...');
        // Aquí se podría implementar actualización AJAX
    }
}, 30000);

// Establecer fechas por defecto más inteligentes
document.addEventListener('DOMContentLoaded', function() {
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    
    // Agregar botones de período rápido
    const quickPeriods = [
        { label: 'Hoy', days: 0 },
        { label: 'Esta semana', days: 7 },
        { label: 'Este mes', days: 30 },
        { label: 'Últimos 3 meses', days: 90 }
    ];
    
    const quickButtons = document.createElement('div');
    quickButtons.className = 'mt-2';
    quickButtons.innerHTML = '<small>Períodos rápidos: </small>';
    
    quickPeriods.forEach(period => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary me-1';
        btn.textContent = period.label;
        btn.onclick = function() {
            const today = new Date();
            const startDate = new Date(today);
            startDate.setDate(today.getDate() - period.days);
            
            fechaInicioInput.value = startDate.toISOString().split('T')[0];
            fechaFinInput.value = today.toISOString().split('T')[0];
        };
        quickButtons.appendChild(btn);
    });
    
    fechaInicioInput.parentNode.appendChild(quickButtons);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>