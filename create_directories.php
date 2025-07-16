<?php
/**
 * Crear directorios necesarios para el sistema
 * Ejecutar una vez para crear las carpetas de uploads
 */

// Directorios necesarios
$directorios = [
    'assets',
    'assets/uploads',
    'assets/uploads/perfiles',
    'assets/uploads/tickets'
];

echo "<h2>Creando directorios necesarios...</h2>";

foreach ($directorios as $directorio) {
    if (!is_dir($directorio)) {
        if (mkdir($directorio, 0755, true)) {
            echo "✅ Directorio creado: $directorio<br>";
        } else {
            echo "❌ Error al crear directorio: $directorio<br>";
        }
    } else {
        echo "✅ Directorio ya existe: $directorio<br>";
    }
}

// Crear archivo .htaccess para proteger uploads
$htaccessContent = "# Proteger archivos PHP
<Files *.php>
    Order Deny,Allow
    Deny from all
</Files>

# Permitir acceso a imágenes y documentos
<FilesMatch \"\.(jpg|jpeg|png|gif|pdf|doc|docx|txt)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>";

file_put_contents('assets/uploads/.htaccess', $htaccessContent);
echo "✅ Archivo .htaccess creado en uploads<br>";

echo "<br><strong>¡Directorios creados exitosamente!</strong><br>";
echo "<a href='views/auth/register.php'>Ir al registro</a>";
?>