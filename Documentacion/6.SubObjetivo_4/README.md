# Subobjetivo 4: Gestión de Horarios y Recursos

## 1. Descripción General

El módulo de Gestión de Horarios y Recursos proporciona herramientas para la planificación, asignación y administración eficiente de horarios escolares y recursos institucionales. Este sistema optimiza la distribución de tiempo, espacios y materiales, garantizando un uso eficiente de los recursos disponibles.

## 2. Componentes Principales

### 2.1 Gestión de Horarios
- Programación de clases
- Asignación de aulas
- Distribución de docentes
- Períodos académicos
- Eventos especiales

### 2.2 Gestión de Recursos
- Aulas y laboratorios
- Equipamiento
- Material didáctico
- Recursos digitales
- Instalaciones deportivas

### 2.3 Reservas
- Sistema de reservas
- Calendario de uso
- Conflictos y soluciones
- Notificaciones
- Historial

## 3. Estructura de Datos

### 3.1 Modelo de Horarios
```sql
CREATE TABLE schedules (
    id BIGINT PRIMARY KEY,
    subject_id BIGINT,
    teacher_id BIGINT,
    classroom_id BIGINT,
    day_of_week INTEGER,
    start_time TIME,
    end_time TIME,
    academic_period_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id),
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id),
    FOREIGN KEY (academic_period_id) REFERENCES academic_periods(id)
);

CREATE TABLE resources (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    type VARCHAR(50),
    location VARCHAR(255),
    capacity INTEGER,
    status VARCHAR(20),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE reservations (
    id BIGINT PRIMARY KEY,
    resource_id BIGINT,
    user_id BIGINT,
    purpose VARCHAR(255),
    start_datetime TIMESTAMP,
    end_datetime TIMESTAMP,
    status VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## 4. Funcionalidades Principales

### 4.1 Generación de Horarios
```php
class ScheduleGenerator
{
    public function generateSchedule($academicPeriod)
    {
        $constraints = $this->getConstraints();
        $subjects = Subject::with('teachers')->get();
        $classrooms = Classroom::available()->get();
        
        $schedule = [];
        
        foreach ($subjects as $subject) {
            $availableSlots = $this->findAvailableSlots(
                $subject,
                $constraints,
                $classrooms
            );
            
            if (empty($availableSlots)) {
                throw new ScheduleConflictException(
                    "No slots available for {$subject->name}"
                );
            }
            
            $slot = $this->selectOptimalSlot($availableSlots);
            $schedule[] = $this->createScheduleEntry(
                $subject,
                $slot,
                $academicPeriod
            );
        }
        
        return $schedule;
    }
    
    private function findAvailableSlots($subject, $constraints, $classrooms)
    {
        $slots = [];
        
        foreach ($classrooms as $classroom) {
            $availableTimes = $this->getAvailableTimes(
                $classroom,
                $constraints
            );
            
            foreach ($availableTimes as $time) {
                if ($this->validateSlot($subject, $classroom, $time)) {
                    $slots[] = [
                        'classroom' => $classroom,
                        'time' => $time
                    ];
                }
            }
        }
        
        return $slots;
    }
}
```

### 4.2 Sistema de Reservas
```php
class ResourceReservationService
{
    public function makeReservation(Request $request)
    {
        $validated = $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        // Verificar disponibilidad
        if (!$this->isResourceAvailable(
            $validated['resource_id'],
            $validated['start_datetime'],
            $validated['end_datetime']
        )) {
            throw new ResourceNotAvailableException(
                'El recurso no está disponible en el horario solicitado'
            );
        }

        // Crear reserva
        $reservation = Reservation::create([
            'resource_id' => $validated['resource_id'],
            'user_id' => Auth::id(),
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'],
            'status' => 'confirmed'
        ]);

        // Notificar a los interesados
        event(new ReservationCreated($reservation));

        return $reservation;
    }
}
```

## 5. Interfaces de Usuario

### 5.1 Calendario de Horarios
```html
<div class="schedule-calendar">
    <div class="calendar-header">
        <button @click="previousWeek">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h2>{{ currentWeek }}</h2>
        <button @click="nextWeek">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    
    <div class="calendar-grid">
        <div class="time-column">
            <div v-for="hour in hours" class="time-slot">
                {{ formatHour(hour) }}
            </div>
        </div>
        
        <div v-for="day in days" class="day-column">
            <div class="day-header">
                {{ formatDay(day) }}
            </div>
            
            <div v-for="event in getEventsForDay(day)" 
                 class="calendar-event"
                 :style="getEventStyle(event)">
                <div class="event-title">
                    {{ event.title }}
                </div>
                <div class="event-details">
                    {{ event.location }}
                </div>
            </div>
        </div>
    </div>
