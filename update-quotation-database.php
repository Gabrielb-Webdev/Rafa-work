<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Verificar que sea admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Acceso denegado");
}

$conn = getConnection();
$results = [];

// 1. Crear tabla order_messages
try {
    $sql = "CREATE TABLE IF NOT EXISTS order_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_proposal TINYINT(1) DEFAULT 0,
        proposal_data JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    $results[] = ['status' => 'success', 'message' => 'Tabla order_messages creada correctamente'];
} catch (PDOException $e) {
    $results[] = ['status' => 'error', 'message' => 'Error al crear order_messages: ' . $e->getMessage()];
}

// 2. Agregar campos a orders
$ordersColumns = [
    'proposal_sent' => 'TINYINT(1) DEFAULT 0',
    'proposal_date' => 'DATETIME NULL',
    'proposal_total' => 'DECIMAL(10,2) NULL',
    'proposal_accepted' => 'TINYINT(1) DEFAULT 0',
    'proposal_accepted_date' => 'DATETIME NULL'
];

foreach ($ordersColumns as $column => $definition) {
    try {
        // Verificar si la columna existe
        $stmt = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE orders ADD COLUMN $column $definition";
            $conn->exec($sql);
            $results[] = ['status' => 'success', 'message' => "Columna orders.$column agregada"];
        } else {
            $results[] = ['status' => 'info', 'message' => "Columna orders.$column ya existe"];
        }
    } catch (PDOException $e) {
        $results[] = ['status' => 'error', 'message' => "Error en orders.$column: " . $e->getMessage()];
    }
}

// 3. Agregar campos a order_items
$itemsColumns = [
    'proposed_price' => 'DECIMAL(10,2) NULL',
    'proposed_subtotal' => 'DECIMAL(10,2) NULL'
];

foreach ($itemsColumns as $column => $definition) {
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM order_items LIKE '$column'");
        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE order_items ADD COLUMN $column $definition";
            $conn->exec($sql);
            $results[] = ['status' => 'success', 'message' => "Columna order_items.$column agregada"];
        } else {
            $results[] = ['status' => 'info', 'message' => "Columna order_items.$column ya existe"];
        }
    } catch (PDOException $e) {
        $results[] = ['status' => 'error', 'message' => "Error en order_items.$column: " . $e->getMessage()];
    }
}

// 4. Modificar columnas existentes de order_items para permitir NULL
try {
    $conn->exec("ALTER TABLE order_items MODIFY COLUMN price DECIMAL(10,2) NULL");
    $results[] = ['status' => 'success', 'message' => "Columna order_items.price ahora permite NULL"];
} catch (PDOException $e) {
    $results[] = ['status' => 'info', 'message' => "order_items.price: " . $e->getMessage()];
}

try {
    $conn->exec("ALTER TABLE order_items MODIFY COLUMN subtotal DECIMAL(10,2) NULL");
    $results[] = ['status' => 'success', 'message' => "Columna order_items.subtotal ahora permite NULL"];
} catch (PDOException $e) {
    $results[] = ['status' => 'info', 'message' => "order_items.subtotal: " . $e->getMessage()];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Sistema de Cotización</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2em;
        }
        
        .result {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .result.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .result.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .result.info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
        
        .icon {
            font-size: 1.5em;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        
        .back-button:hover {
            background: #764ba2;
        }
        
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        
        .summary h2 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Actualización del Sistema de Cotización</h1>
        
        <?php 
        $successCount = 0;
        $errorCount = 0;
        $infoCount = 0;
        
        foreach ($results as $result): 
            if ($result['status'] === 'success') $successCount++;
            if ($result['status'] === 'error') $errorCount++;
            if ($result['status'] === 'info') $infoCount++;
        ?>
            <div class="result <?php echo $result['status']; ?>">
                <span class="icon">
                    <?php 
                    if ($result['status'] === 'success') echo '✅';
                    if ($result['status'] === 'error') echo '❌';
                    if ($result['status'] === 'info') echo 'ℹ️';
                    ?>
                </span>
                <span><?php echo htmlspecialchars($result['message']); ?></span>
            </div>
        <?php endforeach; ?>
        
        <div class="summary">
            <h2>Resumen</h2>
            <p><strong><?php echo $successCount; ?></strong> operaciones exitosas</p>
            <p><strong><?php echo $infoCount; ?></strong> elementos ya existían</p>
            <?php if ($errorCount > 0): ?>
                <p style="color: #dc3545;"><strong><?php echo $errorCount; ?></strong> errores encontrados</p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center;">
            <a href="/admin/" class="back-button">← Volver al Panel Admin</a>
        </div>
    </div>
</body>
</html>
