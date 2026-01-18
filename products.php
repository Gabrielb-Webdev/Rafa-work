<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Productos - Forethink Health';

// Obtener categorías
try {
    $stmt = executeQuery("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Filtros
$categoryFilter = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Construir la consulta
$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
";

$params = [];

if ($categoryFilter) {
    $sql .= " AND c.slug = ?";
    $params[] = $categoryFilter;
}

if ($searchQuery) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sql .= " ORDER BY p.created_at DESC";

try {
    $stmt = executeQuery($sql, $params);
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $products = [];
}

include __DIR__ . '/includes/header.php';
?>

<style>
.products-page {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.products-header {
    margin-bottom: 30px;
}

.products-header h1 {
    margin-bottom: 10px;
}

.filters {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid var(--border-color);
    background-color: white;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
}

.filter-btn:hover,
.filter-btn.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.products-count {
    color: var(--text-light);
    margin-bottom: 20px;
}
</style>

<div class="products-page">
    <div class="products-header">
        <h1>Nuestros Productos</h1>
        <?php if ($searchQuery): ?>
            <p>Resultados de búsqueda para: <strong>"<?php echo htmlspecialchars($searchQuery); ?>"</strong></p>
        <?php endif; ?>
    </div>
    
    <div class="filters">
        <a href="<?php echo BASE_URL; ?>/products.php">
            <button class="filter-btn <?php echo !$categoryFilter ? 'active' : ''; ?>">
                Todos
            </button>
        </a>
        <?php foreach ($categories as $category): ?>
            <a href="<?php echo BASE_URL; ?>/products.php?category=<?php echo $category['slug']; ?>">
                <button class="filter-btn <?php echo $categoryFilter === $category['slug'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
            </a>
        <?php endforeach; ?>
    </div>
    
    <div class="products-count">
        Mostrando <?php echo count($products); ?> producto(s)
    </div>
    
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <?php if ($product['discount_price']): ?>
                    <div class="product-badge">Sale</div>
                <?php endif; ?>
                
                <img src="<?php echo BASE_URL . '/uploads/products/' . ($product['image'] ?: 'default.jpg'); ?>" 
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
                    
                    <?php if ($product['description']): ?>
                        <p style="font-size: 14px; color: var(--text-light); margin: 10px 0;">
                            <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="product-price">
                        <span class="price"><?php echo formatPrice($product['discount_price'] ?: $product['price']); ?></span>
                        <?php if ($product['discount_price']): ?>
                            <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <button class="btn-add-cart" 
                                data-product-id="<?php echo $product['id']; ?>"
                                data-product-price="<?php echo $product['discount_price'] ?: $product['price']; ?>">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    <?php else: ?>
                        <button class="btn-add-cart" disabled style="background-color: #ccc; cursor: not-allowed;">
                            Agotado
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($products)): ?>
            <p style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #666;">
                <i class="fas fa-search" style="font-size: 60px; display: block; margin-bottom: 20px; opacity: 0.3;"></i>
                No se encontraron productos.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
