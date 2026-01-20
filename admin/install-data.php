<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    die("Acceso denegado");
}

echo "<h2>Instalando Datos de Ejemplo</h2>";
echo "<pre>";

try {
    echo "Conectando a la base de datos...\n";
    $conn = getConnection();
    echo "✓ Conexión exitosa\n\n";
    
    // Crear tablas si no existen
    echo "=== CREANDO TABLAS ===\n";
    
    // Tabla products
    echo "Creando tabla products... ";
    $conn->exec("CREATE TABLE IF NOT EXISTS `products` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `category_id` int(11) NOT NULL,
      `name` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL,
      `description` text DEFAULT NULL,
      `price` decimal(10,2) NOT NULL DEFAULT 0.00,
      `discount_price` decimal(10,2) DEFAULT NULL,
      `stock` int(11) NOT NULL DEFAULT 0,
      `image` varchar(255) DEFAULT NULL,
      `rating` decimal(2,1) DEFAULT 0.0,
      `is_featured` tinyint(1) NOT NULL DEFAULT 0,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `slug` (`slug`),
      KEY `idx_category_id` (`category_id`),
      KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓\n";
    
    // Tabla categories
    echo "Creando tabla categories... ";
    $conn->exec("CREATE TABLE IF NOT EXISTS `categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `description` text DEFAULT NULL,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓\n";
    
    // Tabla contacts
    echo "Creando tabla contacts... ";
    $conn->exec("CREATE TABLE IF NOT EXISTS `contacts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `subject` varchar(255) NOT NULL,
      `message` text NOT NULL,
      `is_read` tinyint(1) NOT NULL DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_is_read` (`is_read`),
      KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓\n";
    
    // Tabla newsletter
    echo "Creando tabla newsletter... ";
    $conn->exec("CREATE TABLE IF NOT EXISTS `newsletter` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_email` (`email`),
      KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓\n\n";
    
    // Insertar categorías
    echo "=== INSERTANDO CATEGORÍAS ===\n";
    $categories = [
        ['Suplementos', 'suplementos', 'Suplementos alimenticios y nutricionales'],
        ['Vitaminas', 'vitaminas', 'Vitaminas y minerales esenciales'],
        ['Proteínas', 'proteinas', 'Proteínas en polvo y batidos'],
        ['Salud General', 'salud-general', 'Productos para el bienestar general'],
        ['Deportivos', 'deportivos', 'Suplementos para deportistas']
    ];
    
    foreach ($categories as $cat) {
        try {
            executeQuery(
                "INSERT INTO categories (name, slug, description, is_active) VALUES (?, ?, ?, 1)",
                $cat
            );
            echo "✓ Categoría: {$cat[0]}\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "- Categoría ya existe: {$cat[0]}\n";
            } else {
                echo "✗ Error: {$cat[0]} - " . $e->getMessage() . "\n";
            }
        }
    }
    echo "\n";
    
    // Insertar productos
    echo "=== INSERTANDO PRODUCTOS ===\n";
    
    // Primero obtener los IDs de las categorías
    $categoryIds = [];
    $stmt = executeQuery("SELECT id, name FROM categories");
    while ($cat = $stmt->fetch()) {
        $categoryIds[$cat['name']] = $cat['id'];
    }
    
    $products = [
        ['Multivitamínico Premium', 'multi-vitaminico-premium', 'Complejo multivitamínico completo con minerales esenciales', 1299.00, 50, 'Vitaminas'],
        ['Proteína Whey', 'proteina-whey', 'Proteína de suero de leche de alta calidad, sabor vainilla', 2499.00, 30, 'Proteínas'],
        ['Omega 3', 'omega-3', 'Aceite de pescado rico en EPA y DHA para salud cardiovascular', 899.00, 75, 'Salud General'],
        ['Vitamina D3', 'vitamina-d3', 'Vitamina D3 de alta potencia para huesos y sistema inmune', 699.00, 100, 'Vitaminas'],
        ['BCAA 2:1:1', 'bcaa-211', 'Aminoácidos ramificados para recuperación muscular', 1599.00, 40, 'Deportivos'],
        ['Colágeno Hidrolizado', 'colageno-hidrolizado', 'Colágeno tipo 1 y 3 para piel, cabello y articulaciones', 1199.00, 60, 'Salud General'],
        ['Creatina Monohidrato', 'creatina-monohidrato', 'Creatina micronizada de alta pureza para fuerza y rendimiento', 799.00, 80, 'Deportivos'],
        ['Magnesio Complex', 'magnesio-complex', 'Complejo de magnesio con vitamina B6 para músculos y nervios', 549.00, 90, 'Vitaminas'],
        ['Pre-Workout Energía', 'pre-workout-energia', 'Fórmula pre-entrenamiento con cafeína y beta-alanina', 1899.00, 35, 'Deportivos'],
        ['Probióticos Advanced', 'probioticos-advanced', 'Probióticos de 10 cepas para salud digestiva e inmune', 1399.00, 45, 'Salud General']
    ];
    
    foreach ($products as $prod) {
        try {
            $categoryName = $prod[5];
            $categoryId = $categoryIds[$categoryName] ?? 1; // Default a 1 si no existe
            
            executeQuery(
                "INSERT INTO products (category_id, name, slug, description, price, stock, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)",
                [$categoryId, $prod[0], $prod[1], $prod[2], $prod[3], $prod[4]]
            );
            echo "✓ Producto: {$prod[0]} - \${$prod[3]} (Categoría: {$categoryName})\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                echo "- Producto ya existe: {$prod[0]}\n";
            } else {
                echo "✗ Error: {$prod[0]} - " . $e->getMessage() . "\n";
            }
        }
    }
    echo "\n";
    
    // Verificar resultados
    echo "=== VERIFICACIÓN FINAL ===\n";
    $stmt = executeQuery("SELECT COUNT(*) as total FROM categories");
    $total = $stmt->fetch()['total'];
    echo "Total categorías: $total\n";
    
    $stmt = executeQuery("SELECT COUNT(*) as total FROM products");
    $total = $stmt->fetch()['total'];
    echo "Total productos: $total\n";
    
    echo "\n✓✓✓ INSTALACIÓN COMPLETADA ✓✓✓\n";
    echo "\n<a href='/admin/index.php' style='display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;margin-top:20px;'>Ir al Panel de Administración</a>";
    
} catch (Exception $e) {
    echo "\n✗✗✗ ERROR ✗✗✗\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
