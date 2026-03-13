<?php
/**
 * Login Page
 * @version 0.5
 * @date 2026-02-02
 * Clean rebuild - no duplicate code
 */
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect('/');
}

$pageTitle = 'Login - Forethink Health';
$error = '';
$success = $_GET['registered'] ?? '';

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
                
                if (!empty($_SESSION['cart'])) {
                    try {
                        foreach ($_SESSION['cart'] as $productId => $item) {
                            $stmt = executeQuery(
                                "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", 
                                [$user['id'], $productId]
                            );
                            $existing = $stmt->fetch();
                            
                            if ($existing) {
                                $newQty = $existing['quantity'] + $item['quantity'];
                                executeQuery(
                                    "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", 
                                    [$newQty, $existing['id']]
                                );
                            } else {
                                executeQuery(
                                    "INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
                                    [$user['id'], $productId, $item['quantity']]
                                );
                            }
                        }
                    } catch (Exception $e) {}
                }
                
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
                } catch (Exception $e) {}
                
                $_SESSION['cart_loaded'] = true;
                
                if ($user['role'] === 'admin') {
                    redirect('/admin/index.php');
                } else {
                    redirect('/');
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
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }
        
        .required { color: #e53e3e; }
        
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
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #2d3748;
        }
        
        .remember input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }
        
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .forgot-link:hover {
            color: #764ba2;
        }
        
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
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="hero">
            <div class="logo-box">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo">
            </div>
            <h1>Welcome Back!</h1>
            <p>Access your account and continue your journey with premium health products.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <h4>Secure Login</h4>
                        <p>Your credentials are encrypted</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-shopping-bag"></i>
                    <div>
                        <h4>Your Orders</h4>
                        <p>Track and manage your purchases</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-heart"></i>
                    <div>
                        <h4>Personalized</h4>
                        <p>Experience tailored to your needs</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-container">
            <div class="form-header">
                <h2>Sign In</h2>
                <p>Welcome back! Please enter your details</p>
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
                    <span>Registration successful! Please log in.</span>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="form">
                <div class="form-group">
                    <label>Email Address<span class="required">*</span></label>
                    <input type="email" name="email" id="email" required 
                           placeholder="Enter your email"
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Password<span class="required">*</span></label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="pass" required 
                               placeholder="Enter your password">
                        <button type="button" class="toggle-btn" id="togglePass">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="submit-btn" id="btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
                
                <div class="links">
                    <p>Don't have an account? <a href="<?php echo BASE_URL; ?>/register">Sign up</a></p>
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
        const email = document.getElementById('email');
        const pass = document.getElementById('pass');
        const btn = document.getElementById('btn');
        
        // Toggle password
        document.getElementById('togglePass').addEventListener('click', function() {
            const type = pass.type === 'password' ? 'text' : 'password';
            pass.type = type;
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
        
        // Email validation
        email.addEventListener('input', function() {
            const val = this.value.trim();
            
            if (!val) {
                this.classList.remove('is-valid', 'is-invalid');
                return;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
        
        // Password validation
        pass.addEventListener('input', function() {
            const val = this.value;
            
            if (!val) {
                this.classList.remove('is-valid', 'is-invalid');
                return;
            }
            
            if (val.length < 6) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
        
        // Form submit
        form.addEventListener('submit', function(e) {
            const valid = 
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()) &&
                pass.value.length >= 6;
            
            if (!valid) {
                e.preventDefault();
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) email.classList.add('is-invalid');
                if (pass.value.length < 6) pass.classList.add('is-invalid');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
        });
    })();
    </script>
</body>
</html>

<?php include __DIR__ . '/includes/footer.php'; ?>
