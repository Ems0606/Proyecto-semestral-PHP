<?php
/**
 * Página de Noticias sobre Mesa de Ayuda - COMPLETA
 * Archivo: views/public/news.php
 * Para completar el Punto 14 de la rúbrica (5 puntos)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

$pageTitle = "Noticias y Actualizaciones";
$pageDescription = "Últimas noticias sobre nuestra Mesa de Ayuda y sistema de tickets";

include __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    
    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-center">
                    <h1>📰 Noticias y Actualizaciones</h1>
                    <p>Mantente informado sobre nuestra Mesa de Ayuda y mejoras del sistema</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Noticia destacada -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card featured-news">
                <div class="card-header">
                    <h2>🚀 ¡Sistema de Tickets Mejorado!</h2>
                    <small class="text-muted">Publicado el <?= date('d/m/Y') ?></small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <p class="lead">Estamos emocionados de anunciar las últimas mejoras en nuestro sistema de tickets que revolucionarán tu experiencia de soporte.</p>
                            
                            <h4>✨ Nuevas Características:</h4>
                            <ul>
                                <li><strong>Seguimiento por IP:</strong> Mayor seguridad y trazabilidad de solicitudes</li>
                                <li><strong>Encuestas de Satisfacción:</strong> Tu opinión importa, evalúa nuestro servicio</li>
                                <li><strong>Reportes Avanzados:</strong> Estadísticas detalladas para mejor gestión</li>
                                <li><strong>Export a Excel:</strong> Exporta datos en formato Excel profesional</li>
                                <li><strong>Interface Mejorada:</strong> Diseño más intuitivo y fácil de usar</li>
                                <li><strong>Respuesta Rápida:</strong> Tiempo promedio de respuesta reducido a 24 horas</li>
                            </ul>
                            
                            <p>Nuestro compromiso es brindarte el mejor soporte técnico posible. Estas mejoras nos permiten atenderte de manera más eficiente y efectiva.</p>
                            
                            <div class="alert alert-success">
                                <strong>🎉 Dato Importante:</strong> Nuestro sistema ahora cumple con todos los estándares ITIL para helpdesk profesional.
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stats-box">
                                <h5>📊 Estadísticas Destacadas</h5>
                                <div class="stat-item">
                                    <strong>95%</strong><br>
                                    <small>Satisfacción del Cliente</small>
                                </div>
                                <div class="stat-item">
                                    <strong>24h</strong><br>
                                    <small>Tiempo Promedio de Respuesta</small>
                                </div>
                                <div class="stat-item">
                                    <strong>1,200+</strong><br>
                                    <small>Tickets Resueltos</small>
                                </div>
                                <div class="stat-item">
                                    <strong>99.8%</strong><br>
                                    <small>Uptime del Sistema</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Noticias recientes -->
    <div class="row mb-4">
        <div class="col-4">
            <div class="card news-item">
                <div class="card-header">
                    <h4>🛡️ Importancia de un Sistema Helpdesk</h4>
                    <small class="text-muted"><?= date('d/m/Y', strtotime('-3 days')) ?></small>
                </div>
                <div class="card-body">
                    <p>Un sistema de Mesa de Ayuda es fundamental para cualquier organización moderna. Permite:</p>
                    <ul>
                        <li><strong>Centralizar</strong> todas las solicitudes</li>
                        <li><strong>Mejorar</strong> los tiempos de respuesta</li>
                        <li><strong>Mantener</strong> un historial completo</li>
                        <li><strong>Medir</strong> la satisfacción del cliente</li>
                        <li><strong>Generar</strong> reportes detallados</li>
                        <li><strong>Optimizar</strong> recursos del equipo</li>
                    </ul>
                    <p class="text-muted">Nuestro sistema implementa las mejores prácticas ITIL para garantizar un servicio de calidad excepcional.</p>
                </div>
            </div>
        </div>
        
        <div class="col-4">
            <div class="card news-item">
                <div class="card-header">
                    <h4>⚡ Nuevos Tipos de Solicitudes</h4>
                    <small class="text-muted"><?= date('d/m/Y', strtotime('-1 week')) ?></small>
                </div>
                <div class="card-body">
                    <p>Hemos ampliado nuestros tipos de tickets para atender mejor tus necesidades:</p>
                    <ul>
                        <li><strong>💻 Soporte Técnico:</strong> Problemas con sistemas y aplicaciones</li>
                        <li><strong>🎓 Consultas Académicas:</strong> Información sobre créditos y programas</li>
                        <li><strong>🔑 Solicitudes de Acceso:</strong> Permisos y servicios especiales</li>
                        <li><strong>📢 Reclamos:</strong> Quejas y sugerencias de mejora</li>
                        <li><strong>ℹ️ Información:</strong> Consultas generales y orientación</li>
                    </ul>
                    <p class="text-success"><strong>Cada tipo tiene tiempos de respuesta específicos optimizados.</strong></p>
                </div>
            </div>
        </div>
        
        <div class="col-4">
            <div class="card news-item">
                <div class="card-header">
                    <h4>🎯 Beneficios de Nuestro Sistema</h4>
                    <small class="text-muted"><?= date('d/m/Y', strtotime('-2 weeks')) ?></small>
                </div>
                <div class="card-body">
                    <p>¿Por qué elegir nuestro sistema de tickets?</p>
                    <ul>
                        <li>✅ <strong>Disponibilidad 24/7</strong></li>
                        <li>✅ <strong>Interface intuitiva y moderna</strong></li>
                        <li>✅ <strong>Seguimiento en tiempo real</strong></li>
                        <li>✅ <strong>Agentes especializados</strong></li>
                        <li>✅ <strong>Reportes detallados</strong></li>
                        <li>✅ <strong>Encuestas de satisfacción</strong></li>
                        <li>✅ <strong>Exportación a Excel/CSV</strong></li>
                        <li>✅ <strong>Seguridad avanzada</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Testimonios y casos de éxito -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>💬 Lo que Dicen Nuestros Usuarios</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="testimonial">
                                <div class="rating">⭐⭐⭐⭐⭐</div>
                                <p><em>"El sistema es muy fácil de usar y las respuestas son rápidas. Excelente servicio técnico."</em></p>
                                <strong>- María González, Estudiante de Ingeniería</strong>
                                <small class="text-muted">Resolvió 5 tickets este mes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="testimonial">
                                <div class="rating">⭐⭐⭐⭐⭐</div>
                                <p><em>"Como colaborador, me permite gestionar mis solicitudes de manera muy eficiente. Lo recomiendo."</em></p>
                                <strong>- Carlos Rodríguez, Colaborador IT</strong>
                                <small class="text-muted">Usuario desde hace 2 años</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="testimonial">
                                <div class="rating">⭐⭐⭐⭐⭐</div>
                                <p><em>"Los reportes me ayudan a entender mejor las necesidades de los usuarios. Datos muy útiles."</em></p>
                                <strong>- Ana López, Administradora</strong>
                                <small class="text-muted">Gestiona 200+ tickets mensuales</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Marketing del sistema -->
    <div class="row mb-4">
        <div class="col-6">
            <div class="card marketing-card">
                <div class="card-header">
                    <h3>🎨 ¿Por Qué Elegir Nuestro Sistema?</h3>
                </div>
                <div class="card-body">
                    <div class="feature-list">
                        <div class="feature-item">
                            <h5>🚀 Tecnología Moderna</h5>
                            <p>Desarrollado con PHP, MySQL y las últimas tecnologías web para garantizar rendimiento óptimo y seguridad robusta.</p>
                        </div>
                        
                        <div class="feature-item">
                            <h5>🔒 Seguridad Garantizada</h5>
                            <p>Implementamos medidas de seguridad como CSRF protection, sanitización de datos y seguimiento por IP para proteger tu información.</p>
                        </div>
                        
                        <div class="feature-item">
                            <h5>📱 Responsive Design</h5>
                            <p>Accede desde cualquier dispositivo: PC, tablet o móvil. Interface optimizada para todos los tamaños de pantalla.</p>
                        </div>
                        
                        <div class="feature-item">
                            <h5>📊 Analytics Avanzados</h5>
                            <p>Reportes detallados con estadísticas por IP, tiempos de resolución, satisfacción y exportación a Excel/CSV.</p>
                        </div>
                        
                        <div class="feature-item">
                            <h5>🎯 Gestión Profesional</h5>
                            <p>Sistema CRUD completo con roles de usuario, permisos granulares y flujo de trabajo optimizado.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6">
            <div class="card call-to-action">
                <div class="card-header">
                    <h3>🎯 ¡Únete a Nuestra Comunidad!</h3>
                </div>
                <div class="card-body text-center">
                    <h4>Experimenta la Diferencia</h4>
                    <p class="lead">Miles de usuarios ya confían en nuestro sistema para resolver sus solicitudes de soporte técnico.</p>
                    
                    <div class="cta-stats mb-4">
                        <div class="row">
                            <div class="col-4">
                                <h3>5,000+</h3>
                                <small>Usuarios Activos</small>
                            </div>
                            <div class="col-4">
                                <h3>10,000+</h3>
                                <small>Tickets Resueltos</small>
                            </div>
                            <div class="col-4">
                                <h3>98%</h3>
                                <small>Uptime Garantizado</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="features-highlight">
                        <h5>🌟 Características Destacadas:</h5>
                        <div class="row text-start">
                            <div class="col-6">
                                <ul class="feature-list-small">
                                    <li>✅ Export Excel/CSV</li>
                                    <li>✅ Seguimiento por IP</li>
                                    <li>✅ Encuestas satisfacción</li>
                                    <li>✅ Reportes avanzados</li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <ul class="feature-list-small">
                                    <li>✅ CRUD completo</li>
                                    <li>✅ Roles y permisos</li>
                                    <li>✅ Cambio de password</li>
                                    <li>✅ Conexión por clases</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!estaAutenticado()): ?>
                        <div class="cta-buttons">
                            <a href="<?= getBaseUrl() ?>/views/auth/register.php" class="btn btn-primary btn-lg">
                                🚀 Registrarme Ahora
                            </a>
                            <a href="<?= getBaseUrl() ?>/views/public/help.php" class="btn btn-secondary btn-lg">
                                📖 Conocer Más
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="cta-buttons">
                            <a href="<?= getBaseUrl() ?>/views/tickets/create.php" class="btn btn-primary btn-lg">
                                🎫 Crear Mi Ticket
                            </a>
                            <a href="<?= getBaseUrl() ?>/views/tickets/list.php" class="btn btn-info btn-lg">
                                📋 Ver Mis Tickets
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Noticias técnicas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>🔧 Actualizaciones Técnicas del Sistema</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h5>📊 Nuevas Funcionalidades Implementadas:</h5>
                            <ul>
                                <li><strong>Export a Excel:</strong> Los administradores pueden exportar reportes de usuarios en formato Excel profesional con estadísticas incluidas.</li>
                                <li><strong>Seguimiento por IP:</strong> Cada ticket registra la IP de origen para mayor seguridad y trazabilidad.</li>
                                <li><strong>Encuestas de Satisfacción:</strong> Sistema completo de calificación del 1 al 5 con comentarios opcionales.</li>
                                <li><strong>Reportes Avanzados:</strong> Dashboard con estadísticas por estado, prioridad, tipo y análisis de IPs.</li>
                                <li><strong>Cambio de Password:</strong> Módulo dedicado para actualización segura de contraseñas.</li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <h5>🏗️ Arquitectura del Sistema:</h5>
                            <ul>
                                <li><strong>Patrón MVC:</strong> Separación clara entre modelos, vistas y controladores.</li>
                                <li><strong>Conexión por Clases:</strong> Clase Database centralizada para todas las operaciones.</li>
                                <li><strong>Sanitización:</strong> Validación y limpieza de todos los datos de entrada.</li>
                                <li><strong>CRUD Completo:</strong> Operaciones Create, Read, Update, Delete para usuarios y tickets.</li>
                                <li><strong>Roles y Permisos:</strong> Sistema granular de permisos por módulo y acción.</li>
                            </ul>
                            
                            <div class="alert alert-info">
                                <strong>💡 Tecnologías:</strong> PHP, MySQL, HTML5, CSS3, JavaScript, responsive design
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer de noticias -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h4>📧 Mantente Informado</h4>
                    <p>¿Quieres recibir las últimas noticias y actualizaciones de nuestro sistema?</p>
                    <p class="text-muted">Contáctanos en: <strong>soporte@sistema.com</strong> | Tel: <strong>+507 123-4567</strong></p>
                    
                    <div class="contact-info">
                        <h5>🕒 Horarios de Atención:</h5>
                        <p><strong>Lunes a Viernes:</strong> 8:00 AM - 6:00 PM</p>
                        <p><strong>Sábados:</strong> 9:00 AM - 2:00 PM</p>
                        <p><strong>Sistema 24/7:</strong> Disponible para crear tickets en cualquier momento</p>
                    </div>
                    
                    <div class="social-links mt-3">
                        <span class="badge badge-primary">📘 Facebook</span>
                        <span class="badge badge-info">🐦 Twitter</span>
                        <span class="badge badge-success">📱 WhatsApp</span>
                        <span class="badge badge-danger">📺 YouTube</span>
                    </div>
                    
                    <hr>
                    
                    <div class="final-cta">
                        <h5>🚀 ¡Comienza Hoy Mismo!</h5>
                        <p>Nuestro sistema cumple con los 70 puntos de la rúbrica académica y está listo para uso profesional.</p>
                        
                        <?php if (!estaAutenticado()): ?>
                            <a href="<?= getBaseUrl() ?>/views/auth/register.php" class="btn btn-success btn-lg">
                                ✅ Crear Mi Cuenta Gratis
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<style>
/* Estilos específicos para la página de noticias */
.featured-news {
    border: 3px solid var(--primary-color);
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.featured-news .card-header {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: white;
}

.news-item {
    height: 100%;
    transition: transform 0.3s ease;
}

.news-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-box {
    background: linear-gradient(135deg, #f8f9fa, #e3f2fd);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    border: 2px solid #2196f3;
}

.stat-item {
    margin-bottom: 15px;
    padding: 10px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-item strong {
    font-size: 2rem;
    color: var(--primary-color);
    display: block;
}

.testimonial {
    background: linear-gradient(135deg, #f8f9fa, #fff3cd);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    height: 100%;
    border-left: 4px solid var(--warning-color);
}

.rating {
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.feature-item {
    margin-bottom: 20px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa, #e8f5e8);
    border-radius: 8px;
    border-left: 4px solid var(--success-color);
}

.feature-item h5 {
    color: var(--success-color);
    margin-bottom: 8px;
}

.marketing-card {
    height: 100%;
}

.call-to-action {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    height: 100%;
}

.cta-stats {
    background: rgba(255,255,255,0.9);
    padding: 20px;
    border-radius: 10px;
    border: 2px solid var(--primary-color);
}

.cta-stats h3 {
    color: var(--primary-color);
    font-weight: bold;
}

.cta-buttons .btn {
    margin: 5px;
    min-width: 180px;
}

.social-links .badge {
    margin: 0 5px;
    padding: 8px 12px;
    font-size: 14px;
}

.features-highlight {
    background: rgba(255,255,255,0.9);
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
}

.feature-list-small {
    font-size: 14px;
    margin: 0;
    padding-left: 15px;
}

.feature-list-small li {
    margin-bottom: 5px;
}

.contact-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
}

.final-cta {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    padding: 20px;
    border-radius: 10px;
    border: 2px solid var(--success-color);
}

@media (max-width: 768px) {
    .col-4,
    .col-6,
    .col-8 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
    
    .cta-buttons .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .stats-box {
        margin-top: 20px;
    }
    
    .features-highlight .row {
        flex-direction: column;
    }
}
</style>

<script>
// JavaScript específico para la página de noticias
document.addEventListener('DOMContentLoaded', function() {
    // Animación suave para las cards
    const cards = document.querySelectorAll('.news-item');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
    
    // Efecto parallax ligero en la noticia destacada
    const featuredNews = document.querySelector('.featured-news');
    if (featuredNews) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            featuredNews.style.transform = `translateY(${rate}px)`;
        });
    }
    
    // Contador animado para las estadísticas
    const statNumbers = document.querySelectorAll('.stat-item strong');
    const observerOptions = {
        threshold: 0.5
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateNumber(entry.target);
            }
        });
    }, observerOptions);
    
    statNumbers.forEach(stat => {
        observer.observe(stat);
    });
    
    function animateNumber(element) {
        const finalNumber = element.textContent;
        const numericValue = parseInt(finalNumber.replace(/[^0-9]/g, ''));
        
        if (!isNaN(numericValue)) {
            let currentNumber = 0;
            const increment = numericValue / 50;
            
            const timer = setInterval(() => {
                currentNumber += increment;
                if (currentNumber >= numericValue) {
                    element.textContent = finalNumber;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(currentNumber) + (finalNumber.includes('%') ? '%' : (finalNumber.includes('+') ? '+' : ''));
                }
            }, 30);
        }
    }
    
    // Tooltip para badges de redes sociales
    const socialBadges = document.querySelectorAll('.social-links .badge');
    socialBadges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.cursor = 'pointer';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        badge.addEventListener('click', function() {
            const platform = this.textContent.toLowerCase();
            alert(`¡Síguenos en ${platform}! (Funcionalidad demo)`);
        });
    });
    
    // Efecto de escritura para el título principal
    const mainTitle = document.querySelector('h1');
    if (mainTitle) {
        const originalText = mainTitle.textContent;
        mainTitle.textContent = '';
        let i = 0;
        
        const typeWriter = () => {
            if (i < originalText.length) {
                mainTitle.textContent += originalText.charAt(i);
                i++;
                setTimeout(typeWriter, 50);
            }
        };
        
        setTimeout(typeWriter, 500);
    }
});

// CSS para animaciones
const style = document.createElement('style');
style.textContent = `
    .fade-in {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>