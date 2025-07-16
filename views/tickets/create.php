<?php
/**
 * Página para crear tickets
 * Archivo: views/tickets/create.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/Ticket.php';

// Verificar autenticación
requerirAutenticacion();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../controllers/TicketController.php';
    $ticketController = new TicketController();
    $ticketController->crear();
}

// Obtener tipos de tickets
$ticketModel = new Ticket();
$tiposTickets = $ticketModel->obtenerTipos();

$usuario = obtenerUsuarioActual();
$pageTitle = "Crear Ticket";
$pageDescription = "Crear nueva solicitud de soporte";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h1>➕ Crear Nuevo Ticket</h1>
                    <p>Complete el formulario para enviar su solicitud de soporte</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formulario de creación -->
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3>📝 Información del Ticket</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" data-validate="true">
                        <!-- Token CSRF -->
                        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                        
                        <!-- Título -->
                        <div class="form-group">
                            <label for="titulo" class="form-label">Título del Ticket: *</label>
                            <input type="text" 
                                   id="titulo" 
                                   name="titulo" 
                                   class="form-control" 
                                   placeholder="Resuma brevemente su solicitud"
                                   value="<?= htmlspecialchars($_SESSION['form_data']['titulo'] ?? '') ?>"
                                   maxlength="200"
                                   required>
                            <div class="form-text">Máximo 200 caracteres. Sea específico y descriptivo.</div>
                        </div>
                        
                        <!-- Tipo de ticket -->
                        <div class="form-group">
                            <label for="tipo_ticket_id" class="form-label">Tipo de Solicitud: *</label>
                            <select id="tipo_ticket_id" name="tipo_ticket_id" class="form-control form-select" required>
                                <option value="">Seleccione el tipo de solicitud</option>
                                <?php foreach ($tiposTickets as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>" 
                                            <?= ($_SESSION['form_data']['tipo_ticket_id'] ?? '') == $tipo['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Seleccione la categoría que mejor describa su solicitud.</div>
                        </div>
                        
                        <!-- Prioridad -->
                        <div class="form-group">
                            <label for="prioridad" class="form-label">Prioridad: *</label>
                            <select id="prioridad" name="prioridad" class="form-control form-select" required>
                                <option value="baja" <?= ($_SESSION['form_data']['prioridad'] ?? 'media') === 'baja' ? 'selected' : '' ?>>
                                    🟢 Baja - No es urgente
                                </option>
                                <option value="media" <?= ($_SESSION['form_data']['prioridad'] ?? 'media') === 'media' ? 'selected' : '' ?>>
                                    🟡 Media - Importante pero no urgente
                                </option>
                                <option value="alta" <?= ($_SESSION['form_data']['prioridad'] ?? 'media') === 'alta' ? 'selected' : '' ?>>
                                    🟠 Alta - Requiere atención pronta
                                </option>
                                <option value="urgente" <?= ($_SESSION['form_data']['prioridad'] ?? 'media') === 'urgente' ? 'selected' : '' ?>>
                                    🔴 Urgente - Problema crítico
                                </option>
                            </select>
                            <div class="form-text">Seleccione la prioridad según la urgencia de su solicitud.</div>
                        </div>
                        
                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion" class="form-label">Descripción Detallada: *</label>
                            <textarea id="descripcion" 
                                      name="descripcion" 
                                      class="form-control" 
                                      rows="6" 
                                      placeholder="Describa detalladamente su problema o solicitud. Incluya:&#10;- Qué estaba haciendo cuando ocurrió el problema&#10;- Pasos para reproducir el error&#10;- Mensaje de error específico (si aplica)&#10;- Qué resultado esperaba vs. qué obtuvo"
                                      required><?= htmlspecialchars($_SESSION['form_data']['descripcion'] ?? '') ?></textarea>
                            <div class="form-text">Sea lo más específico posible. Esto nos ayuda a resolver su solicitud más rápidamente.</div>
                        </div>
                        
                        <!-- Archivo adjunto -->
                        <div class="form-group">
                            <label for="archivo" class="form-label">Archivo Adjunto (Opcional):</label>
                            <input type="file" 
                                   id="archivo" 
                                   name="archivo" 
                                   class="form-control" 
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt"
                                   data-preview="#archivo-preview">
                            <div class="form-text">
                                Formatos permitidos: JPG, PNG, GIF, PDF, DOC, DOCX, TXT. Máximo 5MB.<br>
                                <strong>Útil para:</strong> Capturas de pantalla, documentos de error, archivos de ejemplo.
                            </div>
                            <div id="archivo-preview" class="mt-2"></div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="form-group">
                            <div class="alert alert-info">
                                <strong>💡 Consejos para un ticket efectivo:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Use un título descriptivo y específico</li>
                                    <li>Incluya detalles sobre su sistema operativo y navegador</li>
                                    <li>Adjunte capturas de pantalla si muestran el problema</li>
                                    <li>Mencione si el problema es recurrente o específico</li>
                                    <li>Indique la urgencia real para priorización correcta</li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Botones -->
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                        🚀 Enviar Ticket
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="<?= getBaseUrl() ?>/views/tickets/list.php" class="btn btn-secondary btn-lg" style="width: 100%;">
                                        📋 Mis Tickets
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
                    <h4>👤 Información del Solicitante</h4>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                    <p><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']) ?></p>
                    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i') ?></p>
                </div>
            </div>
            
            <!-- Descripción de tipos de tickets -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4>📋 Tipos de Solicitud</h4>
                </div>
                <div class="card-body">
                    <?php foreach ($tiposTickets as $tipo): ?>
                        <div class="tipo-info mb-2">
                            <strong><?= htmlspecialchars($tipo['nombre']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($tipo['descripcion']) ?></small>
                        </div>
                        <?php if ($tipo !== end($tiposTickets)): ?><hr><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Información de contacto -->
            <div class="card">
                <div class="card-header">
                    <h4>📞 Contacto Directo</h4>
                </div>
                <div class="card-body">
                    <p><strong>📧 Email:</strong> soporte@sistema.com</p>
                    <p><strong>📱 Teléfono:</strong> +507 123-4567</p>
                    <p><strong>⏰ Horario:</strong> L-V 8AM-6PM</p>
                    <hr>
                    <div class="alert alert-warning">
                        <small>
                            <strong>Emergencias:</strong> Para problemas críticos que requieren atención inmediata, 
                            contáctenos directamente por teléfono.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para crear ticket */
