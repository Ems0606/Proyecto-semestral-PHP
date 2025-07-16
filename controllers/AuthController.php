<?php
/**
 * Controlador de autenticación - CÓDIGO COMPLETO
 * Archivo: controllers/AuthController.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Procesar login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                // Validar datos
                $email = trim($_POST['email'] ?? '');
                $password = trim($_POST['password'] ?? '');
                
                if (empty($email) || empty($password)) {
                    throw new Exception("Email y contraseña son requeridos");
                }
                
                // Intentar autenticar
                $usuario = $this->userModel->autenticar($email, $password);
                
                if ($usuario) {
                    // Establecer sesión
                    establecerSesion($usuario);
                    
                    // Redirigir según el rol
                    $this->redirigirSegunRol($usuario['rol_nombre']);
                } else {
                    throw new Exception("Credenciales incorrectas");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: ' . APP_URL . '/views/auth/login.php');
                exit();
            }
        }
    }
    
    /**
     * Procesar registro
     */
    public function registro() {
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
                
                
                // Validar datos requeridos
                $datos = [
                    'primer_nombre' => trim($_POST['primer_nombre'] ?? ''),
                    'segundo_nombre' => trim($_POST['segundo_nombre'] ?? ''),
                    'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
                    'segundo_apellido' => trim($_POST['segundo_apellido'] ?? ''),
                    'email' => trim($_POST['email'] ?? ''),
                    'password' => $_POST['password'] ?? '',
                    'confirmar_password' => $_POST['confirmar_password'] ?? '',
                    'sexo' => $_POST['sexo'] ?? '',
                    'identificacion' => trim($_POST['identificacion'] ?? ''),
                    'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
                    'rol_id' => intval($_POST['rol_id'] ?? 3), // Por defecto: Estudiante
                ];

                // Solo agregar foto_perfil si existe
                if ($fotoPerfil !== null) {
                    $datos['foto_perfil'] = $fotoPerfil;
                }
                // Validaciones adicionales
                if ($datos['password'] !== $datos['confirmar_password']) {
                    throw new Exception("Las contraseñas no coinciden");
                }
                
                if (strlen($datos['password']) < 6) {
                    throw new Exception("La contraseña debe tener al menos 6 caracteres");
                }
                
                // Remover confirmación de password
                unset($datos['confirmar_password']);
                
                // Crear usuario
                $usuarioId = $this->userModel->crear($datos);
                
                if ($usuarioId) {
                    $_SESSION['exito'] = "Usuario registrado exitosamente. Puede iniciar sesión.";
                    header('Location: ' . APP_URL . '/views/auth/login.php');
                    exit();
                } else {
                    throw new Exception("Error al crear el usuario");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $_SESSION['form_data'] = $_POST; // Mantener datos del formulario
                header('Location: ' . APP_URL . '/views/auth/register.php');
                exit();
            }
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        iniciarSesion();
        
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Si se usan cookies de sesión, eliminarlas también
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
        
        // Redirigir a la página de login
        header('Location: ' . APP_URL . '/views/auth/login.php?logout=success');
        exit();
    }
    
    /**
     * Cambiar contraseña
     */
    public function cambiarPassword() {
        requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $usuario = obtenerUsuarioActual();
                $passwordAnterior = $_POST['password_anterior'] ?? '';
                $passwordNuevo = $_POST['password_nuevo'] ?? '';
                $confirmarPassword = $_POST['confirmar_password'] ?? '';
                
                // Validaciones
                if (empty($passwordAnterior) || empty($passwordNuevo) || empty($confirmarPassword)) {
                    throw new Exception("Todos los campos son requeridos");
                }
                
                if ($passwordNuevo !== $confirmarPassword) {
                    throw new Exception("Las contraseñas nuevas no coinciden");
                }
                
                if (strlen($passwordNuevo) < 6) {
                    throw new Exception("La contraseña nueva debe tener al menos 6 caracteres");
                }
                
                // Cambiar password
                $resultado = $this->userModel->cambiarPassword($usuario['id'], $passwordAnterior, $passwordNuevo);
                
                if ($resultado) {
                    $_SESSION['exito'] = "Contraseña cambiada exitosamente";
                } else {
                    throw new Exception("Error al cambiar la contraseña");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            header('Location: ' . APP_URL . '/views/auth/perfil.php');
            exit();
        }
    }
    
    /**
     * Redirigir según el rol del usuario
     */
    private function redirigirSegunRol($rol) {
        switch ($rol) {
            case 'Admin':
                header('Location: ' . APP_URL . '/views/admin/dashboard.php');
                break;
            case 'Agente':
                header('Location: ' . APP_URL . '/views/tickets/list.php');
                break;
            case 'Estudiante':
            case 'Colaborador':
            default:
                header('Location: ' . APP_URL . '/views/public/home.php');
                break;
        }
        exit();
    }
    
    /**
     * Verificar permisos para una acción específica
     */
    public function verificarPermisos($modulo, $accion) {
        if (!tienePermiso($modulo, $accion)) {
            $_SESSION['error'] = "No tiene permisos para realizar esta acción";
            header('Location: ' . APP_URL . '/views/public/home.php');
            exit();
        }
        return true;
    }
    
    /**
     * Procesar recuperación de contraseña (para implementación futura)
     */
    public function recuperarPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = trim($_POST['email'] ?? '');
                
                if (empty($email)) {
                    throw new Exception("El email es requerido");
                }
                
                // TODO: Implementar envío de email para recuperación
                $_SESSION['info'] = "Si el email existe en nuestro sistema, recibirá instrucciones para recuperar su contraseña";
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            header('Location: ' . APP_URL . '/views/auth/recuperar_password.php');
            exit();
        }
    }
    
    /**
     * Middleware para verificar autenticación en AJAX
     */
    public function verificarAjax() {
        if (!estaAutenticado() || sesionExpirada()) {
            http_response_code(401);
            echo json_encode(['error' => 'Sesión expirada']);
            exit();
        }
        renovarSesion();
        return true;
    }
    
    /**
     * Verificar si el usuario puede acceder a una página
     */
    public function validarAcceso($paginaRequerida) {
        // Páginas públicas que no requieren autenticación
        $paginasPublicas = [
            '/views/auth/login.php',
            '/views/auth/register.php',
            '/views/public/home.php',
            '/views/public/help.php',
            '/index.php'
        ];
        
        // Si es una página pública, permitir acceso
        foreach ($paginasPublicas as $pagina) {
            if (strpos($paginaRequerida, $pagina) !== false) {
                return true;
            }
        }
        
        // Para páginas privadas, verificar autenticación
        if (!estaAutenticado() || sesionExpirada()) {
            header('Location: ' . APP_URL . '/views/auth/login.php');
            exit();
        }
        
        return true;
    }
    
    /**
     * Subir foto de perfil
     */
    private function subirFotoPerfil($archivo) {
        // Verificar errores de subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir la foto de perfil: " . $this->getUploadErrorMessage($archivo['error']));
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
            throw new Exception("Solo se permiten imágenes JPG, PNG o GIF. Tipo detectado: " . $tipoArchivo);
        }
        
        // Verificar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception("Extensión de archivo no permitida: " . $extension);
        }
        
        // Generar nombre único
        $nombreArchivo = 'perfil_' . uniqid() . '_' . time() . '.' . $extension;
        
        // Crear directorio si no existe
        $directorioUploads = __DIR__ . '/../assets/uploads/';
        $directorioPerfiles = $directorioUploads . 'perfiles/';
        
        if (!is_dir($directorioUploads)) {
            if (!mkdir($directorioUploads, 0755, true)) {
                throw new Exception("No se pudo crear el directorio de uploads");
            }
        }
        
        if (!is_dir($directorioPerfiles)) {
            if (!mkdir($directorioPerfiles, 0755, true)) {
                throw new Exception("No se pudo crear el directorio de perfiles");
            }
        }
        
        $rutaCompleta = $directorioPerfiles . $nombreArchivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return 'perfiles/' . $nombreArchivo;
        } else {
            throw new Exception("Error al guardar la foto de perfil en: " . $rutaCompleta);
        }
    }
    
    /**
     * Obtener mensaje de error de subida de archivos
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "El archivo es demasiado grande (límite del servidor)";
            case UPLOAD_ERR_FORM_SIZE:
                return "El archivo excede el tamaño permitido por el formulario";
            case UPLOAD_ERR_PARTIAL:
                return "El archivo se subió parcialmente";
            case UPLOAD_ERR_NO_FILE:
                return "No se subió ningún archivo";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Falta el directorio temporal";
            case UPLOAD_ERR_CANT_WRITE:
                return "Error al escribir el archivo";
            case UPLOAD_ERR_EXTENSION:
                return "Subida detenida por extensión";
            default:
                return "Error desconocido";
        }
    }
    
    /**
     * Procesar acciones del controlador
     */
    public function procesarAccion() {
        $accion = $_GET['action'] ?? $_POST['action'] ?? '';
        
        switch ($accion) {
            case 'login':
                $this->login();
                break;
                
            case 'registro':
                $this->registro();
                break;
                
            case 'logout':
                $this->logout();
                break;
                
            case 'cambiar_password':
                $this->cambiarPassword();
                break;
                
            case 'recuperar_password':
                $this->recuperarPassword();
                break;
                
            default:
                // Si no hay acción específica, redirigir según el estado de autenticación
                if (estaAutenticado()) {
                    $usuario = obtenerUsuarioActual();
                    $this->redirigirSegunRol($usuario['rol']);
                } else {
                    header('Location: ' . APP_URL . '/views/auth/login.php');
                    exit();
                }
                break;
        }
    }
}

// Si se accede directamente al controlador
if (basename($_SERVER['SCRIPT_NAME']) === 'AuthController.php') {
    $controller = new AuthController();
    $controller->procesarAccion();
}
?>