# IAEDU API Documentation

## Descripción General

La API de IAEDU proporciona endpoints RESTful para gestionar todos los aspectos del sistema educativo, incluyendo estudiantes, profesores, calificaciones, asistencia, alertas y más.

## Características

- **RESTful API**: Todos los endpoints siguen las convenciones REST
- **Documentación Swagger**: Interfaz interactiva para probar la API
- **Autenticación**: Soporte para autenticación con Sanctum
- **Validación**: Validación robusta de datos de entrada
- **Paginación**: Soporte para paginación en todos los endpoints de listado
- **Filtros**: Múltiples opciones de filtrado y búsqueda
- **Relaciones**: Carga de relaciones relacionadas con el parámetro `with`

## Instalación y Configuración

### 1. Instalar Dependencias

```bash
composer require darkaonline/l5-swagger
```

### 2. Publicar Configuración

```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### 3. Generar Documentación

```bash
php artisan swagger:generate
```

### 4. Acceder a la Documentación

Visita: `http://tu-dominio.com/api/documentation`

## Estructura de Respuestas

Todas las respuestas de la API siguen un formato consistente:

### Respuesta Exitosa
```json
{
    "success": true,
    "message": "Operación completada exitosamente",
    "data": {
        // Datos de la respuesta
    }
}
```

### Respuesta de Error
```json
{
    "success": false,
    "message": "Mensaje de error",
    "errors": {
        // Detalles de errores de validación
    }
}
```

## Endpoints Principales

### 1. Usuarios (Users)
- `GET /api/users` - Listar usuarios
- `GET /api/users/{id}` - Obtener usuario específico
- `POST /api/users` - Crear usuario
- `PUT /api/users/{id}` - Actualizar usuario
- `DELETE /api/users/{id}` - Eliminar usuario

### 2. Estudiantes (Students)
- `GET /api/students` - Listar estudiantes
- `GET /api/students/{id}` - Obtener estudiante específico
- `POST /api/students` - Crear estudiante
- `PUT /api/students/{id}` - Actualizar estudiante
- `DELETE /api/students/{id}` - Eliminar estudiante
- `GET /api/students/{id}/grades` - Obtener calificaciones del estudiante
- `GET /api/students/{id}/attendance` - Obtener asistencia del estudiante

### 3. Calificaciones (Grades)
- `GET /api/grades` - Listar calificaciones
- `GET /api/grades/{id}` - Obtener calificación específica
- `POST /api/grades` - Crear calificación
- `PUT /api/grades/{id}` - Actualizar calificación
- `DELETE /api/grades/{id}` - Eliminar calificación
- `GET /api/grades/statistics` - Obtener estadísticas de calificaciones

### 4. Profesores (Teachers)
- `GET /api/teachers` - Listar profesores
- `GET /api/teachers/{id}` - Obtener profesor específico
- `POST /api/teachers` - Crear profesor
- `PUT /api/teachers/{id}` - Actualizar profesor
- `DELETE /api/teachers/{id}` - Eliminar profesor

### 5. Grupos (Groups)
- `GET /api/groups` - Listar grupos
- `GET /api/groups/{id}` - Obtener grupo específico
- `POST /api/groups` - Crear grupo
- `PUT /api/groups/{id}` - Actualizar grupo
- `DELETE /api/groups/{id}` - Eliminar grupo
- `GET /api/groups/{id}/students` - Obtener estudiantes del grupo

### 6. Materias (Subjects)
- `GET /api/subjects` - Listar materias
- `GET /api/subjects/{id}` - Obtener materia específica
- `POST /api/subjects` - Crear materia
- `PUT /api/subjects/{id}` - Actualizar materia
- `DELETE /api/subjects/{id}` - Eliminar materia

### 7. Asistencia (Attendance)
- `GET /api/attendance` - Listar asistencias
- `GET /api/attendance/{id}` - Obtener asistencia específica
- `POST /api/attendance` - Crear asistencia
- `PUT /api/attendance/{id}` - Actualizar asistencia
- `DELETE /api/attendance/{id}` - Eliminar asistencia
- `GET /api/attendance/statistics` - Obtener estadísticas de asistencia

### 8. Alertas (Alerts)
- `GET /api/alerts` - Listar alertas
- `GET /api/alerts/{id}` - Obtener alerta específica
- `POST /api/alerts` - Crear alerta
- `PUT /api/alerts/{id}` - Actualizar alerta
- `DELETE /api/alerts/{id}` - Eliminar alerta
- `GET /api/alerts/statistics` - Obtener estadísticas de alertas

### 9. Horarios (Schedules)
- `GET /api/schedules` - Listar horarios
- `GET /api/schedules/{id}` - Obtener horario específico
- `POST /api/schedules` - Crear horario
- `PUT /api/schedules/{id}` - Actualizar horario
- `DELETE /api/schedules/{id}` - Eliminar horario

