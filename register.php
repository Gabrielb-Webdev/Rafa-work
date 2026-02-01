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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    overflow-x: hidden;
    margin: 0;
}

/* Animated background */
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
    animation: float 15s ease-in-out infinite;
    pointer-events: none;
    z-index: 0;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) scale(1); }
    50% { transform: translateY(-20px) scale(1.05); }
}

.auth-wrapper {
    width: 100%;
    max-width: 500px;
    padding: 0;
    position: relative;
    z-index: 1;
}

.auth-container {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    box-shadow: 
        0 30px 90px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(255, 255, 255, 0.5) inset;
    padding: 50px 45px;
    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
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

.auth-logo {
    width: 90px;
    height: 90px;
    margin: 0 auto 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 22px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    padding: 18px;
    animation: logoFloat 3s ease-in-out infinite;
}

@keyframes logoFloat {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
}

.auth-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: brightness(0) invert(1);
}

.auth-container h2 {
    text-align: center;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 32px;
    font-weight: 900;
    letter-spacing: -0.5px;
}

.auth-subtitle {
    text-align: center;
    color: #6c757d;
    margin-bottom: 35px;
    font-size: 15px;
    font-weight: 500;
}

.auth-form .form-group {
    margin-bottom: 22px;
    position: relative;
}

.auth-form label {
    display: block;
    margin-bottom: 10px;
    font-weight: 700;
    color: #2c3e50;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.auth-form .input-icon {
    position: relative;
}

.auth-form .input-icon i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 17px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 2;
}

.auth-form .input-icon input {
    padding-left: 52px;
}

.auth-form .input-icon input:focus ~ i {
    color: #667eea;
    transform: translateY(-50%) scale(1.15);
}

.password-toggle {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 3;
    font-size: 16px;
    padding: 4px;
}

.password-toggle:hover {
    color: #667eea;
}

.auth-form input {
    width: 100%;
    padding: 16px 18px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: #f8f9fa;
    font-weight: 500;
    color: #2c3e50;
}

.auth-form input::placeholder {
    color: #adb5bd;
    font-weight: 400;
}

.auth-form input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
    background-color: #fff;
    transform: translateY(-1px);
}

.auth-form input:hover:not(:focus) {
    border-color: #d1d5db;
}

.auth-form input.valid {
    border-color: #28a745;
    background-color: #f0fff4;
}

.auth-form input.valid:focus {
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.12);
}

.auth-form input.invalid {
    border-color: #dc3545;
    background-color: #fff5f5;
    animation: shake 0.4s ease;
}

.auth-form input.invalid:focus {
    box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.12);
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-8px); }
    75% { transform: translateX(8px); }
}

.password-strength {
    margin-top: 8px;
    display: none;
}

.password-strength.active {
    display: block;
}

.strength-bar {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 6px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-fill.weak {
    width: 33%;
    background: #dc3545;
}

.strength-fill.medium {
    width: 66%;
    background: #ffc107;
}

.strength-fill.strong {
    width: 100%;
    background: #28a745;
}

.strength-text {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.strength-text.weak { color: #dc3545; }
.strength-text.medium { color: #ffc107; }
.strength-text.strong { color: #28a745; }

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
    padding: 18px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 10px;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
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
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
}

.auth-form button:active {
    transform: translateY(-1px);
}

.auth-form button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.auth-links {
    text-align: center;
    margin-top: 28px;
    padding-top: 28px;
    border-top: 2px solid #f0f0f0;
}

.auth-links p {
    color: #6c757d;
    font-size: 15px;
    font-weight: 500;
}

.auth-links a {
    color: #667eea;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
    position: relative;
}

.auth-links a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.auth-links a:hover::after {
    width: 100%;
}

.auth-links a:hover {
    color: #764ba2;
}

.back-home {
    text-align: center;
    margin-top: 24px;
}

.back-home a {
    color: white;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.back-home a:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    animation: slideDown 0.4s ease;
    border: 2px solid;
}

.alert-error {
    background: #fff5f5;
    color: #dc3545;
    border-color: #dc3545;
}

.alert-success {
    background: #f0fff4;
    color: #28a745;
    border-color: #28a745;
}

.alert i {
    font-size: 20px;
}

@media (max-width: 576px) {
    .auth-container {
        padding: 40px 30px;
    }
    
    .auth-container h2 {
        font-size: 28px;
    }
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
                <label for="full_name">Full Name *</label>
                <div class="input-icon">
                    <input type="text" id="full_name" name="full_name" required 
                           placeholder="John Doe"
                           value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                    <i class="fas fa-user"></i>
                </div>
                <div id="nameValidation" class="validation-message"></div>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <div class="input-icon">
                    <input type="email" id="email" name="email" required 
                           placeholder="your@email.com"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <i class="fas fa-envelope"></i>
                </div>
                <div id="emailValidation" class="validation-message"></div>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone (Optional)</label>
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
                <label for="confirm_password">Confirm Password *</label>
                <div class="input-icon">
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Repeat your password">
                    <i class="fas fa-lock"></i>
                    <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                </div>
                <div id="confirmValidation" class="validation-message"></div>
            </div>
            
            <button type="submit" id="submitBtn">
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
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
}

if (toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('click', function() {
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