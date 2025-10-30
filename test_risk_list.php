<?php

$url = 'http://localhost/IAEDU1/public/api/risk-analysis';

echo "===========================================\n";
echo "Probando: GET /api/risk-analysis\n";
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
    echo "✅ Lista de riesgos obtenida!\n\n";
    echo "Total de registros: " . count($data['data'] ?? []) . "\n\n";
    
    if (!empty($data['data'])) {
        echo "Primeros 3 estudiantes en riesgo:\n\n";
        foreach(array_slice($data['data'], 0, 3) as $item) {
            echo json_encode($item, JSON_PRETTY_PRINT);
            echo "\n---\n";
        }
    }
} else {
    echo "❌ Error:\n";
    echo $response;
}

echo "\n===========================================\n";







