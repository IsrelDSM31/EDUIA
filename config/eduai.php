<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EduAI Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the EduAI platform.
    |
    */

    'roles' => [
        'admin' => 'Administrador',
        'teacher' => 'Profesor',
        'student' => 'Estudiante',
    ],

    'attendance_status' => [
        'present' => 'Presente',
        'absent' => 'Ausente',
        'late' => 'Retardo',
        'justified' => 'Justificado',
    ],

    'alert_types' => [
        'academic' => 'Académico',
        'behavioral' => 'Conductual',
        'administrative' => 'Administrativo',
    ],

    'alert_urgency' => [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
    ],

    'evaluation_types' => [
        'exam' => 'Examen',
        'homework' => 'Tarea',
        'project' => 'Proyecto',
        'participation' => 'Participación',
    ],

    'shifts' => [
        'morning' => 'Matutino',
        'afternoon' => 'Vespertino',
        'evening' => 'Nocturno',
    ],

    'academic_period_types' => [
        'semester' => 'Semestre',
        'quarter' => 'Trimestre',
        'year' => 'Año',
    ],
]; 