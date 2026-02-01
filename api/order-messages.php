<?php
/**
 * API de Mensajes de Pedidos
 * Version: 1.0
 * Fecha: 31/01/2026
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Verificar que esté logueado
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'send':
        $order_id = (int)($_POST['order_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        
        if (!$order_id || !$message) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        try {
            // Verificar que el pedido pertenezca al usuario o que sea admin
            if (!isAdmin()) {
                $stmt = executeQuery(
                    "SELECT id FROM orders WHERE id = ? AND user_id = ?",
                    [$order_id, $_SESSION['user_id']]
                );
                $order = $stmt->fetch();
                
                if (!$order) {
                    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
                    exit;
                }
            }
            
            // Insertar mensaje
            $stmt = executeQuery(
                "INSERT INTO order_messages (order_id, user_id, message, created_at) 
                 VALUES (?, ?, ?, NOW())",
                [$order_id, $_SESSION['user_id'], $message]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Mensaje enviado correctamente'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'get':
        $order_id = (int)($_GET['order_id'] ?? 0);
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Order ID requerido']);
            exit;
        }
        
        try {
            // Verificar permisos
            if (!isAdmin()) {
                $stmt = executeQuery(
                    "SELECT id FROM orders WHERE id = ? AND user_id = ?",
                    [$order_id, $_SESSION['user_id']]
                );
                $order = $stmt->fetch();
                
                if (!$order) {
                    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
                    exit;
                }
            }
            
            // Obtener mensajes
            $stmt = executeQuery(
                "SELECT om.*, u.full_name as sender_name, u.user_role
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
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
?>
