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

<!-- Login Page -->
<section class="login-page-section">
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.jpeg" alt="Forethink Health">
            </div>
            
            <h2 class="login-title">Welcome</h2>
            <p class="login-subtitle">Log in to your Forethink Health account</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" required 
                               placeholder="your@email.com"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" required
                               placeholder="Enter your password">
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>
                
                <div class="forgot-password">
                    <a href="#">Forgot your password?</a>
                </div>
                
                <button type="submit" class="btn-login">
                    Login
                </button>
            </form>
            
            <div class="login-divider">
                <span>o</span>
            </div>
            
            <div class="login-footer">
                <p>Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php">Register here</a></p>
            </div>
        </div>
        
        <div class="back-home-link">
            <a href="<?php echo BASE_URL; ?>">
                <i class="fas fa-home"></i> Back to home
            </a>
        </div>
    </div>
</section>

<script>
// Toggle password visibility
document.querySelector('.toggle-password')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this;
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
