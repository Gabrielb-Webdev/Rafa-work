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
        $error = 'Please fill in all fields';
    } else {
        try {
            $stmt = executeQuery("SELECT * FROM users WHERE email = ?", [$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Sincronizar carrito de sesión con BD
                if (!empty($_SESSION['cart'])) {
                    try {
                        foreach ($_SESSION['cart'] as $productId => $item) {
                            // Verificar si ya existe en BD
                            $stmt = executeQuery(
                                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", 
                                [$user['id'], $productId]
                            );
                            $existing = $stmt->fetch();
                            
                            if ($existing) {
                                // Sumar cantidades
                                $newQty = $existing['quantity'] + $item['quantity'];
                                executeQuery(
                                    "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", 
                                    [$newQty, $existing['id']]
                                );
                            } else {
                                // Insertar nuevo
                                executeQuery(
                                    "INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
                                    [$user['id'], $productId, $item['quantity']]
                                );
                            }
                        }
                    } catch (Exception $e) {
                        // Continuar aunque falle la sincronización
                    }
                }
                
                // Cargar carrito desde BD
                try {
                    $stmt = executeQuery(
                        "SELECT c.*, p.name, p.price, p.stock, p.image 
                         FROM cart c 
                         JOIN products p ON c.product_id = p.id 
                         WHERE c.user_id = ? AND p.is_active = 1",
                        [$user['id']]
                    );
                    $items = $stmt->fetchAll();
                    
                    $_SESSION['cart'] = [];
                    foreach ($items as $item) {
                        $_SESSION['cart'][(int)$item['product_id']] = [
                            'id' => (int)$item['product_id'],
                            'name' => $item['name'],
                            'price' => (float)$item['price'],
                            'quantity' => (int)$item['quantity'],
                            'image' => $item['image']
                        ];
                    }
                } catch (Exception $e) {
                    // Mantener carrito de sesión si hay error
                }
                
                // Marcar carrito como cargado
                $_SESSION['cart_loaded'] = true;
                
                // Redirigir según el rol
                if ($user['role'] === 'admin') {
                    redirect('/admin/index.php');
                } else {
                    redirect('/index.php');
                }
            } else {
                $error = 'Incorrect email or password';
            }
        } catch (Exception $e) {
            $error = 'Error processing login. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
/* Hide navigation and footer completely */
.top-bar,
header,
footer {
    display: none !important;
}

body {
    background: #f5f7fa;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    position: relative;
    overflow-x: hidden;
    margin: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.auth-wrapper {
    width: 100%;
    max-width: 1200px;
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
    position: relative;
    z-index: 1;
}

@media (max-width: 968px) {
    .auth-wrapper {
        grid-template-columns: 1fr;
    }
    .auth-hero {
        display: none;
    }
}

/* Hero Section - Left Side */
.auth-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px;
    color: white;
}

.auth-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    animation: float 15s ease-in-out infinite;
    pointer-events: none;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) scale(1); }
    50% { transform: translateY(-20px) scale(1.05); }
}

.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
    max-width: 450px;
}

.hero-icon {
    width: 140px;
    height: 140px;
    margin: 0 auto 40px;
    background: white;
    backdrop-filter: blur(10px);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 70px;
    animation: logoFloat 3s ease-in-out infinite;
    border: 3px solid rgba(255, 255, 255, 0.3);
    padding: 20px;
}

.hero-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

@keyframes logoFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-12px); }
}

.hero-title {
    font-size: 42px;
    font-weight: 900;
    margin-bottom: 20px;
    line-height: 1.2;
    text-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
}

.hero-description {
    font-size: 18px;
    line-height: 1.7;
    opacity: 0.95;
    font-weight: 400;
}

.hero-features {
    margin-top: 50px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    text-align: left;
}

.hero-feature {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255, 255, 255, 0.1);
    padding: 15px 20px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.hero-feature:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(5px);
}

.hero-feature i {
    font-size: 24px;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-feature-text h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 700;
}

