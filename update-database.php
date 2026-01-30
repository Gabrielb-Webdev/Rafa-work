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
        
        // 1. Verificar si las columnas ya existen
        $stmt = $conn->query("SHOW COLUMNS FROM orders LIKE 'street'");
        $columnExists = $stmt->fetch();
        
        if ($columnExists) {
            $errors[] = 'Las columnas ya existen en la base de datos. No es necesario actualizar.';
        } else {
            // 2. Agregar las nuevas columnas
            $sql = "ALTER TABLE `orders` 
                    ADD COLUMN `street` VARCHAR(255) AFTER `phone`,
                    ADD COLUMN `street_number` VARCHAR(50) AFTER `street`,
                    ADD COLUMN `neighborhood` VARCHAR(255) AFTER `street_number`,
                    ADD COLUMN `city` VARCHAR(255) AFTER `neighborhood`,
                    ADD COLUMN `postal_code` VARCHAR(20) AFTER `city`";
            
            $conn->exec($sql);
            $results[] = '✓ Columnas agregadas exitosamente: street, street_number, neighborhood, city, postal_code';
            
            // 3. Verificar la estructura actualizada
            $stmt = $conn->query("DESCRIBE orders");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $requiredColumns = ['street', 'street_number', 'neighborhood', 'city', 'postal_code'];
            $allPresent = true;
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $columns)) {
                    $allPresent = false;
                    $errors[] = "✗ Columna '$col' no se encontró";
                }
            }
            
            if ($allPresent) {
                $results[] = '✓ Todas las columnas fueron verificadas correctamente';
                $success = true;
            }
            
            // 4. Opcional: Verificar si existe la columna antigua 'address'
            if (in_array('address', $columns)) {
                $results[] = 'ℹ Nota: La columna antigua "address" aún existe. Puedes eliminarla manualmente si ya no la necesitas.';
            }
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
                    <h3>Esta actualización agregará los siguientes campos:</h3>
                    <ul>
                        <li><i class="fas fa-plus"></i> <strong>street</strong> - Calle</li>
                        <li><i class="fas fa-plus"></i> <strong>street_number</strong> - Número</li>
                        <li><i class="fas fa-plus"></i> <strong>neighborhood</strong> - Colonia</li>
                        <li><i class="fas fa-plus"></i> <strong>city</strong> - Ciudad</li>
                        <li><i class="fas fa-plus"></i> <strong>postal_code</strong> - Código Postal</li>
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
