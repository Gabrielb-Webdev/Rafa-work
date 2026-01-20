<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Products - Online Medicine Store';

include __DIR__ . '/includes/header.php';

// Obtener productos de la base de datos
try {
    $stmt = executeQuery("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC");
    $allProducts = $stmt->fetchAll();
} catch (Exception $e) {
    $allProducts = [];
}

// Pagination settings
$productsPerPage = 12;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage);

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
                    <div class="product-card-page" style="cursor: pointer;">
                        <div class="product-badge-page">Disponible</div>
                        <div class="product-image-wrapper-page" onclick='showProductModal(<?php echo json_encode($product); ?>)'>
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="product-placeholder-page">
                                    <i class="fas fa-pills"></i>
                                    <span>Sin Imagen</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info-page">
                            <h3 class="product-name" onclick='showProductModal(<?php echo json_encode($product); ?>)' style="cursor: pointer; font-size: 16px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-category-page"><?php echo htmlspecialchars($product['category'] ?? 'Sin categoría'); ?></div>
                            <div class="product-price-page">
                                <span class="price-symbol">$</span>
                                <span class="price-amount"><?php echo number_format($product['price'], 2); ?></span>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <button onclick='showProductModal(<?php echo json_encode($product); ?>)' class="btn-view-details" style="flex: 1; padding: 10px; background: #17a2b8; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
                                    <i class="fas fa-eye"></i> Ver detalles
                                </button>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn-add-cart" style="flex: 1; padding: 10px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
                                    <i class="fas fa-cart-plus"></i> Agregar
                                </button>
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

<!-- Modal de Producto -->
<div id="productModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
        <button onclick="closeModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 28px; cursor: pointer; color: #6c757d;">&times;</button>
        <div style="padding: 30px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div id="modalImage" style="width: 100%; height: 400px; background: #f8f9fa; border-radius: 12px; display: flex; align-items: center; justify-content: center;"></div>
                <div>
                    <h2 id="modalName" style="font-size: 28px; margin-bottom: 15px; color: #2c3e50;"></h2>
                    <div id="modalCategory" style="display: inline-block; background: #00d4d4; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 15px;"></div>
                    <div id="modalPrice" style="font-size: 36px; font-weight: 700; color: #00d4d4; margin-bottom: 20px;"></div>
                    <div id="modalDescription" style="color: #6c757d; line-height: 1.6; margin-bottom: 20px;"></div>
                    <div id="modalStock" style="margin-bottom: 20px; padding: 12px; background: #e7f9f9; border-radius: 8px;"></div>
                    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 20px;">
                        <label style="font-weight: 600;">Cantidad:</label>
                        <button onclick="changeQty(-1)" style="width: 40px; height: 40px; border: 2px solid #00d4d4; background: white; color: #00d4d4; border-radius: 6px; cursor: pointer; font-size: 20px;">−</button>
                        <input type="number" id="modalQuantity" value="1" min="1" readonly style="width: 80px; text-align: center; padding: 10px; border: 2px solid #eee; border-radius: 6px; font-size: 18px;">
                        <button onclick="changeQty(1)" style="width: 40px; height: 40px; border: 2px solid #00d4d4; background: white; color: #00d4d4; border-radius: 6px; cursor: pointer; font-size: 20px;">+</button>
                    </div>
                    <button onclick="addToCartFromModal()" style="width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: 700; cursor: pointer;">
                        <i class="fas fa-cart-plus"></i> Agregar al Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentProduct = null;

function showProductModal(product) {
    currentProduct = product;
    const modal = document.getElementById('productModal');
    
    // Imagen
    const imgDiv = document.getElementById('modalImage');
    if (product.image) {
        imgDiv.innerHTML = `<img src="<?php echo BASE_URL; ?>/uploads/products/${product.image}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">`;
    } else {
        imgDiv.innerHTML = '<i class="fas fa-pills" style="font-size: 80px; color: #00d4d4;"></i>';
    }
    
    document.getElementById('modalName').textContent = product.name;
    document.getElementById('modalCategory').textContent = product.category || 'Sin categoría';
    document.getElementById('modalPrice').textContent = '$' + parseFloat(product.price).toFixed(2);
    document.getElementById('modalDescription').textContent = product.description || 'Sin descripción disponible';
    document.getElementById('modalStock').innerHTML = `<i class="fas fa-box"></i> Stock disponible: <strong>${product.stock}</strong> unidades`;
    document.getElementById('modalQuantity').value = 1;
    document.getElementById('modalQuantity').max = product.stock;
    
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

function changeQty(change) {
    const input = document.getElementById('modalQuantity');
    let value = parseInt(input.value) + change;
    if (value < 1) value = 1;
    if (value > currentProduct.stock) value = currentProduct.stock;
    input.value = value;
}

function addToCart(productId, quantity = 1) {
    fetch('<?php echo BASE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✓ Producto agregado al carrito');
            // Actualizar contador del carrito en el header si existe
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) cartBadge.textContent = data.cartCount;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Error al agregar al carrito'));
}

function addToCartFromModal() {
    const quantity = parseInt(document.getElementById('modalQuantity').value);
    addToCart(currentProduct.id, quantity);
    closeModal();
}

// Cerrar modal al hacer clic fuera
document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
