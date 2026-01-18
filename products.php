<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Products - Online Medicine Store';

include __DIR__ . '/includes/header.php';

// Sample products data (replace with database query)
$medicineProducts = [
    ['id' => 1, 'name' => 'Pain Relief Medicine', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'bottle-1.png'],
    ['id' => 2, 'name' => 'Vitamin C Tablets', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'bottle-2.png'],
    ['id' => 3, 'name' => 'B12 Supplement', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'bottle-3.png'],
    ['id' => 4, 'name' => 'Multivitamin Complex', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'bottle-4.png'],
    ['id' => 5, 'name' => 'Pain Relief Capsules', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'capsule-1.png'],
    ['id' => 6, 'name' => 'Antibiotic Capsules', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'capsule-2.png'],
    ['id' => 7, 'name' => 'Vitamin D Capsules', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'capsule-3.png'],
    ['id' => 8, 'name' => 'Omega-3 Capsules', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'capsule-4.png'],
];

$vitaminsProducts = [
    ['id' => 9, 'name' => 'Vitamin C 1000mg', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-1.png'],
    ['id' => 10, 'name' => 'B-Complex Vitamins', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-2.png'],
    ['id' => 11, 'name' => 'Calcium + D3', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-3.png'],
    ['id' => 12, 'name' => 'Iron Supplement', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-4.png'],
    ['id' => 13, 'name' => 'Zinc Tablets', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-5.png'],
    ['id' => 14, 'name' => 'Magnesium Citrate', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-6.png'],
    ['id' => 15, 'name' => 'Biotin 5000mcg', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-7.png'],
    ['id' => 16, 'name' => 'Folic Acid 400mcg', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-8.png'],
];
?>

<!-- Products Page -->
<section class="products-page-section">
    <div class="container">
        <!-- Medicine & Health Section -->
        <div class="products-category-section">
            <div class="category-header">
                <h2 class="category-title">MEDICINE & HEALTH</h2>
                <div class="category-nav">
                    <button class="nav-arrow nav-prev" data-category="medicine">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-arrow nav-next" data-category="medicine">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="products-grid" id="medicine-grid">
                <?php foreach ($medicineProducts as $product): ?>
                    <div class="product-card-page">
                        <div class="product-badge-page">Buy Now</div>
                        <div class="product-image-wrapper-page">
                            <div class="product-placeholder-page">
                                <i class="fas fa-pills"></i>
                                <span>Product Image</span>
                            </div>
                        </div>
                        <div class="product-info-page">
                            <div class="product-rating-page">
                                <div class="stars">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="<?php echo $i < $product['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="product-category-page"><?php echo $product['category']; ?></div>
                            <div class="product-price-page">
                                <span class="price-symbol">$</span>
                                <span class="price-amount"><?php echo $product['price']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Vitamins & Supplements Section -->
        <div class="products-category-section">
            <div class="category-header">
                <h2 class="category-title">VITAMINS & SUPPLEMENTS</h2>
                <div class="category-nav">
                    <button class="nav-arrow nav-prev" data-category="vitamins">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-arrow nav-next" data-category="vitamins">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="products-grid" id="vitamins-grid">
                <?php foreach ($vitaminsProducts as $product): ?>
                    <div class="product-card-page">
                        <div class="product-badge-page">Buy Now</div>
                        <div class="product-image-wrapper-page">
                            <div class="product-placeholder-page">
                                <i class="fas fa-capsules"></i>
                                <span>Product Image</span>
                            </div>
                        </div>
                        <div class="product-info-page">
                            <div class="product-rating-page">
                                <div class="stars">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="<?php echo $i < $product['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="product-category-page"><?php echo $product['category']; ?></div>
                            <div class="product-price-page">
                                <span class="price-symbol">$</span>
                                <span class="price-amount"><?php echo $product['price']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- See More Button -->
        <div class="see-more-section">
            <button class="btn-see-more">See more</button>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
