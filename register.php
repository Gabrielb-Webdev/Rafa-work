<?php
require_once __DIR__ . '/config/config.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    redirect('/index.php');
}

$pageTitle = 'Registro - Forethink Health';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        try {
            // Verificar si el email ya existe
            $stmt = executeQuery("SELECT id FROM users WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                $error = 'Este email ya está registrado';
            } else {
                // Crear usuario
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                executeQuery(
                    "INSERT INTO users (email, password, full_name, phone, role) VALUES (?, ?, ?, ?, 'customer')",
                    [$email, $hashed_password, $full_name, $phone]
                );
                
                $success = 'Registro exitoso. Ya puedes iniciar sesión.';
                
                // Auto-login
                $stmt = executeQuery("SELECT * FROM users WHERE email = ?", [$email]);
                $user = $stmt->fetch();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirigir después de 2 segundos
                header("refresh:2;url=" . BASE_URL . "/index.php");
            }
        } catch (Exception $e) {
            $error = 'Error al crear la cuenta. Por favor intenta de nuevo.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
.auth-container {
    max-width: 500px;
    margin: 80px auto;
    padding: 40px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.auth-container h2 {
    text-align: center;
    margin-bottom: 30px;
    color: var(--text-dark);
}

.auth-form .form-group {
    margin-bottom: 20px;
}

.auth-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-dark);
}

.auth-form input {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    font-size: 14px;
}

.auth-form input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.auth-form button {
    width: 100%;
    padding: 14px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.auth-form button:hover {
    background-color: var(--secondary-color);
}

.auth-links {
    text-align: center;
    margin-top: 20px;
}

.auth-links a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.auth-links a:hover {
    text-decoration: underline;
}
</style>

<div class="auth-container">
    <h2>Crear Cuenta</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form class="auth-form" method="POST">
        <div class="form-group">
            <label for="full_name">Nombre Completo *</label>
            <input type="text" id="full_name" name="full_name" required 
                   value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?php echo htmlspecialchars($phone ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña *</label>
            <input type="password" id="password" name="password" required 
                   minlength="6" placeholder="Mínimo 6 caracteres">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirmar Contraseña *</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit">Registrarse</button>
    </form>
    
    <div class="auth-links">
        <p>¿Ya tienes cuenta? <a href="<?php echo BASE_URL; ?>/login.php">Inicia sesión aquí</a></p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
