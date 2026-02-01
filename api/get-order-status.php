<?php
/**
 * API para obtener estado del pedido
 * Version: 1.0
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido requerido']);
    exit;
}

try {
    // Verificar permisos
    if (isAdmin()) {
        $stmt = executeQuery(
            "SELECT status, proposal_sent FROM orders WHERE id = ?",
            [$order_id]
        );
    } else {
        $stmt = executeQuery(
            "SELECT status, proposal_sent FROM orders WHERE id = ? AND user_id = ?",
            [$order_id, $_SESSION['user_id']]
        );
    }
    
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'status' => $order['status'],
        'proposal_sent' => (bool)$order['proposal_sent']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener estado: ' . $e->getMessage()
    ]);
}
?>
