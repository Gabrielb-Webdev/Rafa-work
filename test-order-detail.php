<?php
// Test simple para ver el detalle del pedido de usuario
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';

echo "<h1>Test de Ver Pedido - Usuario</h1>";
echo "<p>Usuario logueado: " . (isLoggedIn() ? 'Sí' : 'No') . "</p>";
echo "<p>User ID en sesión: " . ($_SESSION['user_id'] ?? 'No definido') . "</p>";
echo "<p>Order ID solicitado: " . ($_GET['id'] ?? 'No definido') . "</p>";

if (!isLoggedIn()) {
    echo "<p style='color: red;'>ERROR: No estás logueado</p>";
    exit;
}

$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    echo "<p style='color: red;'>ERROR: No se proporcionó ID de pedido</p>";
    exit;
}

try {
    $stmt = executeQuery(
        "SELECT o.* 
         FROM orders o 
         WHERE o.id = ? AND o.user_id = ?",
        [$order_id, $_SESSION['user_id']]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        echo "<p style='color: red;'>ERROR: Pedido no encontrado o no pertenece a tu usuario</p>";
        echo "<p>Buscando pedido ID: " . $order_id . " para usuario ID: " . $_SESSION['user_id'] . "</p>";
        exit;
    }
    
    echo "<h2>✓ Pedido encontrado:</h2>";
    echo "<pre>";
    print_r($order);
    echo "</pre>";
    
    echo "<p><a href='order-detail.php?id=" . $order_id . "'>Ir a la página real</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR de base de datos: " . $e->getMessage() . "</p>";
}
?>
