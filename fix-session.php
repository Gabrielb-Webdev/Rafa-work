<?php
/**
 * Reparar Sesión de Usuario
 * Version: 1.0 - 31/01/2026
 */
session_start();
require_once __DIR__ . '/config/database.php';

$message = '';
$currentUserId = $_SESSION['user_id'] ?? null;
$userExists = false;

// Verificar si el usuario de la sesión existe
if ($currentUserId) {
    try {
        $stmt = executeQuery("SELECT id, full_name, email FROM users WHERE id = ?", [$currentUserId]);
        $user = $stmt->fetch();
        $userExists = (bool)$user;
    } catch (Exception $e) {
        $message = "Error al verificar usuario: " . $e->getMessage();
    }
}

// Si se solicita limpiar sesión
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    session_destroy();
    session_start();
    header('Location: fix-session.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reparar Sesión - Forethink Health</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .status-error {
            background: #fed7d7;
            border-left: 4px solid #f56565;
            color: #742a2a;
        }
        .status-success {
            background: #c6f6d5;
            border-left: 4px solid #48bb78;
            color: #22543d;
        }
        .status-warning {
            background: #feebc8;
            border-left: 4px solid #ed8936;
            color: #7c2d12;
        }
        .info-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        .info-item {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #00d4d4;
        }
        .info-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn-primary {
            background: #00d4d4;
            color: white;
        }
        .btn-primary:hover {
            background: #00b8b8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 212, 212, 0.4);
        }
        .btn-danger {
            background: #f56565;
            color: white;
        }
        .btn-danger:hover {
            background: #e53e3e;
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .users-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-top: 20px;
        }
        .user-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-item:last-child {
            border-bottom: none;
        }
        .user-info {
            flex: 1;
        }
        .user-name {
            font-weight: 600;
            color: #2d3748;
        }
        .user-email {
            font-size: 12px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔧</div>
        <h1>Reparar Sesión de Usuario</h1>
        <p class="subtitle">Diagnóstico y corrección de problemas de sesión</p>

        <?php if ($currentUserId): ?>
            <?php if ($userExists): ?>
                <div class="status-box status-success">
                    <strong>✓ Sesión válida</strong><br>
                    Tu usuario existe correctamente en la base de datos.
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ID de Usuario</div>
                        <div class="info-value"><?php echo $currentUserId; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nombre</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                </div>

                <p style="margin-bottom: 20px;">Tu sesión está funcionando correctamente. Puedes continuar usando el sistema.</p>

                <a href="products.php" class="btn btn-primary">Ver Productos</a>
                <a href="cart.php" class="btn btn-secondary">Ver Carrito</a>
                
            <?php else: ?>
                <div class="status-box status-error">
                    <strong>✗ Sesión inválida</strong><br>
                    El usuario con ID <?php echo $currentUserId; ?> no existe en la base de datos. Necesitas limpiar la sesión e iniciar sesión nuevamente.
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ID de Usuario en Sesión</div>
                        <div class="info-value"><?php echo $currentUserId; ?> (inválido)</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Session ID</div>
                        <div class="info-value"><?php echo substr(session_id(), 0, 20) . '...'; ?></div>
                    </div>
                </div>

                <p style="margin-bottom: 20px; color: #742a2a;">
                    <strong>Problema:</strong> Tu sesión contiene un ID de usuario que ya no existe. 
                    Esto puede ocurrir si el usuario fue eliminado de la base de datos.
                </p>

                <a href="?action=clear" class="btn btn-danger">Limpiar Sesión</a>
                <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
            <?php endif; ?>
        <?php else: ?>
            <div class="status-box status-warning">
                <strong>⚠ Sin sesión activa</strong><br>
                No hay ningún usuario logueado actualmente.
            </div>

            <p style="margin-bottom: 20px;">Necesitas iniciar sesión para usar el carrito de compras.</p>

            <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
            <a href="register.php" class="btn btn-secondary">Registrarse</a>
        <?php endif; ?>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;">

        <h3 style="margin-bottom: 15px; color: #2d3748;">Usuarios Disponibles</h3>
        <?php
        try {
            $stmt = executeQuery("SELECT id, full_name, email, user_role FROM users ORDER BY id");
            $users = $stmt->fetchAll();
            
            if (!empty($users)) {
                echo '<div class="users-list">';
                foreach ($users as $u) {
                    echo '<div class="user-item">';
                    echo '<div class="user-info">';
                    echo '<div class="user-name">' . htmlspecialchars($u['full_name']) . ' <span style="font-size: 11px; background: #00d4d4; color: white; padding: 2px 6px; border-radius: 3px;">' . $u['user_role'] . '</span></div>';
                    echo '<div class="user-email">' . htmlspecialchars($u['email']) . '</div>';
                    echo '</div>';
                    echo '<div style="color: #718096; font-size: 14px;">ID: ' . $u['id'] . '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<p style="color: #f56565;">Error al cargar usuarios: ' . $e->getMessage() . '</p>';
        }
        ?>

        <div style="margin-top: 30px; padding: 15px; background: #edf2f7; border-radius: 8px; font-size: 13px; color: #4a5568;">
            <strong>💡 Consejo:</strong> Si continúas teniendo problemas, ejecuta el archivo 
            <code style="background: white; padding: 2px 6px; border-radius: 3px;">fix-user-sessions.sql</code> 
            en phpMyAdmin para limpiar datos incorrectos.
        </div>
    </div>
</body>
</html>
