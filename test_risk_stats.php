<?php

$url = 'http://localhost/IAEDU1/public/api/risk-analysis/statistics';

echo "===========================================\n";
echo "Probando: GET /api/risk-analysis/statistics\n";
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
    echo "✅ Estadísticas obtenidas!\n\n";
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo "❌ Error:\n";
    echo $response;
}

echo "\n\n===========================================\n";







