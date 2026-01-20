<?php
/**
 * RESET COMPLETO DE USUARIOS - Forethink Health
 * Esta página elimina TODOS los usuarios y crea nuevos con contraseñas correctas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

$executed = false;
$results = [];

if (isset($_POST['reset_users'])) {
    $executed = true;
    
    try {
        $conn = getDBConnection();
        
        // Paso 1: Eliminar TODOS los usuarios
        $results[] = "<h3>📋 Paso 1: Eliminando todos los usuarios existentes...</h3>";
        $deleteStmt = $conn->prepare("DELETE FROM users");
        $deleteStmt->execute();
        $deleted = $deleteStmt->rowCount();
        $results[] = "<p style='color: orange;'>🗑️ <strong>{$deleted} usuarios eliminados</strong></p>";
        
        // Paso 2: Generar hash correcto
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $results[] = "<h3>🔐 Paso 2: Generando hash de contraseña...</h3>";
        $results[] = "<p>Password: <strong>{$password}</strong></p>";
        $results[] = "<p>Hash: <code style='font-size: 10px;'>{$hash}</code></p>";
        
        // Verificar que el hash funciona
        $verify = password_verify($password, $hash);
        if (!$verify) {
            throw new Exception("El hash generado no se verifica correctamente");
        }
        $results[] = "<p style='color: green;'>✅ Hash verificado correctamente</p>";
        
        // Paso 3: Crear nuevos usuarios
        $results[] = "<h3>👥 Paso 3: Creando nuevos usuarios...</h3>";
        
        $usuarios = [
            [
                'email' => 'admin@test.com',
                'name' => 'Administrator',
                'phone' => '+52 555 100 1000',
                'role' => 'admin'
            ],
            [
                'email' => 'admin@forethink.com',
                'name' => 'Admin Forethink',
                'phone' => '+52 555 100 2000',
                'role' => 'admin'
            ],
            [
                'email' => 'usuario@test.com',
                'name' => 'Carlos Rodríguez',
                'phone' => '+52 555 888 9999',
                'role' => 'customer'
            ],
            [
                'email' => 'cliente1@test.com',
                'name' => 'María González',
                'phone' => '+52 555 111 2222',
                'role' => 'customer'
            ]
        ];
        
        $insertStmt = $conn->prepare(
            "INSERT INTO users (email, password, full_name, phone, role, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        
        foreach ($usuarios as $usuario) {
            $insertStmt->execute([
                $usuario['email'],
                $hash,
                $usuario['name'],
                $usuario['phone'],
                $usuario['role']
            ]);
            $emoji = $usuario['role'] === 'admin' ? '👨‍💼' : '👤';
            $results[] = "<p>{$emoji} Usuario creado: <strong>{$usuario['email']}</strong> - {$usuario['name']} ({$usuario['role']})</p>";
        }
        
        $results[] = "<h3>✅ Paso 4: Verificación final</h3>";
        $finalStmt = executeQuery("SELECT id, email, full_name, phone, role FROM users ORDER BY id");
        $finalUsers = $finalStmt->fetchAll();
        
        $results[] = "<ul style='background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #00d4d4;'>";
        foreach ($finalUsers as $user) {
            $roleEmoji = $user['role'] === 'admin' ? '👨‍💼' : '👤';
            $results[] = "<li>{$roleEmoji} <strong>{$user['email']}</strong> - {$user['full_name']} ({$user['role']})</li>";
        }
        $results[] = "</ul>";
        
        $results[] = "<div style='background: #d4edda; border: 2px solid #00d4d4; padding: 30px; border-radius: 12px; margin-top: 30px; text-align: center;'>";
        $results[] = "<h2 style='color: #00d4d4; margin-bottom: 20px;'>🎉 ¡PROCESO COMPLETADO!</h2>";
        $results[] = "<p style='font-size: 18px;'><strong>Todos los usuarios tienen la contraseña:</strong> <code style='background: white; padding: 8px 16px; border-radius: 6px; font-size: 20px; color: #00d4d4;'>admin123</code></p>";
        $results[] = "<a href='login.php' style='display: inline-block; background: #00d4d4; color: white; padding: 15px 40px; border-radius: 8px; text-decoration: none; margin-top: 20px; font-size: 18px; font-weight: bold;'>Ir al Login</a>";
        $results[] = "</div>";
        
    } catch (Exception $e) {
        $results[] = "<div style='background: #fee; border: 2px solid #c33; padding: 20px; border-radius: 8px;'>";
        $results[] = "<h3 style='color: #c33;'>❌ Error</h3>";
        $results[] = "<p>" . $e->getMessage() . "</p>";
        $results[] = "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset de Usuarios - Forethink Health</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e8f8f8 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 212, 212, 0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #00d4d4;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 16px;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .warning-box h3 {
            color: #856404;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .warning-box ul {
            color: #856404;
            margin-left: 30px;
            line-height: 1.8;
        }
        
        .action-box {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 212, 212, 0.1);
            text-align: center;
        }
        
        .btn-reset {
            background: #00d4d4;
            color: white;
            border: none;
            padding: 18px 50px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }
        
        .btn-reset:hover {
            background: #00bfbf;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 212, 212, 0.3);
        }
        
        .btn-reset:active {
            transform: translateY(0);
        }
        
        .results-box {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 212, 212, 0.1);
            margin-top: 30px;
        }
        
        .results-box h3 {
            color: #333;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .results-box p {
            margin: 8px 0;
            line-height: 1.6;
        }
        
        .results-box ul {
            margin: 15px 0;
        }
        
        .results-box li {
            margin: 10px 0;
            line-height: 1.8;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: #00d4d4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users-cog"></i> Reset de Usuarios</h1>
            <p>Forethink Health - Panel de Administración</p>
        </div>
        
        <?php if (!$executed): ?>
            <div class="warning-box">
                <h3><i class="fas fa-exclamation-triangle"></i> ADVERTENCIA</h3>
                <p><strong>Esta acción realizará lo siguiente:</strong></p>
                <ul>
                    <li>Eliminará TODOS los usuarios existentes en la base de datos</li>
                    <li>Creará 4 usuarios nuevos con contraseñas funcionales</li>
                    <li>Todos los usuarios tendrán la contraseña: <strong>admin123</strong></li>
                    <li>Esta acción NO se puede deshacer</li>
                </ul>
            </div>
            
            <div class="action-box">
                <h2 style="margin-bottom: 20px; color: #333;">¿Estás seguro?</h2>
                <form method="POST">
                    <button type="submit" name="reset_users" class="btn-reset">
                        <i class="fas fa-sync-alt"></i>
                        RESETEAR TODOS LOS USUARIOS
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="results-box">
                <?php echo implode("\n", $results); ?>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.php"><i class="fas fa-home"></i> Volver al inicio</a>
        </div>
    </div>
</body>
</html>
