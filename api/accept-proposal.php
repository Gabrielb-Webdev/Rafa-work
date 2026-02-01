<?php
/**
 * API to Accept Proposal
 * Version: 1.0
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $order_id = (int)($_POST['order_id'] ?? 0);
    
    if (!$order_id) {
        throw new Exception('Invalid order ID');
    }
    
    // Verify that order belongs to user
    $stmt = executeQuery(
        "SELECT id, user_id, proposal_sent, status 
         FROM orders 
         WHERE id = ? AND user_id = ?",
        [$order_id, $_SESSION['user_id']]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    if (!$order['proposal_sent']) {
        throw new Exception('No pending proposal for this order');
    }
    
    if ($order['status'] === 'delivered' || $order['status'] === 'cancelled') {
        throw new Exception('This order has already been completed');
    }
    
    // Update order status
    executeQuery(
        "UPDATE orders 
         SET status = 'processing',
             proposal_accepted = 1,
             proposal_accepted_date = NOW()
         WHERE id = ?",
        [$order_id]
    );
    
    // Send chat message notifying acceptance
    try {
        executeQuery(
            "INSERT INTO order_messages (order_id, user_id, message, created_at) 
             VALUES (?, ?, ?, NOW())",
            [$order_id, $_SESSION['user_id'], '✅ I have accepted the proposal. Proceeding with the order.']
        );
    } catch (Exception $e) {
        // If message fails, continue anyway
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Proposal accepted successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
