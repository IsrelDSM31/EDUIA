# 📊 TABLAS Y DATOS AGREGADOS EN LA BASE DE DATOS - IAEDU1

## 📋 RESUMEN GENERAL

**Total de Tablas:** 38 tablas creadas por migraciones  
**Datos Poblados:** 4 seeders ejecutados (`DatabaseSeeder`, `AchievementsSeeder`, `CalendarMessagingSeeder`, `CompleteDataSeeder`)

---

## 🗂️ TABLAS CREADAS POR MIGRACIONES

### 1. **TABLAS DEL SISTEMA (Laravel Core)**

#### `cache`
- **Propósito:** Almacenamiento de cache
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

#### `cache_locks`
- **Propósito:** Bloqueos de cache
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

#### `jobs`
- **Propósito:** Cola de trabajos
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

#### `job_batches`
- **Propósito:** Lotes de trabajos
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

#### `failed_jobs`
- **Propósito:** Trabajos fallidos
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

#### `sessions`
- **Propósito:** Sesiones de usuario
- **Datos agregados:** ✅ Se crean automáticamente al iniciar sesión

#### `password_reset_tokens`
- **Propósito:** Tokens para resetear contraseñas
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

#### `personal_access_tokens`
- **Propósito:** Tokens de acceso para API (Sanctum)
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

### 2. **TABLAS DE USUARIOS Y AUTENTICACIÓN**

#### `users`
- **Propósito:** Usuarios del sistema (admin, profesores, estudiantes)
- **Datos agregados:** ✅ **SÍ - 26 usuarios creados**

**Usuarios creados:**
1. ✅ **Admin:**
   - Email: `admin@eduai.com`
   - Nombre: `Admin`
   - Rol: `admin`
   - Contraseña: `password`

2. ✅ **Profesores (5 creados):**
   - `juan@eduai.com` - Juan Pérez (Matemáticas)
   - `maria.gonzalez@eduai.com` - María González (Matemáticas)
   - `pedro.martinez@eduai.com` - Pedro Martínez (Inglés)
   - `ana.lopez@eduai.com` - Ana López (Química)
   - `jose.hernandez@eduai.com` - José Hernández (Programación)

3. ✅ **Estudiantes (21 creados):**
   - `maria@eduai.com` - María García López
   - `carlos.rodriguez@eduai.com` - Carlos Rodríguez Martínez
   - `ana.hernandez@eduai.com` - Ana Hernández García
   - `luis.gonzalez@eduai.com` - Luis González Pérez
   - `laura.sanchez@eduai.com` - Laura Sánchez López
   - `miguel.ramirez@eduai.com` - Miguel Ramírez Torres
   - `patricia.flores@eduai.com` - Patricia Flores Morales
   - `roberto.castro@eduai.com` - Roberto Castro Jiménez
   - `sofia.ruiz@eduai.com` - Sofía Ruiz Vargas
   - `diego.mendoza@eduai.com` - Diego Mendoza Ortega
   - `carmen.delgado@eduai.com` - Carmen Delgado Ramos
   - `fernando.silva@eduai.com` - Fernando Silva Cruz
   - `gabriela.mora@eduai.com` - Gabriela Mora Guzmán
   - `javier.vega@eduai.com` - Javier Vega Reyes
   - `isabel.cortes@eduai.com` - Isabel Cortés Moreno
   - `ricardo.navarro@eduai.com` - Ricardo Navarro Aguilar
   - `elena.pena@eduai.com` - Elena Peña Medina
   - `oscar.rivas@eduai.com` - Óscar Rivas Campos
   - `adriana.soto@eduai.com` - Adriana Soto Guerrero
   - `andres.contreras@eduai.com` - Andrés Contreras Palacios
   - `daniela.villa@eduai.com` - Daniela Villa Espinoza

---

### 3. **TABLAS ACADÉMICAS PRINCIPALES**

#### `groups`
- **Propósito:** Grupos de estudiantes
- **Datos agregados:** ✅ **SÍ - 15 grupos creados**

