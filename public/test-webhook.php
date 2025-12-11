<?php
// Arquivo de teste para o novo webhook

$url = 'http://localhost/webhook/backoffice'; // Nova rota do webhook

$data = [
    'payment' => [
        'id' => 'pay_test_123456',
        'status' => 'CONFIRMED',
        'transactionReceiptUrl' => 'https://example.com/receipt.pdf'
    ]
];

$json = json_encode($data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

curl_close($ch);
?> 