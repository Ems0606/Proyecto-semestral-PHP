# 📧 Sistema de Tickets - Proyecto Universitario

## 📋 Descripción del Proyecto
Sistema web completo para gestión de tickets de soporte técnico desarrollado en PHP con arquitectura MVC, base de datos MySQL y interfaz responsive.

## 🎯 Objetivos
- Gestionar solicitudes de soporte de manera eficiente
- Proporcionar seguimiento en tiempo real de tickets
- Administrar usuarios con diferentes roles y permisos
- Generar reportes y estadísticas del sistema

## 🏗️ Arquitectura del Sistema

### Patrón MVC (Model-View-Controller)
```
sistema-tickets/
├── models/          # Modelos (lógica de datos)
│   ├── Database.php
│   ├── User.php
│   └── Ticket.php
├── views/           # Vistas (interfaz usuario)
│   ├── auth/
│   ├── admin/
│   ├── tickets/
│   └── layouts/
├── controllers/     # Controladores (lógica negocio)
│   ├── AuthController.php
│   ├── UserController.php
│   └── TicketController.php
└── config/          # Configuración
    ├── database.php
    └── session.php
```

## 🛠️ Tecnologías Utilizadas
- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript ES6
- **Servidor**: Apache (XAMPP)
- **Arquitectura**: MVC (Model-View-Controller)
- **Seguridad**: Tokens CSRF, prepared statements, sanitización de datos

## 👥 Roles del Sistema
1. **Administrador**: Gestión completa de usuarios, tickets y reportes
2. **Agente**: Gestión y respuesta de tickets asignados
3. **Estudiante/Colaborador**: Creación y seguimiento de tickets propios

## 🚀 Características Principales

### Gestión de Usuarios
- Registro de usuarios con validación completa
- Autenticación segura con hash de contraseñas
- Gestión de permisos por roles
- Perfiles de usuario editables

### Sistema de Tickets
- Creación de tickets con tipos y prioridades
- Archivo adjuntos (imágenes, documentos)
- Sistema de respuestas/conversación
- Seguimiento de estado (abierto, en proceso, resuelto, cerrado)
- Asignación de agentes
- Captura de IP de origen para auditoría

### Panel Administrativo
- Dashboard con estadísticas en tiempo real
- Gestión completa de usuarios
- Reportes avanzados con filtros de fecha
- Exportación de datos a CSV

### Seguridad Implementada
- Tokens CSRF en todos los formularios
- Prepared statements contra SQL injection
- Sanitización de datos de entrada
- Validación de archivos subidos
- Control de sesiones con expiración

## 📊 Diagramas UML

### Diagrama de Clases
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│    Database     │    │      User       │    │     Ticket      │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ +connect()      │◄───┤ -db: Database   │    │ -db: Database   │
│ +select()       │    │ +crear()        │    │ +crear()        │
│ +insert()       │    │ +autenticar()   │    │ +obtenerPorId() │
│ +update()       │    │ +obtenerPorId() │    │ +actualizar()   │
│ +delete()       │    │ +actualizar()   │    │ +responder()    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Diagrama de Casos de Uso
```
    ┌─────────────┐
    │   Usuario   │
    └──────┬──────┘
           │
    ┌──────▼──────┐     ┌─────────────────┐
    │ Registrarse │     │ Iniciar Sesión  │
    └─────────────┘     └─────────────────┘
           │                     │
    ┌──────▼──────┐     ┌──────▼──────┐
    │Crear Ticket │     │Ver Tickets  │
    └─────────────┘     └─────────────┘
```

## 🗄️ Modelo de Base de Datos

### Tablas Principales
- **usuarios**: Información de usuarios del sistema
- **roles**: Definición de roles y permisos
- **tickets**: Tickets del sistema con IP de origen
- **tipos_tickets**: Categorías de tickets
- **respuestas_tickets**: Conversaciones de tickets
- **encuestas_satisfaccion**: Evaluaciones del servicio

### Relaciones
- Usuario 1:N Tickets (un usuario puede tener múltiples tickets)
- Ticket 1:N Respuestas (un ticket puede tener múltiples respuestas)
- Rol 1:N Usuarios (un rol puede tener múltiples usuarios)


### Flujo Principal del Usuario
```
[Login] → [Dashboard] → [Crear Ticket] → [Ver Ticket] → [Responder]
    ↓           ↓              ↓             ↓
[Registro] [Mis Tickets] [Lista Tickets] [Encuesta]
```

### Flujo del Agente
```
[Login] → [Dashboard] → [Todos los Tickets] → [Asignar] → [Responder] → [Resolver]
```

### Flujo del Administrador
```
[Login] → [Dashboard Admin] → [Gestión Usuarios] → [Reportes] → [Configuración]
```

## 🔧 Instalación y Configuración

### Requisitos Previos
- XAMPP (Apache + MySQL + PHP)
- Navegador web moderno
- Editor de texto/IDE

### Pasos de Instalación
1. **Clonar el repositorio**
```bash
git clone [URL_DEL_REPOSITORIO]
cd sistema-tickets
```

2. **Configurar base de datos**
- Abrir phpMyAdmin
- Crear base de datos 'sistema_tickets'
- Importar schema.sql

3. **Configurar conexión**
- Editar config/database.php
- Verificar credenciales de DB

4. **Crear directorios**
```bash
php create_directories.php
```

5. **Acceder al sistema**
- http://localhost/sistema-tickets
- Usuario admin: admin@sistema.com / password
