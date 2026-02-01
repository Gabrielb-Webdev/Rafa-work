<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Dashboard';

// Obtener estadísticas
try {
    $stmt = executeQuery("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $result = $stmt->fetch();
    $totalProducts = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as total FROM orders");
    $result = $stmt->fetch();
    $totalOrders = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $result = $stmt->fetch();
    $totalUsers = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status != 'cancelled'");
    $result = $stmt->fetch();
    $totalSales = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $result = $stmt->fetch();
    $pendingOrders = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10");
    $recentOrders = $stmt->fetchAll();
    
} catch (Exception $e) {
    $totalProducts = $totalOrders = $totalUsers = $totalSales = $pendingOrders = 0;
    $recentOrders = [];
}

include 'header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-pills"></i>
        <h3>Total Products</h3>
        <div class="stat-value"><?php echo $totalProducts; ?></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-shopping-bag"></i>
        <h3>Total Orders</h3>
        <div class="stat-value"><?php echo $totalOrders; ?></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-clock"></i>
        <h3>Pending</h3>
        <div class="stat-value" style="color: #ff9800;"><?php echo $pendingOrders; ?></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <h3>Users</h3>
        <div class="stat-value"><?php echo $totalUsers; ?></div>
    </div>
    <div class="stat-card">
        <i class="fas fa-dollar-sign"></i>
        <h3>Total Sales</h3>
        <div class="stat-value">$<?php echo number_format($totalSales, 2); ?></div>
        <small style="color: var(--text-light);">USD</small>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 20px;"><i class="fas fa-shopping-bag"></i> Recent Orders</h3>
    <?php if (!empty($recentOrders)): ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('m/d/Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; padding: 40px; color: var(--text-light);">No orders yet</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
