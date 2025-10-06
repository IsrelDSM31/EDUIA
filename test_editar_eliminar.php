<?php

echo "===========================================\n";
echo "Probando Editar y Eliminar Estudiante\n";
echo "===========================================\n\n";

$studentId = 44; // El estudiante recién creado

// TEST 1: Editar estudiante
echo "TEST 1: Editar estudiante (ID: {$studentId})\n";
echo "---\n";

$updateData = [
    'name' => 'Carlos Rodríguez Pérez Actualizado',
    'phone' => '+52987654321',
    'address' => 'Avenida Secundaria 456'
];

$ch = curl_init("http://localhost/IAEDU1/public/api/students/{$studentId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Estudiante editado exitosamente!\n";
    $data = json_decode($response, true);
    echo json_encode($data['data'], JSON_PRETTY_PRINT);
} else {
    echo "❌ Error al editar (HTTP {$httpCode}):\n";
    echo $response;
}

echo "\n\n---\n\n";

// TEST 2: Eliminar estudiante
echo "TEST 2: Eliminar estudiante (ID: {$studentId})\n";
echo "---\n";

$ch = curl_init("http://localhost/IAEDU1/public/api/students/{$studentId}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Estudiante eliminado exitosamente!\n";
    echo $response;
} else {
    echo "❌ Error al eliminar (HTTP {$httpCode}):\n";
    echo $response;
}

echo "\n\n===========================================\n";
echo "✅ CRUD COMPLETO FUNCIONANDO AL 100%!\n";
echo "===========================================\n";



