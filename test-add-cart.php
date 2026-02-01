<?php
/**
 * Test de Agregar al Carrito - Debug Completo
 * Version: 1.0
 */
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Agregar al Carrito</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #00d4d4; border-bottom: 2px solid #00d4d4; padding-bottom: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f8f8f8; padding: 10px; border-radius: 4px; overflow-x: auto; }
        button { padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; margin: 5px; }
        button:hover { background: #218838; }
        .output { border: 2px solid #00d4d4; padding: 15px; margin: 10px 0; border-radius: 6px; }
    </style>
    <script>
        function testAddToCart(productId, quantity) {
            const outputDiv = document.getElementById('output');
            outputDiv.innerHTML = '<p>Enviando solicitud...</p>';
            
            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=add&product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta:', data);
                outputDiv.innerHTML = '<h3>Respuesta del API:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                if (data.success) {
                    outputDiv.innerHTML += '<p class=\"success\">✓ Producto agregado exitosamente</p>';
                    outputDiv.innerHTML += '<p><strong>Cantidad en carrito:</strong> ' + data.cartCount + '</p>';
                    setTimeout(() => location.reload(), 2000);
                } else {
                    outputDiv.innerHTML += '<p class=\"error\">✗ Error: ' + data.message + '</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                outputDiv.innerHTML = '<p class=\"error\">✗ Error de conexión: ' + error + '</p>';
            });
        }
        
        function testGetCart() {
            const outputDiv = document.getElementById('output');
            outputDiv.innerHTML = '<p>Obteniendo carrito...</p>';
            
            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=get'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Carrito:', data);
                outputDiv.innerHTML = '<h3>Contenido del Carrito:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                console.error('Error:', error);
                outputDiv.innerHTML = '<p class=\"error\">✗ Error: ' + error + '</p>';
            });
        }
    </script>
</head>
<body>";

echo "<h1>🧪 Test de Agregar al Carrito</h1>";

// 1. Estado de sesión
echo "<div class='box'>";
echo "<h2>1. Estado de Sesión</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Usuario logueado:</strong> " . (isset($_SESSION['user_id']) ? "<span class='success'>SÍ (ID: {$_SESSION['user_id']})</span>" : "<span class='warning'>NO</span>") . "</p>";
if (isset($_SESSION['user_id'])) {
    echo "<p><strong>Nombre:</strong> " . ($_SESSION['user_name'] ?? 'N/A') . "</p>";
}
echo "</div>";

// 2. Carrito actual
echo "<div class='box'>";
echo "<h2>2. Carrito Actual en Sesión</h2>";
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    echo "<p class='success'>✓ Carrito tiene " . count($_SESSION['cart']) . " producto(s)</p>";
    echo "<pre>" . print_r($_SESSION['cart'], true) . "</pre>";
    $total_items = array_sum(array_column($_SESSION['cart'], 'quantity'));
    echo "<p><strong>Total de items:</strong> {$total_items}</p>";
} else {
    echo "<p class='warning'>⚠ Carrito vacío</p>";
}
echo "</div>";

// 3. Productos disponibles
echo "<div class='box'>";
echo "<h2>3. Productos Disponibles para Prueba</h2>";
try {
    $stmt = executeQuery("SELECT id, name, price, is_active FROM products WHERE is_active = 1 LIMIT 5");
    $products = $stmt->fetchAll();
    
    if (!empty($products)) {
        echo "<table style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th style='padding: 10px; border: 1px solid #ddd;'>ID</th><th style='padding: 10px; border: 1px solid #ddd;'>Nombre</th><th style='padding: 10px; border: 1px solid #ddd;'>Precio</th><th style='padding: 10px; border: 1px solid #ddd;'>Acción</th></tr>";
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$product['id']}</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>{$product['name']}</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>\${$product['price']}</td>";
            echo "<td style='padding: 10px; border: 1px solid #ddd;'>";
            echo "<button onclick=\"testAddToCart({$product['id']}, 1)\">+1</button>";
            echo "<button onclick=\"testAddToCart({$product['id']}, 5)\">+5</button>";
            echo "<button onclick=\"testAddToCart({$product['id']}, 10)\">+10</button>";
            echo "<button onclick=\"testAddToCart({$product['id']}, 100)\">+100</button>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>✗ No hay productos activos</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 4. Área de output
echo "<div class='box'>";
echo "<h2>4. Resultados de Prueba</h2>";
echo "<button onclick=\"testGetCart()\">🔄 Obtener Carrito Actual</button>";
echo "<button onclick=\"location.reload()\">🔄 Recargar Página</button>";
echo "<a href='cart.php' style='display: inline-block; padding: 12px 24px; background: #00d4d4; color: white; text-decoration: none; border-radius: 6px; margin: 5px;'>Ver Carrito</a>";
echo "<div id='output' class='output'>";
echo "<p>Haz clic en los botones de arriba para probar agregar productos</p>";
echo "</div>";
echo "</div>";

// 5. Verificar tabla cart
if (isset($_SESSION['user_id'])) {
    echo "<div class='box'>";
    echo "<h2>5. Carrito en Base de Datos</h2>";
    try {
        $stmt = executeQuery("SELECT * FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
        $dbCart = $stmt->fetchAll();
        
        if (!empty($dbCart)) {
            echo "<p class='success'>✓ Hay " . count($dbCart) . " producto(s) en BD</p>";
            echo "<pre>" . print_r($dbCart, true) . "</pre>";
        } else {
            echo "<p class='warning'>⚠ No hay productos en BD</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
}

echo "</body></html>";
?>
