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

.auth-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 640px) {
    .auth-form .form-row {
        grid-template-columns: 1fr;
    }
}

.auth-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #2d3748;
    font-size: 14px;
    letter-spacing: 0;
}

.auth-form label .required-star {
    color: #e53e3e;
    margin-left: 2px;
}

.auth-form label .optional-badge {
    font-size: 11px;
    background: #edf2f7;
    color: #718096;
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 600;
    margin-left: 6px;
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
    padding: 8px;
    z-index: 10;
    pointer-events: auto;
    user-select: none;
}

.password-toggle:hover {
    color: #667eea;
}

.auth-form .input-icon input {
    padding-right: 45px;
    position: relative;
    z-index: 1;
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

.auth-form input.valid {
    border-color: #48bb78;
    background-color: #f0fff4;
    padding-right: 45px;
}

.auth-form input.valid:focus {
    box-shadow: 0 0 0 3px rgba(72, 187, 120, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
}

.auth-form input.invalid {
    border-color: #f56565;
    background-color: #fff5f5;
    animation: shake 0.4s ease;
    padding-right: 45px;
}

.auth-form input.invalid:focus {
    box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
    20%, 40%, 60%, 80% { transform: translateX(4px); }
}

.password-strength {
    margin-top: 10px;
    display: none;
    animation: slideDown 0.3s ease;
}

.password-strength.active {
    display: block;
}

.strength-bar {
    height: 5px;
    background: #edf2f7;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 8px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 10px;
}

.strength-fill.weak {
    width: 33%;
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
}

.strength-fill.medium {
    width: 66%;
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
}

.strength-fill.strong {
    width: 100%;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

.strength-text {
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.strength-text::before {
    content: '●';
    font-size: 14px;
}

.strength-text.weak { color: #f56565; }
.strength-text.medium { color: #ed8936; }
.strength-text.strong { color: #48bb78; }

.validation-message {
    margin-top: 8px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    animation: slideDown 0.3s ease;
    font-weight: 600;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.validation-message.success {
    color: #28a745;
}

.validation-message.success i {
    animation: checkPop 0.4s ease;
}

@keyframes checkPop {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.validation-message.error {
    color: #dc3545;
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
            <h1 class="hero-title">Welcome to Forethink Health</h1>
            <p class="hero-description">Join our community and get access to premium health products and services tailored to your needs.</p>
            
            <div class="hero-features">
                <div class="hero-feature">
                    <i class="fas fa-shield-alt"></i>
                    <div class="hero-feature-text">
                        <h4>Secure & Private</h4>
                        <p>Your data is encrypted and protected</p>
                    </div>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-shipping-fast"></i>
                    <div class="hero-feature-text">
                        <h4>Fast Delivery</h4>
                        <p>Quick and reliable shipping worldwide</p>
                    </div>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-headset"></i>
                    <div class="hero-feature-text">
                        <h4>24/7 Support</h4>
                        <p>Always here to help you</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Section - Right Side -->
    <div class="auth-container">
        <div class="form-header">
            <h2>Create Account</h2>
            <p>Start your journey with us today</p>
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
        
        <form class="auth-form" method="POST" id="registerForm">
            <div class="form-group">
                <label for="full_name">
                    Full Name<span class="required-star">*</span>
                </label>
                <input type="text" id="full_name" name="full_name" required 
                       placeholder="Enter your full name"
                       value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                <div id="nameValidation" class="validation-message"></div>
            </div>
            
            <div class="form-group">
                <label for="email">
                    Email Address<span class="required-star">*</span>
                </label>
                <input type="email" id="email" name="email" required 
                       placeholder="Enter your email"
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                <div id="emailValidation" class="validation-message"></div>
            </div>
            
            <div class="form-group">
                <label for="phone">
                    Phone Number<span class="optional-badge">Optional</span>
                </label>
                <input type="tel" id="phone" name="phone" 
                       placeholder="Enter your phone number"
                       value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        Password<span class="required-star">*</span>
                    </label>
                    <div class="input-icon">
                        <input type="password" id="password" name="password" required 
                               minlength="6" placeholder="Create password">
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        Confirm Password<span class="required-star">*</span>
                    </label>
                    <div class="input-icon">
                        <input type="password" id="confirm_password" name="confirm_password" required
                               placeholder="Confirm password">
                        <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                    </div>
                    <div id="confirmValidation" class="validation-message"></div>
                </div>
            </div>
            
            <button type="submit" id="submitBtn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
            
            <div class="auth-links">
                <p>Already have an account? <a href="<?php echo BASE_URL; ?>/login.php">Sign in</a></p>
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
// Real-time validation
const form = document.getElementById('registerForm');
const fullNameInput = document.getElementById('full_name');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const submitBtn = document.getElementById('submitBtn');

// Password toggle functionality
const togglePassword = document.getElementById('togglePassword');
const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');

if (togglePassword) {
    togglePassword.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

if (toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
        confirmPasswordInput.type = type;
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

// Name validation
fullNameInput.addEventListener('input', function() {
    const nameValidation = document.getElementById('nameValidation');
    const value = this.value.trim();
    
    if (value.length === 0) {
        this.classList.remove('valid', 'invalid');
        nameValidation.innerHTML = '';
        return;
    }
    
    if (value.length < 3) {
        this.classList.remove('valid');
        this.classList.add('invalid');
        nameValidation.innerHTML = '<i class="fas fa-times-circle"></i> Name must be at least 3 characters';
        nameValidation.className = 'validation-message error';
    } else {
        this.classList.remove('invalid');
        this.classList.add('valid');
        nameValidation.innerHTML = '<i class="fas fa-check-circle"></i> Valid name';
        nameValidation.className = 'validation-message success';
    }
});

// Email validation
emailInput.addEventListener('input', function() {
    const emailValidation = document.getElementById('emailValidation');
    const value = this.value.trim();
    
    if (value.length === 0) {
        this.classList.remove('valid', 'invalid');
        emailValidation.innerHTML = '';
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(value)) {
        this.classList.remove('valid');
        this.classList.add('invalid');
        emailValidation.innerHTML = '<i class="fas fa-times-circle"></i> Please enter a valid email';
        emailValidation.className = 'validation-message error';
    } else {
        this.classList.remove('invalid');
        this.classList.add('valid');
        emailValidation.innerHTML = '<i class="fas fa-check-circle"></i> Valid email';
        emailValidation.className = 'validation-message success';
    }
});

// Password strength
passwordInput.addEventListener('input', function() {
    const strengthIndicator = document.getElementById('passwordStrength');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    const value = this.value;
    
    if (value.length === 0) {
        strengthIndicator.classList.remove('active');
        this.classList.remove('valid', 'invalid');
        return;
    }
    
    strengthIndicator.classList.add('active');
    
    let strength = 0;
    if (value.length >= 6) strength++;
    if (value.length >= 10) strength++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) strength++;
    if (/\d/.test(value)) strength++;
    if (/[^a-zA-Z0-9]/.test(value)) strength++;
    
    strengthFill.className = 'strength-fill';
    strengthText.className = 'strength-text';
    
    if (strength <= 2) {
        strengthFill.classList.add('weak');
        strengthText.classList.add('weak');
        strengthText.textContent = 'Weak password';
        this.classList.remove('valid');
        this.classList.add('invalid');
    } else if (strength <= 3) {
        strengthFill.classList.add('medium');
        strengthText.classList.add('medium');
        strengthText.textContent = 'Medium password';
        this.classList.remove('invalid');
        this.classList.add('valid');
    } else {
        strengthFill.classList.add('strong');
        strengthText.classList.add('strong');
        strengthText.textContent = 'Strong password';
        this.classList.remove('invalid');
        this.classList.add('valid');
    }
    
    // Re-validate confirm password if it has value
    if (confirmPasswordInput.value) {
        confirmPasswordInput.dispatchEvent(new Event('input'));
    }
});

// Confirm password validation
confirmPasswordInput.addEventListener('input', function() {
    const confirmValidation = document.getElementById('confirmValidation');
    const value = this.value;
    const passwordValue = passwordInput.value;
    
    if (value.length === 0) {
        this.classList.remove('valid', 'invalid');
        confirmValidation.innerHTML = '';
        return;
    }
    
    if (value !== passwordValue) {
        this.classList.remove('valid');
        this.classList.add('invalid');
        confirmValidation.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
        confirmValidation.className = 'validation-message error';
    } else {
        this.classList.remove('invalid');
        this.classList.add('valid');
        confirmValidation.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
        confirmValidation.className = 'validation-message success';
    }
});

// Form submission
form.addEventListener('submit', function(e) {
    // Validate all fields
    const isValid = 
        fullNameInput.value.trim().length >= 3 &&
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim()) &&
        passwordInput.value.length >= 6 &&
        passwordInput.value === confirmPasswordInput.value;
    
    if (!isValid) {
        e.preventDefault();
        
        // Shake invalid fields
        if (fullNameInput.value.trim().length < 3) {
            fullNameInput.classList.add('invalid');
            fullNameInput.focus();
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
            emailInput.classList.add('invalid');
        }
        if (passwordInput.value.length < 6) {
            passwordInput.classList.add('invalid');
        }
        if (passwordInput.value !== confirmPasswordInput.value) {
            confirmPasswordInput.classList.add('invalid');
        }
        
        return;
    }
    
    // Disable button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
});

// Clear validation on blur if empty
[fullNameInput, emailInput, passwordInput, confirmPasswordInput].forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            this.classList.remove('valid', 'invalid');
        }
    });
});
</script>