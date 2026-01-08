<?php
/**
 * =====================================================
 * INDEX SIMPLIFICADO - MEDICAONLINE
 * =====================================================
 * Versión independiente sin dependencias
 */

// Activar errores solo para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar sesión
session_start();

// Conexión a base de datos directa
$is_local = (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1'));

if ($is_local) {
    $host = 'localhost';
    $dbname = 'multigamer360';
    $username = 'root';
    $password = '';
} else {
    $host = 'localhost';
    $dbname = 'u851317150_mg360_db';
    $username = 'u851317150_mg360_user';
    $password = 'MultiGamer2025';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener productos de la BD
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 LIMIT 8");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}

// Usuario actual
$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = [];
if ($isLoggedIn) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $isLoggedIn = false;
    }
}

// Carrito
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCareOnline - Tu Farmacia Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00D4FF;
            --primary-dark: #00A8CC;
            --secondary-color: #0088AA;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
        }
        
        /* Header */
        .main-header {
            background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%);
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .logo i {
            font-size: 2.5rem;
            margin-right: 15px;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        
        .nav-links a:hover {
            opacity: 0.8;
        }
        
        .btn-cart {
            background: white;
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        /* Hero Section */
        .hero-pharmacy {
            background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%);
            padding: 80px 0;
            min-height: 500px;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
        }
        
        .hero-subtitle {
            color: white;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        .btn-primary-pharmacy {
            background: white;
            color: var(--primary-color);
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary-pharmacy:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        /* Services */
        .services-section {
            padding: 80px 0;
            background: white;
        }
        
        .service-card {
            text-align: center;
            padding: 40px 20px;
            border-radius: 15px;
            transition: all 0.3s;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,212,255,0.2);
        }
        
        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }
        
        /* Products */
        .products-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 50px;
            color: #333;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            margin-bottom: 30px;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0,212,255,0.2);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            padding: 20px;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .product-body {
            padding: 20px;
        }
        
        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            min-height: 50px;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .btn-add-cart {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-add-cart:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        
        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="index.php" class="logo">
                        <i class="fas fa-pills"></i>
                        <span>MediCare<span style="color: #1a1a1a;">Online</span></span>
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="nav-links justify-content-end">
                        <a href="productos.php">Productos</a>
                        <a href="contacto.php">Contacto</a>
                        <?php if ($isLoggedIn): ?>
                            <a href="profile.php"><?= htmlspecialchars($currentUser['first_name'] ?? 'Mi Cuenta') ?></a>
                        <?php else: ?>
                            <a href="login.php">Iniciar Sesión</a>
                        <?php endif; ?>
                        <a href="carrito.php" class="btn-cart">
                            <i class="fas fa-shopping-cart"></i> (<?= $cartCount ?>)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-pharmacy">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Welcome To Our<br>Online Medicine</h1>
                    <p class="hero-subtitle">Tu farmacia digital de confianza. Encuentra los mejores medicamentos y suplementos con entrega rápida y segura.</p>
                    <a href="productos.php" class="btn-primary-pharmacy">
                        <i class="fas fa-pills me-2"></i> Comprar Ahora
                    </a>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/medicine-hero.png" alt="Medicine" class="img-fluid" 
                         onerror="this.src='https://via.placeholder.com/500x400/00D4FF/ffffff?text=MediCare'">
                </div>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section class="services-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3>Fast Delivery</h3>
                        <p>Envío rápido a todo el país</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <h3>Online of Government</h3>
                        <p>Certificados y autorizados</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>Support 24/7</h3>
                        <p>Atención al cliente siempre</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products -->
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">MEDICINE & HEALTH</h2>
            <div class="row">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($product['primary_image'])): ?>
                                    <img src="uploads/products/<?= htmlspecialchars($product['primary_image']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         onerror="this.src='https://via.placeholder.com/200x200/00D4FF/ffffff?text=<?= urlencode(substr($product['name'], 0, 10)) ?>'">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/200x200/00D4FF/ffffff?text=<?= urlencode(substr($product['name'], 0, 10)) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php endif; ?>
                            </div>
                            <div class="product-body">
                                <h5 class="product-name"><?= htmlspecialchars($product['name']) ?></h5>
                                <div class="product-price">
                                    $<?= number_format($product['price'], 2) ?>
                                </div>
                                <button class="btn-add-cart" onclick="addToCart(<?= $product['id'] ?>)">
                                    <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <p>No hay productos disponibles. <a href="setup_pharmacy.php">Haz clic aquí para configurar la tienda</a></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-logo">
                <i class="fas fa-pills"></i> MediCareOnline
            </div>
            <p>&copy; <?= date('Y') ?> MediCareOnline. Todos los derechos reservados.</p>
            <p>info@medicareonline.com | +1 (555) 123-4567</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(productId) {
            alert('Producto agregado al carrito! ID: ' + productId);
            // Aquí iría la lógica AJAX para agregar al carrito
        }
    </script>
</body>
</html>
