<?php
// Test simple para ver el detalle del pedido
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';

echo "<h1>Test de Ver Pedido - Admin</h1>";
echo "<p>Usuario logueado: " . (isLoggedIn() ? 'Sí' : 'No') . "</p>";
echo "<p>Es admin: " . (isAdmin() ? 'Sí' : 'No') . "</p>";
echo "<p>Order ID: " . ($_GET['id'] ?? 'No definido') . "</p>";

if (!isLoggedIn() || !isAdmin()) {
    echo "<p style='color: red;'>ERROR: No estás logueado o no eres admin</p>";
    exit;
}

$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    echo "<p style='color: red;'>ERROR: No se proporcionó ID de pedido</p>";
    exit;
}

try {
    $stmt = executeQuery(
        "SELECT o.*, u.full_name as user_name, u.email as user_email 
         FROM orders o 
         JOIN users u ON o.user_id = u.id 
         WHERE o.id = ?",
        [$order_id]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        echo "<p style='color: red;'>ERROR: Pedido no encontrado</p>";
        exit;
    }
    
    echo "<h2>✓ Pedido encontrado:</h2>";
    echo "<pre>";
    print_r($order);
    echo "</pre>";
    
    echo "<p><a href='ver-pedido.php?id=" . $order_id . "'>Ir a la página real</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR de base de datos: " . $e->getMessage() . "</p>";
}
?>
