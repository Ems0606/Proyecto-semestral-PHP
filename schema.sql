-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS sistema_tickets;
USE sistema_tickets;

-- Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    permisos JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50),
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50),
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    sexo ENUM('M', 'F') NOT NULL,
    identificacion VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    foto_perfil VARCHAR(255),
    rol_id INT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Tabla de tipos de tickets
CREATE TABLE tipos_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de tickets
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    tipo_ticket_id INT,
    usuario_id INT NOT NULL,
    agente_id INT NULL,
    estado ENUM('abierto', 'en_proceso', 'resuelto', 'cerrado') DEFAULT 'abierto',
    prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,
    archivo_adjunto VARCHAR(255),
    FOREIGN KEY (tipo_ticket_id) REFERENCES tipos_tickets(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (agente_id) REFERENCES usuarios(id)
);

-- Tabla de respuestas a tickets
CREATE TABLE respuestas_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    archivo_adjunto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de encuestas de satisfacción
CREATE TABLE encuestas_satisfaccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    calificacion INT NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
    comentario TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de estadísticas (para reportes)
CREATE TABLE estadisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    tickets_creados INT DEFAULT 0,
    tickets_resueltos INT DEFAULT 0,
    tickets_cerrados INT DEFAULT 0,
    tiempo_promedio_resolucion DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar roles básicos
INSERT INTO roles (nombre, descripcion, permisos) VALUES
('Admin', 'Administrador del sistema', '{"usuarios": ["create", "read", "update", "delete"], "tickets": ["create", "read", "update", "delete"], "reportes": ["read"]}'),
('Agente', 'Agente de soporte técnico', '{"tickets": ["read", "update"], "reportes": ["read"]}'),
('Estudiante', 'Estudiante del sistema', '{"tickets": ["create", "read"]}'),
('Colaborador', 'Colaborador del sistema', '{"tickets": ["create", "read"]}');

-- Insertar tipos de tickets
INSERT INTO tipos_tickets (nombre, descripcion) VALUES
('Soporte Técnico', 'Problemas técnicos con el sistema'),
('Académico', 'Consultas relacionadas con créditos oficiales'),
('Solicitud de Acceso', 'Solicitudes de acceso a internet u otros servicios'),
('Reclamo', 'Quejas y reclamos del usuario'),
('Información', 'Solicitudes de información general');

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, email, password, sexo, identificacion, fecha_nacimiento, rol_id) VALUES
('Admin', NULL, 'Sistema', NULL, 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'M', '00000000', '1990-01-01', 1);

-- Insertar datos de estadísticas iniciales
INSERT INTO estadisticas (fecha, tickets_creados, tickets_resueltos, tickets_cerrados, tiempo_promedio_resolucion) VALUES
(CURDATE(), 0, 0, 0, 0.00);