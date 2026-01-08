<?php
/**
 * DIAGNÓSTICO DE ERRORES
 * Este archivo muestra todos los errores de PHP
 */

// Mostrar TODOS los errores
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

echo "<h1>Test de Diagnóstico - MediCareOnline</h1>";
echo "<hr>";

// Test 1: PHP funcionando
echo "<h2>✓ PHP está funcionando</h2>";
echo "Versión de PHP: " . phpversion() . "<br><br>";

// Test 2: Archivos de configuración
echo "<h2>Test 2: Archivos de Configuración</h2>";

$files_to_check = [
    'config/database.php',
    'config/user_manager_simple.php',
    'includes/functions.php',
    'admin/inc/auth.php',
    'admin/inc/header.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ <strong>$file</strong> - EXISTS<br>";
    } else {
        echo "❌ <strong>$file</strong> - NOT FOUND<br>";
    }
}

echo "<br>";

// Test 3: Intentar cargar database.php
echo "<h2>Test 3: Conexión a Base de Datos</h2>";
try {
    require_once 'config/database.php';
    echo "✅ config/database.php cargado correctamente<br>";
    echo "✅ Conexión PDO establecida<br>";
    echo "Base de datos: " . (defined('DB_NAME') ? DB_NAME : $dbname) . "<br>";
} catch (Exception $e) {
    echo "❌ Error cargando database.php: " . $e->getMessage() . "<br>";
}

echo "<br>";

// Test 4: Test de sesión
echo "<h2>Test 4: Sesiones</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "✅ Sesión iniciada correctamente<br>";
} else {
    echo "✅ Sesión ya estaba activa<br>";
}
echo "Session ID: " . session_id() . "<br>";

echo "<br>";

// Test 5: Verificar tabla users
echo "<h2>Test 5: Base de Datos - Tabla Users</h2>";
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $result = $stmt->fetch();
        echo "✅ Tabla 'users' existe<br>";
        echo "Total usuarios: " . $result['total'] . "<br><br>";
        
        // Ver usuarios administradores
        $stmt = $pdo->query("SELECT id, email, first_name, role FROM users WHERE role = 'administrador'");
        $admins = $stmt->fetchAll();
        
        if (count($admins) > 0) {
            echo "✅ Usuarios administradores encontrados: " . count($admins) . "<br>";
            foreach ($admins as $admin) {
                echo "- ID: {$admin['id']}, Email: {$admin['email']}, Nombre: {$admin['first_name']}, Rol: {$admin['role']}<br>";
            }
        } else {
            echo "⚠️ <strong>NO HAY USUARIOS ADMINISTRADORES</strong><br>";
            echo "<a href='create_admin.php'>Crear usuario administrador</a><br>";
        }
    } catch (PDOException $e) {
        echo "❌ Error consultando tabla users: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ No hay conexión PDO disponible<br>";
}

echo "<br><hr>";
echo "<h2>Links de Navegación</h2>";
echo "<a href='index.php'>Ir a Inicio</a> | ";
echo "<a href='admin/login.php'>Admin Login</a> | ";
echo "<a href='create_admin.php'>Crear Admin</a>";
?>
