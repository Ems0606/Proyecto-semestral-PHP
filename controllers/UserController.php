<?php
/**
 * Controlador de usuarios - COMPLETO CON EXPORT EXCEL
 * Archivo: controllers/UserController.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Listar usuarios
     */
    public function listar() {
        requerirPermiso('usuarios', 'read');
        
        $page = intval($_GET['page'] ?? 1);
        
        // Preparar filtros
        $filtros = [];
        
        if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
            $filtros['nombre'] = trim($_GET['nombre']);
        }
        
        if (isset($_GET['rol_id']) && !empty($_GET['rol_id'])) {
            $filtros['rol_id'] = intval($_GET['rol_id']);
        }
        
        return $this->userModel->obtenerTodos($page, $filtros);
    }
    
    /**
     * Ver perfil de usuario
     */
    public function perfil($id = null) {
        requerirAutenticacion();
        
        $usuarioActual = obtenerUsuarioActual();
        
        // Si no se especifica ID, mostrar perfil del usuario actual
        if ($id === null) {
            $id = $usuarioActual['id'];
        }
        
        // Verificar permisos: solo puede ver su propio perfil o si es admin
        if ($id != $usuarioActual['id'] && !esAdministrador()) {
            $_SESSION['error'] = "No tiene permisos para ver este perfil";
            header('Location: ' . getBaseUrl() . '/views/public/home.php');
            exit();
        }
        
        $usuario = $this->userModel->obtenerPorId($id);
        
        if (!$usuario) {
            $_SESSION['error'] = "Usuario no encontrado";
            header('Location: ' . getBaseUrl() . '/views/public/home.php');
            exit();
        }
        
        return $usuario;
    }
    
    /**
     * Crear usuario
     */
    public function crear() {
        requerirPermiso('usuarios', 'create');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                // Procesar foto de perfil si existe
                $fotoPerfil = null;
                if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                    $fotoPerfil = $this->subirFotoPerfil($_FILES['foto_perfil']);
                }
                
                // Preparar datos del usuario
                $datos = [
                    'primer_nombre' => trim($_POST['primer_nombre'] ?? ''),
                    'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''),
                    'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
                    'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? '',
                    'sexo' => $_POST['sexo'] ?? '',
                    'identificacion' => trim($_POST['identificacion'] ?? ''),
                    'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                    'rol_id' => intval($_POST['rol_id'] ?? 3),
                    'foto_perfil' => $fotoPerfil
                ];
                
                // Validar confirmación de password
                if ($datos['password'] !== ($_POST['confirmar_password'] ?? '')) {
                    throw new Exception("Las contraseñas no coinciden");
                }
                
                if (strlen($datos['password']) < 6) {
                    throw new Exception("La contraseña debe tener al menos 6 caracteres");
                }
                
                // Crear usuario
                $usuarioId = $this->userModel->crear($datos);
                
                if ($usuarioId) {
                    $_SESSION['exito'] = "Usuario creado exitosamente";
                    header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
                } else {
                    throw new Exception("Error al crear el usuario");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . getBaseUrl() . '/views/admin/create_user.php');
                exit();
            }
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function actualizar($id) {
        requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $usuarioActual = obtenerUsuarioActual();
                
                // Verificar permisos
                if ($id != $usuarioActual['id'] && !tienePermiso('usuarios', 'update')) {
                    throw new Exception("No tiene permisos para actualizar este usuario");
                }
                
                // Procesar foto de perfil si existe
                $fotoPerfil = null;
                if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                    $fotoPerfil = $this->subirFotoPerfil($_FILES['foto_perfil']);
                }
                
                // Preparar datos para actualización
                $datos = [];
                
                // Campos que el usuario puede actualizar en su propio perfil
                $camposPermitidos = ['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 
                                   'email', 'sexo', 'identificacion', 'fecha_nacimiento'];
                
                foreach ($camposPermitidos as $campo) {
                    if (isset($_POST[$campo]) && !empty(trim($_POST[$campo]))) {
                        $datos[$campo] = trim($_POST[$campo]);
                    }
                }
                
                // Solo admin puede cambiar rol y estado
                if (tienePermiso('usuarios', 'update')) {
                    if (isset($_POST['rol_id'])) {
                        $datos['rol_id'] = intval($_POST['rol_id']);
                    }
                    if (isset($_POST['activo'])) {
                        $datos['activo'] = intval($_POST['activo']);
                    }
                }
                
                if ($fotoPerfil) {
                    $datos['foto_perfil'] = $fotoPerfil;
                }
                
                // Actualizar usuario
                if (!empty($datos)) {
                    $resultado = $this->userModel->actualizar($id, $datos);
                    
                    if ($resultado) {
                        $_SESSION['exito'] = "Usuario actualizado exitosamente";
                        
                        // Si el usuario actualizó su propio perfil, actualizar sesión
                        if ($id == $usuarioActual['id']) {
                            $usuarioActualizado = $this->userModel->obtenerPorId($id);
                            establecerSesion($usuarioActualizado);
                        }
                    } else {
                        throw new Exception("No se realizaron cambios");
                    }
                } else {
                    throw new Exception("No hay datos para actualizar");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            // Redirigir según el contexto
            if (tienePermiso('usuarios', 'read')) {
                header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
            } else {
                header('Location: ' . getBaseUrl() . '/views/auth/perfil.php');
            }
            exit();
        }
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function eliminar($id) {
        requerirPermiso('usuarios', 'delete');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $usuarioActual = obtenerUsuarioActual();
                
                // No permitir que el usuario se elimine a sí mismo
                if ($id == $usuarioActual['id']) {
                    throw new Exception("No puede eliminarse a sí mismo");
                }
                
                $resultado = $this->userModel->eliminar($id);
                
                if ($resultado) {
                    $_SESSION['exito'] = "Usuario eliminado exitosamente";
                } else {
                    throw new Exception("Error al eliminar el usuario");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
            exit();
        }
    }
    
    /**
     * Buscar usuarios (AJAX)
     */
    public function buscar() {
        requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
            $termino = trim($_GET['q']);
            $limite = intval($_GET['limite'] ?? 10);
            
            $resultados = $this->userModel->buscar($termino, $limite);
            
            header('Content-Type: application/json');
            echo json_encode($resultados);
            exit();
        }
    }
    
    /**
     * Obtener estadísticas de usuarios
     */
    public function estadisticas() {
        requerirPermiso('usuarios', 'read');
        return $this->userModel->obtenerEstadisticas();
    }
    
    /**
     * Obtener roles disponibles
     */
    public function obtenerRoles() {
        return $this->userModel->obtenerRoles();
    }
    
    /**
     * Activar/Desactivar usuario
     */
    public function toggleEstado($id) {
        requerirPermiso('usuarios', 'update');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $usuarioActual = obtenerUsuarioActual();
                
                // No permitir que el usuario se desactive a sí mismo
                if ($id == $usuarioActual['id']) {
                    throw new Exception("No puede desactivarse a sí mismo");
                }
                
                $usuario = $this->userModel->obtenerPorId($id);
                if (!$usuario) {
                    throw new Exception("Usuario no encontrado");
                }
                
                $nuevoEstado = $usuario['activo'] ? 0 : 1;
                $resultado = $this->userModel->actualizar($id, ['activo' => $nuevoEstado]);
                
                if ($resultado) {
                    $mensaje = $nuevoEstado ? "Usuario activado" : "Usuario desactivado";
                    $_SESSION['exito'] = $mensaje . " exitosamente";
                } else {
                    throw new Exception("Error al cambiar el estado del usuario");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
            exit();
        }
    }
    
    /**
     * Exportar usuarios a CSV
     */
    public function exportarCSV() {
        requerirPermiso('usuarios', 'read');
        
        $usuarios = $this->userModel->obtenerTodos(1, [])['data'];
        
        // Configurar headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="usuarios_' . date('Y-m-d') . '.csv"');
        
        // Crear salida CSV
        $output = fopen('php://output', 'w');
        
        // Escribir BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir encabezados
        fputcsv($output, [
            'ID',
            'Primer Nombre',
            'Segundo Nombre', 
            'Primer Apellido',
            'Segundo Apellido',
            'Email',
            'Identificación',
            'Sexo',
            'Fecha Nacimiento',
            'Rol',
            'Estado',
            'Fecha Registro'
        ]);
        
        // Escribir datos
        foreach ($usuarios as $usuario) {
            fputcsv($output, [
                $usuario['id'],
                $usuario['primer_nombre'],
                $usuario['segundo_nombre'],
                $usuario['primer_apellido'],
                $usuario['segundo_apellido'],
                $usuario['email'],
                $usuario['identificacion'],
                $usuario['sexo'] === 'M' ? 'Masculino' : 'Femenino',
                $usuario['fecha_nacimiento'],
                $usuario['rol_nombre'],
                $usuario['activo'] ? 'Activo' : 'Inactivo',
                date('Y-m-d H:i:s', strtotime($usuario['created_at']))
            ]);
        }
        
        fclose($output);
        exit();
    }
    
    /**
     * Exportar usuarios a Excel - NUEVA FUNCIÓN
     */
    public function exportarExcel() {
        requerirPermiso('usuarios', 'read');
        
        $usuarios = $this->userModel->obtenerTodos(1, [])['data'];
        
        // Configurar headers para descarga de Excel
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="usuarios_' . date('Y-m-d') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Crear contenido HTML que Excel interpretará
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo '<meta name="ProgId" content="Excel.Sheet">';
        echo '<style>';
        echo 'table { border-collapse: collapse; width: 100%; }';
        echo 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
        echo 'th { background-color: #4CAF50; color: white; font-weight: bold; }';
        echo 'tr:nth-child(even) { background-color: #f2f2f2; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        
        echo '<h1>Reporte de Usuarios - Sistema de Tickets</h1>';
        echo '<p>Generado el: ' . date('d/m/Y H:i:s') . '</p>';
        echo '<p>Total de usuarios: ' . count($usuarios) . '</p>';
        echo '<br>';
        
        echo '<table>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Primer Nombre</th>';
        echo '<th>Segundo Nombre</th>';
        echo '<th>Primer Apellido</th>';
        echo '<th>Segundo Apellido</th>';
        echo '<th>Email</th>';
        echo '<th>Identificación</th>';
        echo '<th>Sexo</th>';
        echo '<th>Fecha Nacimiento</th>';
        echo '<th>Edad</th>';
        echo '<th>Rol</th>';
        echo '<th>Estado</th>';
        echo '<th>Fecha Registro</th>';
        echo '</tr>';
        
        foreach ($usuarios as $usuario) {
            // Calcular edad
            $fechaNac = new DateTime($usuario['fecha_nacimiento']);
            $hoy = new DateTime();
            $edad = $hoy->diff($fechaNac)->y;
            
            echo '<tr>';
            echo '<td>' . $usuario['id'] . '</td>';
            echo '<td>' . htmlspecialchars($usuario['primer_nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($usuario['segundo_nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($usuario['primer_apellido']) . '</td>';
            echo '<td>' . htmlspecialchars($usuario['segundo_apellido']) . '</td>';
            echo '<td>' . htmlspecialchars($usuario['email']) . '</td>';
            echo '<td>' . htmlspecialchars($usuario['identificacion']) . '</td>';
            echo '<td>' . ($usuario['sexo'] === 'M' ? 'Masculino' : 'Femenino') . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($usuario['fecha_nacimiento'])) . '</td>';
            echo '<td>' . $edad . ' años</td>';
            echo '<td>' . htmlspecialchars($usuario['rol_nombre']) . '</td>';
            echo '<td style="' . ($usuario['activo'] ? 'color: green; font-weight: bold;' : 'color: red; font-weight: bold;') . '">' . 
                 ($usuario['activo'] ? 'Activo' : 'Inactivo') . '</td>';
            echo '<td>' . date('d/m/Y H:i:s', strtotime($usuario['created_at'])) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        
        // Estadísticas adicionales
        $activos = array_filter($usuarios, function($u) { return $u['activo']; });
        $inactivos = array_filter($usuarios, function($u) { return !$u['activo']; });
        
        echo '<br><br>';
        echo '<h2>Estadísticas Resumen</h2>';
        echo '<table>';
        echo '<tr><th>Estadística</th><th>Valor</th></tr>';
        echo '<tr><td>Total de Usuarios</td><td>' . count($usuarios) . '</td></tr>';
        echo '<tr><td>Usuarios Activos</td><td>' . count($activos) . '</td></tr>';
        echo '<tr><td>Usuarios Inactivos</td><td>' . count($inactivos) . '</td></tr>';
        echo '<tr><td>Porcentaje Activos</td><td>' . (count($usuarios) > 0 ? round((count($activos) / count($usuarios)) * 100, 2) : 0) . '%</td></tr>';
        echo '</table>';
        
        echo '</body>';
        echo '</html>';
        exit();
    }
    
    /**
     * Subir foto de perfil
     */
    private function subirFotoPerfil($archivo) {
        // Verificar errores de subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir la foto de perfil");
        }
        
        // Verificar tamaño (máximo 2MB para fotos)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($archivo['size'] > $maxSize) {
            throw new Exception("La foto es demasiado grande (máximo 2MB)");
        }
        
        // Verificar que sea una imagen
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipoArchivo = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            throw new Exception("Solo se permiten imágenes JPG, PNG o GIF");
        }
        
        // Verificar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception("Extensión de archivo no permitida");
        }
        
        // Generar nombre único
        $nombreArchivo = 'perfil_' . uniqid() . '_' . time() . '.' . $extension;
        $rutaCompleta = __DIR__ . '/../' . UPLOAD_PATH . 'perfiles/' . $nombreArchivo;
        
        // Crear directorio si no existe
        $directorio = dirname($rutaCompleta);
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return 'perfiles/' . $nombreArchivo;
        } else {
            throw new Exception("Error al guardar la foto de perfil");
        }
    }
    
    /**
     * Obtener agentes disponibles
     */
    public function obtenerAgentes() {
        requerirAutenticacion();
        
        $query = "SELECT u.id, u.primer_nombre, u.primer_apellido, u.email
                  FROM usuarios u 
                  INNER JOIN roles r ON u.rol_id = r.id 
                  WHERE u.activo = 1 AND r.nombre IN ('Admin', 'Agente')
                  ORDER BY u.primer_nombre, u.primer_apellido";
        
        $db = new Database();
        return $db->select($query);
    }
    
    /**
     * Procesar acciones via AJAX o formulario
     */
    public function procesarAccion() {
        $accion = $_GET['action'] ?? $_POST['action'] ?? '';
        $id = $_GET['id'] ?? $_POST['id'] ?? null;
        
        switch ($accion) {
            case 'crear':
                $this->crear();
                break;
                
            case 'actualizar':
                if ($id) {
                    $this->actualizar($id);
                } else {
                    $_SESSION['error'] = "ID de usuario requerido";
                    header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
                }
                break;
                
            case 'eliminar':
                if ($id) {
                    $this->eliminar($id);
                } else {
                    $_SESSION['error'] = "ID de usuario requerido";
                    header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
                }
                break;
                
            case 'toggle_estado':
                if ($id) {
                    $this->toggleEstado($id);
                } else {
                    $_SESSION['error'] = "ID de usuario requerido";
                    header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
                }
                break;
                
            case 'buscar':
                $this->buscar();
                break;
                
            case 'exportar_csv':
                $this->exportarCSV();
                break;
                
            case 'exportar_excel':
                $this->exportarExcel();
                break;
                
            default:
                $_SESSION['error'] = "Acción no válida";
                header('Location: ' . getBaseUrl() . '/views/admin/manage_users.php');
                break;
        }
    }
}

// Si se accede directamente al controlador
if ($_SERVER['SCRIPT_NAME'] === '/sistema-tickets/controllers/UserController.php') {
    $controller = new UserController();
    $controller->procesarAccion();
}
?>