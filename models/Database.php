<?php
/**
 * Clase para manejo de conexión a base de datos
 * Archivo: models/Database.php
 */

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    private $pdo;
    
    /**
     * Constructor - Inicializa la configuración de la base de datos
     */
    public function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }
    
    /**
     * Crear conexión PDO a la base de datos
     */
    public function connect() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
                $opciones = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $opciones);
            } catch (PDOException $e) {
                throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        
        return $this->pdo;
    }
    
    /**
     * Ejecutar una consulta SELECT
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error en consulta SELECT: " . $e->getMessage());
        }
    }
    
    /**
     * Ejecutar una consulta SELECT que retorna un solo registro
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error en consulta SELECT: " . $e->getMessage());
        }
    }
    
    /**
     * Ejecutar una consulta INSERT
     */
    public function insert($query, $params = []) {
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->execute($params);
            return $this->connect()->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error en consulta INSERT: " . $e->getMessage());
        }
    }
    
    /**
     * Ejecutar una consulta UPDATE
     */
    public function update($query, $params = []) {
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Error en consulta UPDATE: " . $e->getMessage());
        }
    }
    
    /**
     * Ejecutar una consulta DELETE
     */
    public function delete($query, $params = []) {
        try {
            $stmt = $this->connect()->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Error en consulta DELETE: " . $e->getMessage());
        }
    }
    
    /**
     * Contar registros
     */
    public function count($table, $where = '', $params = []) {
        $query = "SELECT COUNT(*) as total FROM {$table}";
        if (!empty($where)) {
            $query .= " WHERE {$where}";
        }
        
        $result = $this->selectOne($query, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->connect()->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->connect()->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->connect()->rollback();
    }
    
    /**
     * Cerrar conexión
     */
    public function close() {
        $this->pdo = null;
    }
    
    /**
     * Sanitizar datos de entrada
     */
    public function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar email
     */
    public function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Obtener registros con paginación
     */
    public function paginate($table, $page = 1, $perPage = 10, $where = '', $params = [], $orderBy = 'id DESC') {
        $offset = ($page - 1) * $perPage;
        
        // Consulta para obtener registros
        $query = "SELECT * FROM {$table}";
        if (!empty($where)) {
            $query .= " WHERE {$where}";
        }
        $query .= " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        
        $records = $this->select($query, $params);
        
        // Consulta para obtener total de registros
        $total = $this->count($table, $where, $params);
        
        return [
            'data' => $records,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
}
?>