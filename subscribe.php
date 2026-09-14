<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!empty($_POST['company'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$phone = trim((string) ($_POST['phone'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

$payload = json_encode([
    'name' => mb_substr($name, 0, 100),
    'email' => mb_substr($email, 0, 190),
    'phone' => mb_substr($phone, 0, 30),
]);

$endpoint = 'https://script.google.com/macros/s/AKfycbxvxyKnkNLbZIHiYWadMT4iOvK4USU-5zmj6CtwPEj9xuIQYNufAuxKJWVkQwJbg5mrfA/exec';
$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$result = is_string($response) ? json_decode($response, true) : null;
if ($curlError || $httpCode < 200 || $httpCode >= 300 || !is_array($result) || empty($result['ok'])) {
    error_log('Email community signup forwarding failed: ' . ($curlError ?: (string) $response));
    http_response_code(502);
    echo json_encode(['ok' => false, 'message' => 'Unable to save your signup right now']);
    exit;
}

echo json_encode(['ok' => true, 'duplicate' => !empty($result['duplicate'])]);