**Grupos creados:**
- ✅ `2ºAME` - 2º año, turno matutino
- ✅ `2ºBME` - 2º año, turno matutino
- ✅ `2ºCMG` - 2º año, turno matutino
- ✅ `2ºDMP` - 2º año, turno matutino
- ✅ `2ºEMP` - 2º año, turno matutino
- ✅ `2ºFMC` - 2º año, turno matutino
- ✅ `2ºGMC` - 2º año, turno matutino
- ✅ `2ºHML` - 2º año, turno matutino
- ✅ `2ºAVE` - 2º año, turno vespertino
- ✅ `2ºBVE` - 2º año, turno vespertino
- ✅ `2ºCVG` - 2º año, turno vespertino
- ✅ `2ºDVP` - 2º año, turno vespertino
- ✅ `2ºEVP` - 2º año, turno vespertino
- ✅ `2ºFVC` - 2º año, turno vespertino
- ✅ `2ºGVL` - 2º año, turno vespertino

**Configuración:**
- Nivel: `2` (segundo año)
- Año académico: `2024` (o año actual)
- Turno: `morning` o `afternoon`

---

#### `subjects`
- **Propósito:** Materias/Asignaturas
- **Datos agregados:** ✅ **SÍ - 5 materias creadas**

**Materias creadas:**
1. ✅ **TRIGONOMETRÍA**
   - Código: `MAT201`
   - Créditos: `8`
   - Descripción: Curso de trigonometría

2. ✅ **INGLÉS 2**
   - Código: `ING201`
   - Créditos: `6`
   - Descripción: Curso avanzado de inglés

3. ✅ **QUÍMICA 2**
   - Código: `QUI201`
   - Créditos: `6`
   - Descripción: Curso avanzado de química

4. ✅ **LEOYE**
   - Código: `LEO101`
   - Créditos: `6`
   - Descripción: Lectura, Expresión Oral y Escrita

5. ✅ **MÓDULO 1 DESARROLLA SOFTWARE DE APLICACIÓN CON PROGRAMACIÓN ESTRUCTURADA**
   - Código: `MOD101`
   - Créditos: `10`
   - Descripción: Desarrolla software de aplicación con programación estructurada

---

#### `teachers`
- **Propósito:** Profesores del sistema
- **Datos agregados:** ✅ **SÍ - 5 profesores creados**

**Profesores creados:**
1. ✅ **Juan Pérez**
   - Licencia: `PROF123456`
   - Especialización: `Matemáticas`
   - Nivel educativo: `Licenciatura`
   - Años de experiencia: `5`

2. ✅ **María González**
   - Especialización: `Matemáticas`
   - Años de experiencia: `3-15` (aleatorio)

3. ✅ **Pedro Martínez**
   - Especialización: `Inglés`
   - Años de experiencia: `3-15` (aleatorio)

4. ✅ **Ana López**
   - Especialización: `Química`
   - Años de experiencia: `3-15` (aleatorio)

5. ✅ **José Hernández**
   - Especialización: `Programación`
   - Años de experiencia: `3-15` (aleatorio)

---

#### `subject_teacher` (Tabla pivot)
- **Propósito:** Relación muchos a muchos entre profesores y materias
- **Datos agregados:** ✅ **SÍ - Relaciones creadas**

**Relaciones:**
- ✅ Todos los profesores tienen asignadas todas las materias
- ✅ Profesores especializados tienen materias relacionadas asignadas

---

#### `students`
- **Propósito:** Estudiantes del sistema
- **Datos agregados:** ✅ **SÍ - 21 estudiantes creados**

**Datos de cada estudiante:**
- ✅ Matrícula única (2024001, 2024002, etc.)
- ✅ Nombre completo (nombre, apellido paterno, apellido materno)
- ✅ Fecha de nacimiento (17 años, aleatoria)
- ✅ Tipo de sangre (aleatorio: O+, O-, A+, A-, B+, B-, AB+, AB-)
- ✅ Alergias (Ninguna o Polen, Polvo)
- ✅ Contacto de emergencia (nombre, teléfono, relación)
- ✅ Datos de padres (padre y madre con nombres y teléfonos)
- ✅ Grupo asignado (distribuidos entre los 15 grupos)

