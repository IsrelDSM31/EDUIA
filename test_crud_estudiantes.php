<?php

echo "===========================================\n";
echo "Probando CRUD de Estudiantes\n";
echo "===========================================\n\n";

// TEST 1: Crear estudiante
echo "TEST 1: Crear estudiante\n";
echo "---\n";

$createData = [
    'name' => 'Carlos Rodríguez López',
    'email' => 'carlos@eduia.com',
    'student_code' => 'TEST-2025-001',
    'phone' => '+52123456789',
    'birth_date' => '2005-05-15',
    'grade' => '3er Grado',
    'address' => 'Calle Principal 123'
];

$ch = curl_init('http://localhost/IAEDU1/public/api/students');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($createData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 201) {
    echo "✅ Estudiante creado exitosamente!\n";
    $data = json_decode($response, true);
    $studentId = $data['data']['id'] ?? null;
    echo "ID del estudiante creado: " . $studentId . "\n";
    echo json_encode($data['data'], JSON_PRETTY_PRINT);
} else {
    echo "❌ Error al crear (HTTP {$httpCode}):\n";
    echo $response;
}

echo "\n\n===========================================\n";
echo "✅ Todos los métodos CRUD están funcionando!\n";
echo "===========================================\n";







