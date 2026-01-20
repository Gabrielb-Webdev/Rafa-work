<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = 'Panel de Administración - Forethink Health';

// Obtener estadísticas
try {
    // Total de productos
    $stmt = executeQuery("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $totalProducts = $stmt->fetch()['total'];
    
    // Total de órdenes
    $stmt = executeQuery("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];
    
    // Total de usuarios
    $stmt = executeQuery("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $totalUsers = $stmt->fetch()['total'];
    
    // Ventas totales
    $stmt = executeQuery("SELECT SUM(total) as total FROM orders WHERE status != 'cancelled'");
    $totalSales = $stmt->fetch()['total'] ?? 0;
    
    // Órdenes recientes
    $stmt = executeQuery("
        SELECT o.*, u.full_name, u.email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $recentOrders = $stmt->fetchAll();
    
    // Pedidos pendientes
    $stmt = executeQuery("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $pendingOrders = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    $totalProducts = $totalOrders = $totalUsers = $totalSales = $pendingOrders = 0;
    $recentOrders = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/jpeg" href="<?php echo BASE_URL; ?>/assets/images/logo.jpeg">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/assets/images/logo.jpeg">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=6.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-cyan: #00d4d4;
            --primary-dark: #1a1a1a;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --border-color: #dee2e6;
            --shadow-sm: 0 2px 10px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.12);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-light);
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 260px;
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: white;
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .admin-sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .admin-sidebar::-webkit-scrollbar-thumb {
            background: var(--primary-cyan);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .sidebar-header h2 {
            color: var(--primary-cyan);
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }
        
        .admin-menu {
            list-style: none;
            padding: 15px 10px;
        }
        
        .admin-menu li {
            margin-bottom: 5px;
        }
        
        .admin-menu a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 14px 18px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .admin-menu a:hover {
            background: rgba(0, 212, 212, 0.15);
            color: var(--primary-cyan);
            transform: translateX(5px);
        }
        
        .admin-menu a.active {
            background: var(--primary-cyan);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 212, 212, 0.3);
        }
        
        .admin-menu i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
            background: var(--bg-light);
        }
        
        .admin-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
        }
        
        .admin-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        
        .admin-header p {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .admin-header span {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-cyan);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-card h3 {
            color: var(--text-light);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        
        .stat-card small {
            color: var(--text-light);
            font-size: 12px;
        }
        
        .stat-card i {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 40px;
            color: var(--primary-cyan);
            opacity: 0.2;
        }
        
        .data-table {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
        }
        
        .data-table h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .data-table h3 i {
            color: var(--primary-cyan);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: var(--bg-light);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-dark);
            font-size: 14px;
        }
        
        table tr:hover {
            background: var(--bg-light);
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            margin-right: 5px;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
        }
        
        .btn-view { 
            background: #17a2b8;
        }
        
        .btn-view:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 64px;
            color: var(--primary-cyan);
            opacity: 0.3;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 70px;
            }
            
            .admin-sidebar .sidebar-header h2,
            .admin-menu a span {
                display: none;
            }
            
            .admin-content {
                margin-left: 70px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.jpeg" alt="<?php echo SITE_NAME; ?>">
                <h2>Panel Admin</h2>
            </div>
            
            <ul class="admin-menu">
                <li><a href="<?php echo BASE_URL; ?>/admin/index.php" class="active">
                    <i class="fas fa-dashboard"></i> <span>Dashboard</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/products.php">
                    <i class="fas fa-pills"></i> <span>Productos</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/categories.php">
                    <i class="fas fa-tags"></i> <span>Categorías</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/orders.php">
                    <i class="fas fa-shopping-bag"></i> <span>Pedidos</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/users.php">
                    <i class="fas fa-users"></i> <span>Usuarios</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/contacts.php">
                    <i class="fas fa-envelope"></i> <span>Contactos</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/newsletter.php">
                    <i class="fas fa-newspaper"></i> <span>Newsletter</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/index.php">
                    <i class="fas fa-globe"></i> <span>Ver Sitio</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </a></li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1>Dashboard</h1>
                    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                </div>
                <div>
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-pills"></i>
                    <h3>Total Productos</h3>
                    <div class="stat-value"><?php echo $totalProducts; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Total Pedidos</h3>
                    <div class="stat-value"><?php echo $totalOrders; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Pedidos Pendientes</h3>
                    <div class="stat-value" style="color: #ff9800;"><?php echo $pendingOrders; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Usuarios</h3>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>Ventas Totales</h3>
                    <div class="stat-value">$<?php echo number_format($totalSales, 2); ?></div>
                    <small style="color: var(--text-light);">ARS</small>
                </div>
            </div>
            
            <div class="data-table">
                <h3><i class="fas fa-shopping-bag"></i> Pedidos Recientes</h3>
                <?php if (!empty($recentOrders)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nº Pedido</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td>$<?php echo number_format($order['total'], 2); ?> ARS</td>
                                    <td><span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn-action btn-view">Ver Todos</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-light);">
                        <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3;"></i><br>
                        No hay pedidos todavía. Los pedidos de los clientes aparecerán aquí.
                    </p>
                <?php endif; ?>
            </div>
            
            
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js?v=5.9"></script>
</body>
</html>
