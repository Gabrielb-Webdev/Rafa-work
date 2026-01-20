<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Carrito de Compras - Forethink Health';

include __DIR__ . '/includes/header.php';
?>

<style>
.cart-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

.cart-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.cart-items {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid var(--border-color);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    object-fit: contain;
    margin-right: 20px;
    background-color: var(--light-bg);
    border-radius: 8px;
    padding: 10px;
}

.cart-item-details {
    flex: 1;
}

.cart-item-details h3 {
    margin-bottom: 10px;
    color: var(--text-dark);
}

.cart-item-price {
    font-size: 20px;
    font-weight: bold;
    color: var(--primary-color);
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 15px 0;
}

.quantity-btn {
    width: 35px;
    height: 35px;
    border: 1px solid var(--border-color);
    background-color: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s;
}

.quantity-btn:hover {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.quantity-display {
    font-size: 18px;
    font-weight: 600;
    min-width: 30px;
    text-align: center;
}

.btn-remove {
    background-color: var(--danger-color);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: opacity 0.3s;
}

.btn-remove:hover {
    opacity: 0.8;
}

.cart-summary {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.cart-summary h3 {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-color);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 16px;
}

.summary-row.total {
    font-size: 22px;
    font-weight: bold;
    color: var(--primary-color);
    padding-top: 15px;
    border-top: 2px solid var(--border-color);
    margin-top: 15px;
}

.btn-checkout {
    width: 100%;
    padding: 15px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 20px;
}

.btn-checkout:hover {
    background-color: var(--secondary-color);
}

.btn-continue {
    width: 100%;
    padding: 12px;
    background-color: white;
    color: var(--text-dark);
    border: 2px solid var(--border-color);
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    margin-top: 10px;
}

.btn-continue:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart i {
    font-size: 80px;
    color: var(--text-light);
    margin-bottom: 20px;
}

.empty-cart h2 {
    margin-bottom: 15px;
    color: var(--text-dark);
}

.empty-cart p {
    color: var(--text-light);
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        flex-direction: column;
        text-align: center;
    }
    
    .cart-item-image {
        margin-right: 0;
        margin-bottom: 15px;
    }
}
</style>

<div class="cart-container">
    <h1 style="margin-bottom: 30px;">Carrito de Compras</h1>
    
    <div id="cart-content">
        <!-- El contenido del carrito se cargará dinámicamente con JavaScript -->
    </div>
</div>

<script>
function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartContent = document.getElementById('cart-content');
    
    if (cart.length === 0) {
        cartContent.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Tu carrito está vacío</h2>
                <p>Agrega productos para comenzar tu compra</p>
                <a href="<?php echo BASE_URL; ?>/products.php" class="btn-primary">Ver Productos</a>
            </div>
        `;
        return;
    }
    
    let subtotal = 0;
    let cartItemsHTML = '';
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        cartItemsHTML += `
            <div class="cart-item" data-product-id="${item.id}">
                <img src="${item.image}" alt="${item.name}" class="cart-item-image" 
                     onerror="this.src='<?php echo BASE_URL; ?>/assets/images/product-placeholder.svg'">
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <div class="cart-item-price">${formatPrice(item.price)}</div>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">−</button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                        <button class="btn-remove" onclick="removeFromCart(${item.id})">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                    <div style="margin-top: 10px; font-weight: 600;">
                        Subtotal: ${formatPrice(itemTotal)}
                    </div>
                </div>
            </div>
        `;
    });
    
    const shipping = subtotal > 500 ? 0 : 50;
    const total = subtotal + shipping;
    
    cartContent.innerHTML = `
        <div class="cart-content">
            <div class="cart-items">
                <h2 style="margin-bottom: 20px;">Productos (${cart.length})</h2>
                ${cartItemsHTML}
            </div>
            
            <div class="cart-summary">
                <h3>Resumen del Pedido</h3>
                
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>${formatPrice(subtotal)}</span>
                </div>
                
                <div class="summary-row">
                    <span>Envío:</span>
                    <span>${shipping === 0 ? 'GRATIS' : formatPrice(shipping)}</span>
                </div>
                
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>${formatPrice(total)}</span>
                </div>
                
                <button class="btn-checkout" onclick="checkout()">
                    Proceder al Pago
                </button>
                
                <a href="<?php echo BASE_URL; ?>/products.php">
                    <button class="btn-continue">Continuar Comprando</button>
                </a>
                
                ${subtotal < 500 ? '<p style="text-align: center; margin-top: 15px; color: var(--text-light); font-size: 14px;">Envío gratis en compras mayores a $500 ARS</p>' : ''}
            </div>
        </div>
    `;
}

function checkout() {
    <?php if (isLoggedIn()): ?>
        window.location.href = '<?php echo BASE_URL; ?>/checkout.php';
    <?php else: ?>
        if (confirm('Necesitas iniciar sesión para continuar con la compra. ¿Deseas ir a la página de login?')) {
            window.location.href = '<?php echo BASE_URL; ?>/login.php';
        }
    <?php endif; ?>
}

// Cargar el carrito al cargar la página
document.addEventListener('DOMContentLoaded', loadCart);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
