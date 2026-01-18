<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Script para actualizar las rutas de imágenes en la base de datos de PNG a SVG

try {
    // Actualizar productos que tengan product-placeholder.png
    $query = "UPDATE products 
              SET image = REPLACE(image, 'product-placeholder.png', 'product-placeholder.svg')
              WHERE image LIKE '%product-placeholder.png%'";
    
    executeQuery($query);
    
    echo "✓ URLs de productos actualizadas de PNG a SVG<br>";
    
    // Mostrar productos actualizados
    $check = executeQuery("SELECT id, name, image FROM products WHERE image LIKE '%placeholder%'");
    $products = $check->fetchAll();
    
    if (!empty($products)) {
        echo "<br><strong>Productos con placeholders:</strong><br>";
        foreach ($products as $product) {
            echo "- {$product['name']}: {$product['image']}<br>";
        }
    }
    
    echo "<br><strong style='color: green;'>✓ Actualización completada!</strong><br>";
    echo "<a href='index.php'>Ir al inicio</a> | <a href='products.php'>Ver productos</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>Error: " . $e->getMessage() . "</strong>";
}
?>
