<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Home - Online Medicine Store';

// Get featured products
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

    // Get vitamins products
    $stmtVitamins = executeQuery("
        SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND c.slug = 'vitamins-supplements'
        ORDER BY p.created_at DESC
        LIMIT 4
    ");
    $vitaminsProducts = $stmtVitamins->fetchAll();

} catch (Exception $e) {
    $featuredProducts = [];
    $vitaminsProducts = [];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-text">
                    <h1 class="hero-title">Welcome to Our<br><span class="highlight">Online Medicine</span></h1>
                    <p class="hero-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec nec odio vitae mauris sagittis aliquet. Nam viverra pharetra est, ut vehicula tortor tincidunt vel.</p>
                    <a href="<?php echo BASE_URL; ?>/products.php" class="btn-shop">Shop Now</a>
                </div>
                <div class="hero-image">
                    <div class="hero-placeholder">
                        <div class="hero-placeholder-icon">💊</div>
                        <div class="hero-placeholder-text">Hero Pills Image</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h3>FAST DELIVERY</h3>
                <p>It is a long established fact that a reader will be distracted by</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-laptop-medical"></i>
                </div>
                <h3>ONLINE ORDER</h3>
                <p>It is a long established fact that a reader will be distracted by</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>SUPPORT</h3>
                <p>It is a long established fact that a reader will be distracted by</p>
            </div>
        </div>
    </div>
</section>

<!-- Discount Banner -->
<section class="discount-banner">
    <div class="container">
        <div class="discount-content">
            <div class="discount-text">
                <h2>YOU GET<br>ANY MEDICINE<br>ON <span class="discount-highlight">10% DISCOUNT</span></h2>
                <p>It is a long established fact that a reader will be distracted by the readable content of a page.</p>
                <a href="<?php echo BASE_URL; ?>/products.php" class="btn-get-now">Get Now</a>
            </div>
            <div class="discount-image">
                <div class="discount-placeholder">
                    <div class="discount-placeholder-content">
                        <div class="discount-placeholder-icon">💊💊💊</div>
                        <div class="discount-placeholder-text">Scattered Pills Image</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Medicine & Health Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">MEDICINE & HEALTH</h2>
            <div class="section-arrows">
                <button class="arrow-btn prev-btn" onclick="scrollProducts('medicine', 'left')"><i class="fas fa-chevron-left"></i></button>
                <button class="arrow-btn next-btn" onclick="scrollProducts('medicine', 'right')"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="products-carousel" id="medicine-carousel">
            <?php foreach (array_slice($featuredProducts, 0, 8) as $product): ?>
                <div class="product-card">
                    <?php if ($product['discount_price']): ?>
                        <span class="sale-badge">SALE</span>
                    <?php endif; ?>
                    
                    <div class="product-image">
                        <?php if (!empty($product['image']) && file_exists(__DIR__ . '/uploads/products/' . $product['image'])): ?>
                            <img src="<?php echo BASE_URL . '/uploads/products/' . $product['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="img-placeholder">
                                <div class="img-placeholder-icon">📦</div>
                                <div class="img-placeholder-text">Product Image</div>
                                <div class="img-placeholder-subtext">Upload image here</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <div class="product-rating">
                            <?php 
                            $rating = $product['rating'] ?? 4;
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="price"><?php echo formatPrice($product['discount_price']); ?></span>
                                <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php else: ?>
                                <span class="price"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn-buy-now" 
                                data-product-id="<?php echo $product['id']; ?>"
                                data-product-price="<?php echo $product['discount_price'] ?: $product['price']; ?>">
                            Buy Now
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="view-all-center">
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn-view-all">View All</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Vitamins & Supplements -->
<?php if (!empty($vitaminsProducts) || !empty($featuredProducts)): 
$vitaminsToShow = !empty($vitaminsProducts) ? $vitaminsProducts : array_slice($featuredProducts, 0, 4);
?>
<section class="products-section vitamins-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">VITAMINS & SUPPLEMENTS</h2>
            <div class="section-arrows">
                <button class="arrow-btn prev-btn" onclick="scrollProducts('vitamins', 'left')"><i class="fas fa-chevron-left"></i></button>
                <button class="arrow-btn next-btn" onclick="scrollProducts('vitamins', 'right')"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="products-carousel" id="vitamins-carousel">
            <?php foreach ($vitaminsToShow as $product): ?>
                <div class="product-card">
                    <?php if ($product['discount_price']): ?>
                        <span class="sale-badge">SALE</span>
                    <?php endif; ?>
                    
                    <div class="product-image">
                        <?php if (!empty($product['image']) && file_exists(__DIR__ . '/uploads/products/' . $product['image'])): ?>
                            <img src="<?php echo BASE_URL . '/uploads/products/' . $product['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="img-placeholder">
                                <div class="img-placeholder-icon">📦</div>
                                <div class="img-placeholder-text">Product Image</div>
                                <div class="img-placeholder-subtext">Upload image here</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <div class="product-rating">
                            <?php 
                            $rating = $product['rating'] ?? 5;
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        
                        <div class="product-price">
                            <?php if ($product['discount_price']): ?>
                                <span class="price"><?php echo formatPrice($product['discount_price']); ?></span>
                                <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php else: ?>
                                <span class="price"><?php echo formatPrice($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn-buy-now" 
                                data-product-id="<?php echo $product['id']; ?>"
                                data-product-price="<?php echo $product['discount_price'] ?: $product['price']; ?>">
                            Buy Now
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="view-all-center">
            <a href="<?php echo BASE_URL; ?>/products.php?category=vitamins-supplements" class="btn-view-all">View All</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- About Us Section -->
<section class="about-section">
    <div class="container">
        <h2 class="section-title centered">ABOUT US</h2>
        <div class="about-content">
            <div class="about-image">
                <div class="about-placeholder">
                    <div class="about-placeholder-icon">🏥</div>
                    <div class="about-placeholder-text">About Section Image</div>
                    <div class="img-placeholder-subtext">Medical products showcase</div>
                </div>
            </div>
            <div class="about-text">
                <p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English.</p>
                <a href="<?php echo BASE_URL; ?>/about.php" class="btn-read-more">Read More</a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonial Section -->
<section class="testimonial-section">
    <div class="container">
        <h2 class="section-title centered">WHAT IS <span class="highlight-blue">SAYS</span> OUR CLIENTS</h2>
        <div class="testimonial-card">
            <p class="testimonial-text">"There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be"</p>
            <div class="testimonial-author">
                <div class="author-avatar placeholder">
                    <i class="fas fa-user"></i>
                </div>
                <div class="author-info">
                    <h4>Venison Aune</h4>
                    <p>Customer</p>
                    <div class="rating-dots">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Request Callback Section -->
<section class="callback-section">
    <div class="container">
        <div class="callback-grid">
            <div class="callback-form-wrapper">
                <h2 class="callback-title">REQUEST A CALL BACK</h2>
                <form class="callback-form" id="callbackForm">
                    <input type="text" placeholder="Name" required>
                    <input type="text" placeholder="Phone Number" required>
                    <input type="email" placeholder="Email" required>
                    <textarea placeholder="Message" rows="4" required></textarea>
                    <button type="submit" class="btn-send">SEND</button>
                </form>
            </div>
            <div class="callback-banner">
                <h2>Get Now<br>Medicines</h2>
                <p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even</p>
            </div>
        </div>
    </div>
</section>

<script>
// Scroll products carousel
function scrollProducts(section, direction) {
    const carousel = document.getElementById(section + '-carousel');
    const scrollAmount = 300;
    if (direction === 'left') {
        carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

// Add to cart functionality
document.querySelectorAll('.btn-buy-now').forEach(button => {
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
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
        
        // Visual feedback
        const originalText = this.textContent;
        this.textContent = 'Added!';
        this.style.backgroundColor = '#00d4aa';
        
        setTimeout(() => {
            this.textContent = originalText;
            this.style.backgroundColor = '';
        }, 1500);
    });
});

// Callback form
document.getElementById('callbackForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Thank you! We will contact you soon.');
    this.reset();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