.tipo-info {
    padding: 8px;
    border-left: 3px solid var(--primary-color);
    background: #f8f9fa;
}

.form-text {
    font-size: 13px;
    color: #6c757d;
    margin-top: 5px;
}

textarea.form-control {
    resize: vertical;
    min-height: 120px;
}

.alert ul {
    padding-left: 20px;
}

@media (max-width: 768px) {
    .col-8,
    .col-4,
    .col-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .btn-lg {
        margin-bottom: 10px;
    }
}
</style>

<script>
// JavaScript específico para crear ticket
document.addEventListener('DOMContentLoaded', function() {
    // Contador de caracteres para el título
    const tituloInput = document.getElementById('titulo');
    const descripcionTextarea = document.getElementById('descripcion');
    
    // Agregar contador para título
    if (tituloInput) {
        const tituloCounter = document.createElement('div');
        tituloCounter.className = 'form-text text-right';
        tituloCounter.style.fontSize = '12px';
        tituloInput.parentNode.appendChild(tituloCounter);
        
        function updateTituloCounter() {
            const current = tituloInput.value.length;
            const max = 200;
            tituloCounter.textContent = `${current}/${max} caracteres`;
            if (current > max * 0.9) {
                tituloCounter.style.color = '#dc3545';
            } else {
                tituloCounter.style.color = '#6c757d';
            }
        }
        
        tituloInput.addEventListener('input', updateTituloCounter);
        updateTituloCounter();
    }
    
    // Auto-resize para textarea
    if (descripcionTextarea) {
        descripcionTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
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
    
    // Guardar borrador en localStorage
    function saveDraft() {
        const formData = {
            titulo: document.getElementById('titulo')?.value || '',
            tipo_ticket_id: document.getElementById('tipo_ticket_id')?.value || '',
            prioridad: document.getElementById('prioridad')?.value || '',
            descripcion: document.getElementById('descripcion')?.value || ''
        };
        
        localStorage.setItem('ticket_draft', JSON.stringify(formData));
    }
    
    // Cargar borrador desde localStorage
    function loadDraft() {
        const draft = localStorage.getItem('ticket_draft');
        if (draft) {
            const formData = JSON.parse(draft);
            
            if (document.getElementById('titulo').value === '') {
                Object.keys(formData).forEach(key => {
                    const element = document.getElementById(key);
                    if (element && element.value === '') {
                        element.value = formData[key];
                    }
                });
                
                if (Object.values(formData).some(val => val !== '')) {
                    if (confirm('Se encontró un borrador guardado. ¿Desea cargarlo?')) {
                        // Ya se cargó arriba
                    } else {
                        localStorage.removeItem('ticket_draft');
                    }
                }
            }
        }
    }
    
    // Configurar auto-guardado
    const formElements = ['titulo', 'tipo_ticket_id', 'prioridad', 'descripcion'];
    formElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', saveDraft);
            element.addEventListener('change', saveDraft);
        }
    });
    
    // Cargar borrador al inicio
    loadDraft();
    
    // Limpiar borrador al enviar
    document.querySelector('form').addEventListener('submit', function() {
        localStorage.removeItem('ticket_draft');
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>