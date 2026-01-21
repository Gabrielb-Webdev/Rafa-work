<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Carrito de Compras - Forethink Health';

// Validar productos del carrito contra la base de datos
$cartItems = $_SESSION['cart'] ?? [];
$validCartItems = [];

if (!empty($cartItems)) {
    foreach ($cartItems as $key => $item) {
        // La clave puede ser string o int, asegurar que sea int
        $productId = intval($key);
        
        if ($productId <= 0) {
            continue; // Saltar claves inválidas
        }
        
        // Verificar que el producto existe en la base de datos
        $stmt = executeQuery("SELECT id, name, price, stock, image FROM products WHERE id = ? AND is_active = 1", [$productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Producto válido, actualizar información por si cambió
            // IMPORTANTE: usar el productId como clave explícita
            $validCartItems[$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => min($item['quantity'], $product['stock']), // No exceder stock
                'image' => $product['image']
            ];
        }
        // Si el producto no existe, simplemente no se agrega a validCartItems
    }
    
    // Actualizar sesión con solo productos válidos
    $_SESSION['cart'] = $validCartItems;
    $cartItems = $validCartItems;
}

// Calcular total del carrito
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 0; // Envío gratis
$total = $subtotal + $shipping;

include __DIR__ . '/includes/header.php';
?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.cart-container {
    max-width: 1400px;
    margin: 80px auto;
    padding: 20px;
}

.cart-header {
    margin-bottom: 40px;
    text-align: center;
}

.cart-header h1 {
    font-size: 42px;
    color: #2c3e50;
    margin-bottom: 10px;
    font-weight: 800;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.cart-header p {
    color: #6c757d;
    font-size: 18px;
}

.cart-content {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 30px;
    align-items: start;
}

@media (max-width: 992px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
}

.cart-items {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

.cart-item {
    display: grid;
    grid-template-columns: 120px 1fr auto;
    gap: 25px;
    padding: 25px 0;
    border-bottom: 2px solid #f0f0f0;
    align-items: center;
    transition: all 0.3s ease;
}

.cart-item:hover {
    transform: translateX(5px);
    background: #f8f9fa;
    margin: 0 -15px;
    padding: 25px 15px;
    border-radius: 12px;
}

.cart-item:first-child {
    padding-top: 0;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 120px;
    height: 120px;
    border-radius: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
}

.cart-item-image::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(0, 212, 212, 0.1), transparent);
    transform: rotate(45deg);
}

.cart-item-image i {
    font-size: 50px;
    color: #00d4d4;
}

.cart-item-info h3 {
    font-size: 20px;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 700;
}

.cart-item-price {
    font-size: 24px;
    font-weight: 800;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 15px;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 12px;
}

.qty-btn {
    width: 38px;
    height: 38px;
    border: none;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-size: 18px;
    font-weight: 700;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(0, 212, 212, 0.3);
}

.qty-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 212, 212, 0.4);
}

.qty-btn:active {
    transform: translateY(0);
}

.qty-input {
    width: 70px;
    text-align: center;
    padding: 10px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
}

.cart-item-actions {
    text-align: right;
}

.cart-item-total {
    font-size: 28px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 15px;
}

.btn-remove {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-remove:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

.cart-summary {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    position: sticky;
    top: 100px;
    border: 2px solid #e9ecef;
}

.cart-summary h2 {
    font-size: 26px;
    margin-bottom: 25px;
    color: #2c3e50;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}

.cart-summary h2 i {
    color: #00d4d4;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 18px 0;
    border-bottom: 2px solid #e9ecef;
    font-size: 16px;
    color: #6c757d;
}

.summary-row strong {
    color: #2c3e50;
    font-weight: 700;
}

.summary-row.total {
    border-bottom: none;
    font-size: 28px;
    font-weight: 800;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    padding-top: 25px;
    margin-top: 10px;
    border-top: 3px solid #00d4d4;
}

.btn-checkout {
    width: 100%;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    color: white;
    border: none;
    padding: 18px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    margin-top: 25px;
    transition: all 0.3s;
    box-shadow: 0 8px 20px rgba(0, 212, 212, 0.3);
    text-decoration: none;
    display: block;
    text-align: center;
}

.btn-checkout:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 212, 212, 0.4);
}

.btn-checkout i {
    margin-right: 10px;
}

