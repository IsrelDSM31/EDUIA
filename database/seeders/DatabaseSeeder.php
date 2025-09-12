<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear grupos predeterminados primero
        $grupos = [
            '2ºAME', '2ºBME', '2ºCMG', '2ºDMP', '2ºEMP', '2ºFMC', '2ºGMC', '2ºHML',
            '2ºAVE', '2ºBVE', '2ºCVG', '2ºDVP', '2ºEVP', '2ºFVC', '2ºGVL'
        ];
        foreach ($grupos as $grupo) {
            \App\Models\Group::firstOrCreate(
                ['name' => $grupo],
                [
                    'grade_level' => '2',
                    'academic_year' => date('Y'),
                    'shift' => 'morning',
                ]
            );
        }

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@eduai.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create a teacher
        $teacherUser = User::firstOrCreate(
            ['email' => 'juan@eduai.com'],
            [
                'name' => 'Juan Pérez',
                'password' => Hash::make('password'),
                'role' => 'teacher',
            ]
        );

        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'professional_license' => 'PROF123456',
                'specialization' => 'Matemáticas',
                'education_level' => 'Licenciatura',
                'experience_years' => 5,
                'availability' => json_encode(['Lunes' => ['08:00-14:00'], 'Martes' => ['08:00-14:00']]),
            ]
        );

        // Create subjects
        $subjects = [
            [
                'name' => 'TRIGONOMETRÍA',
                'code' => 'MAT201',
                'description' => 'Curso de trigonometría',
                'credits' => 8,
            ],
            [
                'name' => 'INGLÉS 2',
                'code' => 'ING201',
                'description' => 'Curso avanzado de inglés',
                'credits' => 6,
            ],
            [
                'name' => 'QUÍMICA 2',
                'code' => 'QUI201',
                'description' => 'Curso avanzado de química',
                'credits' => 6,
            ],
            [
                'name' => 'LEOYE',
                'code' => 'LEO101',
                'description' => 'Lectura, Expresión Oral y Escrita',
                'credits' => 6,
            ],
            [
                'name' => 'MÓDULO 1 DESARROLLA SOFTWARE DE APLICACIÓN CON PROGRAMACIÓN ESTRUCTURADA',
                'code' => 'MOD101',
                'description' => 'Desarrolla software de aplicación con programación estructurada',
                'credits' => 10,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['code' => $subject['code']],
                $subject
            );
        }

        // Create a student
        $studentUser = User::firstOrCreate(
            ['email' => 'maria@eduai.com'],
            [
                'name' => 'María García',
                'password' => Hash::make('password'),
                'role' => 'student',
            ]
        );

        Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'matricula' => '2024001',
                'nombre' => 'María',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'López',
                'birth_date' => '2000-01-01',
                'blood_type' => 'O+',
                'allergies' => 'Ninguna',
                'emergency_contact' => json_encode([
                    'name' => 'Juan García',
                    'phone' => '9876543210',
                    'relationship' => 'Padre'
                ]),
                'parent_data' => json_encode([
                    'father_name' => 'Juan García',
                    'father_phone' => '9876543210',
                    'mother_name' => 'Ana López',
                    'mother_phone' => '9876543211'
                ]),
                'group_id' => \App\Models\Group::first()->id, // Usar el primer grupo creado
            ]
        );
    }
}
