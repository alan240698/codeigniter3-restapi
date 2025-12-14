<?php
// Quick diagnostic for Rules API
echo "<h1>Rules API Diagnostic</h1>";
echo "<pre>";

// Test Approval Rules
echo "\n=== Testing Approval Rules API ===\n";
$ch = curl_init('http://codeigniter3-restapi.test/approval-rules');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test Level Rules
echo "=== Testing Level Rules API ===\n";
$ch = curl_init('http://codeigniter3-restapi.test/level-rules');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test Custom Fields
echo "=== Testing Custom Fields API ===\n";
$ch = curl_init('http://codeigniter3-restapi.test/custom-fields');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

echo "</pre>";
