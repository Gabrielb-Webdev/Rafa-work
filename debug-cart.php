<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>Debug del Carrito</h2>";
echo "<pre>";

echo "=== CONTENIDO DE \$_SESSION['cart'] ===\n";
print_r($_SESSION['cart'] ?? 'Carrito vacío');

echo "\n\n=== CLAVES DEL CARRITO ===\n";
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
        echo "Clave: $key (tipo: " . gettype($key) . ") => Producto ID: " . ($item['id'] ?? 'NO DEFINIDO') . "\n";
    }
}

echo "\n\n=== BOTÓN DE LIMPIEZA ===\n";
echo '<form method="post"><button type="submit" name="clear_cart">Limpiar Carrito</button></form>';

if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    echo "\n✓ Carrito limpiado. <a href='debug-cart.php'>Recargar</a>";
}

echo "</pre>";
?>
