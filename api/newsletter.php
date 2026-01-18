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
$email = sanitizeInput($data['email'] ?? '');

if (empty($email)) {
    $response['message'] = 'Por favor ingresa un email';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Email inválido';
    echo json_encode($response);
    exit;
}

try {
    // Verificar si ya está suscrito
    $stmt = executeQuery("SELECT id FROM newsletter_subscriptions WHERE email = ?", [$email]);
    if ($stmt->fetch()) {
        $response['message'] = 'Este email ya está suscrito';
        echo json_encode($response);
        exit;
    }
    
    // Suscribir
    executeQuery("INSERT INTO newsletter_subscriptions (email) VALUES (?)", [$email]);
    
    $response['success'] = true;
    $response['message'] = '¡Gracias por suscribirte!';
} catch (Exception $e) {
    $response['message'] = 'Error al procesar la suscripción';
}

echo json_encode($response);
