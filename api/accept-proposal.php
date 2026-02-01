<?php
/**
 * API para Aceptar Propuesta
 * Version: 1.0
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $order_id = (int)($_POST['order_id'] ?? 0);
    
    if (!$order_id) {
        throw new Exception('ID de pedido no válido');
    }
    
    // Verificar que el pedido pertenezca al usuario
    $stmt = executeQuery(
        "SELECT id, user_id, proposal_sent, status 
         FROM orders 
         WHERE id = ? AND user_id = ?",
        [$order_id, $_SESSION['user_id']]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Pedido no encontrado');
    }
    
    if (!$order['proposal_sent']) {
        throw new Exception('No hay propuesta pendiente para este pedido');
    }
    
    if ($order['status'] === 'delivered' || $order['status'] === 'cancelled') {
        throw new Exception('Este pedido ya ha sido finalizado');
    }
    
    // Actualizar el estado del pedido
    executeQuery(
        "UPDATE orders 
         SET status = 'processing',
             proposal_accepted = 1,
             proposal_accepted_date = NOW()
         WHERE id = ?",
        [$order_id]
    );
    
    // Enviar mensaje en el chat notificando la aceptación
    try {
        executeQuery(
            "INSERT INTO order_messages (order_id, user_id, message, created_at) 
             VALUES (?, ?, ?, NOW())",
            [$order_id, $_SESSION['user_id'], '✅ He aceptado la propuesta. Procedo con el pedido.']
        );
    } catch (Exception $e) {
        // Si falla el mensaje, continuar igual
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Propuesta aceptada correctamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
