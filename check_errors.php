<?php
/**
 * DIAGNÓSTICO DE ERRORES
 * Accede a: https://mediumvioletred-lobster-199641.hostingersite.com/check_errors.php
 */

// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>🔍 Diagnóstico del Sistema</h1>";
echo "<hr>";

// 1. Verificar PHP
echo "<h2>✅ PHP Version</h2>";
echo "Versión: " . phpversion() . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";
echo "<hr>";

// 2. Verificar archivos importantes
echo "<h2>📁 Archivos Importantes</h2>";
$files_to_check = [
    'config/database.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/auth.php',
    'includes/functions.php',
    'index.php',
    'setup_pharmacy.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file NO existe<br>";
    }
}
echo "<hr>";

// 3. Probar conexión a base de datos
echo "<h2>🗄️ Conexión a Base de Datos</h2>";
try {
    // Configuración manual
    $is_local = (
        (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1'))
    );

    if ($is_local) {
        $host = 'localhost';
        $dbname = 'multigamer360';
        $username = 'root';
        $password = '';
        echo "Entorno: LOCAL<br>";
    } else {
        $host = 'localhost';
        $dbname = 'u851317150_mg360_db';
        $username = 'u851317150_mg360_user';
        $password = 'MultiGamer2025';
        echo "Entorno: PRODUCCIÓN (Hostinger)<br>";
    }
    
    echo "Host: $host<br>";
    echo "Database: $dbname<br>";
    echo "Username: $username<br>";
    echo "<br>";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ <strong>Conexión exitosa a la base de datos!</strong><br>";
    
    // Verificar tablas
    echo "<br><h3>📊 Tablas en la BD:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Total de tablas: " . count($tables) . "<br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "❌ <strong>Error de conexión:</strong><br>";
    echo $e->getMessage() . "<br>";
    echo "Código: " . $e->getCode() . "<br>";
}
echo "<hr>";

// 4. Verificar permisos de escritura
echo "<h2>🔒 Permisos</h2>";
$dirs_to_check = ['uploads/', 'assets/css/', 'config/'];
foreach ($dirs_to_check as $dir) {
    if (is_writable($dir)) {
        echo "✅ $dir es escribible<br>";
    } else {
        echo "⚠️ $dir NO es escribible<br>";
    }
}
echo "<hr>";

// 5. Variables de servidor
echo "<h2>🖥️ Información del Servidor</h2>";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "<br>";
echo "<hr>";

echo "<h2>✅ Diagnóstico Completo</h2>";
echo "<p>Si ves este mensaje, PHP está funcionando correctamente.</p>";
echo "<p><a href='index.php'>Ir a Index</a> | <a href='setup_pharmacy.php'>Ir a Setup</a></p>";
?>
