<?php
require_once __DIR__ . '/config/config.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'Finalizar Pedido - Forethink Health';
$success = '';
$error = '';

// Obtener datos del usuario
try {
    $stmt = executeQuery("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {
    $error = 'Error al cargar los datos del usuario.';
}

// Procesar el pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    $cart_data = $_POST['cart_data'] ?? '';
    
    if (empty($full_name) || empty($phone) || empty($address) || empty($cart_data)) {
        $error = 'Por favor completa todos los campos requeridos';
    } else {
        try {
            $cart = json_decode($cart_data, true);
            if (empty($cart)) {
                $error = 'El carrito está vacío';
            } else {
                // Calcular totales
                $subtotal = 0;
                foreach ($cart as $item) {
                    $subtotal += $item['price'] * $item['quantity'];
                }
                
                $shipping = $subtotal > 500 ? 0 : 50;
                $total = $subtotal + $shipping;
                
                // Generar número de pedido único
                $order_number = 'FTH-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT) . '-' . date('Y');
                
                // Insertar pedido
                $conn = getDBConnection();
                $conn->beginTransaction();
                
                try {
                    $stmt = $conn->prepare(
                        "INSERT INTO orders (user_id, order_number, full_name, email, phone, address, subtotal, shipping, total, status, notes, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())"
                    );
                    
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $order_number,
                        $full_name,
                        $user['email'],
                        $phone,
                        $address,
                        $subtotal,
                        $shipping,
                        $total,
                        $notes
                    ]);
                    
                    $order_id = $conn->lastInsertId();
                    
                    // Insertar items del pedido
                    $stmt_item = $conn->prepare(
                        "INSERT INTO order_items (order_id, product_name, product_price, quantity, subtotal) 
                         VALUES (?, ?, ?, ?, ?)"
                    );
                    
                    foreach ($cart as $item) {
                        $item_subtotal = $item['price'] * $item['quantity'];
                        $stmt_item->execute([
                            $order_id,
                            $item['name'],
                            $item['price'],
                            $item['quantity'],
                            $item_subtotal
                        ]);
                    }
                    
                    $conn->commit();
                    
                    // Limpiar carrito y redirigir
                    echo '<script>
                        localStorage.removeItem("cart");
                        window.location.href = "' . BASE_URL . '/order-confirmation.php?order=' . $order_number . '";
                    </script>';
                    exit;
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    $error = 'Error al procesar el pedido: ' . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            $error = 'Error al procesar el pedido.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
.checkout-page {
    background: var(--bg-light);
    min-height: calc(100vh - 200px);
    padding: 60px 0;
}

.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.checkout-header {
    text-align: center;
    margin-bottom: 50px;
}

.checkout-header h1 {
    font-size: 36px;
    color: var(--text-dark);
    margin-bottom: 10px;
}

.checkout-content {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 30px;
}

.checkout-form {
    background: var(--white);
    border-radius: 16px;
    padding: 30px;
    box-shadow: var(--shadow-sm);
}

.form-section {
    margin-bottom: 30px;
}

.section-title {
    font-size: 20px;
    color: var(--text-dark);
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--bg-light);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--primary-cyan);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-dark);
    font-size: 14px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-cyan);
    box-shadow: 0 0 0 4px rgba(0, 212, 212, 0.08);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.order-summary {
    background: var(--white);
    border-radius: 16px;
    padding: 30px;
    box-shadow: var(--shadow-sm);
    height: fit-content;
}

.summary-title {
    font-size: 24px;
    color: var(--text-dark);
    margin-bottom: 25px;
}

.summary-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid var(--border-color);
}

.item-image {
    width: 60px;
    height: 60px;
    background: var(--bg-light);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-cyan);
    font-size: 24px;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.item-qty {
    font-size: 14px;
    color: var(--text-light);
}

.item-price {
    font-weight: 700;
    color: var(--text-dark);
}

.summary-totals {
    margin-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    font-size: 16px;
}

.summary-total {
    padding-top: 20px;
    margin-top: 15px;
    border-top: 2px solid var(--bg-light);
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-cyan);
}

.btn-place-order {
    width: 100%;
    background: var(--primary-cyan);
    color: white;
    border: none;
    padding: 16px;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 20px;
}

.btn-place-order:hover {
    background: #00bfbf;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 212, 212, 0.3);
}

.alert {
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

@media (max-width: 992px) {
    .checkout-content {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="checkout-page">
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Finalizar Pedido</h1>
            <p>Completa tu información para procesar el pedido</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-content">
            <div class="checkout-form">
                <form method="POST" id="checkoutForm">
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i>
                            Información de Contacto
                        </h2>
                        
                        <div class="form-group">
                            <label for="full_name">Nombre Completo *</label>
                            <input type="text" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Teléfono *</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Dirección de Envío
                        </h2>
                        
                        <div class="form-group">
                            <label for="address">Dirección Completa *</label>
                            <textarea id="address" name="address" required 
                                      placeholder="Calle, número, colonia, ciudad, código postal"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-comment"></i>
                            Notas Adicionales
                        </h2>
                        
                        <div class="form-group">
                            <label for="notes">Notas del Pedido (opcional)</label>
                            <textarea id="notes" name="notes" 
                                      placeholder="Ej: Llamar antes de entregar, referencias, etc."></textarea>
                        </div>
                    </div>
                    
                    <input type="hidden" name="cart_data" id="cartData">
                    <button type="submit" name="place_order" class="btn-place-order">
                        <i class="fas fa-check-circle"></i> Realizar Pedido
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2 class="summary-title">Resumen del Pedido</h2>
                <div id="summaryItems"></div>
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Envío</span>
                        <span id="summaryShipping">$0.00</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span id="summaryTotal">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        window.location.href = '<?php echo BASE_URL; ?>/cart.php';
        return;
    }
    
    // Guardar cart en el formulario
    document.getElementById('cartData').value = JSON.stringify(cart);
    
    // Mostrar resumen
    let itemsHTML = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        subtotal += item.price * item.quantity;
        itemsHTML += `
            <div class="summary-item">
                <div class="item-image">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="item-details">
                    <div class="item-name">${item.name}</div>
                    <div class="item-qty">Cantidad: ${item.quantity}</div>
                </div>
                <div class="item-price">$${(item.price * item.quantity).toFixed(2)}</div>
            </div>
        `;
    });
    
    document.getElementById('summaryItems').innerHTML = itemsHTML;
    
    const shipping = subtotal > 500 ? 0 : 50;
    const total = subtotal + shipping;
    
    document.getElementById('summarySubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('summaryShipping').textContent = shipping === 0 ? 'GRATIS' : '$' + shipping.toFixed(2);
    document.getElementById('summaryTotal').textContent = '$' + total.toFixed(2);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
