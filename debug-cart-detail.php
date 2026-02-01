<?php
/**
 * Script de depuración del carrito
 * Muestra información detallada del estado del carrito
 */

session_start();
require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug Carrito</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #00d4d4; border-bottom: 2px solid #00d4d4; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #00d4d4; color: white; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f8f8f8; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #00d4d4; color: white; text-decoration: none; border-radius: 6px; margin: 5px; }
        .btn:hover { background: #00b8b8; }
    </style>
</head>
<body>";

echo "<h1>🔍 Debug del Sistema de Carrito</h1>";

// Verificar sesión
echo "<div class='box'>";
echo "<h2>1. Estado de la Sesión</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Usuario logueado:</strong> " . (isset($_SESSION['user_id']) ? "<span class='success'>SÍ (ID: {$_SESSION['user_id']})</span>" : "<span class='warning'>NO</span>") . "</p>";
echo "<p><strong>Nombre de usuario:</strong> " . ($_SESSION['user_name'] ?? 'N/A') . "</p>";
echo "</div>";

// Ver carrito en sesión
echo "<div class='box'>";
echo "<h2>2. Carrito en Sesión (\$_SESSION['cart'])</h2>";
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    echo "<p class='success'>✓ El carrito tiene " . count($_SESSION['cart']) . " producto(s)</p>";
    echo "<table>";
    echo "<tr><th>Clave</th><th>Tipo de Clave</th><th>ID Producto</th><th>Nombre</th><th>Precio</th><th>Cantidad</th><th>Imagen</th></tr>";
    foreach ($_SESSION['cart'] as $key => $item) {
        echo "<tr>";
        echo "<td><code>" . htmlspecialchars($key) . "</code></td>";
        echo "<td>" . gettype($key) . "</td>";
        echo "<td>" . ($item['id'] ?? 'N/A') . "</td>";
        echo "<td>" . ($item['name'] ?? 'N/A') . "</td>";
        echo "<td>$" . ($item['price'] ?? 0) . "</td>";
        echo "<td>" . ($item['quantity'] ?? 0) . "</td>";
        echo "<td>" . ($item['image'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Estructura completa del carrito:</h3>";
    echo "<pre>" . print_r($_SESSION['cart'], true) . "</pre>";
} else {
    echo "<p class='warning'>⚠ El carrito está vacío o no existe</p>";
    echo "<pre>" . print_r($_SESSION['cart'] ?? 'NULL', true) . "</pre>";
}
echo "</div>";

// Ver carrito en base de datos (si está logueado)
if (isset($_SESSION['user_id'])) {
    echo "<div class='box'>";
    echo "<h2>3. Carrito en Base de Datos</h2>";
    try {
        $userId = intval($_SESSION['user_id']);
        $stmt = executeQuery(
            "SELECT c.*, p.name, p.price, p.image, p.is_active 
             FROM cart c 
             LEFT JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ?",
            [$userId]
        );
        $dbCart = $stmt->fetchAll();
        
        if (!empty($dbCart)) {
            echo "<p class='success'>✓ Hay " . count($dbCart) . " producto(s) en la BD</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>User ID</th><th>Product ID</th><th>Cantidad</th><th>Nombre Producto</th><th>Activo</th><th>Fecha Creación</th></tr>";
            foreach ($dbCart as $item) {
                $isActive = $item['is_active'] ? '<span class="success">SÍ</span>' : '<span class="error">NO</span>';
                echo "<tr>";
                echo "<td>{$item['id']}</td>";
                echo "<td>{$item['user_id']}</td>";
                echo "<td>{$item['product_id']}</td>";
                echo "<td>{$item['quantity']}</td>";
                echo "<td>" . ($item['name'] ?? '<span class="error">PRODUCTO NO ENCONTRADO</span>') . "</td>";
                echo "<td>{$isActive}</td>";
                echo "<td>{$item['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠ No hay productos en el carrito de la base de datos</p>";
        }
        
        // Verificar si hay productos huérfanos (sin producto asociado)
        $stmt = executeQuery(
            "SELECT c.* FROM cart c 
             LEFT JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ? AND p.id IS NULL",
            [$userId]
        );
        $orphans = $stmt->fetchAll();
        
        if (!empty($orphans)) {
            echo "<p class='error'>⚠ Hay " . count($orphans) . " producto(s) huérfano(s) (sin producto asociado)</p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Product ID</th><th>Cantidad</th></tr>";
            foreach ($orphans as $orphan) {
                echo "<tr><td>{$orphan['id']}</td><td>{$orphan['product_id']}</td><td>{$orphan['quantity']}</td></tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error al consultar BD: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
}

// Verificar productos activos
echo "<div class='box'>";
echo "<h2>4. Productos Activos en la Base de Datos</h2>";
try {
    $stmt = executeQuery("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "<p>Total de productos activos: <strong>{$result['total']}</strong></p>";
    
    $stmt = executeQuery("SELECT id, name, price, is_active FROM products WHERE is_active = 1 LIMIT 10");
    $products = $stmt->fetchAll();
    
    if (!empty($products)) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Activo</th></tr>";
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>{$product['id']}</td>";
            echo "<td>{$product['name']}</td>";
            echo "<td>\${$product['price']}</td>";
            echo "<td>" . ($product['is_active'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><small>Mostrando máximo 10 productos</small></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test de agregar producto
echo "<div class='box'>";
echo "<h2>5. Prueba Rápida</h2>";
echo "<p>Puedes usar estos enlaces para probar:</p>";
echo "<a href='products.php' class='btn'>Ver Productos</a>";
echo "<a href='cart.php' class='btn'>Ver Carrito</a>";
if (isset($_SESSION['user_id'])) {
    echo "<a href='logout.php' class='btn' style='background: #dc3545;'>Cerrar Sesión</a>";
} else {
    echo "<a href='login.php' class='btn'>Iniciar Sesión</a>";
}
echo "</div>";

// Recomendaciones
echo "<div class='box'>";
echo "<h2>6. Diagnóstico y Recomendaciones</h2>";
$issues = [];

if (!isset($_SESSION['user_id'])) {
    $issues[] = "No estás logueado. Inicia sesión para que el carrito se guarde en la base de datos.";
}

if (empty($_SESSION['cart'] ?? [])) {
    $issues[] = "El carrito en sesión está vacío. Intenta agregar productos desde la página de productos.";
}

if (isset($_SESSION['user_id']) && !empty($orphans ?? [])) {
    $issues[] = "Hay productos en el carrito que ya no existen en la BD. Deberían limpiarse automáticamente.";
}

if (empty($issues)) {
    echo "<p class='success'>✓ Todo parece estar en orden</p>";
} else {
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li class='warning'>⚠ {$issue}</li>";
    }
    echo "</ul>";
}
echo "</div>";

echo "</body></html>";
?>
