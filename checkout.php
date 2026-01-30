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

// Obtener carrito desde sesión/BD
$cartItems = $_SESSION['cart'] ?? [];

// Si el carrito está vacío, redirigir
if (empty($cartItems)) {
    redirect('/cart.php');
}

// Procesar el pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $street = sanitizeInput($_POST['street'] ?? '');
    $street_number = sanitizeInput($_POST['street_number'] ?? '');
    $neighborhood = sanitizeInput($_POST['neighborhood'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $postal_code = sanitizeInput($_POST['postal_code'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    if (empty($full_name) || empty($phone) || empty($street) || empty($city) || empty($postal_code)) {
        $error = 'Por favor completa todos los campos requeridos';
    } else {
        try {
            // Calcular totales
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
                $shipping = $subtotal > 500 ? 0 : 50;
                $total = $subtotal + $shipping;
                
                // Generar número de pedido único
                $order_number = 'FTH-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT) . '-' . date('Y');
                
                // Insertar pedido
                $conn = getConnection();
                $conn->beginTransaction();
                
                try {
                    $stmt = $conn->prepare(
                        "INSERT INTO orders (user_id, order_number, full_name, email, phone, street, street_number, neighborhood, city, postal_code, subtotal, shipping, total, status, notes, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())"
                    );
                    
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $order_number,
                        $full_name,
                        $user['email'],
                        $phone,
                        $street,
                        $street_number,
                        $neighborhood,
                        $city,
                        $postal_code,
                        $subtotal,
                        $shipping,
                        $total,
                        $notes
                    ]);
                    
                    $order_id = $conn->lastInsertId();
                    
                    // Insertar items del pedido y reducir stock
                    $stmt_item = $conn->prepare(
                        "INSERT INTO order_items (order_id, product_name, product_price, quantity, subtotal) 
                         VALUES (?, ?, ?, ?, ?)"
                    );
                    
                    $stmt_stock = $conn->prepare(
                        "UPDATE products SET stock = stock - ? WHERE id = ?"
                    );
                    
                    foreach ($cartItems as $item) {
                        $item_subtotal = $item['price'] * $item['quantity'];
                        $stmt_item->execute([
                            $order_id,
                            $item['name'],
                            $item['price'],
                            $item['quantity'],
                            $item_subtotal
                        ]);
                        
                        // Reducir stock
                        $stmt_stock->execute([
                            $item['quantity'],
                            $item['id']
                        ]);
                    }
                    
                    $conn->commit();
                    
                    // Limpiar carrito de sesión
                    $_SESSION['cart'] = [];
                    
                    // Limpiar carrito de BD
                    try {
                        executeQuery("DELETE FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
                    } catch (Exception $e) {
                        // Continuar aunque falle
                    }
                    
                    // Redirigir
                    redirect('/order-confirmation.php?order=' . $order_number);
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    $error = 'Error al procesar el pedido: ' . $e->getMessage();
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
                            <label for="street">Calle *</label>
                            <input type="text" id="street" name="street" required 
                                   placeholder="Nombre de la calle">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                            <div class="form-group">
                                <label for="street_number">Número *</label>
                                <input type="text" id="street_number" name="street_number" required 
                                       placeholder="Núm.">
                            </div>
                            
                            <div class="form-group">
                                <label for="neighborhood">Colonia</label>
                                <input type="text" id="neighborhood" name="neighborhood" 
                                       placeholder="Colonia o barrio">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="city">Ciudad *</label>
                                <input type="text" id="city" name="city" required 
                                       placeholder="Ciudad">
                            </div>
                            
                            <div class="form-group">
                                <label for="postal_code">Código Postal *</label>
                                <input type="text" id="postal_code" name="postal_code" required 
                                       placeholder="C.P.">
                            </div>
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
                    
                    <button type="submit" name="place_order" class="btn-place-order">
                        <i class="fas fa-check-circle"></i> Realizar Pedido
                    </button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2 class="summary-title">Resumen del Pedido</h2>
                
                <?php 
                $subtotal = 0;
                foreach ($cartItems as $item): 
                    $itemTotal = $item['price'] * $item['quantity'];
                    $subtotal += $itemTotal;
                ?>
                    <div class="summary-item">
                        <div class="item-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <i class="fas fa-pills"></i>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-qty">Cantidad: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div class="item-price">$<?php echo number_format($itemTotal, 2); ?></div>
                    </div>
                <?php endforeach; ?>
                
                <?php 
                $shipping = $subtotal > 500 ? 0 : 50;
                $total = $subtotal + $shipping;
                ?>
                
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Envío</span>
                        <span><?php echo $shipping === 0 ? 'GRATIS' : '$' . number_format($shipping, 2); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
