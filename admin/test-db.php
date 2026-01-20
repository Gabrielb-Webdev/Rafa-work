<?php
require_once __DIR__ . '/../config/config.php';

echo "<h2>Test de Conexión a Base de Datos</h2>";
echo "<pre>";

try {
    // Test 1: Conexión básica
    echo "✓ Conexión establecida\n\n";
    
    // Test 2: Verificar tablas
    echo "=== TABLAS DISPONIBLES ===\n";
    $stmt = executeQuery("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "\n";
    
    // Test 3: Contar productos
    echo "=== PRODUCTOS ===\n";
    try {
        $stmt = executeQuery("SELECT COUNT(*) as total FROM products");
        $result = $stmt->fetch();
        echo "Total productos: " . $result['total'] . "\n";
        
        $stmt = executeQuery("SELECT * FROM products LIMIT 5");
        $products = $stmt->fetchAll();
        foreach ($products as $p) {
            echo "  - ID {$p['id']}: {$p['name']} - \${$p['price']}\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 4: Contar categorías
    echo "=== CATEGORÍAS ===\n";
    try {
        $stmt = executeQuery("SELECT COUNT(*) as total FROM categories");
        $result = $stmt->fetch();
        echo "Total categorías: " . $result['total'] . "\n";
        
        $stmt = executeQuery("SELECT * FROM categories");
        $cats = $stmt->fetchAll();
        foreach ($cats as $c) {
            echo "  - ID {$c['id']}: {$c['name']}\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 5: Contar pedidos
    echo "=== PEDIDOS ===\n";
    try {
        $stmt = executeQuery("SELECT COUNT(*) as total FROM orders");
        $result = $stmt->fetch();
        echo "Total pedidos: " . $result['total'] . "\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 6: Contar usuarios
    echo "=== USUARIOS ===\n";
    try {
        $stmt = executeQuery("SELECT COUNT(*) as total FROM users");
        $result = $stmt->fetch();
        echo "Total usuarios: " . $result['total'] . "\n";
        
        $stmt = executeQuery("SELECT id, full_name, email, role FROM users");
        $users = $stmt->fetchAll();
        foreach ($users as $u) {
            echo "  - {$u['full_name']} ({$u['role']})\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