**Estudiantes creados:**
1. María García López (matrícula: 2024001)
2. Carlos Rodríguez Martínez (matrícula: 2024002)
3. Ana Hernández García (matrícula: 2024003)
4. Luis González Pérez (matrícula: 2024004)
5. Laura Sánchez López (matrícula: 2024005)
6. Miguel Ramírez Torres (matrícula: 2024006)
7. Patricia Flores Morales (matrícula: 2024007)
8. Roberto Castro Jiménez (matrícula: 2024008)
9. Sofía Ruiz Vargas (matrícula: 2024009)
10. Diego Mendoza Ortega (matrícula: 2024010)
11. Carmen Delgado Ramos (matrícula: 2024011)
12. Fernando Silva Cruz (matrícula: 2024012)
13. Gabriela Mora Guzmán (matrícula: 2024013)
14. Javier Vega Reyes (matrícula: 2024014)
15. Isabel Cortés Moreno (matrícula: 2024015)
16. Ricardo Navarro Aguilar (matrícula: 2024016)
17. Elena Peña Medina (matrícula: 2024017)
18. Óscar Rivas Campos (matrícula: 2024018)
19. Adriana Soto Guerrero (matrícula: 2024019)
20. Andrés Contreras Palacios (matrícula: 2024020)
21. Daniela Villa Espinoza (matrícula: 2024021)

---

#### `academic_periods`
- **Propósito:** Periodos académicos (ciclos escolares)
- **Datos agregados:** ✅ **SÍ - 1 período creado**

**Período creado:**
- ✅ **Ciclo Escolar 2024-2025** (o año actual)
  - Fecha inicio: `2024-08-01` (o año actual)
  - Fecha fin: `2025-07-31` (o año siguiente)
  - Tipo: `Anual`
  - Parámetros de evaluación:
    - Calificación mínima: `6.0`
    - Calificación máxima: `10.0`
    - Calificación aprobatoria: `6.0`

---

#### `schedules`
- **Propósito:** Horarios de clases (grupo, materia, profesor, día, hora)
- **Datos agregados:** ✅ **SÍ - Múltiples horarios creados**

**Horarios creados:**
- ✅ Horarios para los primeros 5 grupos
- ✅ Cada grupo tiene horarios para todas las materias
- ✅ Días: Lunes, Martes, Miércoles, Jueves, Viernes
- ✅ Horarios:
  - `08:00 - 09:30`
  - `09:30 - 11:00`
  - `11:00 - 12:30`
  - `12:30 - 14:00`
- ✅ Aulas: Aula 1, Aula 2, Aula 3, etc.

---

#### `attendances`
- **Propósito:** Registro de asistencias de estudiantes
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `attendance`
- **Propósito:** Tabla alternativa de asistencias
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `grades`
- **Propósito:** Calificaciones de estudiantes
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

**Campos importantes:**
- `student_id`, `subject_id`, `teacher_id`
- `score`, `date`, `evaluations` (JSON)
- `estado`, `promedio_final`, `puntos_faltantes`

---

#### `rubrics`
- **Propósito:** Rúbricas de evaluación
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `alerts`
- **Propósito:** Alertas de riesgo para estudiantes
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `student_risks`
- **Propósito:** Análisis de riesgo de estudiantes
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `events`
- **Propósito:** Eventos generales
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

### 4. **TABLAS DE GAMIFICACIÓN**

#### `student_points`
- **Propósito:** Puntos totales de estudiantes
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

**Campos:**
- `total_points`, `attendance_points`, `grade_points`
- `participation_points`, `achievement_points`
- `level`, `points_to_next_level`
- `streak_days`, `last_attendance_date`

---

#### `achievements`
- **Propósito:** Logros/Insignias disponibles
- **Datos agregados:** ✅ **SÍ - 13 logros creados**

**Logros creados:**

**Asistencia:**
1. ✅ **Puntual Principiante** (Common)
   - Descripción: Asiste 5 días consecutivos sin faltas
   - Puntos: 10
   - Categoría: attendance

