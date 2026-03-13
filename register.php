<?php
/**
 * Register Page
 * @version 0.5
 * @date 2026-02-02
 * Clean rebuild - no duplicate code
 */
require_once __DIR__ . '/config/config.php';

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
            $stmt = executeQuery("SELECT id FROM users WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                $error = 'This email is already registered';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                executeQuery(
                    "INSERT INTO users (email, password, full_name, phone, role) VALUES (?, ?, ?, ?, 'customer')",
                    [$email, $hashed_password, $full_name, $phone]
                );
                
                $success = 'Registration successful. You can now log in.';
                
                $stmt = executeQuery("SELECT * FROM users WHERE email = ?", [$email]);
                $user = $stmt->fetch();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                if (!empty($_SESSION['cart'])) {
                    try {
                        foreach ($_SESSION['cart'] as $productId => $item) {
                            executeQuery(
                                "INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
                                [$user['id'], $productId, $item['quantity']]
                            );
                        }
                    } catch (Exception $e) {}
                }
                
                $_SESSION['cart_loaded'] = true;
                header("refresh:2;url=" . BASE_URL . "/");
            }
        } catch (Exception $e) {
            $error = 'Error creating account. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        .top-bar, header, footer { display: none !important; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .wrapper {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }
        
        .logo-box {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .logo-box img {
            width: 90%;
            height: 90%;
            object-fit: contain;
        }
        
        .hero h1 {
            font-size: 40px;
            font-weight: 900;
            margin-bottom: 15px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .hero p {
            font-size: 18px;
            opacity: 0.9;
            text-align: center;
            line-height: 1.6;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }
        
        .features {
            margin-top: 40px;
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }
        
        .feature {
            background: rgba(255,255,255,0.15);
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .feature i {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .feature h4 {
            margin: 0 0 4px 0;
            font-size: 15px;
            font-weight: 700;
        }
        
        .feature p {
            margin: 0;
            font-size: 13px;
            opacity: 0.9;
            text-align: left;
        }
        
        .form-container {
            padding: 60px;
        }
        
        .form-header h2 {
            font-size: 32px;
            font-weight: 900;
            color: #1a202c;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #718096;
            font-size: 15px;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c00;
        }
        
        .alert-success {
            background: #efe;
            border: 1px solid #cfc;
            color: #060;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }
        
        .required { color: #e53e3e; }
        
        .optional {
            font-size: 11px;
            background: #edf2f7;
            color: #718096;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            margin-left: 6px;
        }
        
        .input-wrap {
            position: relative;
        }
        
        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        input.is-valid {
            border-color: #48bb78;
            background: #f0fff4;
        }
        
        input.is-invalid {
            border-color: #f56565;
            background: #fff5f5;
        }
        
        .input-wrap input {
            padding-right: 45px;
        }
        
        .toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            font-size: 16px;
            padding: 8px;
        }
        
        .toggle-btn:hover {
            color: #667eea;
        }
        
        .msg {
            margin-top: 6px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .msg.success { color: #28a745; }
        .msg.error { color: #dc3545; }
        
        .strength {
            margin-top: 8px;
            display: none;
        }
        
        .strength.show { display: block; }
        
        .strength-bar {
            height: 4px;
            background: #edf2f7;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        
        .strength-fill {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }
        
        .strength-fill.weak { width: 33%; background: #f56565; }
        .strength-fill.medium { width: 66%; background: #ed8936; }
        .strength-fill.strong { width: 100%; background: #48bb78; }
        
        .strength-text {
            font-size: 12px;
            font-weight: 600;
        }
        
        .strength-text.weak { color: #f56565; }
        .strength-text.medium { color: #ed8936; }
        .strength-text.strong { color: #48bb78; }
        
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.2s;
        }
        
        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .links p {
            margin: 0 0 15px 0;
            color: #718096;
            font-size: 14px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .links a:hover {
            color: #764ba2;
        }
        
        @media (max-width: 968px) {
            .wrapper {
                grid-template-columns: 1fr;
            }
            .hero {
                display: none;
            }
        }
        
        @media (max-width: 640px) {
            .form-container {
                padding: 40px 25px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="hero">
            <div class="logo-box">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo">
            </div>
            <h1>Welcome to Forethink Health</h1>
            <p>Join our community and get access to premium health products and services tailored to your needs.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <h4>Secure & Private</h4>
                        <p>Your data is encrypted and protected</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-shipping-fast"></i>
                    <div>
                        <h4>Fast Delivery</h4>
                        <p>Quick and reliable shipping worldwide</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-headset"></i>
                    <div>
                        <h4>24/7 Support</h4>
                        <p>Always here to help you</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-container">
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
            
            <form method="POST" id="form">
                <div class="form-group">
                    <label>Full Name<span class="required">*</span></label>
                    <input type="text" name="full_name" id="name" required 
                           placeholder="Enter your full name"
                           value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                    <div id="nameMsg" class="msg"></div>
                </div>
                
                <div class="form-group">
                    <label>Email Address<span class="required">*</span></label>
                    <input type="email" name="email" id="email" required 
                           placeholder="Enter your email"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <div id="emailMsg" class="msg"></div>
                </div>
                
                <div class="form-group">
                    <label>Phone Number<span class="optional">Optional</span></label>
                    <input type="tel" name="phone" id="phone" 
                           placeholder="Enter your phone number"
                           value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Password<span class="required">*</span></label>
                        <div class="input-wrap">
                            <input type="password" name="password" id="pass" required 
                                   minlength="6" placeholder="Create password">
                            <button type="button" class="toggle-btn" id="togglePass">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="strength" class="strength">
                            <div class="strength-bar">
                                <div id="strengthBar" class="strength-fill"></div>
                            </div>
                            <div id="strengthText" class="strength-text"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password<span class="required">*</span></label>
                        <div class="input-wrap">
                            <input type="password" name="confirm_password" id="confirm" required 
                                   placeholder="Confirm password">
                            <button type="button" class="toggle-btn" id="toggleConfirm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="confirmMsg" class="msg"></div>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn" id="btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
                
                <div class="links">
                    <p>Already have an account? <a href="<?php echo BASE_URL; ?>/login">Sign in</a></p>
                    <a href="<?php echo BASE_URL; ?>">
                        <i class="fas fa-arrow-left"></i> Back to home
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    (function() {
        const form = document.getElementById('form');
        const name = document.getElementById('name');
        const email = document.getElementById('email');
        const pass = document.getElementById('pass');
        const confirm = document.getElementById('confirm');
        const btn = document.getElementById('btn');
        
        // Toggle password
        document.getElementById('togglePass').addEventListener('click', function() {
            const type = pass.type === 'password' ? 'text' : 'password';
            pass.type = type;
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
        
        document.getElementById('toggleConfirm').addEventListener('click', function() {
            const type = confirm.type === 'password' ? 'text' : 'password';
            confirm.type = type;
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
        
        // Name validation
        name.addEventListener('input', function() {
            const msg = document.getElementById('nameMsg');
            const val = this.value.trim();
            
            if (!val) {
                this.classList.remove('is-valid', 'is-invalid');
                msg.innerHTML = '';
                return;
            }
            
            if (val.length < 3) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                msg.innerHTML = '<i class="fas fa-times-circle"></i> Name must be at least 3 characters';
                msg.className = 'msg error';
            } else {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
                msg.innerHTML = '<i class="fas fa-check-circle"></i> Valid name';
                msg.className = 'msg success';
            }
        });
        
        // Email validation
        email.addEventListener('input', function() {
            const msg = document.getElementById('emailMsg');
            const val = this.value.trim();
            
            if (!val) {
                this.classList.remove('is-valid', 'is-invalid');
                msg.innerHTML = '';
                return;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                msg.innerHTML = '<i class="fas fa-times-circle"></i> Please enter a valid email';
                msg.className = 'msg error';
            } else {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
                msg.innerHTML = '<i class="fas fa-check-circle"></i> Valid email';
                msg.className = 'msg success';
            }
        });
        
        // Password strength
        pass.addEventListener('input', function() {
            const div = document.getElementById('strength');
            const bar = document.getElementById('strengthBar');
            const text = document.getElementById('strengthText');
            const val = this.value;
            
            if (!val || val.length < 3) {
                div.classList.remove('show');
                this.classList.remove('is-valid', 'is-invalid');
                return;
            }
            
            div.classList.add('show');
            
            let s = 0;
            if (val.length >= 6) s++;
            if (val.length >= 10) s++;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) s++;
            if (/\d/.test(val)) s++;
            if (/[^a-zA-Z0-9]/.test(val)) s++;
            
            bar.className = 'strength-fill';
            text.className = 'strength-text';
            
            if (s <= 2) {
                bar.classList.add('weak');
                text.classList.add('weak');
                text.textContent = 'Weak password';
                if (val.length >= 6) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            } else if (s <= 3) {
                bar.classList.add('medium');
                text.classList.add('medium');
                text.textContent = 'Medium password';
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                bar.classList.add('strong');
                text.classList.add('strong');
                text.textContent = 'Strong password';
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
            
            if (confirm.value) confirm.dispatchEvent(new Event('input'));
        });
        
        // Confirm password
        confirm.addEventListener('input', function() {
            const msg = document.getElementById('confirmMsg');
            const val = this.value;
            
            if (!val) {
                this.classList.remove('is-valid', 'is-invalid');
                msg.innerHTML = '';
                return;
            }
            
            if (val !== pass.value) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                msg.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
                msg.className = 'msg error';
            } else {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
                msg.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
                msg.className = 'msg success';
            }
        });
        
        // Form submit
        form.addEventListener('submit', function(e) {
            const valid = 
                name.value.trim().length >= 3 &&
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()) &&
                pass.value.length >= 6 &&
                pass.value === confirm.value;
            
            if (!valid) {
                e.preventDefault();
                if (name.value.trim().length < 3) name.classList.add('is-invalid');
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) email.classList.add('is-invalid');
                if (pass.value.length < 6) pass.classList.add('is-invalid');
                if (pass.value !== confirm.value) confirm.classList.add('is-invalid');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
        });
    })();
    </script>
</body>
</html>

<?php include __DIR__ . '/includes/footer.php'; ?>
