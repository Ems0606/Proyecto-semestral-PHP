<?php
/**
 * Controlador de tickets - ACTUALIZADO CON CAPTURA DE IP
 * Archivo: controllers/TicketController.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Ticket.php';

class TicketController {
    private $ticketModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->ticketModel = new Ticket();
    }
    
    /**
     * Crear nuevo ticket
     */
    public function crear() {
        requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $usuario = obtenerUsuarioActual();
                
                // Procesar archivo adjunto si existe
                $archivoAdjunto = null;
                if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                    $archivoAdjunto = $this->subirArchivo($_FILES['archivo']);
                }
                
                // Capturar IP del usuario
                $ipOrigen = $this->obtenerIPUsuario();
                
                // Preparar datos del ticket
                $datos = [
                    'titulo' => trim($_POST['titulo'] ?? ''),
                    'descripcion' => trim($_POST['descripcion'] ?? ''),
                    'tipo_ticket_id' => intval($_POST['tipo_ticket_id'] ?? 0),
                    'prioridad' => $_POST['prioridad'] ?? 'media',
                    'usuario_id' => $usuario['id'],
                    'archivo_adjunto' => $archivoAdjunto,
                    'ip_origen' => $ipOrigen
                ];
                
                // Crear ticket
                $ticketId = $this->ticketModel->crear($datos);
                
                if ($ticketId) {
                    $_SESSION['exito'] = "Ticket creado exitosamente. ID: #" . $ticketId;
                    header('Location: ' . getBaseUrl() . '/views/tickets/view.php?id=' . $ticketId);
                } else {
                    throw new Exception("Error al crear el ticket");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                $_SESSION['form_data'] = $_POST;
                header('Location: ' . getBaseUrl() . '/views/tickets/create.php');
                exit();
            }
        }
    }
    
    /**
     * Obtener IP del usuario de forma segura
     * Considera proxies, load balancers y CDNs
     */
    private function obtenerIPUsuario() {
        // Lista de headers que pueden contener la IP real
        $ipHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                // Validar que sea una IP válida
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
                
                // Si no es pública, usar la IP privada (para desarrollo local)
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        // Fallback a REMOTE_ADDR
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Listar tickets
     */
    public function listar() {
        requerirAutenticacion();
        
        $usuario = obtenerUsuarioActual();
        $page = intval($_GET['page'] ?? 1);
        
        // Preparar filtros
        $filtros = [];
        
        if (isset($_GET['estado']) && !empty($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }
        
        if (isset($_GET['prioridad']) && !empty($_GET['prioridad'])) {
            $filtros['prioridad'] = $_GET['prioridad'];
        }
        
        if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
            $filtros['tipo_ticket_id'] = $_GET['tipo'];
        }
        
        if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
            $filtros['busqueda'] = $_GET['busqueda'];
        }
        
        // Si no es admin o agente, solo mostrar sus propios tickets
        if (!esAdministrador() && !esAgente()) {
            $filtros['usuario_id'] = $usuario['id'];
        }
        
        // Si es agente, puede ver todos o filtrar por asignados
        if (esAgente() && !esAdministrador() && isset($_GET['asignados']) && $_GET['asignados'] === '1') {
            $filtros['agente_id'] = $usuario['id'];
        }
        
        return $this->ticketModel->obtenerTodos($page, $filtros);
    }
    
    /**
     * Ver ticket individual
     */
    public function ver($id) {
        requerirAutenticacion();
        
        $ticket = $this->ticketModel->obtenerPorId($id);
        
        if (!$ticket) {
            $_SESSION['error'] = "Ticket no encontrado";
            header('Location: ' . getBaseUrl() . '/views/tickets/list.php');
            exit();
        }
        
        $usuario = obtenerUsuarioActual();
        
        // Verificar permisos: el usuario puede ver su propio ticket o si es agente/admin
        if ($ticket['usuario_id'] != $usuario['id'] && !esAgente()) {
            $_SESSION['error'] = "No tiene permisos para ver este ticket";
            header('Location: ' . getBaseUrl() . '/views/tickets/list.php');
            exit();
        }
        
        // Obtener respuestas del ticket
        $respuestas = $this->ticketModel->obtenerRespuestas($id);
        
        // Obtener encuesta si existe
        $encuesta = $this->ticketModel->obtenerEncuesta($id);
        
        return [
            'ticket' => $ticket,
            'respuestas' => $respuestas,
            'encuesta' => $encuesta
        ];
    }
    
    /**
     * Actualizar ticket
     */
    public function actualizar($id) {
        requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $ticket = $this->ticketModel->obtenerPorId($id);
                if (!$ticket) {
                    throw new Exception("Ticket no encontrado");
                }
                
                $usuario = obtenerUsuarioActual();
                
                // Verificar permisos
                if (!esAgente() && $ticket['usuario_id'] != $usuario['id']) {
                    throw new Exception("No tiene permisos para actualizar este ticket");
                }
                
                // Preparar datos para actualización
                $datos = [];
                
                // Solo agentes pueden cambiar estado y asignar
                if (esAgente()) {
                    if (isset($_POST['estado'])) {
                        $datos['estado'] = $_POST['estado'];
                    }
                    if (isset($_POST['agente_id'])) {
                        $datos['agente_id'] = $_POST['agente_id'];
                    }
                    if (isset($_POST['prioridad'])) {
                        $datos['prioridad'] = $_POST['prioridad'];
                    }
                }
                
                // El usuario puede actualizar título y descripción si el ticket está abierto
                if ($ticket['estado'] === 'abierto' && $ticket['usuario_id'] == $usuario['id']) {
                    if (isset($_POST['titulo'])) {
                        $datos['titulo'] = trim($_POST['titulo']);
                    }
                    if (isset($_POST['descripcion'])) {
                        $datos['descripcion'] = trim($_POST['descripcion']);
                    }
                }
                
                if (!empty($datos)) {
                    $resultado = $this->ticketModel->actualizar($id, $datos);
                    
                    if ($resultado) {
                        $_SESSION['exito'] = "Ticket actualizado exitosamente";
                    } else {
                        throw new Exception("No se realizaron cambios");
                    }
                } else {
                    throw new Exception("No hay datos para actualizar");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            header('Location: ' . getBaseUrl() . '/views/tickets/view.php?id=' . $id);
            exit();
        }
    }
    
    /**
     * Agregar respuesta a ticket
     */
    public function responder($id) {
        requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $ticket = $this->ticketModel->obtenerPorId($id);
                if (!$ticket) {
                    throw new Exception("Ticket no encontrado");
                }
                
                $usuario = obtenerUsuarioActual();
                
                // Verificar permisos
                if ($ticket['usuario_id'] != $usuario['id'] && !esAgente()) {
                    throw new Exception("No tiene permisos para responder este ticket");
                }
                
                $mensaje = trim($_POST['mensaje'] ?? '');
                if (empty($mensaje)) {
                    throw new Exception("El mensaje es requerido");
                }
                
                // Procesar archivo adjunto si existe
                $archivoAdjunto = null;
                if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                    $archivoAdjunto = $this->subirArchivo($_FILES['archivo']);
                }
                
                // Agregar respuesta
                $respuestaId = $this->ticketModel->agregarRespuesta($id, $usuario['id'], $mensaje, $archivoAdjunto);
                
                if ($respuestaId) {
                    $_SESSION['exito'] = "Respuesta agregada exitosamente";
                } else {
                    throw new Exception("Error al agregar la respuesta");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            header('Location: ' . getBaseUrl() . '/views/tickets/view.php?id=' . $id);
            exit();
        }
    }
    
    /**
     * Asignar ticket a agente
     */
    public function asignar($id) {
        requerirPermiso('tickets', 'update');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $agenteId = intval($_POST['agente_id'] ?? 0);
                
                if ($agenteId <= 0) {
                    throw new Exception("Debe seleccionar un agente válido");
                }
                
                $resultado = $this->ticketModel->asignarAgente($id, $agenteId);
                
                if ($resultado) {
                    $_SESSION['exito'] = "Ticket asignado exitosamente";
                } else {
                    throw new Exception("Error al asignar el ticket");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            header('Location: ' . getBaseUrl() . '/views/tickets/view.php?id=' . $id);
            exit();
        }
    }
    
    /**
     * Crear encuesta de satisfacción
     */
    public function encuesta($id) {
        requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verificar token CSRF
                if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
                    throw new Exception("Token de seguridad inválido");
                }
                
                $ticket = $this->ticketModel->obtenerPorId($id);
                if (!$ticket) {
                    throw new Exception("Ticket no encontrado");
                }
                
                $usuario = obtenerUsuarioActual();
                
                // Solo el usuario que creó el ticket puede crear la encuesta
                if ($ticket['usuario_id'] != $usuario['id']) {
                    throw new Exception("No tiene permisos para evaluar este ticket");
                }
                
                // El ticket debe estar resuelto o cerrado
                if (!in_array($ticket['estado'], ['resuelto', 'cerrado'])) {
                    throw new Exception("Solo puede evaluar tickets resueltos o cerrados");
                }
                
                $calificacion = intval($_POST['calificacion'] ?? 0);
                $comentario = trim($_POST['comentario'] ?? '');
                
                if ($calificacion < 1 || $calificacion > 5) {
                    throw new Exception("La calificación debe estar entre 1 y 5");
                }
                
                $encuestaId = $this->ticketModel->crearEncuesta($id, $usuario['id'], $calificacion, $comentario);
                
                if ($encuestaId) {
                    $_SESSION['exito'] = "Gracias por su evaluación";
                } else {
                    throw new Exception("Error al guardar la evaluación");
                }
                
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            
            header('Location: ' . getBaseUrl() . '/views/tickets/view.php?id=' . $id);
            exit();
        }
    }
    
    /**
     * Obtener estadísticas de tickets
     */
    public function estadisticas() {
        requerirPermiso('reportes', 'read');
        return $this->ticketModel->obtenerEstadisticas();
    }
    
    /**
     * Buscar tickets (AJAX)
     */
    public function buscar() {
        requerirAutenticacion();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
            $termino = trim($_GET['q']);
            $limite = intval($_GET['limite'] ?? 10);
            
            $resultados = $this->ticketModel->buscar($termino, $limite);
            
            header('Content-Type: application/json');
            echo json_encode($resultados);
            exit();
        }
    }
    
    /**
     * Subir archivo adjunto
     */
    private function subirArchivo($archivo) {
        // Verificar errores de subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir el archivo");
        }
        
        // Verificar tamaño
        if ($archivo['size'] > MAX_FILE_SIZE) {
            throw new Exception("El archivo es demasiado grande (máximo 5MB)");
        }
        
        // Verificar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            throw new Exception("Tipo de archivo no permitido");
        }
        
        // Generar nombre único
        $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
        $rutaCompleta = __DIR__ . '/../' . UPLOAD_PATH . $nombreArchivo;
        
        // Crear directorio si no existe
        $directorio = dirname($rutaCompleta);
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $nombreArchivo;
        } else {
            throw new Exception("Error al guardar el archivo");
        }
    }
    
    /**
     * Obtener tipos de tickets
     */
    public function obtenerTipos() {
        return $this->ticketModel->obtenerTipos();
    }
}
?>