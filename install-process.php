<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$response = [
    'success' => false,
    'message' => '',
    'messages' => [],
    'errors' => []
];

try {
    // Obtener datos del formulario
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    
    if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_pass)) {
        throw new Exception('Todos los campos son requeridos');
    }
    
    // Intentar conexión
    $dsn = "mysql:host={$db_host};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $response['messages'][] = '✓ Conexión a MySQL establecida';
    
    // Crear/seleccionar base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$db_name}`");
    $response['messages'][] = "✓ Base de datos '{$db_name}' lista";
    
    // Leer y ejecutar el script SQL
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('Archivo database.sql no encontrado');
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Dividir por comandos (separados por ;)
    $commands = array_filter(array_map('trim', explode(';', $sql)), function($cmd) {
        return !empty($cmd) && !preg_match('/^--/', $cmd);
    });
    
    $tablesCreated = 0;
    foreach ($commands as $command) {
        if (!empty($command)) {
            try {
                $pdo->exec($command);
                if (stripos($command, 'CREATE TABLE') !== false) {
                    $tablesCreated++;
                }
            } catch (PDOException $e) {
                // Ignorar errores de "tabla ya existe"
                if (strpos($e->getMessage(), 'already exists') === false) {
                    $response['errors'][] = 'Error SQL: ' . $e->getMessage();
                }
            }
        }
    }
    
    $response['messages'][] = "✓ {$tablesCreated} tablas creadas";
    
    // Verificar que las tablas se crearon
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['users', 'categories', 'products', 'cart', 'orders', 'order_items', 'contact_requests', 'newsletter_subscriptions'];
    
    $missingTables = array_diff($requiredTables, $tables);
    if (!empty($missingTables)) {
        throw new Exception('Faltan tablas: ' . implode(', ', $missingTables));
    }
    
    $response['messages'][] = '✓ Todas las tablas verificadas';
    
    // Actualizar archivo de configuración
    $configFile = __DIR__ . '/config/database.php';
    $configContent = <<<PHP
<?php
// Configuración de la base de datos de Hostinger
// Generado automáticamente por el instalador

define('DB_HOST', '{$db_host}');
define('DB_NAME', '{$db_name}');
define('DB_USER', '{$db_user}');
define('DB_PASS', '{$db_pass}');
define('DB_CHARSET', 'utf8mb4');

// Crear conexión
function getConnection() {
    try {
        \$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        \$options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);
        return \$pdo;
    } catch (PDOException \$e) {
        error_log("Error de conexión: " . \$e->getMessage());
        die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
    }
}

// Función auxiliar para ejecutar consultas preparadas
function executeQuery(\$sql, \$params = []) {
    \$conn = getConnection();
    \$stmt = \$conn->prepare(\$sql);
    \$stmt->execute(\$params);
    return \$stmt;
}

PHP;
    
    if (file_put_contents($configFile, $configContent)) {
        $response['messages'][] = '✓ Archivo de configuración actualizado';
    }
    
    // Verificar que el usuario admin existe
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetch()['count'];
    
    if ($adminCount > 0) {
        $response['messages'][] = '✓ Usuario administrador creado';
    }
    
    // Verificar productos
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $productCount = $stmt->fetch()['count'];
    $response['messages'][] = "✓ {$productCount} productos disponibles";
    
    $response['success'] = true;
    $response['message'] = '¡Instalación completada exitosamente!';
    
} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    $response['errors'][] = 'Verifica que los datos de conexión sean correctos';
    $response['errors'][] = 'Verifica que el usuario tenga permisos para crear bases de datos';
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