2. ✅ **Asistencia Perfecta** (Epic)
   - Descripción: Asiste 30 días consecutivos sin faltas
   - Puntos: 50
   - Categoría: attendance

3. ✅ **Dedicación Total** (Legendary)
   - Descripción: 100% de asistencia en el mes
   - Puntos: 100
   - Categoría: attendance

**Calificaciones:**
4. ✅ **Estudiante Ejemplar** (Rare)
   - Descripción: Obtén un promedio de 9 o más
   - Puntos: 25
   - Categoría: grades

5. ✅ **Excelencia Académica** (Epic)
   - Descripción: Obtén 10 en 5 evaluaciones
   - Puntos: 50
   - Categoría: grades

6. ✅ **Genio** (Legendary)
   - Descripción: Mantén un promedio de 10 durante todo el semestre
   - Puntos: 150
   - Categoría: grades

7. ✅ **Mejora Continua** (Rare)
   - Descripción: Sube tu promedio en 1 punto
   - Puntos: 35
   - Categoría: grades

**Participación:**
8. ✅ **Participativo** (Common)
   - Descripción: Participa en 10 actividades
   - Puntos: 15
   - Categoría: participation

9. ✅ **Líder de Clase** (Rare)
   - Descripción: Participa en 50 actividades
   - Puntos: 30
   - Categoría: participation

**Especiales:**
10. ✅ **Primera Victoria** (Common)
    - Descripción: Completa tu primera semana de clases
    - Puntos: 5
    - Categoría: special

11. ✅ **Racha Imparable** (Rare)
    - Descripción: Gana puntos durante 7 días consecutivos
    - Puntos: 40
    - Categoría: special

12. ✅ **Top 3** (Epic)
    - Descripción: Entra al top 3 del ranking
    - Puntos: 75
    - Categoría: special

13. ✅ **Campeón** (Legendary)
    - Descripción: Alcanza el #1 en el ranking
    - Puntos: 200
    - Categoría: special

---

#### `student_achievements`
- **Propósito:** Logros desbloqueados por estudiantes
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `points_history`
- **Propósito:** Historial de puntos ganados
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `rankings`
- **Propósito:** Rankings semanales/mensuales
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

### 5. **TABLAS DE CALENDARIO Y MENSAJERÍA**

#### `calendar_events`
- **Propósito:** Eventos del calendario
- **Datos agregados:** ✅ **SÍ - 5 eventos creados**

**Eventos creados:**
1. ✅ **Examen de Matemáticas**
   - Tipo: `exam`
   - Fecha: 3 días en el futuro, 9:00-11:00
   - Materia: Matemáticas

2. ✅ **Reunión de Padres**
   - Tipo: `meeting`
   - Fecha: 7 días en el futuro, 15:00-17:00
   - Materia: Ninguna

3. ✅ **Examen de Historia**
   - Tipo: `exam`
   - Fecha: 10 días en el futuro, 10:00-12:00
   - Materia: Historia

4. ✅ **Día Festivo**
   - Tipo: `holiday`
   - Fecha: 15 días en el futuro
   - Materia: Ninguna

5. ✅ **Entrega de Proyectos**
   - Tipo: `assignment`
   - Fecha: 14 días en el futuro, 23:59
   - Materia: Ciencias

---

#### `channels`
- **Propósito:** Canales de mensajería por materia
- **Datos agregados:** ✅ **SÍ - 5 canales creados**

**Canales creados:**
- ✅ Un canal por cada materia (TRIGONOMETRÍA, INGLÉS 2, QUÍMICA 2, LEOYE, MÓDULO 1)
- Slug generado automáticamente desde el nombre de la materia

---

#### `conversations`
- **Propósito:** Conversaciones individuales
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `messages`
- **Propósito:** Mensajes en canales y conversaciones
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

### 6. **TABLAS DE NOTIFICACIONES Y FACTURACIÓN**

#### `notifications`
- **Propósito:** Notificaciones del sistema
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `invoices`
- **Propósito:** Facturas
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `payments`
- **Propósito:** Pagos
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

