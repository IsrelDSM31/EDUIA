<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;

echo "===========================================\n";
echo "Probando transformación de estudiantes\n";
echo "===========================================\n\n";

$students = Student::with(['user', 'group', 'risk'])->take(3)->get();

echo "Total de estudiantes: " . Student::count() . "\n\n";
echo "Primeros 3 estudiantes transformados:\n\n";

foreach($students as $student) {
    $transformed = [
        'id' => $student->id,
        'name' => trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
        'email' => $student->user->email ?? $student->matricula . '@eduia.com',
        'student_code' => $student->matricula,
        'phone' => $student->emergency_contact['phone'] ?? '',
        'birth_date' => $student->birth_date,
        'grade' => $student->group->name ?? 'Sin grupo',
        'address' => $student->parent_data['address'] ?? '',
        'risk_level' => $student->risk->risk_level ?? 'low',
    ];
    
    echo json_encode($transformed, JSON_PRETTY_PRINT);
    echo "\n---\n";
}

echo "\n✅ Transformación exitosa!\n";
echo "===========================================\n";



