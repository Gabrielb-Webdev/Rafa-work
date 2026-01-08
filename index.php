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

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/product_manager.php';
require_once 'config/user_manager.php';

// =====================================================
// INICIALIZACIÓN DE MANAGERS
// =====================================================

// Crear instancias de los managers para manejo de datos
$productManager = new ProductManager($pdo);
$userManager = new UserManager($pdo);

// =====================================================
// PROCESAMIENTO DE DATOS
// =====================================================

// Verificar si el usuario acaba de registrarse para mostrar mensaje de bienvenida
$showWelcomeMessage = isset($_GET['registered']) && $_GET['registered'] == '1';

// Obtener datos principales para la página de inicio
try {
    $featured_products = $productManager->getFeaturedProducts(10);     // Productos destacados (máx 10)
    $new_products = $productManager->getNewProducts(10);               // Productos nuevos (máx 10)
    $categories = $productManager->getCategories();                    // Categorías disponibles
} catch (Exception $e) {
    error_log("Error en index.php: " . $e->getMessage());
    $featured_products = [];
    $new_products = [];
    $categories = [];
}

// =====================================================
// INCLUIR HEADER
// =====================================================
include 'includes/header.php'; 
?>

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

<div class="container-fluid p-0">
    <!-- Hero Section - Medicina Online -->
    <section class="hero-pharmacy">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content-pharmacy">
                        <h1 class="hero-title-pharmacy">Welcome To Our<br><span class="highlight-cyan">Online Medicine</span></h1>
                        <p class="hero-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec et gravida mauris, sit amet consectetur augue lorem eget gravida mauris lorem eget gravida.</p>
                        <a href="productos.php" class="btn btn-pharmacy-primary">
                            <i class="fas fa-pills me-2"></i> Buy Now
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-pharmacy">
                        <img src="assets/images/medicine-hero.png" alt="Medicine" class="img-fluid" onerror="this.src='assets/images/products/product1.jpg'">
                    </div>
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
                        <button type="submit" class="btn btn-pharmacy-primary w-100">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
// Debug: Mostrar estado actual del carrito en la consola
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DEBUG CARRITO ===');

    // Verificar estado del carrito para cada producto
    const productIds = [1, 2, 3, 4];
    productIds.forEach(productId => {
        fetch(`ajax/check-cart-item.php?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                console.log(`Producto ${productId}:`, data);
            })
            .catch(error => console.error(`Error verificando producto ${productId}:`, error));
    });

    // Verificar contador total del carrito
    fetch('ajax/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            console.log('Estado general del carrito:', data);
        })
        .catch(error => console.error('Error cargando estado del carrito:', error));
});
</script>

<?php include 'includes/footer.php'; ?>