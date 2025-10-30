<?php

// Simular una petición HTTP al endpoint de students
$url = 'http://localhost/IAEDU1/public/api/students';

echo "===========================================\n";
echo "Probando endpoint: GET /api/students\n";
echo "===========================================\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n\n";

if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "✅ Respuesta exitosa!\n\n";
    echo "Total de estudiantes en respuesta: " . count($data['data'] ?? []) . "\n\n";
    
    if (!empty($data['data'])) {
        echo "Primer estudiante:\n";
        echo json_encode($data['data'][0], JSON_PRETTY_PRINT);
    }
} else {
    echo "❌ Error en la respuesta:\n";
    echo $response;
}

echo "\n\n===========================================\n";







