<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Home - Forethink Health';

// Obtener productos destacados
try {
    $stmt = executeQuery("
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND p.is_featured = 1
        ORDER BY p.created_at DESC
        LIMIT 4
    ");
    $featuredProducts = $stmt->fetchAll();

    // Obtener últimos productos
    $stmtLatest = executeQuery("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $latestProducts = $stmtLatest->fetchAll();

} catch (Exception $e) {
    $featuredProducts = [];
    $latestProducts = [];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Modern Hero Section -->
<section class="modern-hero">
    <div class="hero-container">
        <div class="hero-content-wrapper">
            <div class="hero-text-section">
                <span class="hero-badge">🏥 Trusted Healthcare</span>
                <h1 class="hero-title">Your Health, <br><span class="gradient-text">Our Priority</span></h1>
                <p class="hero-description">Encuentra medicamentos, vitaminas y suplementos de la más alta calidad. Entrega rápida y segura directamente a tu puerta.</p>
                <div class="hero-cta">
                    <a href="<?php echo BASE_URL; ?>/products.php" class="btn-hero-primary">
                        <span>Explorar Productos</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#featured" class="btn-hero-secondary">Ver Destacados</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <strong>5000+</strong>
                        <span>Clientes Satisfechos</span>
                    </div>
                    <div class="stat-item">
                        <strong>500+</strong>
                        <span>Productos</span>
                    </div>
                    <div class="stat-item">
                        <strong>24/7</strong>
                        <span>Soporte</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-image-wrapper">
                    <img src="<?php echo BASE_URL; ?>/assets/images/hero-pills.svg" alt="Healthcare" class="floating-image">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="modern-features">
    <div class="container">
        <div class="features-wrapper">
            <div class="feature-box">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3>Entrega Rápida</h3>
                <p>Envío express en 24-48 horas a todo el país</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>100% Seguro</h3>
                <p>Productos certificados y garantizados</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-headphones-alt"></i>
                </div>
                <h3>Soporte 24/7</h3>
                <p>Asistencia profesional siempre disponible</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-tags"></i>
                </div>
                <h3>Mejores Precios</h3>
                <p>Ofertas y descuentos exclusivos</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-title-wrapper">
            <h2 class="section-title">Compra por Categoría</h2>
            <p class="section-subtitle">Encuentra exactamente lo que necesitas</p>
        </div>
        <div class="categories-grid">
            <a href="<?php echo BASE_URL; ?>/products.php?category=medicine-health" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <h3>Medicamentos</h3>
                <p>Prescripción y OTC</p>
                <span class="category-arrow">→</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/products.php?category=vitamins-supplements" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-capsules"></i>
                </div>
                <h3>Vitaminas</h3>
                <p>Suplementos alimenticios</p>
                <span class="category-arrow">→</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/products.php" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3>Salud</h3>
                <p>Cuidado general</p>
                <span class="category-arrow">→</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/products.php" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-first-aid"></i>
                </div>
                <h3>Primeros Auxilios</h3>
                <p>Equipos médicos</p>
                <span class="category-arrow">→</span>
            </a>
        </div>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="featured-products" id="featured">
    <div class="container">
        <div class="section-title-wrapper">
            <h2 class="section-title">Productos Destacados</h2>
            <p class="section-subtitle">Los más populares y mejor valorados</p>
        </div>
        <div class="products-modern-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="product-modern-card">
                    <?php if ($product['discount_price']): ?>
                        <span class="product-discount-badge">-<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%</span>
                    <?php endif; ?>
                    
                    <div class="product-image-container">
                        <img src="<?php echo BASE_URL . '/uploads/products/' . ($product['image'] ?: 'default.svg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='<?php echo BASE_URL; ?>/assets/images/product-placeholder.svg'">
                    </div>
                    
                    <div class="product-modern-info">
                        <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <div class="product-rating-modern">
                            <?php 
                            $rating = $product['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                            <span class="rating-count">(<?php echo $rating; ?>)</span>
                        </div>
                        
                        <div class="product-price-modern">
                            <?php if ($product['discount_price']): ?>
                                <span class="price-new"><?php echo formatPrice($product['discount_price']); ?></span>
                                <span class="price-old"><?php echo formatPrice($product['price']); ?></span>
                            <?php else: ?>
                                <span class="price-new"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn-add-modern" 
                                data-product-id="<?php echo $product['id']; ?>"
                                data-product-price="<?php echo $product['discount_price'] ?: $product['price']; ?>">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Agregar al Carrito</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="view-all-wrapper">
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn-view-all">
                Ver Todos los Productos
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Promo Banner -->
<section class="promo-banner">
    <div class="container">
        <div class="promo-content">
            <div class="promo-text">
                <span class="promo-label">Oferta Especial</span>
                <h2>Hasta 30% de Descuento</h2>
                <p>En productos seleccionados. Aprovecha nuestras ofertas por tiempo limitado.</p>
                <a href="<?php echo BASE_URL; ?>/products.php" class="btn-promo">Comprar Ahora</a>
            </div>
            <div class="promo-image">
                <img src="<?php echo BASE_URL; ?>/assets/images/pills-banner.svg" alt="Special Offer">
            </div>
        </div>
    </div>
</section>

<!-- Latest Products -->
<?php if (!empty($latestProducts)): ?>
<section class="latest-products">
    <div class="container">
        <div class="section-title-wrapper">
            <h2 class="section-title">Últimos Productos</h2>
            <p class="section-subtitle">Recién agregados a nuestro catálogo</p>
        </div>
        <div class="products-modern-grid">
            <?php foreach ($latestProducts as $product): ?>
                <div class="product-modern-card">
                    <?php if ($product['discount_price']): ?>
                        <span class="product-discount-badge">-<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%</span>
                    <?php endif; ?>
                    
                    <div class="product-image-container">
                        <img src="<?php echo BASE_URL . '/uploads/products/' . ($product['image'] ?: 'default.svg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='<?php echo BASE_URL; ?>/assets/images/product-placeholder.svg'">
                    </div>
                    
                    <div class="product-modern-info">
                        <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <div class="product-rating-modern">
                            <?php 
                            $rating = $product['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                            <span class="rating-count">(<?php echo $rating; ?>)</span>
                        </div>
                        
                        <div class="product-price-modern">
                            <?php if ($product['discount_price']): ?>
                                <span class="price-new"><?php echo formatPrice($product['discount_price']); ?></span>
                                <span class="price-old"><?php echo formatPrice($product['price']); ?></span>
                            <?php else: ?>
                                <span class="price-new"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn-add-modern" 
                                data-product-id="<?php echo $product['id']; ?>"
                                data-product-price="<?php echo $product['discount_price'] ?: $product['price']; ?>">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Agregar al Carrito</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Testimonials -->
<section class="modern-testimonials">
    <div class="container">
        <div class="section-title-wrapper">
            <h2 class="section-title">Lo Que Dicen Nuestros Clientes</h2>
            <p class="section-subtitle">Miles de clientes satisfechos confían en nosotros</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-modern-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Excelente servicio. Los productos llegaron rápido y en perfecto estado. Muy recomendado."</p>
                <div class="testimonial-author-modern">
                    <img src="<?php echo BASE_URL; ?>/assets/images/testimonial.svg" alt="Cliente">
                    <div>
                        <strong>María González</strong>
                        <span>Cliente Verificado</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-modern-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Gran variedad de productos y precios competitivos. El equipo de soporte es muy atento."</p>
                <div class="testimonial-author-modern">
                    <img src="<?php echo BASE_URL; ?>/assets/images/user-placeholder.svg" alt="Cliente">
                    <div>
                        <strong>Carlos Rodríguez</strong>
                        <span>Cliente Verificado</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-modern-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">"Confiable y profesional. Siempre encuentro lo que necesito a buenos precios."</p>
                <div class="testimonial-author-modern">
                    <img src="<?php echo BASE_URL; ?>/assets/images/user-placeholder.svg" alt="Cliente">
                    <div>
                        <strong>Ana Martínez</strong>
                        <span>Cliente Verificado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter CTA -->
<section class="newsletter-cta">
    <div class="container">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <h2>Mantente Informado</h2>
                <p>Suscríbete para recibir ofertas exclusivas, noticias de salud y novedades de productos.</p>
            </div>
            <form class="newsletter-form-modern" id="newsletterForm">
                <input type="email" placeholder="Tu correo electrónico" required>
                <button type="submit">Suscribirse</button>
            </form>
        </div>
    </div>
</section>

<script>
// Add to cart functionality
document.querySelectorAll('.btn-add-modern').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productPrice = this.dataset.productPrice;
        
        // Get existing cart
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        
        // Check if product already in cart
        const existingItem = cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: productId,
                price: productPrice,
                quantity: 1
            });
        }
        
        // Save cart
        localStorage.setItem('cart', JSON.stringify(cart));
        
        // Update cart count
        updateCartCount();
        
        // Show notification
        showNotification('Producto agregado al carrito', 'success');
        
        // Visual feedback
        this.innerHTML = '<i class="fas fa-check"></i> <span>Agregado</span>';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-shopping-cart"></i> <span>Agregar al Carrito</span>';
        }, 2000);
    });
});

// Newsletter form
document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    showNotification('¡Gracias por suscribirte!', 'success');
    this.reset();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
