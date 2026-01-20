<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = 'Gestión de Pedidos - Admin';
$success = '';
$error = '';

// Actualizar estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'] ?? 0;
    $new_status = $_POST['status'] ?? '';
    
    try {
        executeQuery(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
            [$new_status, $order_id]
        );
        $success = 'Estado del pedido actualizado correctamente';
    } catch (Exception $e) {
        $error = 'Error al actualizar el pedido';
    }
}

// Obtener todos los pedidos
$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

try {
    $sql = "SELECT o.*, u.full_name, u.email, COUNT(oi.id) as total_items
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE 1=1";
    
    $params = [];
    
    if ($filter_status !== 'all') {
        $sql .= " AND o.status = ?";
        $params[] = $filter_status;
    }
    
    if (!empty($search)) {
        $sql .= " AND (o.order_number LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";
    
    $stmt = executeQuery($sql, $params);
    $orders = $stmt->fetchAll();
    
    // Estadísticas
    $stats = [
        'pending' => 0,
        'processing' => 0,
        'shipped' => 0,
        'delivered' => 0,
        'total_revenue' => 0
    ];
    
    $stmt_stats = executeQuery("SELECT status, COUNT(*) as count, SUM(total) as revenue FROM orders GROUP BY status");
    while ($row = $stmt_stats->fetch()) {
        if (isset($stats[$row['status']])) {
            $stats[$row['status']] = $row['count'];
        }
        if ($row['status'] !== 'cancelled') {
            $stats['total_revenue'] += $row['revenue'];
        }
    }
    
} catch (Exception $e) {
    $orders = [];
    $error = 'Error al cargar los pedidos';
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span style="background: #ff9800; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-hourglass-half"></i> PENDIENTE</span>',
        'processing' => '<span style="background: #ffc107; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-clock"></i> EN PROCESO</span>',
        'shipped' => '<span style="background: #17a2b8; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-truck"></i> ENVIADO</span>',
        'delivered' => '<span style="background: #28a745; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-check-circle"></i> ENTREGADO</span>',
        'cancelled' => '<span style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-times-circle"></i> CANCELADO</span>'
    ];
    return $badges[$status] ?? $status;
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.admin-page {
    background: var(--bg-light);
    min-height: calc(100vh - 200px);
    padding: 40px 0;
}

.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.admin-header h1 {
    font-size: 32px;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--white);
    padding: 25px;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary-cyan);
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-light);
    font-size: 14px;
}

.filters-section {
    background: var(--white);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
}

.filters-row {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-group {
    display: flex;
    gap: 10px;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid var(--border-color);
    background: var(--white);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    color: var(--text-dark);
}

.filter-btn.active {
    border-color: var(--primary-cyan);
    background: var(--primary-cyan);
    color: white;
}

.search-box {
    flex: 1;
    min-width: 250px;
}

.search-box input {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
}

.orders-table {
    background: var(--white);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: var(--bg-light);
}

th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: var(--text-dark);
    font-size: 14px;
    text-transform: uppercase;
}

td {
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
}

tr:last-child td {
    border-bottom: none;
}

.status-select {
    padding: 8px 12px;
    border: 2px solid var(--border-color);
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
}

.btn-update {
    padding: 6px 12px;
    background: var(--primary-cyan);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}

.btn-update:hover {
    background: #00bfbf;
}

.btn-view {
    padding: 6px 12px;
    background: #17a2b8;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
    display: inline-block;
}

.alert {
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 64px;
    color: var(--primary-cyan);
    opacity: 0.3;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .orders-table {
        overflow-x: auto;
    }
    
    table {
        min-width: 800px;
    }
}
</style>

<section class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-shopping-bag"></i> Gestión de Pedidos</h1>
            <a href="<?php echo BASE_URL; ?>/admin" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['processing']; ?></div>
                <div class="stat-label">En Proceso</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['shipped']; ?></div>
                <div class="stat-label">Enviados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['delivered']; ?></div>
                <div class="stat-label">Entregados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-label">Ingresos Totales (ARS)</div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <a href="?status=all" class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">Todos</a>
                    <a href="?status=pending" class="filter-btn <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">Pendientes</a>
                    <a href="?status=processing" class="filter-btn <?php echo $filter_status === 'processing' ? 'active' : ''; ?>">En Proceso</a>
                    <a href="?status=shipped" class="filter-btn <?php echo $filter_status === 'shipped' ? 'active' : ''; ?>">Enviados</a>
                    <a href="?status=delivered" class="filter-btn <?php echo $filter_status === 'delivered' ? 'active' : ''; ?>">Entregados</a>
                </div>
                
                <div class="search-box">
                    <form method="GET">
                        <input type="text" name="search" placeholder="Buscar por número, cliente o email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <?php if ($filter_status !== 'all'): ?>
                            <input type="hidden" name="status" value="<?php echo $filter_status; ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tabla de Pedidos -->
        <?php if (empty($orders)): ?>
            <div class="orders-table">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No hay pedidos</h3>
                    <p>Los pedidos aparecerán aquí cuando los clientes realicen compras</p>
                </div>
            </div>
        <?php else: ?>
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Número de Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td>
                                    <div><?php echo htmlspecialchars($order['full_name']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-light);">
                                        <?php echo htmlspecialchars($order['email']); ?>
                                    </div>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td><?php echo $order['total_items']; ?> productos</td>
                                <td><strong>$<?php echo number_format($order['total'], 2); ?></strong></td>
                                <td><?php echo getStatusBadge($order['status']); ?></td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="status-select">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En Proceso</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Enviado</option>
                                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Entregado</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn-update">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
