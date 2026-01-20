<?php
require_once __DIR__ . '/config/config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'Pedido Confirmado - Forethink Health';
$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    redirect('/orders.php');
}

// Obtener detalles del pedido
try {
    $stmt = executeQuery(
        "SELECT o.*, COUNT(oi.id) as total_items 
         FROM orders o 
         LEFT JOIN order_items oi ON o.id = oi.order_id 
         WHERE o.order_number = ? AND o.user_id = ? 
         GROUP BY o.id",
        [$order_number, $_SESSION['user_id']]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect('/orders.php');
    }
} catch (Exception $e) {
    redirect('/orders.php');
}

include __DIR__ . '/includes/header.php';
?>

<style>
.confirmation-page {
    background: var(--bg-light);
    min-height: calc(100vh - 200px);
    padding: 60px 0;
}

.confirmation-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.success-card {
    background: var(--white);
    border-radius: 16px;
    padding: 60px 40px;
    box-shadow: var(--shadow-sm);
    text-align: center;
}

.success-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary-cyan), #00b8e6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    font-size: 48px;
    color: white;
    animation: scaleIn 0.5s ease;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.success-title {
    font-size: 32px;
    color: var(--text-dark);
    margin-bottom: 15px;
}

.success-message {
    color: var(--text-light);
    font-size: 16px;
    margin-bottom: 40px;
}

.order-number {
    background: var(--bg-light);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 40px;
}

.order-number strong {
    color: var(--primary-cyan);
    font-size: 24px;
}

.order-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 40px;
    text-align: left;
}

.detail-item {
    background: var(--bg-light);
    padding: 20px;
    border-radius: 12px;
}

.detail-label {
    font-size: 13px;
    color: var(--text-light);
    text-transform: uppercase;
    margin-bottom: 8px;
}

.detail-value {
    font-size: 18px;
    color: var(--text-dark);
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn {
    padding: 14px 32px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-primary {
    background: var(--primary-cyan);
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #00bfbf;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 212, 212, 0.3);
}

.btn-outline {
    background: transparent;
    color: var(--text-dark);
    border: 2px solid var(--border-color);
}

.btn-outline:hover {
    border-color: var(--primary-cyan);
    color: var(--primary-cyan);
}

@media (max-width: 768px) {
    .order-details {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<section class="confirmation-page">
    <div class="confirmation-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="success-title">¡Pedido Realizado con Éxito!</h1>
            <p class="success-message">
                Hemos recibido tu pedido y lo estamos procesando. 
                Recibirás una confirmación por correo electrónico.
            </p>
            
            <div class="order-number">
                <div style="color: var(--text-light); margin-bottom: 8px;">Número de Pedido</div>
                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
            </div>
            
            <div class="order-details">
                <div class="detail-item">
                    <div class="detail-label">Total del Pedido</div>
                    <div class="detail-value">$<?php echo number_format($order['total'], 2); ?> MXN</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Productos</div>
                    <div class="detail-value"><?php echo $order['total_items']; ?> artículos</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Estado</div>
                    <div class="detail-value" style="color: #ffc107;">
                        <i class="fas fa-clock"></i> Pendiente
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Fecha</div>
                    <div class="detail-value">
                        <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="<?php echo BASE_URL; ?>/orders.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Ver Mis Pedidos
                </a>
                <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-outline">
                    <i class="fas fa-shopping-cart"></i> Seguir Comprando
                </a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
