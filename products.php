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
<style>
.products-page-section {
    padding: 60px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    min-height: 100vh;
}

.products-category-section {
    margin-bottom: 50px;
}

.category-header {
    text-align: center;
    margin-bottom: 50px;
}

.category-title {
    font-size: 48px;
    font-weight: 900;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 15px;
}

.products-count-info {
    color: #6c757d;
    font-size: 16px;
    font-weight: 500;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

.product-card-page {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    position: relative;
    cursor: pointer;
}

.product-badge-page {
    position: absolute;
    top: 15px;
    left: 15px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-image-wrapper-page {
    height: 250px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.product-image-wrapper-page img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.product-card-page:hover .product-image-wrapper-page img {
    transform: scale(1.1);
}

.product-placeholder-page {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #00d4d4;
}

.product-placeholder-page i {
    font-size: 60px;
    margin-bottom: 10px;
}

.product-placeholder-page span {
    font-size: 14px;
    color: #6c757d;
    font-weight: 600;
}

.product-info-page {
    padding: 25px;
}

.product-name {
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 12px;
    cursor: pointer;
    transition: color 0.3s;
    min-height: 50px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-name:hover {
    color: #00d4d4;
}

.product-category-page {
    display: inline-block;
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    color: #495057;
    padding: 6px 14px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-price-page {
    font-size: 32px;
    font-weight: 900;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
    display: flex;
    align-items: baseline;
}

.price-symbol {
    font-size: 20px;
    margin-right: 2px;
}

.product-actions {
    display: flex;
    gap: 12px;
}

.btn-view-details {
    flex: 1;
    padding: 12px;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
}

.btn-view-details:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
}

.btn-add-cart {
    flex: 1;
    padding: 12px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-add-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-add-cart:active, .btn-view-details:active {
    transform: translateY(0);
}

/* Modal */
.product-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.product-modal.show {
    display: flex;
}

.modal-container {
    background: white;
    border-radius: 25px;
    max-width: 900px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalSlideIn 0.4s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    z-index: 10;
}

.modal-close-btn:hover {
    transform: rotate(90deg) scale(1.1);
}

.modal-content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    padding: 40px;
}

@media (max-width: 768px) {
    .modal-content-grid {
        grid-template-columns: 1fr;
    }
}

.modal-image {
    width: 100%;
    height: 450px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.modal-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 20px;
}

.modal-details h2 {
    font-size: 32px;
    margin-bottom: 15px;
    color: #2c3e50;
    font-weight: 800;
}

.modal-category {
    display: inline-block;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    color: white;
    padding: 8px 18px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 20px;
    text-transform: uppercase;
}

.modal-price {
    font-size: 42px;
    font-weight: 900;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 25px;
}

.modal-description {
    color: #6c757d;
    line-height: 1.8;
    margin-bottom: 25px;
    font-size: 16px;
}

.modal-stock {
    margin-bottom: 25px;
    padding: 15px;
    background: linear-gradient(135deg, #e7f9f9 0%, #d4f4f4 100%);
    border-radius: 12px;
    border-left: 4px solid #00d4d4;
    font-weight: 600;
    color: #2c3e50;
}

.modal-stock i {
    color: #00d4d4;
    margin-right: 8px;
}

.quantity-selector {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 25px;
}

.quantity-selector label {
    font-weight: 700;
    font-size: 16px;
    color: #2c3e50;
}

.qty-control-btn {
    width: 45px;
    height: 45px;
    border: none;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    color: white;
    border-radius: 12px;
    cursor: pointer;
    font-size: 22px;
    font-weight: 700;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(0, 212, 212, 0.3);
}

.qty-control-btn:hover {
    transform: scale(1.1);
}

.qty-display {
    width: 90px;
    text-align: center;
    padding: 12px;
    border: 3px solid #e9ecef;
    border-radius: 12px;
    font-size: 20px;
    font-weight: 800;
    color: #2c3e50;
}

.btn-add-to-cart-modal {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
    transition: all 0.3s;
}

.btn-add-to-cart-modal:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(40, 167, 69, 0.4);
}

.btn-add-to-cart-modal i {
    margin-right: 10px;
}

/* Toast Notifications */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 18px 24px;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 10000;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    min-width: 300px;
    max-width: 400px;
}

.toast-notification.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-notification.success {
    border-left: 4px solid #28a745;
}

.toast-notification.error {
    border-left: 4px solid #dc3545;
}

.toast-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.toast-notification.success .toast-icon {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.toast-notification.error .toast-icon {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
    font-size: 15px;
}

.toast-message {
    color: #6c757d;
    font-size: 13px;
}

.toast-close {
    cursor: pointer;
    color: #adb5bd;
    font-size: 20px;
    transition: color 0.3s;
    flex-shrink: 0;
}

.toast-close:hover {
    color: #495057;
}
</style>

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

// Sistema de notificaciones toast
function showToast(message, type = 'success', title = '') {
    // Remover toast anterior si existe
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }

    // Crear nuevo toast
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const defaultTitle = type === 'success' ? '¡Éxito!' : 'Error';
    
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${icon}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title || defaultTitle}</div>
            <div class="toast-message">${message}</div>
        </div>
        <div class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Mostrar con animación
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Auto-remover después de 4 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

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
            showToast('Producto agregado al carrito exitosamente', 'success', '¡Genial!');
            // Actualizar contador del carrito en el header si existe
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) cartBadge.textContent = data.cartCount;
        } else {
            showToast(data.message, 'error', 'Error');
        }
    })
    .catch(() => showToast('No se pudo conectar con el servidor', 'error', 'Error de conexión'));
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
