<?php
require_once __DIR__ . '/config/config.php';

// Solo permitir acceso a administradores
if (!isLoggedIn() || !isAdmin()) {
    die('Acceso denegado. Solo administradores pueden ejecutar este script.');
}

$pageTitle = 'Actualizar Base de Datos - Dirección de Envío';
$results = [];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_database'])) {
    try {
        $conn = getConnection();
        
        // 1. Obtener columnas actuales
        $stmt = $conn->query("SHOW COLUMNS FROM orders");
        $currentColumns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $currentColumns[] = $row['Field'];
        }
        
        // 2. Verificar si ya está actualizado
        $stmt = $conn->query("SHOW COLUMNS FROM orders LIKE 'street'");
        $alreadyUpdated = $stmt->fetch();
        
        if ($alreadyUpdated) {
            $errors[] = 'Las columnas ya existen en la base de datos. No es necesario actualizar.';
        } else {
            $updates = [];
            
            // 3. Agregar columnas que faltan
            if (!in_array('full_name', $currentColumns)) {
                $conn->exec("ALTER TABLE `orders` ADD COLUMN `full_name` VARCHAR(255) AFTER `order_number`");
                $updates[] = '✓ Agregada columna: full_name';
            }
            
            if (!in_array('email', $currentColumns)) {
                $conn->exec("ALTER TABLE `orders` ADD COLUMN `email` VARCHAR(255) AFTER `full_name`");
                $updates[] = '✓ Agregada columna: email';
            }
            
            if (!in_array('phone', $currentColumns)) {
                $conn->exec("ALTER TABLE `orders` ADD COLUMN `phone` VARCHAR(50) AFTER `email`");
                $updates[] = '✓ Agregada columna: phone';
            }
            
            // 4. Agregar columnas de dirección separada
            $conn->exec("ALTER TABLE `orders` ADD COLUMN `street` VARCHAR(255) AFTER `phone`");
            $updates[] = '✓ Agregada columna: street';
            
            $conn->exec("ALTER TABLE `orders` ADD COLUMN `street_number` VARCHAR(50) AFTER `street`");
            $updates[] = '✓ Agregada columna: street_number';
            
            $conn->exec("ALTER TABLE `orders` ADD COLUMN `neighborhood` VARCHAR(255) AFTER `street_number`");
            $updates[] = '✓ Agregada columna: neighborhood';
            
            $conn->exec("ALTER TABLE `orders` ADD COLUMN `city` VARCHAR(255) AFTER `neighborhood`");
            $updates[] = '✓ Agregada columna: city';
            
            $conn->exec("ALTER TABLE `orders` ADD COLUMN `postal_code` VARCHAR(20) AFTER `city`");
            $updates[] = '✓ Agregada columna: postal_code';
            
            // 5. Agregar columnas de montos si no existen
            if (!in_array('subtotal', $currentColumns)) {
                $conn->exec("ALTER TABLE `orders` ADD COLUMN `subtotal` DECIMAL(10,2) AFTER `postal_code`");
                $updates[] = '✓ Agregada columna: subtotal';
            }
            
            if (!in_array('shipping', $currentColumns)) {
                $conn->exec("ALTER TABLE `orders` ADD COLUMN `shipping` DECIMAL(10,2) AFTER `subtotal`");
                $updates[] = '✓ Agregada columna: shipping';
            }
            
            if (!in_array('total', $currentColumns)) {
                $conn->exec("ALTER TABLE `orders` ADD COLUMN `total` DECIMAL(10,2) AFTER `shipping`");
                $updates[] = '✓ Agregada columna: total';
            }
            
            $results = $updates;
            
            // 6. Notas sobre columnas antiguas
            if (in_array('shipping_address', $currentColumns)) {
                $results[] = 'ℹ Columna antigua "shipping_address" detectada (ahora se usan campos separados)';
            }
            if (in_array('shipping_phone', $currentColumns)) {
                $results[] = 'ℹ Columna antigua "shipping_phone" detectada (ahora se usa "phone")';
            }
            if (in_array('total_amount', $currentColumns)) {
                $results[] = 'ℹ Columna antigua "total_amount" detectada (ahora se usa "total")';
            }
            
            $success = true;
        }
        
    } catch (PDOException $e) {
        $errors[] = 'Error al actualizar la base de datos: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 700px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 40px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-box h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .info-box ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-box li {
            padding: 8px 0;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-box li i {
            color: #667eea;
            width: 20px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            line-height: 1.6;
        }
        
        .alert i {
            font-size: 20px;
            margin-top: 2px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .results {
            margin: 20px 0;
        }
        
        .result-item {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            background: #e7f3ff;
            color: #004085;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .success-animation {
            text-align: center;
            padding: 40px 0;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease;
        }
        
        .success-icon i {
            font-size: 48px;
            color: white;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .footer {
            padding: 20px 40px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-database"></i>
            <h1>Actualizar Base de Datos</h1>
            <p>Actualización de campos de dirección de envío</p>
        </div>
        
        <div class="content">
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?php echo $error; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-animation">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2 style="color: #28a745; margin-bottom: 10px;">¡Actualización Exitosa!</h2>
                    <p style="color: #6c757d;">La base de datos ha sido actualizada correctamente</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($results)): ?>
                <div class="results">
                    <?php foreach ($results as $result): ?>
                        <div class="result-item">
                            <i class="fas fa-check-circle"></i>
                            <div><?php echo $result; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success && empty($errors)): ?>
                <div class="info-box">
                    <h3>Esta actualización agregará/actualizará los siguientes campos:</h3>
                    <ul>
                        <li><i class="fas fa-plus"></i> <strong>full_name</strong> - Nombre completo del cliente</li>
                        <li><i class="fas fa-plus"></i> <strong>email</strong> - Email del cliente</li>
                        <li><i class="fas fa-plus"></i> <strong>phone</strong> - Teléfono</li>
                        <li><i class="fas fa-plus"></i> <strong>street</strong> - Calle</li>
                        <li><i class="fas fa-plus"></i> <strong>street_number</strong> - Número</li>
                        <li><i class="fas fa-plus"></i> <strong>neighborhood</strong> - Colonia</li>
                        <li><i class="fas fa-plus"></i> <strong>city</strong> - Ciudad</li>
                        <li><i class="fas fa-plus"></i> <strong>postal_code</strong> - Código Postal</li>
                        <li><i class="fas fa-plus"></i> <strong>subtotal</strong> - Subtotal del pedido</li>
                        <li><i class="fas fa-plus"></i> <strong>shipping</strong> - Costo de envío</li>
                        <li><i class="fas fa-plus"></i> <strong>total</strong> - Total del pedido</li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Importante:</strong> Esta operación modificará la estructura de la tabla "orders" en la base de datos. 
                        Se recomienda hacer un respaldo antes de continuar.
                    </div>
                </div>
                
                <form method="POST">
                    <button type="submit" name="update_database" class="btn btn-primary">
                        <i class="fas fa-bolt"></i>
                        Ejecutar Actualización
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <a href="<?php echo BASE_URL; ?>/admin/index.php">
                <i class="fas fa-arrow-left"></i> Volver al Panel Admin
            </a>
            <?php if ($success): ?>
                &nbsp;|&nbsp;
                <a href="<?php echo BASE_URL; ?>/checkout.php">
                    <i class="fas fa-shopping-cart"></i> Probar Checkout
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
