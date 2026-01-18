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
body {
    background: linear-gradient(135deg, #A89BC7 0%, #4DB8AC 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

header, footer {
    display: none;
}

.auth-wrapper {
    width: 100%;
    max-width: 480px;
    padding: 20px;
}

.auth-container {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    padding: 48px 40px;
    animation: slideUp 0.5s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-logo {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.auth-container h2 {
    text-align: center;
    margin-bottom: 8px;
    color: var(--text-dark);
    font-size: 28px;
}

.auth-subtitle {
    text-align: center;
    color: var(--text-light);
    margin-bottom: 40px;
    font-size: 15px;
}

.auth-form .form-group {
    margin-bottom: 24px;
    position: relative;
}

.auth-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-dark);
    font-size: 14px;
}

.auth-form .input-icon {
    position: relative;
}

.auth-form .input-icon i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
    font-size: 16px;
    transition: var(--transition);
}

.auth-form .input-icon input {
    padding-left: 48px;
}

.auth-form .input-icon input:focus + i {
    color: var(--primary-color);
}

.auth-form input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 15px;
    transition: var(--transition);
    background-color: var(--light-bg);
}

.auth-form input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    background-color: var(--white);
}

.forgot-password {
    text-align: right;
    margin-bottom: 20px;
}

.forgot-password a {
    color: var(--text-light);
    text-decoration: none;
    font-size: 14px;
    transition: var(--transition);
}

.forgot-password a:hover {
    color: var(--primary-color);
}

.auth-form button {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.auth-form button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.2);
    transition: left 0.3s ease;
}

.auth-form button:hover::before {
    left: 100%;
}

.auth-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 212, 255, 0.4);
}

.auth-form button:active {
    transform: translateY(0);
}

.auth-links {
    text-align: center;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border-color);
}

.auth-links a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.auth-links a:hover {
    color: var(--secondary-color);
}

.back-home {
    text-align: center;
    margin-top: 20px;
}

.back-home a {
    color: var(--white);
    text-decoration: none;
    font-size: 14px;
    opacity: 0.9;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.back-home a:hover {
    opacity: 1;
}

.divider {
    text-align: center;
    margin: 20px 0;
    color: var(--text-light);
}
</style>

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-logo">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.jpeg" alt="Forethink Health">
        </div>
        <h2>Bienvenido</h2>
        <p class="auth-subtitle">Ingresa a tu cuenta de Forethink Health</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form class="auth-form" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-icon">
                    <input type="email" id="email" name="email" required 
                           placeholder="tu@email.com"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-icon">
                    <input type="password" id="password" name="password" required
                           placeholder="Ingresa tu contraseña">
                    <i class="fas fa-lock"></i>
                </div>
            </div>
            
            <div class="forgot-password">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>
            
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="auth-links">
            <p>¿No tienes cuenta? <a href="<?php echo BASE_URL; ?>/register.php">Regístrate aquí</a></p>
        </div>
    </div>
    
    <div class="back-home">
        <a href="<?php echo BASE_URL; ?>">
            <i class="fas fa-home"></i> Volver al inicio
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
