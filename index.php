<?php
/**
 * =====================================================
 * MEDICAONLINE - PÁGINA PRINCIPAL (INDEX)
 * =====================================================
 * 
 * Descripción: Página de inicio de MediCareOnline - Tu Farmacia Digital
 * Autor: MediCareOnline Development Team
 * Fecha: 2026-01-07
 * 
 * Funcionalidades:
 * - Mostrar medicamentos destacados y novedades
 * - Categorías de medicina y suplementos
 * - Sistema de bienvenida para nuevos usuarios
 * - Integración con carrito y wishlist
 * - Diseño responsivo moderno en tonos cyan
 */

// =====================================================
// CONFIGURACIÓN INICIAL
// =====================================================

// Activar reporte de errores solo en desarrollo
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
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos. <a href='check_errors.php'>Ver diagnóstico</a>");
}

// =====================================================
// FUNCIONES AUXILIARES
// =====================================================

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

// =====================================================
// INICIALIZACIÓN
// =====================================================

$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();

// =====================================================
// PROCESAMIENTO DE DATOS
// =====================================================

$showWelcomeMessage = isset($_GET['registered']) && $_GET['registered'] == '1';

// Obtener productos
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 8");
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8");
    $new_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $featured_products = [];
    $new_products = [];
    $categories = [];
}

// Carrito
$cartCount = 0;
$cartTotal = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
    // Calcular total
    foreach ($_SESSION['cart'] as $productId => $qty) {
        try {
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $cartTotal += $product['price'] * $qty;
            }
        } catch (Exception $e) {}
    }
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
    <?php if (file_exists('assets/css/pharmacy-style.css')): ?>
    <link rel="stylesheet" href="assets/css/pharmacy-style.css?v=<?= time() ?>">
    <?php endif; ?>
    <?php if (file_exists('assets/css/style.css')): ?>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <?php endif; ?>
    <style>
        :root {
            --primary-color: #00D4FF;
            --primary-dark: #00A8CC;
            --secondary-color: #0088AA;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; color: #333; }
        .main-header { background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%); padding: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .logo { display: flex; align-items: center; color: white; text-decoration: none; font-size: 1.8rem; font-weight: 700; }
        .logo i { font-size: 2.5rem; margin-right: 15px; }
        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-links a { color: white; text-decoration: none; font-weight: 500; transition: opacity 0.3s; }
        .nav-links a:hover { opacity: 0.8; }
        .btn-cart { background: white; color: var(--primary-color); padding: 8px 20px; border-radius: 50px; font-weight: 600; }
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
                        <?php if ($isLoggedIn && $currentUser): ?>
                            <a href="profile.php"><?= htmlspecialchars($currentUser['first_name'] ?? 'Mi Cuenta') ?></a>
                            <a href="logout.php">Salir</a>
                        <?php else: ?>
                            <a href="login.php">Iniciar Sesión</a>
                            <a href="register.php">Registrarse</a>
                        <?php endif; ?>
                        <a href="carrito.php" class="btn-cart">
                            <i class="fas fa-shopping-cart"></i> <?= $cartCount > 0 ? "($cartCount) $" . number_format($cartTotal, 2) : '(0) $0.00' ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

<?php if ($showWelcomeMessage): ?>
<div class="container mt-3">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>¡Bienvenido a MediCareOnline!</strong> Tu cuenta ha sido creada exitosamente. Ya puedes empezar a
        explorar nuestros productos farmacéuticos.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['logout']) && $_GET['logout'] == '1'): ?>
<div class="container mt-3">
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-sign-out-alt me-2"></i>
        <strong>¡Hasta luego!</strong> Has cerrado sesión exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<style>
