<?php
require_once __DIR__ . '/config/config.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    redirect('/index.php');
}

$pageTitle = 'Login - Forethink Health';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        try {
            $stmt = executeQuery("SELECT * FROM users WHERE email = ?", [$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirigir según el rol
                if ($user['role'] === 'admin') {
                    redirect('/admin/index.php');
                } else {
                    redirect('/index.php');
                }
            } else {
                $error = 'Email o contraseña incorrectos';
            }
        } catch (Exception $e) {
            $error = 'Error al procesar el login. Por favor intenta de nuevo.';
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

.divider {
    text-align: center;
    margin: 20px 0;
    color: var(--text-light);
}
</style>

<div class="auth-container">
    <h2>Iniciar Sesión</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form class="auth-form" method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Iniciar Sesión</button>
    </form>
    
    <div class="auth-links">
        <p>¿No tienes cuenta? <a href="<?php echo BASE_URL; ?>/register.php">Regístrate aquí</a></p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
