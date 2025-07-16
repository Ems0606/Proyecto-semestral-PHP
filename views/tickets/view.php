<?php
/**
 * Página para ver un ticket individual
 * Archivo: views/tickets/view.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../controllers/TicketController.php';

// Verificar autenticación
requerirAutenticacion();

// Obtener ID del ticket
$ticketId = intval($_GET['id'] ?? 0);

if (!$ticketId) {
    $_SESSION['error'] = "ID de ticket no válido";
    header('Location: ' . APP_URL . '/views/tickets/list.php');
    exit();
}

$ticketController = new TicketController();

// Procesar respuestas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'responder':
                $ticketController->responder($ticketId);
                break;
            case 'actualizar':
                $ticketController->actualizar($ticketId);
                break;
            case 'encuesta':
                $ticketController->encuesta($ticketId);
                break;
            case 'asignar':
                $ticketController->asignar($ticketId);
                break;
        }
    }
}

// Obtener datos del ticket
$datos = $ticketController->ver($ticketId);
$ticket = $datos['ticket'];
$respuestas = $datos['respuestas'];
$encuesta = $datos['encuesta'];

$usuario = obtenerUsuarioActual();

$pageTitle = "Ticket #" . $ticket['id'] . " - " . $ticket['titulo'];
$pageDescription = "Detalles y seguimiento del ticket";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header del ticket -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1>🎫 Ticket #<?= $ticket['id'] ?></h1>
                        <p>Creado el <?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></p>
                    </div>
                    <div>
                        <a href="<?= APP_URL ?>/views/tickets/list.php" class="btn btn-secondary">
                            ← Volver a la lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información del ticket -->
    <div class="row mb-4">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3><?= htmlspecialchars($ticket['titulo']) ?></h3>
                </div>
                <div class="card-body">
                    <!-- Descripción del ticket -->
                    <div class="ticket-description mb-4">
                        <h5>📝 Descripción:</h5>
                        <div class="description-content">
                            <?= nl2br(htmlspecialchars($ticket['descripcion'])) ?>
                        </div>
                    </div>
                    
                    <!-- Archivo adjunto inicial -->
                    <?php if (!empty($ticket['archivo_adjunto'])): ?>
                    <div class="attachment mb-4">
                        <h5>📎 Archivo Adjunto:</h5>
                        <div class="attachment-item">
                            <a href="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($ticket['archivo_adjunto']) ?>" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                📄 Ver archivo adjunto
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Formulario para actualizar ticket (solo si es el usuario o agente) -->
                    <?php if ($ticket['usuario_id'] == $usuario['id'] && $ticket['estado'] == 'abierto' || esAgente()): ?>
                    <div class="update-section mb-4">
                        <h5>⚙️ Actualizar Ticket:</h5>
                        <form method="POST" class="update-form">
                            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                            <input type="hidden" name="action" value="actualizar">
                            
                            <div class="row">
                                <?php if (esAgente()): ?>
                                    <!-- Estado (solo agentes) -->
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="estado" class="form-label">Estado:</label>
                                            <select id="estado" name="estado" class="form-control form-select">
                                                <option value="abierto" <?= $ticket['estado'] === 'abierto' ? 'selected' : '' ?>>Abierto</option>
                                                <option value="en_proceso" <?= $ticket['estado'] === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                                <option value="resuelto" <?= $ticket['estado'] === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                                <option value="cerrado" <?= $ticket['estado'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Prioridad (solo agentes) -->
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="prioridad" class="form-label">Prioridad:</label>
                                            <select id="prioridad" name="prioridad" class="form-control form-select">
                                                <option value="baja" <?= $ticket['prioridad'] === 'baja' ? 'selected' : '' ?>>🟢 Baja</option>
                                                <option value="media" <?= $ticket['prioridad'] === 'media' ? 'selected' : '' ?>>🟡 Media</option>
                                                <option value="alta" <?= $ticket['prioridad'] === 'alta' ? 'selected' : '' ?>>🟠 Alta</option>
                                                <option value="urgente" <?= $ticket['prioridad'] === 'urgente' ? 'selected' : '' ?>>🔴 Urgente</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Botón actualizar -->
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-warning d-block">
                                                ⚙️ Actualizar
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12">
                                        <p class="text-info">
                                            <strong>Nota:</strong> Solo puede editar tickets que estén en estado "Abierto".
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Panel lateral con información -->
        <div class="col-4">
            <!-- Información del ticket -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>ℹ️ Información del Ticket</h4>
                </div>
                <div class="card-body">
                    <p><strong>Estado:</strong> 
                        <span class="badge badge-<?= 
                            $ticket['estado'] === 'abierto' ? 'info' : 
                            ($ticket['estado'] === 'en_proceso' ? 'warning' : 
                            ($ticket['estado'] === 'resuelto' ? 'success' : 'secondary')) 
                        ?>">
                            <?= ucfirst(str_replace('_', ' ', $ticket['estado'])) ?>
                        </span>
                    </p>
                    
                    <p><strong>Prioridad:</strong> 
                        <span class="prioridad-<?= $ticket['prioridad'] ?>">
                            <?php
                            $icons = ['baja' => '🟢', 'media' => '🟡', 'alta' => '🟠', 'urgente' => '🔴'];
                            echo $icons[$ticket['prioridad']] . ' ' . ucfirst($ticket['prioridad']);
                            ?>
                        </span>
                    </p>
                    
                    <p><strong>Tipo:</strong> <?= htmlspecialchars($ticket['tipo_nombre']) ?></p>
                    
                    <p><strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></p>
                    
                    <?php if ($ticket['fecha_actualizacion'] != $ticket['fecha_creacion']): ?>
                    <p><strong>Actualizado:</strong> <?= date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($ticket['fecha_cierre'])): ?>
                    <p><strong>Cerrado:</strong> <?= date('d/m/Y H:i', strtotime($ticket['fecha_cierre'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Información del usuario -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>👤 Solicitante</h4>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($ticket['usuario_nombre'] . ' ' . $ticket['usuario_apellido']) ?></p>
                    <p><strong>Email:</strong> 
                        <a href="mailto:<?= htmlspecialchars($ticket['usuario_email']) ?>">
                            <?= htmlspecialchars($ticket['usuario_email']) ?>
                        </a>
                    </p>
                </div>
            </div>
            
            <!-- Información del agente -->
            <?php if (!empty($ticket['agente_nombre'])): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h4>🎯 Agente Asignado</h4>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($ticket['agente_nombre'] . ' ' . $ticket['agente_apellido']) ?></p>
                    <p><strong>Email:</strong> 
                        <a href="mailto:<?= htmlspecialchars($ticket['agente_email']) ?>">
                            <?= htmlspecialchars($ticket['agente_email']) ?>
                        </a>
                    </p>
                </div>
            </div>
            <?php elseif (esAgente()): ?>
            <!-- Formulario para asignar agente -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>👨‍💼 Asignar Agente</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                        <input type="hidden" name="action" value="asignar">
                        
                        <div class="form-group">
                            <label for="agente_id" class="form-label">Seleccionar agente:</label>
                            <select id="agente_id" name="agente_id" class="form-control form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php
                                require_once __DIR__ . '/../../controllers/UserController.php';
                                $userController = new UserController();
                                $agentes = $userController->obtenerAgentes();
                                foreach ($agentes as $agente):
                                ?>
                                    <option value="<?= $agente['id'] ?>">
                                        <?= htmlspecialchars($agente['primer_nombre'] . ' ' . $agente['primer_apellido']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-sm">
                            ✅ Asignar
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Encuesta de satisfacción -->
            <?php if (in_array($ticket['estado'], ['resuelto', 'cerrado']) && $ticket['usuario_id'] == $usuario['id'] && !$encuesta): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h4>⭐ Evaluar Servicio</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                        <input type="hidden" name="action" value="encuesta">
                        
                        <div class="form-group">
                            <label class="form-label">Calificación:</label>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label class="star-label">
                                        <input type="radio" name="calificacion" value="<?= $i ?>" required>
                                        <span class="star">⭐</span>
                                        <span class="star-text"><?= $i ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="comentario" class="form-label">Comentario (opcional):</label>
                            <textarea id="comentario" name="comentario" class="form-control" rows="3" placeholder="Comparta su experiencia..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-sm">
                            📝 Enviar Evaluación
                        </button>
                    </form>
                </div>
            </div>
            <?php elseif ($encuesta): ?>
            <!-- Mostrar encuesta existente -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>⭐ Evaluación</h4>
                </div>
                <div class="card-body">
                    <p><strong>Calificación:</strong> 
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $encuesta['calificacion'] ? '⭐' : '☆' ?>
                        <?php endfor; ?>
                        (<?= $encuesta['calificacion'] ?>/5)
                    </p>
                    
                    <?php if (!empty($encuesta['comentario'])): ?>
                    <p><strong>Comentario:</strong></p>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($encuesta['comentario'])) ?></p>
                    <?php endif; ?>
                    
                    <small class="text-muted">
                        Evaluado el <?= date('d/m/Y H:i', strtotime($encuesta['created_at'])) ?>
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Conversación y respuestas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>💬 Conversación</h3>
                </div>
                <div class="card-body">
                    <!-- Lista de respuestas -->
                    <div class="conversation">
                        <?php if (empty($respuestas)): ?>
                            <p class="text-center text-muted">No hay respuestas aún. ¡Sé el primero en responder!</p>
                        <?php else: ?>
                            <?php foreach ($respuestas as $respuesta): ?>
                                <div class="response-item mb-4">
                                    <div class="response-header d-flex justify-content-between align-items-center">
                                        <div class="response-author">
                                            <strong>
                                                <?= htmlspecialchars($respuesta['usuario_nombre'] . ' ' . $respuesta['usuario_apellido']) ?>
                                            </strong>
                                            <span class="badge badge-<?= $respuesta['rol_usuario'] === 'Admin' ? 'danger' : ($respuesta['rol_usuario'] === 'Agente' ? 'warning' : 'primary') ?>">
                                                <?= htmlspecialchars($respuesta['rol_usuario']) ?>
                                            </span>
                                        </div>
                                        <div class="response-date">
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($respuesta['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="response-content mt-2">
                                        <?= nl2br(htmlspecialchars($respuesta['mensaje'])) ?>
                                    </div>
                                    
                                    <?php if (!empty($respuesta['archivo_adjunto'])): ?>
                                        <div class="response-attachment mt-2">
                                            <a href="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($respuesta['archivo_adjunto']) ?>" 
                                               target="_blank" class="btn btn-outline-secondary btn-sm">
                                                📎 Ver archivo adjunto
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Formulario para responder -->
                    <?php if ($ticket['estado'] !== 'cerrado'): ?>
                    <div class="response-form mt-4">
                        <h5>✍️ Agregar Respuesta:</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                            <input type="hidden" name="action" value="responder">
                            
                            <div class="form-group">
                                <label for="mensaje" class="form-label">Mensaje: *</label>
                                <textarea id="mensaje" 
                                          name="mensaje" 
                                          class="form-control" 
                                          rows="4" 
                                          placeholder="Escriba su respuesta aquí..."
                                          required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="archivo" class="form-label">Archivo Adjunto (Opcional):</label>
                                <input type="file" 
                                       id="archivo" 
                                       name="archivo" 
                                       class="form-control" 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt">
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF, PDF, DOC, DOCX, TXT. Máximo 5MB</div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    📤 Enviar Respuesta
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <strong>ℹ️ Información:</strong> Este ticket está cerrado. No se pueden agregar más respuestas.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para ver ticket */
.ticket-description {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid var(--primary-color);
}