/* Hero Section */
.hero-pharmacy { background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%); padding: 80px 0; min-height: 500px; }
.hero-title { font-size: 3.5rem; font-weight: 700; color: white; margin-bottom: 20px; }
.hero-subtitle { color: white; font-size: 1.2rem; margin-bottom: 30px; opacity: 0.95; }
.btn-primary-pharmacy { background: white; color: var(--primary-color); padding: 15px 40px; border-radius: 50px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.3s; }
.btn-primary-pharmacy:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0,0,0,0.2); }
/* Services */
.services-section { padding: 80px 0; background: white; }
.service-card { text-align: center; padding: 40px 20px; border-radius: 15px; transition: all 0.3s; }
.service-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,212,255,0.2); }
.service-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; color: white; }
/* Products */
.products-section { padding: 80px 0; background: #f8f9fa; }
.section-title { font-size: 2.5rem; font-weight: 700; text-align: center; margin-bottom: 50px; color: #333; position: relative; padding-bottom: 15px; }
.section-title::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 4px; background: linear-gradient(90deg, #00D4FF 0%, #00A8CC 100%); border-radius: 2px; }
.product-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: all 0.3s; margin-bottom: 30px; }
.product-card:hover { transform: translateY(-8px); box-shadow: 0 10px 30px rgba(0,212,255,0.2); }
.product-image { width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; padding: 20px; }
.product-image img { max-width: 100%; max-height: 100%; object-fit: contain; }
.product-body { padding: 20px; }
.product-name { font-size: 1.1rem; font-weight: 600; margin-bottom: 10px; min-height: 50px; color: #333; }
.product-price { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); margin-bottom: 15px; }
.btn-add-cart { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 50px; width: 100%; font-weight: 600; transition: all 0.3s; cursor: pointer; }
.btn-add-cart:hover { background: var(--primary-dark); transform: translateY(-2px); }
.btn-see-more { background: transparent; color: var(--primary-color); border: 2px solid var(--primary-color); padding: 12px 40px; border-radius: 50px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.3s; }
.btn-see-more:hover { background: var(--primary-color); color: white; transform: translateY(-2px); }
/* About */
.about-section { padding: 80px 0; background: white; }
.about-title { font-size: 2.5rem; font-weight: 700; color: #333; margin-bottom: 20px; }
.about-text { color: #666; font-size: 1.1rem; line-height: 1.8; margin-bottom: 30px; }
/* Testimonials */
.testimonials-section { padding: 80px 0; background: #f8f9fa; }
.testimonial-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-align: center; }
.testimonial-image { width: 120px; height: 120px; border-radius: 50%; overflow: hidden; margin: 0 auto 20px; border: 5px solid var(--primary-color); }
.testimonial-image img { width: 100%; height: 100%; object-fit: cover; }
.testimonial-name { font-size: 1.5rem; font-weight: 700; color: #333; margin-bottom: 15px; }
.testimonial-text { color: #666; font-size: 1rem; line-height: 1.8; font-style: italic; }
/* Newsletter */
.newsletter-section { padding: 80px 0; background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%); }
.newsletter-title { font-size: 2.5rem; font-weight: 700; color: white; margin-bottom: 15px; }
.newsletter-text { color: white; font-size: 1.1rem; opacity: 0.95; }
.newsletter-input { border: none; padding: 15px 25px; font-size: 1rem; border-radius: 50px 0 0 50px; flex: 1; }
.btn-subscribe { background: #1a1a1a; color: white; border: none; padding: 15px 40px; font-size: 1rem; font-weight: 600; border-radius: 0 50px 50px 0; transition: all 0.3s; }
.btn-subscribe:hover { background: #333; }
/* Contact */
.contact-section { padding: 80px 0; background: white; }
.contact-input, .contact-textarea { border: 2px solid #e0e0e0; padding: 12px 20px; font-size: 1rem; border-radius: 8px; transition: all 0.3s; width: 100%; }
.contact-input:focus, .contact-textarea:focus { border-color: var(--primary-color); outline: none; box-shadow: 0 0 0 3px rgba(0,212,255,0.1); }
/* Footer */
.footer { background: #1a1a1a; color: white; padding: 40px 0; text-align: center; }
.footer-logo { font-size: 1.8rem; font-weight: 700; margin-bottom: 20px; }
@media (max-width: 768px) {
    .hero-title { font-size: 2.5rem; }
    .section-title { font-size: 2rem; }
    .nav-links { flex-direction: column; gap: 15px; }
}
</style>

<div class="container-fluid p-0">
    <!-- Hero Section -->
    <section class="hero-pharmacy">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Welcome To Our<br>Online Medicine</h1>
                    <p class="hero-subtitle">Tu farmacia digital de confianza. Encuentra los mejores medicamentos y suplementos con entrega rápida y segura.</p>
                    <a href="productos.php" class="btn-primary-pharmacy">
                        <i class="fas fa-pills me-2"></i> Buy Now
                    </a>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/medicine-hero.png" alt="Medicine" class="img-fluid" 
                         onerror="this.src='https://via.placeholder.com/500x400/00D4FF/ffffff?text=MediCare'" style="max-width: 100%;">
                </div>
            </div>
        </div>
    </section>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Servicios Section -->
<section class="services-pharmacy py-5">
    <div class="container">
        <div class="row justify-content-center g-4">
            <!-- Fast Delivery -->
            <div class="col-md-4">
                <div class="service-card-pharmacy text-center">
                    <div class="service-icon-pharmacy">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3 class="service-title-pharmacy">FAST DELIVERY</h3>
                    <p class="service-description-pharmacy">It is a long established fact that a reader will be distracted by the readable</p>
                </div>
            </div>

            <!-- Online of Government -->
            <div class="col-md-4">
                <div class="service-card-pharmacy text-center">
                    <div class="service-icon-pharmacy">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <h3 class="service-title-pharmacy">ONLINE OF GOVERNMENT</h3>
                    <p class="service-description-pharmacy">It is a long established fact that a reader will be distracted by the readable</p>
                </div>
            </div>

            <!-- Support 24*7 -->
            <div class="col-md-4">
                <div class="service-card-pharmacy text-center">
                    <div class="service-icon-pharmacy">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="service-title-pharmacy">SUPPORT (24*7)</h3>
                    <p class="service-description-pharmacy">It is a long established fact that a reader will be distracted by the readable</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección Promocional - 10% Descuento -->
<section class="discount-section-pharmacy py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="discount-content-pharmacy">
                    <h2 class="discount-title-pharmacy">YOU GET<br>ANY MEDICINE<br>ON <span class="highlight-percentage">10% DISCOUNT</span></h2>
                    <p class="discount-description-pharmacy">It is a long established fact that a reader will be distracted by the readable</p>
                    <a href="productos.php" class="btn btn-pharmacy-secondary">Shop Now</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="discount-image-pharmacy">
                    <img src="assets/images/medicine-discount.png" alt="Medicine Discount" class="img-fluid" onerror="this.src='assets/images/products/product1.jpg'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Productos - MEDICINE & HEALTH -->
<section class="products-section-pharmacy py-5">
    <div class="container">
        <h2 class="section-title-pharmacy text-center mb-5">MEDICINE & HEALTH</h2>
        <div class="row g-4">
            <?php if (!empty($new_products)): ?>
                <?php foreach (array_slice($new_products, 0, 8) as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product-card-pharmacy">
                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                        <span class="badge-discount-pharmacy">SALE</span>
                        <?php endif; ?>
                        <div class="product-image-pharmacy">
                            <?php 
                            $product_image = 'assets/images/products/product1.jpg';
                            if (!empty($product['primary_image'])) {
                                $product_image = 'uploads/products/' . htmlspecialchars($product['primary_image']);
                            } elseif (!empty($product['image_url'])) {
                                $product_image = htmlspecialchars($product['image_url']);
                            }
                            ?>
                            <img src="<?php echo $product_image; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='assets/images/products/product1.jpg'">
                        </div>
                        <div class="product-body-pharmacy">
                            <h5 class="product-name-pharmacy"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <div class="product-rating-pharmacy">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="product-price-pharmacy">
                                <span class="price-current-pharmacy">$<?php echo number_format($product['price'], 0); ?></span>
                                <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                <span class="price-old-pharmacy">$<?php echo number_format($product['sale_price'], 0); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-add-pharmacy btn-add-to-cart-modern" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>"
                                    data-product-image="<?php echo htmlspecialchars($product_image); ?>">
                                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No hay productos disponibles en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-5">
            <a href="productos.php" class="btn btn-pharmacy-outline">See More</a>
        </div>
    </div>
</section>

<!-- Sección de VITAMINS & SUPPLEMENTS -->
<section class="vitamins-section-pharmacy py-5 bg-light">
    <div class="container">
        <h2 class="section-title-pharmacy text-center mb-5">VITAMINS & SUPPLEMENTS</h2>
        <div class="row g-4">
            <?php if (!empty($featured_products)): ?>
                <?php foreach (array_slice($featured_products, 0, 8) as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product-card-pharmacy">
                        <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                        <span class="badge-discount-pharmacy">SALE</span>
                        <?php endif; ?>
                        <div class="product-image-pharmacy">
                            <?php 
                            $product_image = 'assets/images/products/product1.jpg';
                            if (!empty($product['primary_image'])) {
                                $product_image = 'uploads/products/' . htmlspecialchars($product['primary_image']);
                            } elseif (!empty($product['image_url'])) {
                                $product_image = htmlspecialchars($product['image_url']);
                            }
                            ?>
                            <img src="<?php echo $product_image; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='assets/images/products/product1.jpg'">
                        </div>
                        <div class="product-body-pharmacy">
                            <h5 class="product-name-pharmacy"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <div class="product-rating-pharmacy">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="product-price-pharmacy">
                                <span class="price-current-pharmacy">$<?php echo number_format($product['price'], 0); ?></span>
                                <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                <span class="price-old-pharmacy">$<?php echo number_format($product['sale_price'], 0); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-add-pharmacy btn-add-to-cart-modern" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>"
                                    data-product-image="<?php echo htmlspecialchars($product_image); ?>">
                                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No hay productos disponibles en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-5">
            <a href="productos.php" class="btn btn-pharmacy-outline">See More</a>
        </div>
    </div>
</section>
<!-- Sección About Us -->
<section class="about-pharmacy py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-image-pharmacy">
                    <img src="assets/images/about-medicine.png" alt="About Us" class="img-fluid" onerror="this.src='assets/images/products/product1.jpg'">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content-pharmacy">
                    <h2 class="about-title-pharmacy">ABOUT US</h2>
                    <p class="about-description-pharmacy">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using, making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text.</p>
                    <a href="contacto.php" class="btn btn-pharmacy-primary">Read More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Testimonios -->
<section class="testimonials-pharmacy py-5 bg-light">
    <div class="container">
        <h2 class="section-title-pharmacy text-center mb-5">WHAT IS SAYS OUR CLIENTS</h2>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="testimonial-card-pharmacy">
                    <div class="testimonial-image-pharmacy">
                        <img src="assets/images/client-testimonial.jpg" alt="Mr. Denny Crood" onerror="this.src='assets/images/products/product1.jpg'">
                    </div>
                    <h5 class="testimonial-name-pharmacy">Mr. Denny Crood</h5>
                    <p class="testimonial-text-pharmacy">"Many or more Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the"</p>
                    <div class="testimonial-dots-pharmacy">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección Get New Medicines -->
<section class="newsletter-pharmacy py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="newsletter-content-pharmacy">
                    <h2 class="newsletter-title-pharmacy">Get New Medicines<br>Update!!!</h2>
                    <p class="newsletter-description-pharmacy">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration</p>
                </div>
            </div>
            <div class="col-lg-6">
                <form class="newsletter-form-pharmacy">
                    <div class="input-group">
                        <input type="email" class="form-control newsletter-input-pharmacy" placeholder="Enter Email" required>
                        <button type="submit" class="btn btn-pharmacy-subscribe">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Contacto -->
<section class="contact-section-pharmacy py-5">
    <div class="container">
        <h2 class="section-title-pharmacy text-center mb-5">REQUEST A CALL BACK</h2>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <form class="contact-form-pharmacy" action="contact_process.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control contact-input-pharmacy" placeholder="Name" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control contact-input-pharmacy" placeholder="Phone Number" name="apellido" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control contact-input-pharmacy" placeholder="Email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control contact-textarea-pharmacy" placeholder="Message" name="mensaje" rows="4" required></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn-primary-pharmacy w-100" style="width: auto !important; display: inline-block;">Send</button>
                    </div>
                </form>
            </div>
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
        <p>info@medicareonline.com | Soporte 24/7</p>
        <div class="mt-3">
            <a href="https://facebook.com" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
            <a href="https://instagram.com" class="text-white me-3"><i class="fab fa-instagram"></i></a>
            <a href="https://twitter.com" class="text-white"><i class="fab fa-twitter"></i></a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (file_exists('assets/js/main.js')): ?>
<script src="assets/js/main.js?v=<?= time() ?>"></script>
<?php endif; ?>
<?php if (file_exists('assets/js/cart-system-advanced.js')): ?>
<script src="assets/js/cart-system-advanced.js?v=<?= time() ?>"></script>
<?php endif; ?>
<?php if (file_exists('assets/js/wishlist-system.js')): ?>
<script src="assets/js/wishlist-system.js?v=<?= time() ?>"></script>
<?php endif; ?>
<script>
function addToCart(productId) {
    // Agregar al carrito via AJAX o redireccionar
    if (confirm('¿Deseas agregar este producto al carrito?')) {
        fetch('ajax/add-to-cart.php?v=<?= time() ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Producto agregado al carrito');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo agregar el producto'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar el producto');
        });
    }
}

// Efecto scroll suave
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
</body>
</html>