<?php
/**
 * Modelo para manejo de tickets - ACTUALIZADO CON SOPORTE PARA IP
 * Archivo: models/Ticket.php
 */

require_once __DIR__ . '/Database.php';

class Ticket {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Crear nuevo ticket
     */
    public function crear($datos) {
        // Validar datos requeridos
        $camposRequeridos = ['titulo', 'descripcion', 'tipo_ticket_id', 'usuario_id'];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }
        
        // Sanitizar datos
        $datos = $this->db->sanitize($datos);
        
        // Insertar ticket
        $query = "INSERT INTO tickets (titulo, descripcion, tipo_ticket_id, usuario_id, estado, prioridad, archivo_adjunto, ip_origen) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $datos['titulo'],
            $datos['descripcion'],
            $datos['tipo_ticket_id'],
            $datos['usuario_id'],
            $datos['estado'] ?? 'abierto',
            $datos['prioridad'] ?? 'media',
            $datos['archivo_adjunto'] ?? null,
            $datos['ip_origen'] ?? 'unknown'
        ];
        
        $ticketId = $this->db->insert($query, $params);
        
        // Actualizar estadísticas
        $this->actualizarEstadisticas();
        
        return $ticketId;
    }
    
    /**
     * Obtener ticket por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT t.*, 
                  tt.nombre as tipo_nombre,
                  u.primer_nombre as usuario_nombre, u.primer_apellido as usuario_apellido, u.email as usuario_email,
                  a.primer_nombre as agente_nombre, a.primer_apellido as agente_apellido, a.email as agente_email
                  FROM tickets t 
                  LEFT JOIN tipos_tickets tt ON t.tipo_ticket_id = tt.id
                  LEFT JOIN usuarios u ON t.usuario_id = u.id
                  LEFT JOIN usuarios a ON t.agente_id = a.id
                  WHERE t.id = ?";
        
        return $this->db->selectOne($query, [$id]);
    }
    
    /**
     * Obtener tickets con filtros y paginación
     */
    public function obtenerTodos($page = 1, $filtros = []) {
        $where = "1=1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $where .= " AND t.estado = ?";
            $params[] = $filtros['estado'];
        }
        
        if (!empty($filtros['prioridad'])) {
            $where .= " AND t.prioridad = ?";
            $params[] = $filtros['prioridad'];
        }
        
        if (!empty($filtros['tipo_ticket_id'])) {
            $where .= " AND t.tipo_ticket_id = ?";
            $params[] = $filtros['tipo_ticket_id'];
        }
        
        if (!empty($filtros['usuario_id'])) {
            $where .= " AND t.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        
        if (!empty($filtros['agente_id'])) {
            $where .= " AND t.agente_id = ?";
            $params[] = $filtros['agente_id'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $where .= " AND (t.titulo LIKE ? OR t.descripcion LIKE ?)";
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $params[] = $busqueda;
            $params[] = $busqueda;
        }
        
        // Query principal - INCLUYE IP_ORIGEN
        $query = "SELECT t.*, 
                  tt.nombre as tipo_nombre,
                  u.primer_nombre as usuario_nombre, u.primer_apellido as usuario_apellido,
                  a.primer_nombre as agente_nombre, a.primer_apellido as agente_apellido
                  FROM tickets t 
                  LEFT JOIN tipos_tickets tt ON t.tipo_ticket_id = tt.id
                  LEFT JOIN usuarios u ON t.usuario_id = u.id
                  LEFT JOIN usuarios a ON t.agente_id = a.id
                  WHERE {$where}
                  ORDER BY t.fecha_creacion DESC";
        
        // Paginación
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $query .= " LIMIT " . ITEMS_PER_PAGE . " OFFSET " . $offset;
        
        $tickets = $this->db->select($query, $params);
        
        // Obtener total
        $totalQuery = "SELECT COUNT(*) as total FROM tickets t WHERE {$where}";
        $total = $this->db->selectOne($totalQuery, $params)['total'];
        
        return [
            'data' => $tickets,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / ITEMS_PER_PAGE)
        ];
    }
    
    /**
     * Actualizar ticket
     */
    public function actualizar($id, $datos) {
        // Validar que el ticket existe
        $ticket = $this->obtenerPorId($id);
        if (!$ticket) {
            throw new Exception("Ticket no encontrado");
        }
        
        // Sanitizar datos
        $datos = $this->db->sanitize($datos);
        
        // Construir query dinámicamente
        $campos = [];
        $params = [];
        $camposPermitidos = ['titulo', 'descripcion', 'tipo_ticket_id', 'agente_id', 'estado', 'prioridad'];
        
        foreach ($datos as $campo => $valor) {
            if (in_array($campo, $camposPermitidos)) {
                $campos[] = "{$campo} = ?";
                $params[] = $valor;
            }
        }
        
        if (empty($campos)) {
            throw new Exception("No hay datos para actualizar");
        }
        
        // Si se está cerrando el ticket, agregar fecha de cierre
        if (isset($datos['estado']) && in_array($datos['estado'], ['resuelto', 'cerrado'])) {
            $campos[] = "fecha_cierre = NOW()";
        }
        
        $params[] = $id; // Para la condición WHERE
        
        $query = "UPDATE tickets SET " . implode(', ', $campos) . " WHERE id = ?";
        
        $resultado = $this->db->update($query, $params);
        
        // Actualizar estadísticas
        $this->actualizarEstadisticas();
        
        return $resultado;
    }
    
    /**
     * Asignar ticket a agente
     */
    public function asignarAgente($ticketId, $agenteId) {
        $query = "UPDATE tickets SET agente_id = ?, estado = 'en_proceso' WHERE id = ?";
        return $this->db->update($query, [$agenteId, $ticketId]);
    }
    
    /**
     * Obtener respuestas de un ticket
     */
    public function obtenerRespuestas($ticketId) {
        $query = "SELECT r.*, 
                  u.primer_nombre as usuario_nombre, u.primer_apellido as usuario_apellido, u.email as usuario_email,
                  roles.nombre as rol_usuario
                  FROM respuestas_tickets r 
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN roles ON u.rol_id = roles.id
                  WHERE r.ticket_id = ?
                  ORDER BY r.created_at ASC";
        
        return $this->db->select($query, [$ticketId]);
    }
    
    /**
     * Agregar respuesta a ticket
     */
    public function agregarRespuesta($ticketId, $usuarioId, $mensaje, $archivoAdjunto = null) {
        if (empty($mensaje)) {
            throw new Exception("El mensaje es requerido");
        }
        
        $query = "INSERT INTO respuestas_tickets (ticket_id, usuario_id, mensaje, archivo_adjunto) 
                  VALUES (?, ?, ?, ?)";
        
        $params = [$ticketId, $usuarioId, $this->db->sanitize($mensaje), $archivoAdjunto];
        
        $respuestaId = $this->db->insert($query, $params);
        
        // Actualizar fecha de actualización del ticket
        $this->db->update("UPDATE tickets SET fecha_actualizacion = NOW() WHERE id = ?", [$ticketId]);
        
        return $respuestaId;
    }
    
    /**
     * Obtener tipos de tickets
     */
    public function obtenerTipos() {
        return $this->db->select("SELECT * FROM tipos_tickets WHERE activo = 1 ORDER BY nombre");
    }
    
    /**
     * Obtener estadísticas de tickets
     */
    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total tickets
        $stats['total'] = $this->db->count('tickets');
        
        // Tickets por estado
        $query = "SELECT estado, COUNT(*) as total FROM tickets GROUP BY estado";
        $stats['por_estado'] = $this->db->select($query);
        
        // Tickets por prioridad
        $query = "SELECT prioridad, COUNT(*) as total FROM tickets GROUP BY prioridad";
        $stats['por_prioridad'] = $this->db->select($query);
        
        // Tickets por tipo
        $query = "SELECT tt.nombre as tipo, COUNT(t.id) as total 
                  FROM tipos_tickets tt 
                  LEFT JOIN tickets t ON tt.id = t.tipo_ticket_id 
                  GROUP BY tt.id, tt.nombre 
                  ORDER BY total DESC";
        $stats['por_tipo'] = $this->db->select($query);
        
        // Tickets creados hoy
        $stats['hoy'] = $this->db->count('tickets', 'DATE(fecha_creacion) = CURDATE()');
        
        // Tickets pendientes
        $stats['pendientes'] = $this->db->count('tickets', "estado IN ('abierto', 'en_proceso')");
        
        // Tiempo promedio de resolución (en horas)
        $query = "SELECT AVG(TIMESTAMPDIFF(HOUR, fecha_creacion, fecha_cierre)) as promedio 
                  FROM tickets 
                  WHERE fecha_cierre IS NOT NULL";
        $resultado = $this->db->selectOne($query);
        $stats['tiempo_promedio_resolucion'] = round($resultado['promedio'] ?? 0, 2);
        
        // Estadísticas por IP (TOP 10 IPs que más tickets crean)
        $query = "SELECT ip_origen, COUNT(*) as total_tickets 
                  FROM tickets 
                  WHERE ip_origen IS NOT NULL AND ip_origen != 'unknown'
                  GROUP BY ip_origen 
                  ORDER BY total_tickets DESC 
                  LIMIT 10";
        $stats['por_ip'] = $this->db->select($query);
        
        return $stats;
    }
    
    /**
     * Buscar tickets
     */
    public function buscar($termino, $limite = 10) {
        $query = "SELECT t.id, t.titulo, t.estado, t.prioridad, t.fecha_creacion, t.ip_origen,
                  u.primer_nombre as usuario_nombre, u.primer_apellido as usuario_apellido
                  FROM tickets t 
                  LEFT JOIN usuarios u ON t.usuario_id = u.id
                  WHERE t.titulo LIKE ? OR t.descripcion LIKE ?
                  ORDER BY t.fecha_creacion DESC 
                  LIMIT ?";
        
        $busqueda = '%' . $termino . '%';
        return $this->db->select($query, [$busqueda, $busqueda, $limite]);
    }
    
    /**
     * Obtener tickets del usuario
     */
    public function obtenerPorUsuario($usuarioId, $page = 1, $estado = null) {
        $where = "t.usuario_id = ?";
        $params = [$usuarioId];
        
        if ($estado) {
            $where .= " AND t.estado = ?";
            $params[] = $estado;
        }
        
        $query = "SELECT t.*, tt.nombre as tipo_nombre,
                  a.primer_nombre as agente_nombre, a.primer_apellido as agente_apellido
                  FROM tickets t 
                  LEFT JOIN tipos_tickets tt ON t.tipo_ticket_id = tt.id
                  LEFT JOIN usuarios a ON t.agente_id = a.id
                  WHERE {$where}
                  ORDER BY t.fecha_creacion DESC";
        
        // Paginación
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $query .= " LIMIT " . ITEMS_PER_PAGE . " OFFSET " . $offset;
        
        $tickets = $this->db->select($query, $params);
        
        // Obtener total
        $totalQuery = "SELECT COUNT(*) as total FROM tickets t WHERE {$where}";
        $total = $this->db->selectOne($totalQuery, $params)['total'];
        
        return [
            'data' => $tickets,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / ITEMS_PER_PAGE)
        ];
    }
    
    /**
     * Crear encuesta de satisfacción
     */
    public function crearEncuesta($ticketId, $usuarioId, $calificacion, $comentario = '') {
        // Validar calificación
        if ($calificacion < 1 || $calificacion > 5) {
            throw new Exception("La calificación debe estar entre 1 y 5");
        }
        
        // Verificar que no existe encuesta para este ticket
        $existeEncuesta = $this->db->count('encuestas_satisfaccion', 'ticket_id = ? AND usuario_id = ?', [$ticketId, $usuarioId]);
        if ($existeEncuesta > 0) {
            throw new Exception("Ya existe una encuesta para este ticket");
        }
        
        $query = "INSERT INTO encuestas_satisfaccion (ticket_id, usuario_id, calificacion, comentario) 
                  VALUES (?, ?, ?, ?)";
        
        return $this->db->insert($query, [$ticketId, $usuarioId, $calificacion, $this->db->sanitize($comentario)]);
    }
    
    /**
     * Obtener encuesta de ticket
     */
    public function obtenerEncuesta($ticketId) {
        $query = "SELECT es.*, u.primer_nombre as usuario_nombre, u.primer_apellido as usuario_apellido
                  FROM encuestas_satisfaccion es
                  LEFT JOIN usuarios u ON es.usuario_id = u.id
                  WHERE es.ticket_id = ?";
        
        return $this->db->selectOne($query, [$ticketId]);
    }
    
    /**
     * Actualizar estadísticas diarias
     */
    private function actualizarEstadisticas() {
        $fecha = date('Y-m-d');
        
        // Verificar si ya existen estadísticas para hoy
        $existe = $this->db->count('estadisticas', 'fecha = ?', [$fecha]);
        
        if ($existe == 0) {
            // Crear registro para hoy
            $this->db->insert("INSERT INTO estadisticas (fecha) VALUES (?)", [$fecha]);
        }
        
        // Actualizar estadísticas
        $query = "UPDATE estadisticas SET 
                  tickets_creados = (SELECT COUNT(*) FROM tickets WHERE DATE(fecha_creacion) = ?),
                  tickets_resueltos = (SELECT COUNT(*) FROM tickets WHERE DATE(fecha_cierre) = ? AND estado = 'resuelto'),
                  tickets_cerrados = (SELECT COUNT(*) FROM tickets WHERE DATE(fecha_cierre) = ? AND estado = 'cerrado'),
                  tiempo_promedio_resolucion = (
                      SELECT COALESCE(AVG(TIMESTAMPDIFF(HOUR, fecha_creacion, fecha_cierre)), 0) 
                      FROM tickets 
                      WHERE DATE(fecha_cierre) = ? AND fecha_cierre IS NOT NULL
                  )
                  WHERE fecha = ?";
        
        $this->db->update($query, [$fecha, $fecha, $fecha, $fecha, $fecha]);
    }
}
?>