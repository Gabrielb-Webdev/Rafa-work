<?php
// Script temporal para actualizar la configuración de la base de datos
// Ejecuta este archivo UNA VEZ desde el navegador y luego BÓRRALO

$configFile = __DIR__ . '/config/database.php';

$newConfig = <<<'PHP'
<?php
// Configuración de la base de datos de Hostinger

define('DB_HOST', 'localhost');
define('DB_NAME', 'u851317150_fh');
define('DB_USER', 'u851317150_fh');
define('DB_PASS', 'Lg030920.');
define('DB_CHARSET', 'utf8mb4');

// Crear conexión
function getConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
    }
}

// Función auxiliar para ejecutar consultas preparadas
function executeQuery($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
PHP;

try {
    if (file_put_contents($configFile, $newConfig)) {
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Configuración Actualizada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #00d4ff 0%, #00bfe6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            text-align: center;
        }
        h1 { color: #00d4ff; margin-bottom: 20px; }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 20px; 
            border-radius: 10px; 
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        a {
            display: inline-block;
            margin: 10px;
            padding: 15px 30px;
            background: #00d4ff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
        a:hover { background: #00bfe6; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>✅ ¡Configuración Actualizada!</h1>
        <div class='success'>
            <strong>La configuración de la base de datos se actualizó correctamente.</strong>
        </div>
        <div class='warning'>
            <strong>⚠️ IMPORTANTE:</strong><br>
            Por seguridad, DEBES BORRAR este archivo (update-config.php) AHORA.
        </div>
        <div>
            <a href='index.php'>→ Ir al Sitio</a>
            <a href='admin/'>→ Ir al Admin</a>
        </div>
        <p style='margin-top: 30px; color: #666;'>
            <strong>Credenciales de Admin:</strong><br>
            Email: admin@forethinkhealth.com<br>
            Password: admin123
        </p>
    </div>
</body>
</html>";
    } else {
        throw new Exception('No se pudo escribir el archivo de configuración');
    }
} catch (Exception $e) {
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Error</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class='error'>
        <h2>❌ Error</h2>
        <p>{$e->getMessage()}</p>
    </div>
</body>
</html>";
}
