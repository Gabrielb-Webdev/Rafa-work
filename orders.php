<?php
/**
 * Mis Pedidos - Forethink Health
 * Version: 2.0 - Estados Dinámicos y Filtros Funcionales
 * Fecha: 31/01/2026
 * Cambios: Estadísticas calculadas desde BD, filtros funcionales por estado
 */
require_once __DIR__ . '/config/config.php';

// Check if logged in
if (!isLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'My Orders - Forethink Health';

// Get user's real orders from database
$filter_status = $_GET['status'] ?? 'all';

try {
    $sql = "SELECT o.*, COUNT(oi.id) as items 
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.user_id = ?";
    
    $params = [$_SESSION['user_id']];
    
    if ($filter_status !== 'all') {
        $sql .= " AND o.status = ?";
        $params[] = $filter_status;
    }
    
    $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";
    
    $stmt = executeQuery($sql, $params);
    $orders = $stmt->fetchAll();
    
    // Transform for compatibility
    foreach ($orders as &$order) {
        $order['tracking'] = $order['order_number'];
        $order['date'] = $order['created_at'];
    }
    
    // Calculate statistics by status (always show all totals)
    $stmt_stats = executeQuery(
        "SELECT status, COUNT(*) as count 
         FROM orders 
         WHERE user_id = ? 
         GROUP BY status",
        [$_SESSION['user_id']]
    );
    
    $stats = [
        'total' => 0,
        'pending' => 0,
        'processing' => 0,
        'shipped' => 0,
        'delivered' => 0
    ];
    
    while ($row = $stmt_stats->fetch()) {
        if (isset($stats[$row['status']])) {
            $stats[$row['status']] = $row['count'];
        }
        $stats['total'] += $row['count'];
    }
} catch (Exception $e) {
    $orders = [];
    $stats = [
        'total' => 0,
        'pending' => 0,
        'processing' => 0,
        'shipped' => 0,
        'delivered' => 0
    ];
}

function getStatusText($status) {
    $statuses = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled'
    ];
    return $statuses[$status] ?? 'Unknown';
}

function getStatusColor($status) {
    $colors = [
        'pending' => '#ff9800',
        'processing' => '#ffc107',
        'shipped' => '#17a2b8',
        'delivered' => '#28a745',
        'cancelled' => '#dc3545'
    ];
    return $colors[$status] ?? '#666';
}

function getStatusIcon($status) {
    $icons = [
        'pending' => 'fa-hourglass-half',
        'processing' => 'fa-clock',
        'shipped' => 'fa-truck',
        'delivered' => 'fa-check-circle',
        'cancelled' => 'fa-times-circle'
    ];
    return $icons[$status] ?? 'fa-question';
}

include __DIR__ . '/includes/header.php';
?>

<style>
.orders-page {
    background: var(--bg-light);
    min-height: calc(100vh - 200px);
    padding: 60px 0;
}

.orders-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.orders-header {
    text-align: center;
    margin-bottom: 50px;
}

.orders-header h1 {
    font-size: 36px;
    color: var(--text-dark);
    margin-bottom: 10px;
}

.orders-header p {
    color: var(--text-light);
    font-size: 16px;
}

.orders-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--white);
    border-radius: 12px;
    padding: 25px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-icon.cyan {
    background: linear-gradient(135deg, var(--primary-cyan), #00b8e6);
}

.stat-icon.green {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.stat-icon.orange {
    background: linear-gradient(135deg, #ffc107, #ff9800);
}

.stat-info h3 {
    font-size: 28px;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.stat-info p {
    color: var(--text-light);
    font-size: 14px;
}

.orders-filter {
    background: var(--white);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-sm);
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid var(--border-color);
    background: var(--white);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: var(--text-dark);
    display: inline-block;
}

.filter-btn:hover,
.filter-btn.active {
    border-color: var(--primary-cyan);
    background: var(--primary-cyan);
    color: white;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.order-card {
    background: var(--white);
    border-radius: 12px;
    padding: 30px;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.order-card:hover {
    box-shadow: var(--shadow-md);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--bg-light);
}

.order-number {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
}

.order-date {
    color: var(--text-light);
    font-size: 14px;
}

.order-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.order-body {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.order-info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.order-info-label {
    color: var(--text-light);
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-info-value {
    color: var(--text-dark);
    font-size: 16px;
    font-weight: 600;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 2px solid var(--bg-light);
}

.order-total {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-cyan);
}

.order-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--primary-cyan);
    color: white;
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

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: var(--white);
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
}

.empty-state i {
    font-size: 80px;
    color: var(--primary-cyan);
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-state h3 {
    font-size: 24px;
    color: var(--text-dark);
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--text-light);
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .order-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .order-actions {
        width: 100%;
    }
    
    .btn {
        flex: 1;
        justify-content: center;
    }
}
</style>

<section class="orders-page">
    <div class="orders-container">
        <div class="orders-header">
            <h1>Mis Pedidos</h1>
            <p>Revisa el estado y detalles de todos tus pedidos</p>
        </div>
        
        <!-- Estadísticas -->
        <div class="orders-stats">
            <div class="stat-card">
                <div class="stat-icon cyan">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total de Pedidos</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['delivered']; ?></h3>
                    <p>Pedidos Entregados</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['shipped']; ?></h3>
                    <p>En Camino</p>
                </div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="orders-filter">
            <a href="?status=all" class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">Todos</a>
            <a href="?status=pending" class="filter-btn <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">Pendientes</a>
            <a href="?status=processing" class="filter-btn <?php echo $filter_status === 'processing' ? 'active' : ''; ?>">En Proceso</a>
            <a href="?status=shipped" class="filter-btn <?php echo $filter_status === 'shipped' ? 'active' : ''; ?>">Enviados</a>
            <a href="?status=delivered" class="filter-btn <?php echo $filter_status === 'delivered' ? 'active' : ''; ?>">Entregados</a>
        </div>
        
        <!-- Lista de Pedidos -->
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>No tienes pedidos aún</h3>
                <p>Explora nuestro catálogo y realiza tu primera compra</p>
                <a href="<?php echo BASE_URL; ?>/products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Ver Productos
                </a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-number">Pedido #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                <div class="order-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($order['date'])); ?>
                                </div>
                            </div>
                            <div class="order-status" style="background: <?php echo getStatusColor($order['status']); ?>20; color: <?php echo getStatusColor($order['status']); ?>;">
                                <i class="fas <?php echo getStatusIcon($order['status']); ?>"></i>
                                <?php echo getStatusText($order['status']); ?>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="order-info-item">
                                <span class="order-info-label">Productos</span>
                                <span class="order-info-value"><?php echo $order['items']; ?> artículos</span>
                            </div>
                            
                            <div class="order-info-item">
                                <span class="order-info-label">Número de Seguimiento</span>
                                <span class="order-info-value"><?php echo $order['tracking']; ?></span>
                            </div>
                        </div>
                        
                        <div class="order-footer">
                            <div class="order-total">
                                <?php if ($order['total']): ?>
                                    $<?php echo number_format($order['total'], 2); ?> ARS
                                <?php else: ?>
                                    <span style="color: #ffc107;">Pendiente cotización</span>
                                <?php endif; ?>
                            </div>
                            <div class="order-actions">
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
