<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$name = sanitizeInput($data['name'] ?? '');
$phone = sanitizeInput($data['phone'] ?? '');
$email = sanitizeInput($data['email'] ?? '');
$medicine = sanitizeInput($data['medicine'] ?? '');
$message = sanitizeInput($data['message'] ?? '');

if (empty($name) || empty($phone) || empty($email)) {
    $response['message'] = 'Please complete all required fields';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email';
    echo json_encode($response);
    exit;
}

try {
    executeQuery(
        "INSERT INTO contact_requests (name, phone, email, medicine, message) VALUES (?, ?, ?, ?, ?)",
        [$name, $phone, $email, $medicine, $message]
    );
    
    $response['success'] = true;
    $response['message'] = 'Request sent successfully';
} catch (Exception $e) {
    $response['message'] = 'Error sending request';
}

echo json_encode($response);
