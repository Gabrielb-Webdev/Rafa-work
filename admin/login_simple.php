<?php
/**
 * LOGIN SIMPLE PARA ADMIN - MediCareOnline
 * Sin dependencias complejas
 */

// Mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

// Cargar base de datos
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Por favor completa todos los campos";
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT id, email, password, first_name, last_name, role, is_active 
                FROM users 
                WHERE email = ? AND role = 'administrador'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['is_active'] != 1) {
                    $error = "Tu cuenta está inactiva";
                } else {
                    // Login exitoso
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['is_admin'] = true;
                    $_SESSION['role'] = $user['role'];
                    
                    header('Location: index.php');
                    exit;
                }
            } else {
                $error = "Email o contraseña incorrectos, o no tienes permisos de administrador";
            }
        } catch (PDOException $e) {
            $error = "Error de conexión: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - MediCareOnline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #00D4FF 0%, #0088AA 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #00D4FF, #00A8CC);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .login-icon i {
            font-size: 40px;
            color: white;
        }
        .btn-login {
            background: linear-gradient(135deg, #00D4FF, #00A8CC);
            border: none;
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,212,255,0.3);
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2>Panel de Administración</h2>
            <p class="text-muted">MediCareOnline</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'access_denied'): ?>
            <div class="alert alert-warning">
                <i class="fas fa-lock"></i> Debes iniciar sesión como administrador
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" class="form-control" name="email" 
                       placeholder="admin@medicareonline.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-lock"></i> Contraseña
                </label>
                <input type="password" class="form-control" name="password" 
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="../index.php" class="text-muted">
                <i class="fas fa-arrow-left"></i> Volver al sitio
            </a>
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">
                ¿No tienes usuario admin? 
                <a href="../create_admin.php">Crear aquí</a>
            </small>
        </div>
    </div>
</body>
</html>
