<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Products - Online Medicine Store';

include __DIR__ . '/includes/header.php';

// All products combined
$allProducts = [
    ['id' => 1, 'name' => 'Pain Relief Medicine', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'bottle-1.png'],
    ['id' => 2, 'name' => 'Vitamin C Tablets', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'bottle-2.png'],
    ['id' => 3, 'name' => 'B12 Supplement', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'bottle-3.png'],
    ['id' => 4, 'name' => 'Multivitamin Complex', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'bottle-4.png'],
    ['id' => 5, 'name' => 'Pain Relief Capsules', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'capsule-1.png'],
    ['id' => 6, 'name' => 'Antibiotic Capsules', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'capsule-2.png'],
    ['id' => 7, 'name' => 'Vitamin D Capsules', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'capsule-3.png'],
    ['id' => 8, 'name' => 'Omega-3 Capsules', 'price' => 30, 'rating' => 4, 'category' => 'HEALTH', 'image' => 'capsule-4.png'],
    ['id' => 9, 'name' => 'Vitamin C 1000mg', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-1.png'],
    ['id' => 10, 'name' => 'B-Complex Vitamins', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-2.png'],
    ['id' => 11, 'name' => 'Calcium + D3', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-3.png'],
    ['id' => 12, 'name' => 'Iron Supplement', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-4.png'],
    ['id' => 13, 'name' => 'Zinc Tablets', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-5.png'],
    ['id' => 14, 'name' => 'Magnesium Citrate', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-6.png'],
    ['id' => 15, 'name' => 'Biotin 5000mcg', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-7.png'],
    ['id' => 16, 'name' => 'Folic Acid 400mcg', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-8.png'],
    ['id' => 17, 'name' => 'CoQ10 100mg', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-9.png'],
    ['id' => 18, 'name' => 'Probiotic Complex', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-10.png'],
    ['id' => 19, 'name' => 'Turmeric Curcumin', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-11.png'],
    ['id' => 20, 'name' => 'Fish Oil 1200mg', 'price' => 30, 'rating' => 4, 'category' => 'MEDICINE', 'image' => 'vitamin-12.png'],
];

// Pagination settings
$productsPerPage = 12;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage); // Ensure page is at least 1

$totalProducts = count($allProducts);
$totalPages = ceil($totalProducts / $productsPerPage);

// Calculate offset
$offset = ($currentPage - 1) * $productsPerPage;

// Get products for current page
$productsToDisplay = array_slice($allProducts, $offset, $productsPerPage);
?>

<!-- Products Page -->
<section class="products-page-section">
    <div class="container">
        <!-- Products Section -->
        <div class="products-category-section">
            <div class="category-header">
                <h2 class="category-title">ALL PRODUCTS</h2>
                <div class="products-count-info">
                    Showing <?php echo count($productsToDisplay); ?> of <?php echo $totalProducts; ?> products
                </div>
            </div>
            
            <div class="products-grid">
                <?php foreach ($productsToDisplay as $product): ?>
                    <div class="product-card-page">
                        <div class="product-badge-page">Buy Now</div>
                        <div class="product-image-wrapper-page">
                            <div class="product-placeholder-page">
                                <i class="fas <?php echo $product['category'] === 'HEALTH' ? 'fa-pills' : 'fa-capsules'; ?>"></i>
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-section">
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?>" class="pagination-btn pagination-prev">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                // Show page numbers
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                if ($startPage > 1): ?>
                    <a href="?page=1" class="pagination-number">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" 
                       class="pagination-number <?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $totalPages; ?>" class="pagination-number"><?php echo $totalPages; ?></a>
                <?php endif; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?>" class="pagination-btn pagination-next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
