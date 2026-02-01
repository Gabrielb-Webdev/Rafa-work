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
    
    // Validations
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        try {
            // Check if email already exists
            $stmt = executeQuery("SELECT id FROM users WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                $error = 'This email is already registered';
            } else {
                // Crear usuario
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                executeQuery(
                    "INSERT INTO users (email, password, full_name, phone, role) VALUES (?, ?, ?, ?, 'customer')",
                    [$email, $hashed_password, $full_name, $phone]
                );
                
                $success = 'Registration successful. You can now log in.';
                
                // Auto-login
                $stmt = executeQuery("SELECT * FROM users WHERE email = ?", [$email]);
                $user = $stmt->fetch();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Sincronizar carrito de sesión con BD
                if (!empty($_SESSION['cart'])) {
                    try {
                        foreach ($_SESSION['cart'] as $productId => $item) {
                            executeQuery(
                                "INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
                                [$user['id'], $productId, $item['quantity']]
                            );
                        }
                    } catch (Exception $e) {
                        // Continuar aunque falle la sincronización
                    }
                }
                
                // Marcar carrito como cargado
                $_SESSION['cart_loaded'] = true;
                
                // Redirigir después de 2 segundos
                header("refresh:2;url=" . BASE_URL . "/index.php");
            }
        } catch (Exception $e) {
            $error = 'Error creating account. Please try again.';
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
</style>

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-logo">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Forethink Health">
        </div>
        <h2>Create Account</h2>
        <p class="auth-subtitle">Join Forethink Health today</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form class="auth-form" method="POST">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <div class="input-icon">
                    <input type="text" id="full_name" name="full_name" required 
                           placeholder="John Doe"
                           value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <div class="input-icon">
                    <input type="email" id="email" name="email" required 
                           placeholder="your@email.com"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone</label>
                <div class="input-icon">
                    <input type="tel" id="phone" name="phone" 
                           placeholder="+1 234 567 8900"
                           value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                    <i class="fas fa-phone"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <div class="input-icon">
                    <input type="password" id="password" name="password" required 
                           minlength="6" placeholder="Minimum 6 characters">
                    <i class="fas fa-lock"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <div class="input-icon">
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Repeat your password">
                    <i class="fas fa-lock"></i>
                </div>
            </div>
            
            <button type="submit">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="auth-links">
            <p>Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Login here</a></p>
        </div>
    </div>
    
    <div class="back-home">
        <a href="<?php echo BASE_URL; ?>">
            <i class="fas fa-home"></i> Back to home
        </a>
    </div>
</div>
