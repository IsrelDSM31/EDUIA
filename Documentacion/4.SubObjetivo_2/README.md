# Subobjetivo 2: Gestión Académica

## 1. Descripción General

El módulo de Gestión Académica es el núcleo operativo del sistema IAEDU1, diseñado para administrar eficientemente todos los aspectos académicos de la institución educativa. Este módulo integra la gestión de calificaciones, asistencia, evaluaciones, y seguimiento del progreso académico de los estudiantes.

## 2. Componentes Principales

### 2.1 Gestión de Calificaciones
- Registro de notas
- Cálculo de promedios
- Histórico académico
- Boletines de calificaciones
- Reportes de rendimiento

### 2.2 Control de Asistencia
- Registro diario
- Justificaciones
- Reportes de ausencias
- Notificaciones automáticas
- Estadísticas de asistencia

### 2.3 Evaluaciones
- Creación de exámenes
- Rúbricas de evaluación
- Calificación automática
- Retroalimentación
- Análisis de resultados

### 2.4 Seguimiento Académico
- Progreso individual
- Indicadores de desempeño
- Alertas tempranas
- Planes de mejora
- Reportes personalizados

## 3. Estructura de Datos

### 3.1 Modelo de Calificaciones
```sql
CREATE TABLE grades (
    id BIGINT PRIMARY KEY,
    student_id BIGINT,
    subject_id BIGINT,
    evaluation_id BIGINT,
    score DECIMAL(5,2),
    comments TEXT,
    created_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE evaluations (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    type VARCHAR(50),
    weight DECIMAL(5,2),
    subject_id BIGINT,
    due_date DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id)
);
```

### 3.2 Modelo de Asistencia
```sql
CREATE TABLE attendance (
    id BIGINT PRIMARY KEY,
    student_id BIGINT,
    class_id BIGINT,
    date DATE,
    status VARCHAR(20),
    justification TEXT,
    created_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

## 4. Funcionalidades Específicas

### 4.1 Registro de Calificaciones
```php
public function registerGrade(Request $request)
{
    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'subject_id' => 'required|exists:subjects,id',
        'evaluation_id' => 'required|exists:evaluations,id',
        'score' => 'required|numeric|min:0|max:100',
        'comments' => 'nullable|string'
    ]);

    $grade = Grade::create([
        'student_id' => $validated['student_id'],
        'subject_id' => $validated['subject_id'],
        'evaluation_id' => $validated['evaluation_id'],
        'score' => $validated['score'],
        'comments' => $validated['comments'],
        'created_by' => Auth::id()
    ]);

    // Notificar a estudiantes y padres
    event(new GradeRegistered($grade));

    return response()->json([
        'message' => 'Calificación registrada exitosamente',
        'grade' => $grade
    ]);
}
```

### 4.2 Control de Asistencia
```php
public function markAttendance(Request $request)
{
    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'class_id' => 'required|exists:classes,id',
        'status' => 'required|in:present,absent,late',
        'justification' => 'nullable|string'
    ]);

    $attendance = Attendance::create([
        'student_id' => $validated['student_id'],
        'class_id' => $validated['class_id'],
        'date' => now(),
        'status' => $validated['status'],
        'justification' => $validated['justification'],
        'created_by' => Auth::id()
    ]);

    if ($attendance->status === 'absent') {
        // Notificar a padres
        event(new AbsenceRecorded($attendance));
    }

    return response()->json([
        'message' => 'Asistencia registrada exitosamente',
        'attendance' => $attendance
    ]);
}
```

## 5. Interfaces de Usuario

### 5.1 Registro de Notas
```html
<form @submit.prevent="submitGrade">
    <div class="form-group">
        <label>Estudiante</label>
        <select v-model="form.student_id" required>
            <option v-for="student in students" :value="student.id">
                {{ student.name }}
            </option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Evaluación</label>
        <select v-model="form.evaluation_id" required>
            <option v-for="eval in evaluations" :value="eval.id">
                {{ eval.name }}
            </option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Calificación</label>
        <input type="number" v-model="form.score" min="0" max="100" required>
    </div>
    
    <div class="form-group">
        <label>Comentarios</label>
        <textarea v-model="form.comments"></textarea>
    </div>
    
    <button type="submit">Guardar Calificación</button>
</form>
```

### 5.2 Lista de Asistencia
```html
<div class="attendance-list">
    <table class="table">
        <thead>
            <tr>
                <th>Estudiante</th>
                <th>Estado</th>
                <th>Justificación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="student in students">
                <td>{{ student.name }}</td>
                <td>
                    <select v-model="attendance[student.id]">
                        <option value="present">Presente</option>
                        <option value="absent">Ausente</option>
                        <option value="late">Tardanza</option>
                    </select>
                </td>
                <td>
                    <input type="text" v-model="justification[student.id]">
                </td>
                <td>
                    <button @click="saveAttendance(student.id)">
                        Guardar
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

## 6. Reportes y Análisis

### 6.1 Boletín de Calificaciones
- Notas por período
- Promedios por materia
- Observaciones
- Ranking
- Gráficos de progreso

### 6.2 Reportes de Asistencia
- Porcentaje de asistencia
- Ausencias justificadas
- Tardanzas
- Tendencias
- Alertas automáticas

### 6.3 Análisis de Rendimiento
- Estadísticas por curso
- Comparativas
- Indicadores de riesgo
- Recomendaciones
- Proyecciones

## 7. Integraciones

### 7.1 Notificaciones
- Correo electrónico
- SMS
- Push notifications
- Portal de padres
- Aplicación móvil

### 7.2 Exportación de Datos
- PDF
- Excel
- CSV
- API REST
- Sincronización

## 8. Seguridad y Auditoría

### 8.1 Control de Acceso
- Permisos por rol
- Validación de datos
- Registro de cambios
- Backup automático
- Encriptación

### 8.2 Trazabilidad
- Historial de modificaciones
- Log de acciones
- Responsables
- Timestamps
- Reportes de auditoría

## 9. Configuración

### 9.1 Períodos Académicos
- Calendario escolar
- Fechas de evaluación
- Horarios
- Eventos
- Cronograma

### 9.2 Evaluaciones
- Tipos de evaluación
- Ponderaciones
- Escalas de calificación
- Criterios
- Rúbricas

## 10. Mantenimiento

### 10.1 Respaldo de Datos
- Backup diario
- Histórico académico
- Documentos digitales
- Recuperación
- Archivado

### 10.2 Optimización
- Rendimiento
- Almacenamiento
- Consultas
- Cache
- Indexación 