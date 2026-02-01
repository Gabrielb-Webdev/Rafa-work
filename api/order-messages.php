<?php
/**
 * Order Messages API
 * Version: 1.0
 * Date: 01/31/2026
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// If POST, send message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if (!$order_id || !$message) {
        echo json_encode(['success' => false, 'message' => 'Incomplete data']);
        exit;
    }
    
    try {
        // Verify that order belongs to user or is admin
        if (!isAdmin()) {
            $stmt = executeQuery(
                "SELECT id FROM orders WHERE id = ? AND user_id = ?",
                [$order_id, $_SESSION['user_id']]
            );
            $order = $stmt->fetch();
            
            if (!$order) {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
                exit;
            }
        }
        
        // Insert message
        $stmt = executeQuery(
            "INSERT INTO order_messages (order_id, user_id, message, created_at) 
             VALUES (?, ?, ?, NOW())",
            [$order_id, $_SESSION['user_id'], $message]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error sending message: ' . $e->getMessage()
        ]);
    }
    exit;
}
    
    // If GET, get messages
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $order_id = (int)($_GET['order_id'] ?? 0);
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            exit;
        }
        
        try {
            // Verify permissions
            if (!isAdmin()) {
                $stmt = executeQuery(
                    "SELECT id FROM orders WHERE id = ? AND user_id = ?",
                    [$order_id, $_SESSION['user_id']]
                );
                $order = $stmt->fetch();
                
                if (!$order) {
                    echo json_encode(['success' => false, 'message' => 'Order not found']);
                    exit;
                }
            }
            
            // Get messages
            $stmt = executeQuery(
                "SELECT om.*, u.full_name as sender_name, u.role as user_role
                 FROM order_messages om
                 JOIN users u ON om.user_id = u.id
                 WHERE om.order_id = ?
                 ORDER BY om.created_at ASC",
                [$order_id]
            );
            $messages = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener mensajes: ' . $e->getMessage()
            ]);
        }
        exit;
    }
?>
