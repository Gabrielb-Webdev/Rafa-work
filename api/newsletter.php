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
$email = sanitizeInput($data['email'] ?? '');

if (empty($email)) {
    $response['message'] = 'Please enter an email';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email';
    echo json_encode($response);
    exit;
}

try {
    // Check if already subscribed
    $stmt = executeQuery("SELECT id FROM newsletter_subscriptions WHERE email = ?", [$email]);
    if ($stmt->fetch()) {
        $response['message'] = 'This email is already subscribed';
        echo json_encode($response);
        exit;
    }
    
    // Subscribe
    executeQuery("INSERT INTO newsletter_subscriptions (email) VALUES (?)", [$email]);
    
    $response['success'] = true;
    $response['message'] = 'Thanks for subscribing!';
} catch (Exception $e) {
    $response['message'] = 'Error processing subscription';
}

echo json_encode($response);
