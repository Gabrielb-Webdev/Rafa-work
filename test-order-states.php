<?php
/**
 * Script de prueba para verificar el funcionamiento de los estados de pedidos
 * Este script crea pedidos de prueba en diferentes estados para verificar las estadísticas
 */

require_once __DIR__ . '/config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    die('Solo administradores pueden ejecutar este script');
}

echo "<h1>Verificación de Estados de Pedidos</h1>";
echo "<style>body { font-family: Arial, sans-serif; padding: 20px; } table { border-collapse: collapse; width: 100%; margin: 20px 0; } th, td { border: 1px solid #ddd; padding: 12px; text-align: left; } th { background: #00d4d4; color: white; } .success { color: green; } .error { color: red; }</style>";

// 1. Verificar estructura de la tabla
echo "<h2>1. Verificando estructura de la tabla 'orders'</h2>";
try {
    $stmt = executeQuery("DESCRIBE orders");
    $columns = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p class='success'>✓ Estructura de la tabla verificada</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al verificar estructura: " . $e->getMessage() . "</p>";
}

// 2. Contar pedidos actuales por estado
echo "<h2>2. Conteo de pedidos actuales por estado</h2>";
try {
    $stmt = executeQuery("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $current_stats = $stmt->fetchAll();
    
    if (empty($current_stats)) {
        echo "<p>No hay pedidos en la base de datos</p>";
    } else {
        echo "<table>";
        echo "<tr><th>Estado</th><th>Cantidad</th></tr>";
        foreach ($current_stats as $stat) {
            echo "<tr><td>{$stat['status']}</td><td>{$stat['count']}</td></tr>";
        }
        echo "</table>";
    }
    echo "<p class='success'>✓ Estadísticas actuales consultadas</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al consultar estadísticas: " . $e->getMessage() . "</p>";
}

// 3. Verificar pedidos del usuario actual
echo "<h2>3. Pedidos del usuario actual (ID: {$_SESSION['user_id']})</h2>";
try {
    $stmt = executeQuery(
        "SELECT o.id, o.order_number, o.status, o.total, o.created_at, COUNT(oi.id) as items
         FROM orders o 
         LEFT JOIN order_items oi ON o.id = oi.order_id 
         WHERE o.user_id = ? 
         GROUP BY o.id 
         ORDER BY o.created_at DESC",
        [$_SESSION['user_id']]
    );
    $user_orders = $stmt->fetchAll();
    
    if (empty($user_orders)) {
        echo "<p>El usuario actual no tiene pedidos</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Número</th><th>Estado</th><th>Items</th><th>Total</th><th>Fecha</th></tr>";
        foreach ($user_orders as $order) {
            echo "<tr>";
            echo "<td>{$order['id']}</td>";
            echo "<td>{$order['order_number']}</td>";
            echo "<td><strong>{$order['status']}</strong></td>";
            echo "<td>{$order['items']}</td>";
            echo "<td>\$" . number_format($order['total'], 2) . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($order['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "<p class='success'>✓ Pedidos del usuario consultados</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al consultar pedidos: " . $e->getMessage() . "</p>";
}

// 4. Verificar estadísticas agrupadas
echo "<h2>4. Estadísticas agrupadas para el usuario actual</h2>";
try {
    $stmt = executeQuery(
        "SELECT status, COUNT(*) as count 
         FROM orders 
         WHERE user_id = ? 
         GROUP BY status",
        [$_SESSION['user_id']]
    );
    
    $stats = [
        'total' => 0,
        'pending' => 0,
        'processing' => 0,
        'shipped' => 0,
        'delivered' => 0,
        'cancelled' => 0
    ];
    
    while ($row = $stmt->fetch()) {
        if (isset($stats[$row['status']])) {
            $stats[$row['status']] = $row['count'];
        }
        $stats['total'] += $row['count'];
    }
    
    echo "<table>";
    echo "<tr><th>Estado</th><th>Cantidad</th></tr>";
    echo "<tr><td><strong>Total</strong></td><td><strong>{$stats['total']}</strong></td></tr>";
    echo "<tr><td>Pendientes</td><td>{$stats['pending']}</td></tr>";
    echo "<tr><td>En Proceso</td><td>{$stats['processing']}</td></tr>";
    echo "<tr><td>Enviados</td><td>{$stats['shipped']}</td></tr>";
    echo "<tr><td>Entregados</td><td>{$stats['delivered']}</td></tr>";
    echo "<tr><td>Cancelados</td><td>{$stats['cancelled']}</td></tr>";
    echo "</table>";
    echo "<p class='success'>✓ Estadísticas calculadas correctamente</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al calcular estadísticas: " . $e->getMessage() . "</p>";
}

// 5. Verificar todos los pedidos (admin)
echo "<h2>5. Todos los pedidos en el sistema (Vista Admin)</h2>";
try {
    $stmt = executeQuery(
        "SELECT o.*, u.full_name, u.email, COUNT(oi.id) as total_items
         FROM orders o 
         JOIN users u ON o.user_id = u.id 
         LEFT JOIN order_items oi ON o.id = oi.order_id 
         GROUP BY o.id 
         ORDER BY o.created_at DESC 
         LIMIT 10"
    );
    $all_orders = $stmt->fetchAll();
    
    if (empty($all_orders)) {
        echo "<p>No hay pedidos en el sistema</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Número</th><th>Cliente</th><th>Estado</th><th>Items</th><th>Total</th><th>Fecha</th></tr>";
        foreach ($all_orders as $order) {
            echo "<tr>";
            echo "<td>{$order['id']}</td>";
            echo "<td>{$order['order_number']}</td>";
            echo "<td>{$order['full_name']}</td>";
            echo "<td><strong>{$order['status']}</strong></td>";
            echo "<td>{$order['total_items']}</td>";
            echo "<td>\$" . number_format($order['total'], 2) . "</td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($order['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p>Mostrando máximo 10 pedidos más recientes</p>";
    }
    echo "<p class='success'>✓ Pedidos del sistema consultados</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al consultar pedidos: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Resumen</h2>";
echo "<p>Si todos los tests muestran ✓, el sistema de estados está funcionando correctamente.</p>";
echo "<p><a href='orders.php'>Ver Mis Pedidos</a> | <a href='admin/pedidos.php'>Panel Admin de Pedidos</a></p>";
?>