#### `subscriptions`
- **Propósito:** Suscripciones
- **Datos agregados:** ❌ No se poblaron datos (solo estructura)

---

### 7. **TABLAS DE AUDITORÍA Y BITÁCORA**

#### `change_logs`
- **Propósito:** Bitácora de cambios (registro de todas las modificaciones)
- **Datos agregados:** ✅ **SÍ - Se crean automáticamente al hacer cambios**

**Ejemplo:**
- ✅ Al crear un estudiante, se registra en `change_logs` con:
  - `user_id`: Usuario que hizo el cambio
  - `model_type`: `App\Models\Student`
  - `model_id`: ID del estudiante
  - `action`: `create`
  - `changes`: Datos del estudiante creado

---

## 📊 RESUMEN DE DATOS POBLADOS

### ✅ **Datos Poblados por Seeders:**

| Categoría | Cantidad | Detalles |
|-----------|----------|----------|
| **Usuarios** | 26 | 1 admin, 5 profesores, 21 estudiantes |
| **Grupos** | 15 | 2º año, turnos matutino y vespertino |
| **Materias** | 5 | TRIGONOMETRÍA, INGLÉS 2, QUÍMICA 2, LEOYE, MÓDULO 1 |
| **Profesores** | 5 | Con especialización y licencia profesional |
| **Estudiantes** | 21 | Con datos completos (matrícula, contacto, padres) |
| **Períodos Académicos** | 1 | Ciclo escolar anual |
| **Horarios** | ~25 | Para 5 grupos con todas las materias |
| **Logros** | 13 | De asistencia, calificaciones, participación y especiales |
| **Eventos de Calendario** | 5 | Exámenes, reuniones, festivos |
| **Canales** | 5 | Uno por materia |

### ❌ **Tablas Sin Datos (Solo Estructura):**

- Cache y trabajos (Laravel)
- Asistencias y calificaciones (se crean durante uso)
- Rúbricas
- Alertas y riesgos (se calculan automáticamente)
- Notificaciones (se crean automáticamente)
- Facturas y pagos
- Historial de puntos y rankings
- Conversaciones y mensajes

---

## 🔄 TABLAS QUE SE POBLAN AUTOMÁTICAMENTE

### Durante el Uso del Sistema:

1. **`sessions`** - Se crean al iniciar sesión
2. **`change_logs`** - Se crean al hacer cambios (crear, editar, eliminar)
3. **`attendances`** - Se crean al registrar asistencias
4. **`grades`** - Se crean al registrar calificaciones
5. **`alerts`** - Se crean cuando se detectan riesgos
6. **`student_risks`** - Se calculan automáticamente
7. **`notifications`** - Se crean con eventos del sistema
8. **`student_points`** - Se actualizan con puntos ganados
9. **`student_achievements`** - Se crean al desbloquear logros
10. **`points_history`** - Se registra cada vez que se ganan puntos

---

## 📝 NOTAS IMPORTANTES

1. **Contraseñas por Defecto:**
   - Todos los usuarios tienen la contraseña: `password`
   - ⚠️ **IMPORTANTE:** Cambiar en producción

2. **Relaciones:**
   - Todos los estudiantes tienen grupo asignado
   - Todos los profesores tienen materias asignadas
   - Todos los grupos tienen horarios configurados

3. **Datos de Prueba:**
   - Los datos son de ejemplo para desarrollo
   - Las fechas son dinámicas (se calculan desde `now()`)
   - Los nombres y datos son ficticios

4. **Extensibilidad:**
   - El sistema está listo para agregar más estudiantes, profesores, materias, etc.
   - Las tablas están preparadas para recibir datos reales

---

## ✅ VERIFICACIÓN

Para verificar los datos en la base de datos:

```bash
# Ver todas las tablas
php artisan migrate:status

# Contar registros (si tienes extensión intl)
php artisan db:show --counts

# O usar tinker
php artisan tinker
>>> User::count()
>>> Student::count()
>>> Group::count()
>>> Subject::count()
```

---

**Última actualización:** Datos poblados por `php artisan db:seed` ejecutado después de reparar la base de datos.