.description-content {
    line-height: 1.6;
    margin-top: 10px;
}

.attachment-item {
    background: #fff;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
}

.conversation {
    max-height: 600px;
    overflow-y: auto;
}

.response-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.response-item:nth-child(odd) {
    background: #fff;
    border-left-color: #28a745;
}

.response-header {
    margin-bottom: 10px;
}

.response-content {
    background: white;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    line-height: 1.6;
}

.response-form {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-top: 3px solid var(--success-color);
}

.rating-stars {
    display: flex;
    gap: 10px;
    margin: 10px 0;
}

.star-label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 20px;
    transition: background-color 0.2s;
}

.star-label:hover {
    background-color: #fff3cd;
}

.star-label input[type="radio"] {
    display: none;
}

.star-label input[type="radio"]:checked + .star {
    color: #ffc107;
}

.update-form {
    background: #fff3cd;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #ffeaa7;
}

.prioridad-baja { color: var(--success-color); font-weight: 600; }
.prioridad-media { color: var(--warning-color); font-weight: 600; }
.prioridad-alta { color: #fd7e14; font-weight: 600; }
.prioridad-urgente { color: var(--danger-color); font-weight: 600; }

@media (max-width: 768px) {
    .col-8,
    .col-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .rating-stars {
        flex-direction: column;
    }
    
    .response-item {
        padding: 10px;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<script>
// JavaScript específico para ver ticket
document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll al final de la conversación
    const conversation = document.querySelector('.conversation');
    if (conversation && conversation.children.length > 3) {
        conversation.scrollTop = conversation.scrollHeight;
    }
    
    // Auto-resize del textarea
    const textarea = document.getElementById('mensaje');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
    
    // Confirmación antes de cerrar ticket
    const estadoSelect = document.getElementById('estado');
    if (estadoSelect) {
        estadoSelect.addEventListener('change', function() {
            if (this.value === 'cerrado') {
                if (!confirm('¿Está seguro que desea cerrar este ticket? Esta acción no se puede deshacer.')) {
                    this.value = '<?= $ticket['estado'] ?>';
                }
            }
        });
    }
    
    // Validación de archivo
    const archivoInput = document.getElementById('archivo');
    if (archivoInput) {
        archivoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('El archivo es demasiado grande. Máximo 5MB permitido.');
                    this.value = '';
                    return;
                }
                
                // Validar extensión
                const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
                const extension = file.name.split('.').pop().toLowerCase();
                if (!allowedTypes.includes(extension)) {
                    alert('Tipo de archivo no permitido. Use: ' + allowedTypes.join(', '));
                    this.value = '';
                    return;
                }
            }
        });
    }
    
    // Actualización automática cada 30 segundos
    setInterval(function() {
        if (!document.hidden) {
            // Solo recargar si hay nueva actividad
            fetch(window.location.href + '&check_updates=1')
                .then(response => response.text())
                .then(data => {
                    // Aquí se podría implementar verificación de nuevas respuestas
                    console.log('Verificando actualizaciones...');
                })
                .catch(error => console.log('Error verificando actualizaciones:', error));
        }
    }, 30000);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>