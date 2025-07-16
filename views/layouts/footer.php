</main>
        
        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?>. Desarrollado para la gestión eficiente de tickets y soporte técnico.</p>
                <p>
                    <a href="<?= getBaseUrl() ?>/views/public/help.php" style="color: #ffffff;">Mesa de Ayuda</a>
                </p>
            </div>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script src="<?= getBaseUrl() ?>/assets/js/main.js"></script>
    
    <!-- JavaScript adicional específico de la página -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $jsFile): ?>
            <script src="<?= getBaseUrl() ?>/assets/js/<?= $jsFile ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- JavaScript inline específico de la página -->
    <?php if (isset($inlineJS)): ?>
        <script>
            <?= $inlineJS ?>
        </script>
    <?php endif; ?>
    
    <!-- Modal de confirmación global -->
    <div id="confirm-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Acción</h3>
                <button type="button" class="btn-close" onclick="closeModal('confirm-modal')">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirm-message">¿Está seguro que desea realizar esta acción?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('confirm-modal')">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirm-action">Confirmar</button>
            </div>
        </div>
    </div>
    
    <!-- Modal de loading global -->
    <div id="loading-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content text-center">
            <div class="spinner"></div>
            <p>Procesando...</p>
        </div>
    </div>
    
    <!-- Scripts para desarrollo (remover en producción) -->
    <?php if (getenv('APP_ENV') === 'development'): ?>
    <script>
        // Console log para debugging
        console.log('Sistema de Tickets - Modo Desarrollo');
        console.log('Usuario actual:', <?= json_encode($usuario ?? null) ?>);
        
        // Error handling global
        window.addEventListener('error', function(e) {
            console.error('Error JavaScript:', e.error);
        });
        
        // Unhandled promise rejection
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Promise rechazada:', e.reason);
        });
    </script>
    <?php endif; ?>
    
</body>
</html>

<?php
// Limpiar datos de formulario de la sesión
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>