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
    $stmt = executeQuery("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
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
    
    // Productos con bajo stock
    $stmt = executeQuery("
        SELECT * FROM products 
        WHERE stock < 10 AND is_active = 1
        ORDER BY stock ASC
        LIMIT 5
    ");
    $lowStockProducts = $stmt->fetchAll();
    
} catch (Exception $e) {
    $totalProducts = $totalOrders = $totalUsers = $totalSales = 0;
    $recentOrders = $lowStockProducts = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: var(--dark-bg);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-sidebar h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 20px;
        }
        
        .admin-menu {
            list-style: none;
        }
        
        .admin-menu li {
            margin-bottom: 10px;
        }
        
        .admin-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .admin-menu a:hover,
        .admin-menu a.active {
            background-color: var(--primary-color);
        }
        
        .admin-menu i {
            margin-right: 10px;
            width: 20px;
        }
        
        .admin-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
            background-color: #f5f5f5;
        }
        
        .admin-header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-card i {
            font-size: 40px;
            color: var(--primary-color);
            opacity: 0.3;
            float: right;
        }
        
        .data-table {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .data-table h3 {
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background-color: var(--light-bg);
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        
        table tr:hover {
            background-color: var(--light-bg);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .btn-view { background-color: #007bff; color: white; }
        .btn-edit { background-color: #28a745; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2><i class="fas fa-capsules"></i> <?php echo SITE_NAME; ?></h2>
            
            <ul class="admin-menu">
                <li><a href="<?php echo BASE_URL; ?>/admin/index.php" class="active">
                    <i class="fas fa-dashboard"></i> Dashboard
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/products.php">
                    <i class="fas fa-pills"></i> Productos
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/categories.php">
                    <i class="fas fa-tags"></i> Categorías
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/orders.php">
                    <i class="fas fa-shopping-bag"></i> Pedidos
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/users.php">
                    <i class="fas fa-users"></i> Usuarios
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/contacts.php">
                    <i class="fas fa-envelope"></i> Contactos
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/newsletter.php">
                    <i class="fas fa-newspaper"></i> Newsletter
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/index.php">
                    <i class="fas fa-globe"></i> Ver Sitio
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
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
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Total Pedidos</h3>
                    <div class="stat-value"><?php echo $totalOrders; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Usuarios</h3>
                    <div class="stat-value"><?php echo $totalUsers; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>Ventas Totales</h3>
                    <div class="stat-value"><?php echo formatPrice($totalSales); ?></div>
                </div>
            </div>
            
            <div class="data-table">
                <h3>Pedidos Recientes</h3>
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
                                    <td>#<?php echo $order['order_number']; ?></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td><span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span></td>
                                    <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <button class="btn-action btn-view">Ver</button>
                                        <button class="btn-action btn-edit">Editar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No hay pedidos recientes.</p>
                <?php endif; ?>
            </div>
            
            <div class="data-table">
                <h3>Productos con Bajo Stock</h3>
                <?php if (!empty($lowStockProducts)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td>-</td>
                                    <td><span style="color: <?php echo $product['stock'] < 5 ? 'red' : 'orange'; ?>; font-weight: bold;">
                                        <?php echo $product['stock']; ?>
                                    </span></td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td>
                                        <button class="btn-action btn-edit">Actualizar Stock</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Todos los productos tienen stock suficiente.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