.hero-feature-text p {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

/* Form Section - Right Side */
.auth-container {
    background: white;
    padding: 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow-y: auto;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-header {
    margin-bottom: 45px;
}

.form-header h2 {
    font-size: 34px;
    font-weight: 900;
    color: #1a202c;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
}

.form-header p {
    color: #718096;
    font-size: 16px;
    font-weight: 400;
    line-height: 1.6;
}

.auth-form .form-group {
    margin-bottom: 24px;
    position: relative;
}

.auth-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #2d3748;
    font-size: 14px;
    letter-spacing: 0;
    position: relative;
}

.auth-form label .required-star {
    color: #e53e3e;
    margin-left: 2px;
}

.auth-form .input-icon {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 16px;
    padding: 4px;
    z-index: 2;
}

.password-toggle:hover {
    color: #667eea;
}

.auth-form .input-icon input {
    padding-right: 45px;
}

.auth-form input {
    width: 100%;
    padding: 13px 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: white;
    font-weight: 400;
    color: #2d3748;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.auth-form input::placeholder {
    color: #a0aec0;
    font-weight: 400;
}

.auth-form input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
    background-color: #fff;
}

.auth-form input:hover:not(:focus) {
    border-color: #cbd5e0;
}

.forgot-password {
    text-align: right;
    margin-top: -12px;
    margin-bottom: 24px;
}

.forgot-password a {
    color: #667eea;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.forgot-password a:hover {
    color: #764ba2;
}

.auth-form button {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
    margin-top: 8px;
    box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4);
}

.auth-form button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.auth-form button:hover::before {
    left: 100%;
}

.auth-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.auth-form button:active {
    transform: translateY(0);
}

.auth-form button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.auth-links {
    text-align: center;
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
}

.auth-links p {
    color: #718096;
    font-size: 14px;
    font-weight: 400;
}

.auth-links a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
    position: relative;
}

.auth-links a:hover {
    color: #764ba2;
}

.back-home {
    text-align: center;
    margin-top: 20px;
}

.back-home a {
    color: #718096;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.back-home a:hover {
    color: #667eea;
}

.alert {
    padding: 14px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    animation: slideDown 0.3s ease;
    border: 1.5px solid;
    font-size: 14px;
}

.alert-error {
    background: #fff5f5;
    color: #c53030;
    border-color: #feb2b2;
}

.alert-success {
    background: #f0fff4;
    color: #2f855a;
    border-color: #9ae6b4;
}

.alert i {
    font-size: 18px;
}

@media (max-width: 576px) {
    .auth-container {
        padding: 40px 30px;
    }
    
    .form-header h2 {
        font-size: 28px;
    }
    
    .hero-icon {
        width: 100px;
        height: 100px;
        font-size: 50px;
    }
    
    .hero-title {
        font-size: 32px;
    }
}
</style>

<div class="auth-wrapper">
    <!-- Hero Section - Left Side -->
    <div class="auth-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Forethink Health Logo">
            </div>
            <h1 class="hero-title">Welcome Back!</h1>
            <p class="hero-description">Access your account to manage orders, track deliveries, and explore our premium health products.</p>
            
            <div class="hero-features">
                <div class="hero-feature">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="hero-feature-text">
                        <h4>Your Orders</h4>
                        <p>Track and manage all your purchases</p>
                    </div>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-star"></i>
                    <div class="hero-feature-text">
                        <h4>Exclusive Deals</h4>
                        <p>Access member-only discounts</p>
                    </div>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-bolt"></i>
                    <div class="hero-feature-text">
                        <h4>Quick Checkout</h4>
                        <p>Save time with stored preferences</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Section - Right Side -->
    <div class="auth-container">
        <div class="form-header">
            <h2>Sign In</h2>
            <p>Enter your credentials to access your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        
        <form class="auth-form" method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">
                    Email Address<span class="required-star">*</span>
                </label>
                <input type="email" id="email" name="email" required 
                       placeholder="Enter your email"
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">
                    Password<span class="required-star">*</span>
                </label>
                <div class="input-icon">
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>
            
            <div class="forgot-password">
                <a href="#">Forgot password?</a>
            </div>
            
            <button type="submit" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php">Create account</a></p>
            </div>
            
            <div class="back-home">
                <a href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-arrow-left"></i> Back to home
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle password visibility
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

if (togglePassword) {
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
