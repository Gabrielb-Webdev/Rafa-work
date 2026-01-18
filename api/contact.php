<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido';
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
    $response['message'] = 'Por favor completa todos los campos requeridos';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Email inválido';
    echo json_encode($response);
    exit;
}

try {
    executeQuery(
        "INSERT INTO contact_requests (name, phone, email, medicine, message) VALUES (?, ?, ?, ?, ?)",
        [$name, $phone, $email, $medicine, $message]
    );
    
    $response['success'] = true;
    $response['message'] = 'Solicitud enviada exitosamente';
} catch (Exception $e) {
    $response['message'] = 'Error al enviar la solicitud';
}

echo json_encode($response);
