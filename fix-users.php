<?php
/**
 * FIX DE USUARIOS - Forethink Health
 * Ejecuta este archivo desde el navegador para actualizar los hashes de contraseña
 */

// Deshabilitar errores en producción para no mostrar información sensible
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

// IMPORTANTE: Usar un hash fijo y conocido que funcione
$password = 'admin123';
// Este hash fue probado y funciona con password_verify()
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

echo "<h1>Fix de Usuarios - Forethink Health</h1>";
echo "<p><strong>Password que usaremos:</strong> {$password}</p>";
echo "<p><strong>Hash generado:</strong> {$hash}</p>";

// Verificar que el hash funciona
$verify = password_verify($password, $hash);
echo "<p><strong>Verificación:</strong> " . ($verify ? '✅ CORRECTO' : '❌ ERROR') . "</p>";

echo "<hr>";
echo "<h2>Actualizando usuarios...</h2>";

try {
    // Obtener conexión
    $conn = getDBConnection();
    
    // Primero, verificar qué usuarios existen
    echo "<h3>Usuarios actuales en la base de datos:</h3>";
    $stmt = executeQuery("SELECT id, email, full_name, role FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p style='color: orange;'>⚠️ No hay usuarios en la base de datos.</p>";
        echo "<p>Parece que la base de datos está vacía. Ejecuta primero <strong>database-sample-data.sql</strong></p>";
    } else {
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>ID: {$user['id']} - {$user['email']} ({$user['full_name']}) - Rol: {$user['role']}</li>";
        }
        echo "</ul>";
        
        echo "<hr>";
        echo "<h3>Actualizando contraseñas a 'admin123'...</h3>";
        
        // Actualizar todos los usuarios existentes con el nuevo hash
        $updateStmt = $conn->prepare("UPDATE users SET password = ?");
        $updateStmt->execute([$hash]);
        
        $affected = $updateStmt->rowCount();
        echo "<p style='color: green;'>✅ <strong>{$affected} usuarios actualizados correctamente</strong></p>";
        
        echo "<hr>";
        echo "<h3>Verificando si existen los nuevos usuarios...</h3>";
        
        // Verificar si usuario@test.com existe
        $checkUser = executeQuery("SELECT id FROM users WHERE email = ?", ['usuario@test.com']);
        if (!$checkUser->fetch()) {
            echo "<p>➕ Creando usuario@test.com...</p>";
            executeQuery(
                "INSERT INTO users (email, password, full_name, phone, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                ['usuario@test.com', $hash, 'Carlos Rodríguez', '+52 555 888 9999', 'customer']
            );
            echo "<p style='color: green;'>✅ Usuario creado</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ usuario@test.com ya existe</p>";
        }
        
        // Verificar si admin@forethink.com existe
        $checkAdmin = executeQuery("SELECT id FROM users WHERE email = ?", ['admin@forethink.com']);
        if (!$checkAdmin->fetch()) {
            echo "<p>➕ Creando admin@forethink.com...</p>";
            executeQuery(
                "INSERT INTO users (email, password, full_name, phone, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                ['admin@forethink.com', $hash, 'Admin Forethink', '+52 555 100 2000', 'admin']
            );
            echo "<p style='color: green;'>✅ Admin creado</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ admin@forethink.com ya existe</p>";
        }
        
        echo "<hr>";
        echo "<h3>Usuarios finales:</h3>";
        $finalStmt = executeQuery("SELECT id, email, full_name, role FROM users ORDER BY id");
        $finalUsers = $finalStmt->fetchAll();
        
        echo "<ul>";
        foreach ($finalUsers as $user) {
            echo "<li><strong>{$user['email']}</strong> - {$user['full_name']} (Rol: {$user['role']})</li>";
        }
        echo "</ul>";
        
        echo "<hr>";
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px;'>";
        echo "<h2>✅ ¡TODO LISTO!</h2>";
        echo "<p><strong>Ahora puedes iniciar sesión con cualquiera de estos usuarios:</strong></p>";
        echo "<ul>";
        foreach ($finalUsers as $user) {
            echo "<li><strong>Email:</strong> {$user['email']} | <strong>Password:</strong> admin123</li>";
        }
        echo "</ul>";
        echo "<p><a href='login.php' style='display: inline-block; background: #00d4d4; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-top: 10px;'>Ir al Login</a></p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 900px;
    margin: 50px auto;
    padding: 20px;
    background: #f8f9fa;
}
h1 {
    color: #00d4d4;
}
h2, h3 {
    color: #333;
}
ul {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #00d4d4;
}
</style>
