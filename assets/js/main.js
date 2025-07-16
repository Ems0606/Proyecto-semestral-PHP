/**
 * JavaScript principal del sistema de tickets
 * Archivo: assets/js/main.js
 */

// Configuración global
const CONFIG = {
    baseUrl: window.location.origin + '/sistema-tickets',
    ajaxTimeout: 30000,
    maxFileSize: 5 * 1024 * 1024, // 5MB
    allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt']
};

// Utilidades generales
const Utils = {
    /**
     * Mostrar mensaje de alerta
     */
    showAlert: function(message, type = 'info', duration = 5000) {
        const alertContainer = document.getElementById('alert-container') || this.createAlertContainer();
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible`;
        alert.innerHTML = `
            <span>${message}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        alertContainer.appendChild(alert);
        
        // Auto-remover después del tiempo especificado
        if (duration > 0) {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, duration);
        }
    },
    
    /**
     * Crear contenedor de alertas si no existe
     */
    createAlertContainer: function() {
        const container = document.createElement('div');
        container.id = 'alert-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
        document.body.appendChild(container);
        return container;
    },
    
    /**
     * Confirmar acción
     */
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },
    
    /**
     * Formatear fecha
     */
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    
    /**
     * Debounce function
     */
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    /**
     * Validar archivo
     */
    validateFile: function(file) {
        const errors = [];
        
        // Validar tamaño
        if (file.size > CONFIG.maxFileSize) {
            errors.push('El archivo es demasiado grande (máximo 5MB)');
        }
        
        // Validar extensión
        const extension = file.name.split('.').pop().toLowerCase();
        if (!CONFIG.allowedExtensions.includes(extension)) {
            errors.push('Tipo de archivo no permitido');
        }
        
        return errors;
    }
};

// Manejo de formularios
const FormHandler = {
    /**
     * Validar formulario
     */
    validate: function(form) {
        const errors = [];
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                errors.push(`El campo ${field.name || field.id} es requerido`);
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });
        
        // Validar emails
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                errors.push('El email no es válido');
                field.classList.add('error');
            }
        });
        
        // Validar confirmación de password
        const password = form.querySelector('input[name="password"]');
        const confirmPassword = form.querySelector('input[name="confirmar_password"]');
        
        if (password && confirmPassword && password.value !== confirmPassword.value) {
            errors.push('Las contraseñas no coinciden');
            confirmPassword.classList.add('error');
        }
        
        return errors;
    },
    
    /**
     * Validar email
     */
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    /**
     * Enviar formulario via AJAX
     */
    submitAjax: function(form, options = {}) {
        const formData = new FormData(form);
        
        // Validar formulario antes de enviar
        const errors = this.validate(form);
        if (errors.length > 0) {
            Utils.showAlert(errors.join('<br>'), 'danger');
            return false;
        }
        
        // Mostrar loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.textContent;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';
        }
        
        fetch(form.action || window.location.href, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Utils.showAlert(data.message || 'Operación exitosa', 'success');
                if (options.onSuccess) options.onSuccess(data);
            } else {
                Utils.showAlert(data.message || 'Error en la operación', 'danger');
                if (options.onError) options.onError(data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Utils.showAlert('Error de conexión', 'danger');
            if (options.onError) options.onError(error);
        })
        .finally(() => {
            // Restaurar botón
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
            if (options.onComplete) options.onComplete();
        });
        
        return false; // Prevenir submit normal
    }
};

