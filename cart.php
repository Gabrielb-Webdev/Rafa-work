<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Carrito de Compras - Forethink Health';

// Calcular total del carrito
$cartItems = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 0; // Envío gratis
$total = $subtotal + $shipping;

include __DIR__ . '/includes/header.php';
?>

<style>
.cart-container {
    max-width: 1200px;
    margin: 60px auto;
    padding: 20px;
}

.cart-header {
    margin-bottom: 30px;
}

.cart-header h1 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.cart-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    align-items: start;
}

@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
}

.cart-items {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 20px;
    padding: 20px 0;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.cart-item:first-child {
    padding-top: 0;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-item-image i {
    font-size: 40px;
    color: #00d4d4;
}

.cart-item-info h3 {
    font-size: 18px;
    margin-bottom: 8px;
    color: #2c3e50;
}

.cart-item-price {
    font-size: 20px;
    font-weight: 700;
    color: #00d4d4;
    margin-bottom: 15px;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 10px;
}

.qty-btn {
    width: 32px;
    height: 32px;
    border: 2px solid #00d4d4;
    background: white;
    color: #00d4d4;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s;
}

.qty-btn:hover {
    background: #00d4d4;
    color: white;
}

.qty-input {
    width: 60px;
    text-align: center;
    padding: 8px;
    border: 2px solid #eee;
    border-radius: 6px;
    font-size: 16px;
}

.cart-item-actions {
    text-align: right;
}

.cart-item-total {
    font-size: 22px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-remove:hover {
    background: #c82333;
}

.cart-summary {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 100px;
}

.cart-summary h2 {
    font-size: 22px;
    margin-bottom: 20px;
    color: #2c3e50;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.summary-row.total {
    border-bottom: none;
    font-size: 22px;
    font-weight: 700;
    color: #00d4d4;
    padding-top: 20px;
}

.btn-checkout {
    width: 100%;
    background: #00d4d4;
    color: white;
    border: none;
    padding: 15px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 20px;
    transition: all 0.3s;
}

.btn-checkout:hover {
    background: #00b8b8;
    transform: translateY(-2px);
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart i {
    font-size: 80px;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-cart h2 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 15px;
}

.empty-cart p {
    color: #6c757d;
    margin-bottom: 30px;
}

.btn-continue {
    background: #00d4d4;
    color: white;
    padding: 12px 30px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-continue:hover {
    background: #00b8b8;
}
</style>

<div class="cart-container">
    <div class="cart-header">
        <h1><i class="fas fa-shopping-cart"></i> Mi Carrito</h1>
        <p style="color: #6c757d;"><?php echo count($cartItems); ?> productos en tu carrito</p>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Tu carrito está vacío</h2>
            <p>Agrega productos para comenzar tu compra</p>
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn-continue">
                <i class="fas fa-arrow-left"></i> Continuar comprando
            </a>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                        <div class="cart-item-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <i class="fas fa-pills"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="cart-item-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <div class="cart-item-quantity">
                                <button class="qty-btn qty-minus" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">−</button>
                                <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                <button class="qty-btn qty-plus" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                            </div>
                        </div>
                        
                        <div class="cart-item-actions">
                            <div class="cart-item-total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            <button class="btn-remove" onclick="removeItem(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Resumen del Pedido</h2>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Envío:</span>
                    <span><?php echo $shipping == 0 ? 'GRATIS' : '$' . number_format($shipping, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/checkout.php" class="btn-checkout">
                        <i class="fas fa-check-circle"></i> Proceder al Pago
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php?redirect=checkout" class="btn-checkout">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión para Continuar
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo BASE_URL; ?>/products.php" style="display: block; text-align: center; margin-top: 15px; color: #00d4d4; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Seguir comprando
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(productId, change) {
    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
    const qtyInput = cartItem.querySelector('.qty-input');
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty + change;
    
    if (newQty < 1) return;
    
    fetch('<?php echo BASE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&product_id=${productId}&quantity=${newQty}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function removeItem(productId) {
    if (!confirm('¿Eliminar este producto del carrito?')) return;
    
    fetch('<?php echo BASE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&product_id=${productId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
