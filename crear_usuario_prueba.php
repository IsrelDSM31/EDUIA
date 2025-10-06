<?php

// Script para crear usuario de prueba para la app móvil
// Ejecutar: php crear_usuario_prueba.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "===========================================\n";
echo "Creando usuarios de prueba para EduIA App\n";
echo "===========================================\n\n";

// Usuario Admin
$admin = User::updateOrCreate(
    ['email' => 'admin@eduia.com'],
    [
        'name' => 'Admin EduIA',
        'password' => Hash::make('admin123'),
        'role' => 'admin'
    ]
);
echo "✅ Usuario Admin creado:\n";
echo "   Email: admin@eduia.com\n";
echo "   Password: admin123\n";
echo "   Role: admin\n\n";

// Usuario Profesor
$profesor = User::updateOrCreate(
    ['email' => 'profesor@eduia.com'],
    [
        'name' => 'Profesor Demo',
        'password' => Hash::make('profesor123'),
        'role' => 'teacher'
    ]
);
echo "✅ Usuario Profesor creado:\n";
echo "   Email: profesor@eduia.com\n";
echo "   Password: profesor123\n";
echo "   Role: teacher\n\n";

// Usuario Estudiante
$estudiante = User::updateOrCreate(
    ['email' => 'estudiante@eduia.com'],
    [
        'name' => 'Estudiante Demo',
        'password' => Hash::make('estudiante123'),
        'role' => 'student'
    ]
);
echo "✅ Usuario Estudiante creado:\n";
echo "   Email: estudiante@eduia.com\n";
echo "   Password: estudiante123\n";
echo "   Role: student\n\n";

echo "===========================================\n";
echo "✨ Usuarios creados exitosamente!\n";
echo "===========================================\n\n";
echo "Ahora puedes usar cualquiera de estas credenciales en la app móvil.\n\n";

