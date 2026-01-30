<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Error</h1>";
echo "<p>Paso 1: PHP funcionando... ✓</p>";

try {
    require_once __DIR__ . '/../config/config.php';
    echo "<p>Paso 2: config.php cargado... ✓</p>";
} catch (Exception $e) {
    die("<p style='color:red'>ERROR en config.php: " . $e->getMessage() . "</p>");
}

echo "<p>Paso 3: Verificando sesión...</p>";
echo "<p>Sesión iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? 'Sí ✓' : 'No ✗') . "</p>";

echo "<p>Paso 4: Verificando funciones...</p>";
echo "<p>isLoggedIn existe: " . (function_exists('isLoggedIn') ? 'Sí ✓' : 'No ✗') . "</p>";
echo "<p>isAdmin existe: " . (function_exists('isAdmin') ? 'Sí ✓' : 'No ✗') . "</p>";
echo "<p>executeQuery existe: " . (function_exists('executeQuery') ? 'Sí ✓' : 'No ✗') . "</p>";

echo "<p>Paso 5: Verificando usuario...</p>";
echo "<p>Usuario logueado: " . (isLoggedIn() ? 'Sí ✓' : 'No ✗') . "</p>";
if (isLoggedIn()) {
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'No definido') . "</p>";
    echo "<p>Es admin: " . (isAdmin() ? 'Sí ✓' : 'No ✗') . "</p>";
}

echo "<p>Paso 6: Probando consulta a BD...</p>";
try {
    $stmt = executeQuery("SELECT COUNT(*) as total FROM orders");
    $result = $stmt->fetch();
    echo "<p>Conexión BD: ✓ (Total pedidos: " . $result['total'] . ")</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>ERROR de BD: " . $e->getMessage() . "</p>";
}

echo "<p>Paso 7: Verificando GET...</p>";
$order_id = $_GET['id'] ?? 0;
echo "<p>Order ID recibido: " . $order_id . "</p>";

if ($order_id) {
    echo "<p>Paso 8: Intentando cargar pedido...</p>";
    try {
        $stmt = executeQuery(
            "SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?",
            [$order_id]
        );
        $order = $stmt->fetch();
        
        if ($order) {
            echo "<p>Pedido encontrado: ✓</p>";
            echo "<pre>";
            print_r($order);
            echo "</pre>";
        } else {
            echo "<p style='color:orange'>Pedido no encontrado</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>ERROR al cargar pedido: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>✓ Todos los pasos completados sin error fatal</h2>";
echo "<p>El problema debe estar en otro archivo. Verifica:</p>";
echo "<ul>";
echo "<li>admin/header.php</li>";
echo "<li>admin/footer.php</li>";
echo "<li>Posibles caracteres invisibles en ver-pedido.php</li>";
echo "</ul>";
?>
