<?php
/**
 * Página de mesa de ayuda
 * Archivo: views/public/help.php
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

$pageTitle = "Mesa de Ayuda";
$pageDescription = "Centro de ayuda y documentación del sistema de tickets";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-center">
                    <h1>❓ Mesa de Ayuda</h1>
                    <p>Centro de información y soporte para el uso del sistema</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preguntas frecuentes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>🔍 Preguntas Frecuentes</h3>
                </div>
                <div class="card-body">
                    <div class="faq-item mb-3">
                        <h5>¿Cómo creo un ticket?</h5>
                        <p>Para crear un ticket, debe estar registrado e iniciar sesión. Luego, vaya al menú "Crear Ticket", complete el formulario con su solicitud y envíelo. Recibirá un número de ticket para hacer seguimiento.</p>
                    </div>
                    
                    <div class="faq-item mb-3">
                        <h5>¿Cuánto tiempo tarda en responderse un ticket?</h5>
                        <p>Nuestro tiempo de respuesta es máximo 24 horas para tickets normales. Los tickets marcados como urgentes son atendidos en un máximo de 4 horas durante horario laboral.</p>
                    </div>
                    
                    <div class="faq-item mb-3">
                        <h5>¿Puedo adjuntar archivos a mi ticket?</h5>
                        <p>Sí, puede adjuntar archivos de hasta 5MB en formatos: JPG, PNG, GIF, PDF, DOC, DOCX, TXT. Esto nos ayuda a entender mejor su solicitud.</p>
                    </div>
                    
                    <div class="faq-item mb-3">
                        <h5>¿Cómo hago seguimiento a mi ticket?</h5>
                        <p>En la sección "Mis Tickets" puede ver todos sus tickets, su estado actual y las respuestas del equipo de soporte. También puede agregar comentarios adicionales.</p>
                    </div>
                    
                    <div class="faq-item mb-3">
                        <h5>¿Qué significan los diferentes estados de un ticket?</h5>
                        <ul>
                            <li><strong>Abierto:</strong> El ticket ha sido creado y está pendiente de asignación</li>
                            <li><strong>En Proceso:</strong> Un agente está trabajando en su solicitud</li>
                            <li><strong>Resuelto:</strong> La solicitud ha sido resuelta, pendiente de confirmación</li>
                            <li><strong>Cerrado:</strong> El ticket ha sido completado y cerrado</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Guías de uso -->
    <div class="row mb-4">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>📚 Guías de Uso</h4>
                </div>
                <div class="card-body">
                    <div class="guide-item mb-3">
                        <h6>🆕 Para Nuevos Usuarios</h6>
                        <ol>
                            <li>Regístrese con sus datos personales</li>
                            <li>Confirme su email (si aplica)</li>
                            <li>Inicie sesión en el sistema</li>
                            <li>Complete su perfil</li>
                            <li>Cree su primer ticket</li>
                        </ol>
                    </div>
                    
                    <div class="guide-item mb-3">
                        <h6>🎫 Creando un Ticket Efectivo</h6>
                        <ul>
                            <li>Use un título descriptivo</li>
                            <li>Explique detalladamente el problema</li>
                            <li>Seleccione la categoría correcta</li>
                            <li>Indique la prioridad apropiada</li>
                            <li>Adjunte archivos si es necesario</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h4>📞 Información de Contacto</h4>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <p><strong>📧 Email:</strong> soporte@sistema.com</p>
                        <p><strong>📱 Teléfono:</strong> +507 123-4567</p>
                        <p><strong>📍 Dirección:</strong> Ciudad de Panamá, Panamá</p>
                    </div>
                    
                    <hr>
                    
                    <h6>⏰ Horarios de Atención</h6>
                    <div class="schedule">
                        <p><strong>Lunes a Viernes:</strong> 8:00 AM - 6:00 PM</p>
                        <p><strong>Sábados:</strong> 9:00 AM - 2:00 PM</p>
                        <p><strong>Domingos:</strong> Cerrado</p>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong>💡 Nota:</strong> El sistema de tickets está disponible 24/7 para crear solicitudes.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tipos de soporte -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>🛠️ Tipos de Soporte Disponibles</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="support-type mb-3">
                                <h6>💻 Soporte Técnico</h6>
                                <p>Problemas con sistemas, aplicaciones, errores técnicos, configuraciones y conectividad.</p>
                                <span class="badge badge-info">Tiempo de respuesta: 2-4 horas</span>
                            </div>
                            
                            <div class="support-type mb-3">
                                <h6>🎓 Consultas Académicas</h6>
                                <p>Información sobre créditos oficiales, programas académicos, certificaciones y documentación.</p>
                                <span class="badge badge-success">Tiempo de respuesta: 4-8 horas</span>
                            </div>
                            
                            <div class="support-type mb-3">
                                <h6>🔐 Solicitudes de Acceso</h6>
                                <p>Permisos de acceso a internet, sistemas, aplicaciones y recursos institucionales.</p>
                                <span class="badge badge-warning">Tiempo de respuesta: 8-24 horas</span>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="support-type mb-3">
                                <h6>📝 Reclamos y Quejas</h6>
                                <p>Quejas sobre servicios, reclamos, sugerencias de mejora y comentarios generales.</p>
                                <span class="badge badge-danger">Tiempo de respuesta: 4-12 horas</span>
                            </div>
                            
                            <div class="support-type mb-3">
                                <h6>ℹ️ Información General</h6>
                                <p>Consultas generales, información sobre servicios, horarios y procedimientos.</p>
                                <span class="badge badge-secondary">Tiempo de respuesta: 2-6 horas</span>
                            </div>
                            
                            <div class="support-type mb-3">
                                <h6>🚨 Emergencias</h6>
                                <p>Problemas críticos que afectan el funcionamiento normal de servicios esenciales.</p>
                                <span class="badge badge-danger">Tiempo de respuesta: Inmediato</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Consejos y mejores prácticas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>💡 Consejos y Mejores Prácticas</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h6>✅ Qué hacer:</h6>
                            <ul>
                                <li>Proporcione información detallada y específica</li>
                                <li>Incluya capturas de pantalla si es relevante</li>
                                <li>Mencione el navegador y sistema operativo</li>
                                <li>Describa los pasos que llevaron al problema</li>
                                <li>Mantenga un tono respetuoso y profesional</li>
                                <li>Responda a las solicitudes del agente</li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h6>❌ Qué evitar:</h6>
                            <ul>
                                <li>Crear múltiples tickets para el mismo problema</li>
                                <li>Usar lenguaje ofensivo o inapropiado</li>
                                <li>Enviar información personal sensible</li>
                                <li>Marcar todo como urgente sin justificación</li>
                                <li>Adjuntar archivos innecesarios o muy grandes</li>
                                <li>Cerrar tickets sin confirmar la resolución</li>
                            </ul>
                        </div>
                    </div>
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
                <div class="card-body text-center">
                    <div class="row">
                        <?php if (estaAutenticado()): ?>
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
                                <a href="<?= getBaseUrl() ?>/views/public/home.php" class="btn btn-success btn-lg d-block">
                                    🏠 Inicio
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="col-4">
                                <a href="<?= getBaseUrl() ?>/views/auth/register.php" class="btn btn-success btn-lg d-block">
                                    ✅ Registrarse
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="<?= getBaseUrl() ?>/views/auth/login.php" class="btn btn-primary btn-lg d-block">
                                    🔐 Iniciar Sesión
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="<?= getBaseUrl() ?>/views/public/home.php" class="btn btn-info btn-lg d-block">
                                    🏠 Inicio
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para la página de ayuda */
.faq-item {
    border-left: 4px solid var(--primary-color);
    padding-left: 15px;
}

.faq-item h5 {
    color: var(--primary-color);
    margin-bottom: 10px;
}

.guide-item h6 {
    color: var(--success-color);
    margin-bottom: 10px;
}

.support-type {
    border: 1px solid #dee2e6;
    border-radius: var(--border-radius);
    padding: 15px;
    background: #f8f9fa;
}

.support-type h6 {
    color: var(--dark-color);
    margin-bottom: 8px;
}

.contact-info p {
    margin-bottom: 8px;
    font-size: 16px;
}

.schedule p {
    margin-bottom: 5px;
}

.btn-lg {
    margin-bottom: 10px;
}

@media (max-width: 768px) {
    .col-6,
    .col-4,
    .col-3 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
    
    .support-type {
        margin-bottom: 15px;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>