### 10. Eventos (Events)
- `GET /api/events` - Listar eventos
- `GET /api/events/{id}` - Obtener evento específico
- `POST /api/events` - Crear evento
- `PUT /api/events/{id}` - Actualizar evento
- `DELETE /api/events/{id}` - Eliminar evento

### 11. Períodos Académicos (Academic Periods)
- `GET /api/academic-periods` - Listar períodos académicos
- `GET /api/academic-periods/{id}` - Obtener período específico
- `POST /api/academic-periods` - Crear período académico
- `PUT /api/academic-periods/{id}` - Actualizar período académico
- `DELETE /api/academic-periods/{id}` - Eliminar período académico

### 12. Rúbricas (Rubrics)
- `GET /api/rubrics` - Listar rúbricas
- `GET /api/rubrics/{id}` - Obtener rúbrica específica
- `POST /api/rubrics` - Crear rúbrica
- `PUT /api/rubrics/{id}` - Actualizar rúbrica
- `DELETE /api/rubrics/{id}` - Eliminar rúbrica

### 13. Riesgos de Estudiantes (Student Risks)
- `GET /api/student-risks` - Listar riesgos de estudiantes
- `GET /api/student-risks/{id}` - Obtener riesgo específico
- `POST /api/student-risks` - Crear riesgo
- `PUT /api/student-risks/{id}` - Actualizar riesgo
- `DELETE /api/student-risks/{id}` - Eliminar riesgo

### 14. Registros de Cambios (Change Logs)
- `GET /api/change-logs` - Listar registros de cambios
- `GET /api/change-logs/{id}` - Obtener registro específico
- `POST /api/change-logs` - Crear registro de cambios
- `PUT /api/change-logs/{id}` - Actualizar registro de cambios
- `DELETE /api/change-logs/{id}` - Eliminar registro de cambios

### 15. Suscripciones (Subscriptions)
- `GET /api/subscriptions` - Listar suscripciones
- `GET /api/subscriptions/{id}` - Obtener suscripción específica
- `POST /api/subscriptions` - Crear suscripción
- `PUT /api/subscriptions/{id}` - Actualizar suscripción
- `DELETE /api/subscriptions/{id}` - Eliminar suscripción

### 16. Facturas (Invoices)
- `GET /api/invoices` - Listar facturas
- `GET /api/invoices/{id}` - Obtener factura específica
- `POST /api/invoices` - Crear factura
- `PUT /api/invoices/{id}` - Actualizar factura
- `DELETE /api/invoices/{id}` - Eliminar factura

### 17. Pagos (Payments)
- `GET /api/payments` - Listar pagos
- `GET /api/payments/{id}` - Obtener pago específico
- `POST /api/payments` - Crear pago
- `PUT /api/payments/{id}` - Actualizar pago
- `DELETE /api/payments/{id}` - Eliminar pago

## Parámetros Comunes

### Paginación
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (default: 15)

### Filtros
- `search`: Búsqueda por texto
- `with`: Cargar relaciones (ej: `with=student,group`)
- Filtros específicos por entidad

### Ejemplo de Uso

```bash
# Obtener estudiantes con paginación y relaciones
GET /api/students?page=1&per_page=10&with=group,grades

# Buscar estudiantes por nombre
GET /api/students?search=Juan

# Obtener calificaciones de un estudiante específico
GET /api/students/1/grades

# Obtener estadísticas de asistencia
GET /api/attendance/statistics
```

## Autenticación

La API soporta autenticación con Laravel Sanctum. Para endpoints protegidos:

```bash
# Obtener token
POST /api/login
{
    "email": "user@example.com",
    "password": "password"
}

# Usar token en headers
Authorization: Bearer {token}
```

## Códigos de Estado HTTP

- `200` - OK (Operación exitosa)
- `201` - Created (Recurso creado)
- `400` - Bad Request (Datos inválidos)
- `401` - Unauthorized (No autenticado)
- `403` - Forbidden (No autorizado)
- `404` - Not Found (Recurso no encontrado)
- `422` - Unprocessable Entity (Errores de validación)
- `500` - Internal Server Error (Error del servidor)

## Ejemplos de Uso

### Crear un Estudiante

```bash
POST /api/students
Content-Type: application/json

{
    "nombre": "Juan Pérez",
    "apellido": "García",
    "email": "juan.perez@email.com",
    "fecha_nacimiento": "2005-03-15",
    "group_id": 1
}
```

### Actualizar Calificación

```bash
PUT /api/grades/1
Content-Type: application/json

{
    "score": 85.5,
    "evaluations": {
        "parcial1": 80,
        "parcial2": 90
    }
}
```

### Obtener Estadísticas

```bash
GET /api/grades/statistics?group_id=1
```

## Desarrollo

### Generar Documentación

```bash
php artisan swagger:generate
```

### Limpiar Cache

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Verificar Rutas

```bash
php artisan route:list --path=api
```

## Soporte

Para soporte técnico o preguntas sobre la API, contacta al equipo de desarrollo.

## Versión

API v1.0.0 