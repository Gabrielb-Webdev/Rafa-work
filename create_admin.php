<?php
/**
 * =====================================================
 * CREAR USUARIO ADMINISTRADOR - MediCareOnline
 * =====================================================
 * 
 * Script para crear un usuario administrador de emergencia
 * Ejecutar solo cuando no puedas acceder al admin
 * 
 * ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO
 */

// Password de seguridad para ejecutar este script
define('CREATE_ADMIN_PASSWORD', 'MediCare2026');

// Mostrar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/config/database.php';

$executed = false;
$message = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['security_password']) || $_POST['security_password'] !== CREATE_ADMIN_PASSWORD) {
        $error = "Password de seguridad incorrecta";
    } else {
        try {
            // Datos del admin
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            
            // Validaciones
            if (empty($email) || empty($password) || empty($first_name)) {
                throw new Exception("Todos los campos son obligatorios");
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email inválido");
            }
            
            if (strlen($password) < 6) {
                throw new Exception("La contraseña debe tener al menos 6 caracteres");
            }
            
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception("El email ya está registrado");
            }
            
            // Hash de contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario administrador
            $stmt = $pdo->prepare("
                INSERT INTO users (
                    email, password, first_name, last_name, 
                    role, is_active, email_verified, created_at
                ) VALUES (
                    ?, ?, ?, ?,
                    'administrador', 1, 1, NOW()
                )
            ");
            
            $stmt->execute([
                $email,
                $password_hash,
                $first_name,
                $last_name
            ]);
            
            $admin_id = $pdo->lastInsertId();
            
            $message = "✅ Usuario administrador creado exitosamente<br><br>";
            $message .= "<strong>ID:</strong> $admin_id<br>";
            $message .= "<strong>Email:</strong> $email<br>";
            $message .= "<strong>Nombre:</strong> $first_name $last_name<br>";
            $message .= "<strong>Rol:</strong> Administrador<br><br>";
            $message .= "<a href='admin/login.php' class='btn btn-primary'>Ir al Login del Admin</a><br><br>";
            $message .= "<strong style='color: red;'>⚠️ ELIMINA ESTE ARCHIVO (create_admin.php) INMEDIATAMENTE</strong>";
            
            $executed = true;
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario Administrador - MediCareOnline</title>
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
        .admin-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #00D4FF, #00A8CC);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .admin-icon i {
            font-size: 40px;
            color: white;
        }
        .btn-create {
            background: linear-gradient(135deg, #00D4FF, #00A8CC);
            border: none;
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
        }
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,212,255,0.3);
        }
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .error-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h2>Crear Usuario Administrador</h2>
            <p class="text-muted">MediCareOnline</p>
        </div>

        <?php if ($executed && $message): ?>
            <div class="success-box">
                <?php echo $message; ?>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error-box">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password de Seguridad
                    </label>
                    <input type="password" class="form-control" name="security_password" 
                           placeholder="MediCare2026" required>
                    <small class="text-muted">Usa el mismo password del setup</small>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> Email del Administrador
                    </label>
                    <input type="email" class="form-control" name="email" 
                           placeholder="admin@medicareonline.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-key"></i> Contraseña
                    </label>
                    <input type="password" class="form-control" name="password" 
                           placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Nombre
                        </label>
                        <input type="text" class="form-control" name="first_name" 
                               placeholder="Juan" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i> Apellido
                        </label>
                        <input type="text" class="form-control" name="last_name" 
                               placeholder="Pérez">
                    </div>
                </div>

                <button type="submit" class="btn btn-create">
                    <i class="fas fa-user-plus"></i> Crear Administrador
                </button>
            </form>

            <div class="alert alert-warning mt-4">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Importante:</strong> Elimina este archivo inmediatamente después de crear el admin.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