</div>
```

### 5.2 Gestión de Recursos
```html
<div class="resource-management">
    <div class="resource-filters">
        <select v-model="selectedType">
            <option value="">Todos los tipos</option>
            <option v-for="type in resourceTypes" 
                    :value="type.id">
                {{ type.name }}
            </option>
        </select>
        
        <select v-model="selectedStatus">
            <option value="">Todos los estados</option>
            <option value="available">Disponible</option>
            <option value="in_use">En uso</option>
            <option value="maintenance">Mantenimiento</option>
        </select>
    </div>
    
    <div class="resource-list">
        <div v-for="resource in filteredResources" 
             class="resource-card">
            <div class="resource-header">
                <h3>{{ resource.name }}</h3>
                <span :class="getStatusClass(resource.status)">
                    {{ resource.status }}
                </span>
            </div>
            
            <div class="resource-details">
                <p>{{ resource.description }}</p>
                <p>Ubicación: {{ resource.location }}</p>
                <p>Capacidad: {{ resource.capacity }}</p>
            </div>
            
            <div class="resource-actions">
                <button @click="reserveResource(resource)">
                    Reservar
                </button>
                <button @click="viewDetails(resource)">
                    Detalles
                </button>
            </div>
        </div>
    </div>
</div>
```

## 6. Algoritmos de Optimización

### 6.1 Asignación de Horarios
- Restricciones de tiempo
- Disponibilidad docente
- Capacidad de aulas
- Preferencias de horario
- Resolución de conflictos

### 6.2 Gestión de Recursos
- Priorización de reservas
- Balanceo de carga
- Mantenimiento preventivo
- Optimización de uso
- Gestión de conflictos

## 7. Reportes y Análisis

### 7.1 Uso de Recursos
- Estadísticas de ocupación
- Patrones de uso
- Recursos más solicitados
- Períodos pico
- Eficiencia de uso

### 7.2 Análisis de Horarios
- Distribución de clases
- Carga docente
- Uso de aulas
- Conflictos resueltos
- Satisfacción de usuarios

## 8. Integración con Otros Módulos

### 8.1 Gestión Académica
- Períodos lectivos
- Asignaturas
- Grupos de estudiantes
- Docentes
- Evaluaciones

### 8.2 Comunicaciones
- Notificaciones de reservas
- Cambios de horario
- Mantenimientos programados
- Disponibilidad de recursos
- Recordatorios

## 9. Mantenimiento y Soporte

### 9.1 Recursos Físicos
- Inventario actualizado
- Mantenimiento preventivo
- Reparaciones
- Reemplazos
- Control de calidad

### 9.2 Sistema
- Backup de datos
- Optimización de rendimiento
- Actualizaciones
- Soporte técnico
- Documentación

## 10. Configuración

### 10.1 Parámetros del Sistema
- Períodos académicos
- Horarios permitidos
- Tipos de recursos
- Políticas de reserva
- Restricciones

### 10.2 Personalización
- Plantillas de horarios
- Reglas de asignación
- Notificaciones
- Reportes
- Interfaces 