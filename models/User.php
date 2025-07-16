<?php
/**
 * Modelo para manejo de usuarios
 * Archivo: models/User.php
 */

require_once __DIR__ . '/Database.php';

class User {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Crear nuevo usuario
     */
    public function crear($datos) {
        // Validar datos requeridos
        $camposRequeridos = ['primer_nombre', 'primer_apellido', 'email', 'password', 'sexo', 'identificacion', 'fecha_nacimiento'];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }
        
        // Validar email
        if (!$this->db->validarEmail($datos['email'])) {
            throw new Exception("El email no es válido");
        }
        
        // Verificar si el email ya existe
        if ($this->existeEmail($datos['email'])) {
            throw new Exception("El email ya está registrado");
        }
        
        // Verificar si la identificación ya existe
        if ($this->existeIdentificacion($datos['identificacion'])) {
            throw new Exception("La identificación ya está registrada");
        }
        
        // Hashear password
        $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        
        // Sanitizar datos
        $datos = $this->db->sanitize($datos);
        
        // Insertar usuario
        $query = "INSERT INTO usuarios (primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, 
                  email, password, sexo, identificacion, fecha_nacimiento, foto_perfil, rol_id, activo) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $params = [
            $datos['primer_nombre'],
            $datos['segundo_nombre'] ?? null,
            $datos['primer_apellido'],
            $datos['segundo_apellido'] ?? null,
            $datos['email'],
            $datos['password'],
            $datos['sexo'],
            $datos['identificacion'],
            $datos['fecha_nacimiento'],
            $datos['foto_perfil'] ?? null,
            $datos['rol_id'] ?? 3 // Por defecto: Estudiante
        ];
        
        return $this->db->insert($query, $params);
    }
    
    /**
     * Autenticar usuario
     */
    public function autenticar($email, $password) {
        $query = "SELECT u.*, r.nombre as rol_nombre, r.permisos 
                  FROM usuarios u 
                  LEFT JOIN roles r ON u.rol_id = r.id 
                  WHERE u.email = ? AND u.activo = 1";
        
        $usuario = $this->db->selectOne($query, [$email]);
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            unset($usuario['password']); // Remover password del resultado
            return $usuario;
        }
        
        return false;
    }
    
    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id) {
        $query = "SELECT u.*, r.nombre as rol_nombre, r.permisos 
                  FROM usuarios u 
                  LEFT JOIN roles r ON u.rol_id = r.id 
                  WHERE u.id = ?";
        
        $usuario = $this->db->selectOne($query, [$id]);
        if ($usuario) {
            unset($usuario['password']);
        }
        
        return $usuario;
    }
    
    /**
     * Obtener todos los usuarios con paginación
     */
    public function obtenerTodos($page = 1, $filtros = []) {
        $where = "u.activo = 1";
        $params = [];
        
        // Aplicar filtros
        if (!empty($filtros['nombre'])) {
            $where .= " AND (u.primer_nombre LIKE ? OR u.primer_apellido LIKE ?)";
            $params[] = '%' . $filtros['nombre'] . '%';
            $params[] = '%' . $filtros['nombre'] . '%';
        }
        
        if (!empty($filtros['rol_id'])) {
            $where .= " AND u.rol_id = ?";
            $params[] = $filtros['rol_id'];
        }
        
        // Consulta base
        $query = "SELECT u.id, u.primer_nombre, u.segundo_nombre, u.primer_apellido, u.segundo_apellido, 
                  u.email, u.identificacion, u.fecha_nacimiento, u.activo, u.created_at,
                  r.nombre as rol_nombre
                  FROM usuarios u 
                  LEFT JOIN roles r ON u.rol_id = r.id";
        
        if (!empty($where)) {
            $query .= " WHERE " . $where;
        }
        
        $query .= " ORDER BY u.created_at DESC";
        
        // Calcular paginación
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $query .= " LIMIT " . ITEMS_PER_PAGE . " OFFSET " . $offset;
        
        $usuarios = $this->db->select($query, $params);
        
        // Obtener total
        $totalQuery = "SELECT COUNT(*) as total FROM usuarios u WHERE " . $where;
        $total = $this->db->selectOne($totalQuery, $params)['total'];
        
        return [
            'data' => $usuarios,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / ITEMS_PER_PAGE)
        ];
    }
    
    /**
     * Actualizar usuario
     */
    public function actualizar($id, $datos) {
        // Validar que el usuario existe
        $usuario = $this->obtenerPorId($id);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }
        
        // Validar email si se está cambiando
        if (isset($datos['email']) && $datos['email'] !== $usuario['email']) {
            if (!$this->db->validarEmail($datos['email'])) {
                throw new Exception("El email no es válido");
            }
            if ($this->existeEmail($datos['email'])) {
                throw new Exception("El email ya está registrado");
            }
        }
        
        // Validar identificación si se está cambiando
        if (isset($datos['identificacion']) && $datos['identificacion'] !== $usuario['identificacion']) {
            if ($this->existeIdentificacion($datos['identificacion'])) {
                throw new Exception("La identificación ya está registrada");
            }
        }
        
        // Hashear password si se proporciona
        if (!empty($datos['password'])) {
            $datos['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
        } else {
            unset($datos['password']); // No actualizar password si está vacío
        }
        
        // Sanitizar datos
        $datos = $this->db->sanitize($datos);
        
        // Construir query dinámicamente
        $campos = [];
        $params = [];
        $camposPermitidos = ['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 
                           'email', 'password', 'sexo', 'identificacion', 'fecha_nacimiento', 
                           'foto_perfil', 'rol_id', 'activo'];
        
        foreach ($datos as $campo => $valor) {
            if (in_array($campo, $camposPermitidos)) {
                $campos[] = "{$campo} = ?";
                $params[] = $valor;
            }
        }
        
        if (empty($campos)) {
            throw new Exception("No hay datos para actualizar");
        }
        
        $params[] = $id; // Para la condición WHERE
        
        $query = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
        
        return $this->db->update($query, $params);
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function eliminar($id) {
        return $this->db->update("UPDATE usuarios SET activo = 0 WHERE id = ?", [$id]);
    }
    
    /**
     * Verificar si existe un email
     */
    public function existeEmail($email) {
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE email = ? AND activo = 1";
        $result = $this->db->selectOne($query, [$email]);
        return $result['total'] > 0;
    }
    
    /**
     * Verificar si existe una identificación
     */
    public function existeIdentificacion($identificacion) {
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE identificacion = ? AND activo = 1";
        $result = $this->db->selectOne($query, [$identificacion]);
        return $result['total'] > 0;
    }
    
    /**
     * Obtener roles disponibles
     */
    public function obtenerRoles() {
        return $this->db->select("SELECT * FROM roles ORDER BY nombre");
    }
    
    /**
     * Cambiar password
     */
    public function cambiarPassword($id, $passwordAnterior, $passwordNuevo) {
        // Obtener usuario actual
        $usuario = $this->db->selectOne("SELECT password FROM usuarios WHERE id = ?", [$id]);
        
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }
        
        // Verificar password anterior
        if (!password_verify($passwordAnterior, $usuario['password'])) {
            throw new Exception("La contraseña anterior es incorrecta");
        }
        
        // Actualizar password
        $nuevoHash = password_hash($passwordNuevo, PASSWORD_DEFAULT);
        return $this->db->update("UPDATE usuarios SET password = ? WHERE id = ?", [$nuevoHash, $id]);
    }
    
    /**
     * Obtener estadísticas de usuarios
     */
    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total usuarios activos
        $stats['total_activos'] = $this->db->count('usuarios', 'activo = 1');
        
        // Usuarios por rol
        $query = "SELECT r.nombre as rol, COUNT(u.id) as total 
                  FROM roles r 
                  LEFT JOIN usuarios u ON r.id = u.rol_id AND u.activo = 1 
                  GROUP BY r.id, r.nombre 
                  ORDER BY total DESC";
        $stats['por_rol'] = $this->db->select($query);
        
        // Usuarios registrados este mes
        $query = "SELECT COUNT(*) as total FROM usuarios 
                  WHERE activo = 1 AND MONTH(created_at) = MONTH(NOW()) 
                  AND YEAR(created_at) = YEAR(NOW())";
        $stats['este_mes'] = $this->db->selectOne($query)['total'];
        
        return $stats;
    }
    
    /**
     * Buscar usuarios
     */
    public function buscar($termino, $limite = 10) {
        $query = "SELECT u.id, u.primer_nombre, u.primer_apellido, u.email, r.nombre as rol_nombre
                  FROM usuarios u 
                  LEFT JOIN roles r ON u.rol_id = r.id 
                  WHERE u.activo = 1 
                  AND (u.primer_nombre LIKE ? OR u.primer_apellido LIKE ? OR u.email LIKE ? OR u.identificacion LIKE ?)
                  ORDER BY u.primer_nombre, u.primer_apellido 
                  LIMIT ?";
        
        $busqueda = '%' . $termino . '%';
        return $this->db->select($query, [$busqueda, $busqueda, $busqueda, $busqueda, $limite]);
    }
}
?>