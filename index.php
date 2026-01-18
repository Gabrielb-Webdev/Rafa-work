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
        LIMIT 8
    ");
    $featuredProducts = $stmt->fetchAll();

    // Obtener productos por categoría
    $stmtMedicine = executeQuery("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND c.slug = 'medicine-health'
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $medicineProducts = $stmtMedicine->fetchAll();

    $stmtVitamins = executeQuery("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND c.slug = 'vitamins-supplements'
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $vitaminsProducts = $stmtVitamins->fetchAll();

} catch (Exception $e) {
    $featuredProducts = [];
    $medicineProducts = [];
    $vitaminsProducts = [];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1>Welcome To Our<br><span style="color: #fff;">Online Medicine</span></h1>
            <p>Tu salud es nuestra prioridad. Encuentra los mejores medicamentos, vitaminas y suplementos para tu bienestar.</p>
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn-primary">Buy Now</a>
        </div>
        <div class="hero-image">
            <img src="<?php echo BASE_URL; ?>/assets/images/hero-pills.svg" alt="Medicine" onerror="this.style.display='none'">
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-truck"></i>
            </div>
            <h3>FAST DELIVERY</h3>
            <p>Entrega rápida y segura en todo el país</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-certificate"></i>
            </div>
            <h3>LICENSE OF GOVERNMENT</h3>
            <p>Todos nuestros productos cuentan con licencia oficial</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-headset"></i>
            </div>
            <h3>SUPPORT 24/7</h3>
            <p>Atención al cliente las 24 horas del día</p>
        </div>
    </div>
</section>

<!-- Discount Section -->
<section class="discount-section">
    <div class="discount-content">
        <div class="discount-text">
            <h2>YOU GET<br>ANY MEDICINE<br>ON <span>10% DISCOUNT</span></h2>
            <p>Aprovecha nuestras ofertas especiales en todos los productos seleccionados.</p>
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn-primary">Buy Now</a>
        </div>
        <div class="discount-image">
            <img src="<?php echo BASE_URL; ?>/assets/images/pills-banner.svg" alt="Discount" onerror="this.style.display='none'">
        </div>
    </div>
</section>

<!-- Medicine & Health Section -->
<section class="products-section">
    <div class="section-header">
        <h2>Medicine & Health</h2>
        <div class="nav-arrows">
            <button class="arrow-btn" onclick="scrollProducts('medicine', -1)"><i class="fas fa-chevron-left"></i></button>
            <button class="arrow-btn" onclick="scrollProducts('medicine', 1)"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <div class="products-grid" id="medicine-products">
        <?php foreach ($medicineProducts as $product): ?>
            <div class="product-card">
                <?php if ($product['discount_price']): ?>
                    <div class="product-badge">Sale</div>
                <?php endif; ?>
                
                <img src="<?php echo BASE_URL . '/uploads/products/' . ($product['image'] ?: 'default.svg'); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-image"
                     onerror="this.src='<?php echo BASE_URL; ?>/assets/images/product-placeholder.svg'">
                
                <div class="product-info">
                    <div class="product-rating">
                        <div class="stars">
                            <?php 
                            $rating = $product['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '★' : '☆';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <div class="product-price">
                        <span class="price"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                        <?php if ($product['discount_price']): ?>
                            <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button class="btn-add-cart" 
                            data-product-id="<?php echo $product['id']; ?>"
                            data-product-price="<?php echo $product['discount_price'] ?: $product['price']; ?>">
                        Buy Now
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($medicineProducts)): ?>
            <p style="grid-column: 1/-1; text-align: center; color: #666;">No hay productos disponibles en este momento.</p>
        <?php endif; ?>
    </div>

    <a href="<?php echo BASE_URL; ?>/products.php?category=medicine-health" class="btn-see-more">See more</a>
</section>

<!-- Vitamins & Supplements Section -->
<section class="products-section" style="background-color: #f5f5f5; padding: 60px 20px; margin: 0;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div class="section-header">
            <h2>Vitamins & Supplements</h2>
            <div class="nav-arrows">
                <button class="arrow-btn" onclick="scrollProducts('vitamins', -1)"><i class="fas fa-chevron-left"></i></button>
                <button class="arrow-btn" onclick="scrollProducts('vitamins', 1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="products-grid" id="vitamins-products">
            <?php foreach ($vitaminsProducts as $product): ?>
                <div class="product-card">
                    <?php if ($product['discount_price']): ?>
                        <div class="product-badge">Sale</div>
                    <?php endif; ?>
                    
                    <img src="<?php echo BASE_URL . '/uploads/products/' . ($product['image'] ?: 'default.svg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="product-image"
                         onerror="this.src='<?php echo BASE_URL; ?>/assets/images/product-placeholder.svg'">
                    
                    <div class="product-info">
                        <div class="product-rating">
                            <div class="stars">
                                <?php 
                                $rating = $product['rating'];
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <div class="product-price">
                            <span class="price"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                            <?php if ($product['discount_price']): ?>
                                <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn-add-cart" 
                                data-product-id="<?php echo $product['id']; ?>"
                                data-product-price="<?php echo $product['discount_price'] ?: $product['price']; ?>">
                            Buy Now
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($vitaminsProducts)): ?>
                <p style="grid-column: 1/-1; text-align: center; color: #666;">No hay productos disponibles en este momento.</p>
            <?php endif; ?>
        </div>

        <a href="<?php echo BASE_URL; ?>/products.php?category=vitamins-supplements" class="btn-see-more">See more</a>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="about-content">
        <h2>ABOUT US</h2>
        <div class="about-image">
            <img src="<?php echo BASE_URL; ?>/assets/images/vitamins.svg" alt="About Us" onerror="this.style.display='none'">
        </div>
        <p>En Forethink Health nos comprometemos con tu bienestar. Ofrecemos productos farmacéuticos de la más alta calidad, respaldados por la confianza de miles de clientes satisfechos.</p>
        <a href="<?php echo BASE_URL; ?>/about.php" class="btn-primary">Read More</a>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials">
    <h2>WHAT IS SAYS OUR CLIENTS</h2>
    <div class="testimonial-card">
        <p class="testimonial-text">"Excelente servicio y productos de calidad. La entrega fue rápida y el personal muy atento. Totalmente recomendado."</p>
        <div class="testimonial-author">
            <img src="<?php echo BASE_URL; ?>/assets/images/testimonial.svg" alt="Client" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/user-placeholder.svg'">
            <h4>Maria González</h4>
            <p>CUSTOMER</p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="contact-wrapper">
        <div class="contact-form">
            <h2>REQUEST A CALL BACK</h2>
            <form id="contactForm" method="POST" action="<?php echo BASE_URL; ?>/api/contact.php">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Select medicine</label>
                    <select name="medicine">
                        <option value="">Select a medicine</option>
                        <option value="medicine-1">Medicine 1</option>
                        <option value="medicine-2">Medicine 2</option>
                        <option value="medicine-3">Medicine 3</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="4"></textarea>
                </div>

                <button type="submit" class="btn-submit">Send</button>
            </form>
        </div>

        <div class="contact-info">
            <h2>Get Now Medicines</h2>
            <p>Contáctanos para obtener tus medicamentos de forma rápida y segura. Nuestro equipo está listo para ayudarte.</p>
        </div>
    </div>
</section>

<script>
// Scroll horizontal de productos
function scrollProducts(category, direction) {
    const container = document.getElementById(category + '-products');
    const scrollAmount = 300;
    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}

// Manejar formulario de contacto
document.getElementById('contactForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('¡Mensaje enviado! Te contactaremos pronto.', 'success');
            this.reset();
        } else {
            showNotification(result.message || 'Error al enviar el mensaje', 'error');
        }
    } catch (error) {
        showNotification('Error al procesar la solicitud', 'error');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