.empty-cart {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

.empty-cart i {
    font-size: 100px;
    color: #e9ecef;
    margin-bottom: 25px;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.empty-cart h2 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 800;
}

.empty-cart p {
    color: #6c757d;
    margin-bottom: 30px;
    font-size: 18px;
}

.btn-continue {
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    color: white;
    padding: 15px 40px;
    border-radius: 12px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
    font-weight: 700;
    font-size: 16px;
    box-shadow: 0 8px 20px rgba(0, 212, 212, 0.3);
}

.btn-continue:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 212, 212, 0.4);
}

.btn-continue i {
    margin-right: 8px;
}

.continue-shopping {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #00d4d4;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.continue-shopping:hover {
    color: #00a0a0;
    transform: translateX(-5px);
}

.continue-shopping i {
    margin-right: 8px;
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

.toast-notification.warning {
    border-left: 4px solid #ffc107;
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

.toast-notification.warning .toast-icon {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
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

/* Modal de Confirmación */
.confirm-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 10001;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

.confirm-modal.show {
    display: flex;
}

.confirm-modal-content {
    background: white;
    border-radius: 20px;
    padding: 35px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: modalBounceIn 0.4s ease;
}

@keyframes modalBounceIn {
    0% {
        opacity: 0;
        transform: scale(0.7);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.confirm-modal-header {
    text-align: center;
    margin-bottom: 25px;
}

.confirm-modal-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;
    color: white;
    box-shadow: 0 8px 20px rgba(255, 193, 7, 0.3);
}

.confirm-modal-title {
    font-size: 24px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 10px;
}

.confirm-modal-message {
    color: #6c757d;
    font-size: 16px;
    line-height: 1.6;
}

.confirm-modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 30px;
}

.confirm-modal-btn {
    flex: 1;
    padding: 14px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}

.confirm-modal-btn.cancel {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.confirm-modal-btn.cancel:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

.confirm-modal-btn.confirm {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.confirm-modal-btn.confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
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
                <?php foreach ($cartItems as $key => $item): 
                    $productId = intval($key); // Convertir clave a entero
                    if ($productId <= 0) continue; // Saltar IDs inválidos
                ?>
                    <div class="cart-item" data-product-id="<?php echo $productId; ?>">
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
                                <button class="qty-btn qty-minus" onclick="updateQuantity(<?php echo $productId; ?>, -1)">−</button>
                                <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                <button class="qty-btn qty-plus" onclick="updateQuantity(<?php echo $productId; ?>, 1)">+</button>
                            </div>
                        </div>
                        
                        <div class="cart-item-actions">
                            <div class="cart-item-total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            <button class="btn-remove" onclick="removeItem(<?php echo $productId; ?>)">
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
                
                <a href="<?php echo BASE_URL; ?>/products.php" class="continue-shopping">
                    <i class="fas fa-arrow-left"></i> Seguir comprando
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Confirmación -->
<div id="confirmModal" class="confirm-modal">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header">
            <div class="confirm-modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="confirm-modal-title">¿Estás seguro?</h3>
            <p class="confirm-modal-message">Este producto será eliminado de tu carrito</p>
        </div>
        <div class="confirm-modal-actions">
            <button class="confirm-modal-btn cancel" onclick="closeConfirmModal()">Cancelar</button>
            <button class="confirm-modal-btn confirm" onclick="confirmRemove()">Eliminar</button>
        </div>
    </div>
</div>

<script>
let productToRemove = null;

// Sistema de notificaciones toast
function showToast(message, type = 'success', title = '') {
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    const titles = {
        success: '¡Éxito!',
        error: 'Error',
        warning: 'Advertencia'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${icons[type]}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title || titles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <div class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </div>
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

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
            showToast(data.message, 'error', 'Error');
        }
    })
    .catch(() => showToast('No se pudo actualizar la cantidad', 'error', 'Error de conexión'));
}

function removeItem(productId) {
    productId = parseInt(productId);
    if (!productId || productId <= 0) {
        showToast('ID de producto inválido', 'error', 'Error');
        return;
    }
    productToRemove = productId;
    document.getElementById('confirmModal').classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
    productToRemove = null;
}

function confirmRemove() {
    if (!productToRemove) return;
    
    closeConfirmModal();
    
    fetch('<?php echo BASE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&product_id=${productToRemove}`
    })
    .then(res => res.json())
    .then(data => {
        console.log('Response:', data); // Para depuración
        if (data.success) {
            showToast('Producto eliminado del carrito', 'success', '¡Listo!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'No se pudo eliminar el producto', 'error', 'Error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showToast('Error de conexión', 'error', 'Error');
    });
    
    productToRemove = null;
}

// Cerrar modal al hacer clic fuera
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