// Manejo de búsqueda
const SearchHandler = {
    /**
     * Configurar búsqueda en vivo
     */
    setup: function(inputSelector, resultsSelector, searchUrl, options = {}) {
        const input = document.querySelector(inputSelector);
        const resultsContainer = document.querySelector(resultsSelector);
        
        if (!input || !resultsContainer) return;
        
        const debouncedSearch = Utils.debounce((query) => {
            this.performSearch(query, resultsContainer, searchUrl, options);
        }, 300);
        
        input.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            if (query.length >= 2) {
                debouncedSearch(query);
            } else {
                resultsContainer.innerHTML = '';
                resultsContainer.style.display = 'none';
            }
        });
        
        // Ocultar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    },
    
    /**
     * Realizar búsqueda
     */
    performSearch: function(query, container, url, options) {
        fetch(`${url}?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            this.displayResults(data, container, options);
        })
        .catch(error => {
            console.error('Error en búsqueda:', error);
            container.innerHTML = '<div class="search-error">Error en la búsqueda</div>';
            container.style.display = 'block';
        });
    },
    
    /**
     * Mostrar resultados de búsqueda
     */
    displayResults: function(results, container, options) {
        if (results.length === 0) {
            container.innerHTML = '<div class="search-no-results">No se encontraron resultados</div>';
        } else {
            const html = results.map(item => {
                if (options.itemTemplate) {
                    return options.itemTemplate(item);
                } else {
                    return `<div class="search-result-item" data-id="${item.id}">${item.title || item.nombre || item.email}</div>`;
                }
            }).join('');
            
            container.innerHTML = html;
            
            // Agregar event listeners a los resultados
            container.querySelectorAll('.search-result-item').forEach(item => {
                item.addEventListener('click', () => {
                    if (options.onSelect) {
                        options.onSelect(results.find(r => r.id == item.dataset.id));
                    }
                    container.style.display = 'none';
                });
            });
        }
        
        container.style.display = 'block';
    }
};

// Manejo de archivos
const FileHandler = {
    /**
     * Configurar preview de archivos
     */
    setupPreview: function(inputSelector, previewSelector) {
        const input = document.querySelector(inputSelector);
        const preview = document.querySelector(previewSelector);
        
        if (!input || !preview) return;
        
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) {
                preview.innerHTML = '';
                return;
            }
            
            // Validar archivo
            const errors = Utils.validateFile(file);
            if (errors.length > 0) {
                Utils.showAlert(errors.join('<br>'), 'danger');
                input.value = '';
                preview.innerHTML = '';
                return;
            }
            
            // Mostrar preview
            this.showPreview(file, preview);
        });
    },
    
    /**
     * Mostrar preview del archivo
     */
    showPreview: function(file, container) {
        const fileName = file.name;
        const fileSize = this.formatFileSize(file.size);
        const fileType = file.type;
        
        let previewHtml = `
            <div class="file-preview">
                <div class="file-info">
                    <strong>${fileName}</strong><br>
                    <small>${fileSize}</small>
                </div>
        `;
        
        // Preview para imágenes
        if (fileType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                container.innerHTML = previewHtml + `
                    <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                </div>`;
            };
            reader.readAsDataURL(file);
        } else {
            previewHtml += `<div class="file-icon">📄</div></div>`;
            container.innerHTML = previewHtml;
        }
    },
    
    /**
     * Formatear tamaño de archivo
     */
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
};

// Manejo de modales
const ModalHandler = {
    /**
     * Abrir modal
     */
    open: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    },
    
    /**
     * Cerrar modal
     */
    close: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    },
    
    /**
     * Configurar modales
     */
    setup: function() {
        // Cerrar modal al hacer clic en el overlay
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.close(e.target.id);
            }
        });
        
        // Cerrar modal con la tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal-overlay[style*="block"]');
                if (openModal) {
                    this.close(openModal.id);
                }
            }
        });
    }
};

// Manejo de filtros
const FilterHandler = {
    /**
     * Aplicar filtros
     */
    apply: function(formSelector) {
        const form = document.querySelector(formSelector);
        if (!form) return;
        
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value.trim()) {
                params.append(key, value);
            }
        }
        
        // Mantener la página actual si existe
        const currentPage = new URLSearchParams(window.location.search).get('page');
        if (currentPage) {
            params.set('page', '1'); // Resetear a página 1 al filtrar
        }
        
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    },
    
    /**
     * Limpiar filtros
     */
    clear: function(formSelector) {
        const form = document.querySelector(formSelector);
        if (!form) return;
        
        form.reset();
        window.location.href = window.location.pathname;
    }
};

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Configurar modales
    ModalHandler.setup();
    
    // Configurar formularios con validación
    document.querySelectorAll('form[data-validate="true"]').forEach(form => {
        form.addEventListener('submit', (e) => {
            const errors = FormHandler.validate(form);
            if (errors.length > 0) {
                e.preventDefault();
                Utils.showAlert(errors.join('<br>'), 'danger');
            }
        });
    });
    
    // Configurar formularios AJAX
    document.querySelectorAll('form[data-ajax="true"]').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            FormHandler.submitAjax(form);
        });
    });
    
    // Configurar preview de archivos
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(input => {
        const previewSelector = input.dataset.preview;
        FileHandler.setupPreview(`#${input.id}`, previewSelector);
    });
    
    // Configurar búsqueda en vivo
    const searchInputs = document.querySelectorAll('input[data-search-url]');
    searchInputs.forEach(input => {
        const searchUrl = input.dataset.searchUrl;
        const resultsSelector = input.dataset.resultsSelector || '#search-results';
        SearchHandler.setup(`#${input.id}`, resultsSelector, searchUrl);
    });
    
    // Auto-ocultar alertas
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            if (!alert.classList.contains('alert-permanent')) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        });
    }, 5000);
    
    // Confirmar acciones peligrosas
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', (e) => {
            const message = element.dataset.confirm;
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Tooltip simple
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.dataset.tooltip;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1000;
                white-space: nowrap;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            
            element.addEventListener('mouseleave', () => {
                tooltip.remove();
            }, { once: true });
        });
    });
});

// Funciones globales para usar en HTML
window.showAlert = Utils.showAlert;
window.openModal = ModalHandler.open;
window.closeModal = ModalHandler.close;
window.applyFilters = FilterHandler.apply;
window.clearFilters = FilterHandler.clear;