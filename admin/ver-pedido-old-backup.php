<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    redirect('/admin/pedidos.php');
}

// Obtener información del pedido
try {
    $stmt = executeQuery(
        "SELECT o.*, u.full_name as user_name, u.email as user_email 
         FROM orders o 
         JOIN users u ON o.user_id = u.id 
         WHERE o.id = ?",
        [$order_id]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect('/admin/pedidos.php');
    }
    
    // Obtener items del pedido
    $stmt = executeQuery(
        "SELECT oi.*, p.image 
         FROM order_items oi 
         LEFT JOIN products p ON oi.product_id = p.id 
         WHERE oi.order_id = ?",
        [$order_id]
    );
    $items = $stmt->fetchAll();
    
} catch (Exception $e) {
    redirect('/admin/pedidos.php');
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span style="background: #ff9800; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;"><i class="fas fa-hourglass-half"></i> PENDIENTE</span>',
        'processing' => '<span style="background: #ffc107; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;"><i class="fas fa-clock"></i> EN PROCESO</span>',
        'shipped' => '<span style="background: #17a2b8; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;"><i class="fas fa-truck"></i> ENVIADO</span>',
        'delivered' => '<span style="background: #28a745; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;"><i class="fas fa-check-circle"></i> ENTREGADO</span>',
        'cancelled' => '<span style="background: #dc3545; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;"><i class="fas fa-times-circle"></i> CANCELADO</span>'
    ];
    return $badges[$status] ?? $status;
}

$pageTitle = 'Detalle del Pedido - Admin';
include __DIR__ . '/header.php';
?>

<style>
.order-detail-page {
    background: var(--bg-light);
    min-height: calc(100vh - 200px);
    padding: 40px 0;
}

.detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.detail-header h1 {
    font-size: 28px;
    color: var(--text-dark);
}

.btn-back {
    padding: 10px 20px;
    background: var(--primary-cyan);
    color: white;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: #00bfbf;
}

.detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.detail-card {
    background: var(--white);
    border-radius: 12px;
    padding: 30px;
    box-shadow: var(--shadow-sm);
}

.card-title {
    font-size: 20px;
    color: var(--text-dark);
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--bg-light);
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-title i {
    color: var(--primary-cyan);
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}

.info-label {
    color: var(--text-light);
    font-weight: 600;
}

.info-value {
    color: var(--text-dark);
    text-align: right;
}

.items-list {
    margin-top: 20px;
}

.item-row {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: var(--bg-light);
    border-radius: 8px;
    margin-bottom: 10px;
}

.item-image {
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-cyan);
    font-size: 24px;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.item-info {
    flex: 1;
}

.item-name {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.item-details {
    font-size: 14px;
    color: var(--text-light);
}

.item-total {
    font-weight: 700;
    color: var(--text-dark);
    font-size: 18px;
}

.summary-box {
    background: var(--bg-light);
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 16px;
}

.summary-total {
    border-top: 2px solid var(--primary-cyan);
    padding-top: 15px;
    margin-top: 10px;
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-cyan);
}

@media (max-width: 992px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="order-detail-page">
    <div class="detail-container">
        <div class="detail-header">
            <h1><i class="fas fa-file-invoice"></i> Detalle del Pedido</h1>
            <a href="<?php echo BASE_URL; ?>/admin/pedidos.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver a Pedidos
            </a>
        </div>
        
        <div class="detail-grid">
            <!-- Información del Pedido -->
            <div class="detail-card">
                <h2 class="card-title">
                    <i class="fas fa-shopping-bag"></i>
                    Información del Pedido
                </h2>
                
                <div class="info-row">
                    <span class="info-label">Número de Pedido:</span>
                    <span class="info-value"><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value"><?php echo getStatusBadge($order['status']); ?></span>
                </div>
                
                <h2 class="card-title" style="margin-top: 30px;">
                    <i class="fas fa-user"></i>
                    Información del Cliente
                </h2>
                
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                
                <h2 class="card-title" style="margin-top: 30px;">
                    <i class="fas fa-map-marker-alt"></i>
                    Dirección de Envío
                </h2>
                
                <div class="info-row">
                    <span class="info-label">Calle y Número:</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars($order['street'] . ' ' . $order['street_number']); ?>
                    </span>
                </div>
                
                <?php if (!empty($order['neighborhood'])): ?>
                <div class="info-row">
                    <span class="info-label">Colonia:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['neighborhood']); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="info-row">
                    <span class="info-label">Ciudad:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['city']); ?></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Código Postal:</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['postal_code']); ?></span>
                </div>
                
                <?php if (!empty($order['notes'])): ?>
                <h2 class="card-title" style="margin-top: 30px;">
                    <i class="fas fa-comment"></i>
                    Notas del Pedido
                </h2>
                <div style="padding: 15px; background: var(--bg-light); border-radius: 8px; color: var(--text-dark);">
                    <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Productos y Total -->
            <div class="detail-card">
                <h2 class="card-title">
                    <i class="fas fa-box"></i>
                    Productos (<?php echo count($items); ?>)
                </h2>
                
                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="item-row">
                            <div class="item-image">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-pills"></i>
                                <?php endif; ?>
                            </div>
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-details">
                                    $<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                            <div class="item-total">
                                $<?php echo number_format($item['subtotal'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span><strong>$<?php echo number_format($order['subtotal'], 2); ?></strong></span>
                    </div>
                    <div class="summary-row">
                        <span>Envío:</span>
                        <span><strong><?php echo $order['shipping'] > 0 ? '$' . number_format($order['shipping'], 2) : 'GRATIS'; ?></strong></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
