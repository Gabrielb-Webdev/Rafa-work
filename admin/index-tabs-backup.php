<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = 'Panel de Administración - Forethink Health';
$success = '';
$error = '';

// ====== PROCESAMIENTO DE PRODUCTOS ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
    }
    
    if (!empty($name) && $price > 0) {
        try {
            executeQuery(
                "INSERT INTO products (name, description, price, stock, category, image, is_active, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [$name, $description, $price, $stock, $category, $image, $is_active]
            );
            $success = 'Producto agregado correctamente';
        } catch (Exception $e) {
            $error = 'Error al agregar el producto';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = intval($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/products/';
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        
        executeQuery(
            "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, image = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
            [$name, $description, $price, $stock, $category, $image, $is_active, $id]
        );
    } else {
        executeQuery(
            "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
            [$name, $description, $price, $stock, $category, $is_active, $id]
        );
    }
    $success = 'Producto actualizado correctamente';
}

if (isset($_GET['delete_product'])) {
    try {
        executeQuery("DELETE FROM products WHERE id = ?", [intval($_GET['delete_product'])]);
        $success = 'Producto eliminado';
    } catch (Exception $e) {
        $error = 'Error al eliminar';
    }
}

// ====== PROCESAMIENTO DE CATEGORÍAS ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['cat_name'] ?? '');
    $description = trim($_POST['cat_description'] ?? '');
    $is_active = isset($_POST['cat_is_active']) ? 1 : 0;
    
    if (!empty($name)) {
        try {
            executeQuery(
                "INSERT INTO categories (name, description, is_active, created_at) VALUES (?, ?, ?, NOW())",
                [$name, $description, $is_active]
            );
            $success = 'Categoría agregada';
        } catch (Exception $e) {
            $error = 'Error al agregar categoría';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = intval($_POST['category_id'] ?? 0);
    $name = trim($_POST['cat_name'] ?? '');
    $description = trim($_POST['cat_description'] ?? '');
    $is_active = isset($_POST['cat_is_active']) ? 1 : 0;
    
    try {
        executeQuery(
            "UPDATE categories SET name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
            [$name, $description, $is_active, $id]
        );
        $success = 'Categoría actualizada';
    } catch (Exception $e) {
        $error = 'Error al actualizar';
    }
}

if (isset($_GET['delete_category'])) {
    try {
        executeQuery("DELETE FROM categories WHERE id = ?", [intval($_GET['delete_category'])]);
        $success = 'Categoría eliminada';
    } catch (Exception $e) {
        $error = 'Error al eliminar';
    }
}

// ====== PROCESAMIENTO DE PEDIDOS ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = $_POST['order_status'] ?? '';
    
    try {
        executeQuery(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
            [$new_status, $order_id]
        );
        $success = 'Estado del pedido actualizado';
    } catch (Exception $e) {
        $error = 'Error al actualizar pedido';
    }
}

// ====== OBTENER DATOS ======
// Inicializar variables por defecto
$totalProducts = $totalOrders = $totalUsers = $totalSales = $pendingOrders = 0;
$recentOrders = $products = $categories = $allOrders = [];
$prod_stats = ['total' => 0, 'active' => 0, 'low_stock' => 0, 'total_value' => 0];
$order_stats = ['pending' => 0, 'processing' => 0, 'shipped' => 0, 'delivered' => 0, 'total_revenue' => 0];

try {
    // Dashboard stats
    $stmt = executeQuery("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $result = $stmt->fetch();
    $totalProducts = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as total FROM orders");
    $result = $stmt->fetch();
    $totalOrders = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $result = $stmt->fetch();
    $totalUsers = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT SUM(total) as total FROM orders WHERE status != 'cancelled'");
    $result = $stmt->fetch();
    $totalSales = $result && $result['total'] ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $result = $stmt->fetch();
    $pendingOrders = $result ? $result['total'] : 0;
    
    $stmt = executeQuery("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10");
    $recentOrders = $stmt->fetchAll();
    
    // Productos
    $stmt = executeQuery("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
    
    $stmt_prod_stats = executeQuery("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END) as low_stock,
        SUM(price * stock) as total_value
        FROM products");
    $result = $stmt_prod_stats->fetch();
    if ($result) {
        $prod_stats = [
            'total' => $result['total'] ?? 0,
            'active' => $result['active'] ?? 0,
            'low_stock' => $result['low_stock'] ?? 0,
            'total_value' => $result['total_value'] ?? 0
        ];
    }
    
    // Categorías
    $stmt = executeQuery("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.name = p.category GROUP BY c.id ORDER BY c.name ASC");
    $categories = $stmt->fetchAll();
    
    // Pedidos
    $stmt = executeQuery("SELECT o.*, u.full_name, u.email, COUNT(oi.id) as total_items
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        GROUP BY o.id ORDER BY o.created_at DESC");
    $allOrders = $stmt->fetchAll();
    
    $stmt_order_stats = executeQuery("SELECT status, COUNT(*) as count, SUM(total) as revenue FROM orders GROUP BY status");
    while ($row = $stmt_order_stats->fetch()) {
        if (isset($order_stats[$row['status']])) {
            $order_stats[$row['status']] = $row['count'];
        }
        if ($row['status'] !== 'cancelled') {
            $order_stats['total_revenue'] += $row['revenue'] ?? 0;
        }
    }
    
} catch (Exception $e) {
    // Mantener valores por defecto ya inicializados
    error_log("Error en admin panel: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
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
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
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
            cursor: pointer;
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
        }
        
        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 0;
        }
        
        .admin-top-bar {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .admin-top-bar h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
        }
        
        .tabs-navigation {
            background: white;
            padding: 0 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: none; /* OCULTO */
            gap: 5px;
            overflow-x: auto;
        }
        
        .tab-button {
            padding: 16px 24px;
            border: none;
            background: transparent;
            color: var(--text-light);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            display: none; /* OCULTO */
            align-items: center;
            gap: 8px;
        }
        
        .tab-button:hover {
            color: var(--primary-cyan);
        }
        
        .tab-button.active {
            color: var(--primary-cyan);
            border-bottom-color: var(--primary-cyan);
        }
        
        .tab-content {
            padding: 30px;
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
        
        .stat-card h3 {
            color: var(--text-light);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-dark);
        }
        
        .stat-card i {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 40px;
            color: var(--primary-cyan);
            opacity: 0.2;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-add {
            background: #28a745;
            color: white;
        }
        
        .btn-add:hover {
            background: #218838;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: var(--bg-light);
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-cyan);
            margin-bottom: 8px;
        }
        
        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .btn-edit {
            flex: 1;
            padding: 8px;
            background: #17a2b8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .btn-delete {
            flex: 1;
            padding: 8px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        th {
            background: var(--bg-light);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 13px;
            text-transform: uppercase;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
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
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .category-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
        }
        
        .category-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }
        
        .stock-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .stock-high { background: #d4edda; color: #155724; }
        .stock-low { background: #fff3cd; color: #856404; }
        .stock-out { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo">
                <h2>Panel Admin</h2>
            </div>
            
            <ul class="admin-menu">
                <li><a class="menu-link active" data-tab="dashboard">
                    <i class="fas fa-dashboard"></i> <span>Dashboard</span>
                </a></li>
                <li><a class="menu-link" data-tab="products">
                    <i class="fas fa-pills"></i> <span>Productos</span>
                </a></li>
                <li><a class="menu-link" data-tab="categories">
                    <i class="fas fa-tags"></i> <span>Categorías</span>
                </a></li>
                <li><a class="menu-link" data-tab="orders">
                    <i class="fas fa-shopping-bag"></i> <span>Pedidos</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/index.php" target="_blank">
                    <i class="fas fa-globe"></i> <span>Ver Sitio</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
                </a></li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <div class="admin-top-bar">
                <h1 id="pageTitle">Dashboard</h1>
                <div>
                    <span style="color: var(--text-light); font-size: 14px;"><?php echo date('d/m/Y'); ?> - <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div style="padding: 20px 30px 0;">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div style="padding: 20px 30px 0;">
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- TAB: Dashboard -->
            <div id="dashboard" class="tab-content active">
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
                        <h3>Pendientes</h3>
                        <div class="stat-value" style="color: #ff9800;"><?php echo $pendingOrders; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>Usuarios</h3>
                        <div class="stat-value"><?php echo $totalUsers; ?></div>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-dollar-sign"></i>
                        <h3>Ventas Totales</h3>
                        <div class="stat-value">$<?php echo number_format($totalSales, 2); ?></div>
                        <small style="color: var(--text-light);">ARS</small>
                    </div>
                </div>
                
                <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: var(--shadow-sm);">
                    <h3 style="margin-bottom: 20px;"><i class="fas fa-shopping-bag"></i> Pedidos Recientes</h3>
                    <?php if (!empty($recentOrders)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nº Pedido</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 40px; color: var(--text-light);">No hay pedidos todavía</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- TAB: Productos -->
            <div id="products" class="tab-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div class="stats-grid" style="flex: 1; grid-template-columns: repeat(4, 1fr); margin-bottom: 0;">
                        <div class="stat-card">
                            <h3>Total</h3>
                            <div class="stat-value" style="font-size: 24px;"><?php echo $prod_stats['total']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Activos</h3>
                            <div class="stat-value" style="font-size: 24px;"><?php echo $prod_stats['active']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Bajo Stock</h3>
                            <div class="stat-value" style="font-size: 24px; color: #ff9800;"><?php echo $prod_stats['low_stock']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Valor Total</h3>
                            <div class="stat-value" style="font-size: 20px;">$<?php echo number_format($prod_stats['total_value'], 0); ?></div>
                        </div>
                    </div>
                    <button class="btn btn-add" onclick="openProductModal()" style="margin-left: 20px;">
                        <i class="fas fa-plus"></i> Agregar Producto
                    </button>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo $product['image']; ?>" class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="product-image" style="display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="font-size: 48px; color: var(--text-light); opacity: 0.3;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                    <span class="stock-badge stock-<?php echo $product['stock'] == 0 ? 'out' : ($product['stock'] < 10 ? 'low' : 'high'); ?>">
                                        Stock: <?php echo $product['stock']; ?>
                                    </span>
                                    <span style="font-size: 12px; color: var(--text-light);">
                                        <?php echo htmlspecialchars($product['category'] ?? 'Sin categoría'); ?>
                                    </span>
                                </div>
                                <div class="product-actions">
                                    <button class="btn-edit" onclick='editProduct(<?php echo json_encode($product); ?>)'>
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- TAB: Categorías -->
            <div id="categories" class="tab-content">
                <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                    <button class="btn btn-add" onclick="openCategoryModal()">
                        <i class="fas fa-plus"></i> Agregar Categoría
                    </button>
                </div>
                
                <div class="categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-card">
                            <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 15px;">
                                <?php echo htmlspecialchars($category['description'] ?? 'Sin descripción'); ?>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--text-light); font-size: 13px;">
                                    <i class="fas fa-box"></i> <?php echo $category['product_count']; ?> productos
                                </span>
                                <div>
                                    <button class="btn-edit" style="margin-right: 5px;" onclick='editCategory(<?php echo json_encode($category); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- TAB: Pedidos -->
            <div id="orders" class="tab-content">
                <div class="stats-grid" style="margin-bottom: 20px;">
                    <div class="stat-card">
                        <h3>Pendientes</h3>
                        <div class="stat-value" style="font-size: 24px;"><?php echo $order_stats['pending']; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>En Proceso</h3>
                        <div class="stat-value" style="font-size: 24px;"><?php echo $order_stats['processing']; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Enviados</h3>
                        <div class="stat-value" style="font-size: 24px;"><?php echo $order_stats['shipped']; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Entregados</h3>
                        <div class="stat-value" style="font-size: 24px;"><?php echo $order_stats['delivered']; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Ingresos</h3>
                        <div class="stat-value" style="font-size: 20px;">$<?php echo number_format($order_stats['total_revenue'], 0); ?></div>
                    </div>
                </div>
                
                <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: var(--shadow-sm);">
                    <h3 style="margin-bottom: 20px;"><i class="fas fa-shopping-bag"></i> Todos los Pedidos</h3>
                    <?php if (!empty($allOrders)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nº Pedido</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allOrders as $order): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo $order['total_items']; ?></td>
                                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        <td>
                                            <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="order_status" style="padding: 6px; border-radius: 4px; border: 1px solid var(--border-color); font-size: 12px;">
                                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En Proceso</option>
                                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Enviado</option>
                                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Entregado</option>
                                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                                </select>
                                                <button type="submit" name="update_order_status" class="btn-edit" style="padding: 6px 12px; margin: 0;">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 40px; color: var(--text-light);">No hay pedidos todavía</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal Producto -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productModalTitle">Agregar Producto</h2>
                <button class="btn-close" onclick="closeProductModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="product_id">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="name" id="product_name" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" id="product_description"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Precio (ARS) *</label>
                        <input type="number" name="price" id="product_price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Stock *</label>
                        <input type="number" name="stock" id="product_stock" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <input type="text" name="category" id="product_category">
                </div>
                <div class="form-group">
                    <label>Imagen</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="product_active" checked>
                        Producto Activo
                    </label>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeProductModal()" style="background: var(--border-color);">Cancelar</button>
                    <button type="submit" name="add_product" id="productSubmitBtn" class="btn btn-add">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Categoría -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="categoryModalTitle">Agregar Categoría</h2>
                <button class="btn-close" onclick="closeCategoryModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="category_id" id="category_id">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="cat_name" id="cat_name" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="cat_description" id="cat_description"></textarea>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="cat_is_active" id="cat_is_active" checked>
                        Categoría Activa
                    </label>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeCategoryModal()" style="background: var(--border-color);">Cancelar</button>
                    <button type="submit" name="add_category" id="categorySubmitBtn" class="btn btn-add">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Sistema de Tabs
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.menu-link').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            const menuLink = document.querySelector(`[data-tab="${tabName}"].menu-link`);
            if (menuLink) menuLink.classList.add('active');
            
            const titles = {
                'dashboard': 'Dashboard',
                'products': 'Gestión de Productos',
                'categories': 'Gestión de Categorías',
                'orders': 'Gestión de Pedidos'
            };
            document.getElementById('pageTitle').textContent = titles[tabName] || 'Dashboard';
        }
        
        document.querySelectorAll('.menu-link').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tabName = btn.getAttribute('data-tab');
                if (tabName) {
                    e.preventDefault();
                    switchTab(tabName);
                }
            });
        });
        
        // Productos
        function openProductModal() {
            document.getElementById('productModalTitle').textContent = 'Agregar Producto';
            document.querySelector('form').reset();
            document.getElementById('product_id').value = '';
            document.getElementById('productSubmitBtn').name = 'add_product';
            document.getElementById('productModal').classList.add('show');
        }
        
        function editProduct(product) {
            document.getElementById('productModalTitle').textContent = 'Editar Producto';
            document.getElementById('product_id').value = product.id;
            document.getElementById('product_name').value = product.name;
            document.getElementById('product_description').value = product.description || '';
            document.getElementById('product_price').value = product.price;
            document.getElementById('product_stock').value = product.stock;
            document.getElementById('product_category').value = product.category || '';
            document.getElementById('product_active').checked = product.is_active == 1;
            document.getElementById('productSubmitBtn').name = 'update_product';
            document.getElementById('productModal').classList.add('show');
        }
        
        function closeProductModal() {
            document.getElementById('productModal').classList.remove('show');
        }
        
        function deleteProduct(id, name) {
            if (confirm(`¿Eliminar "${name}"?`)) {
                window.location.href = '?delete_product=' + id;
            }
        }
        
        // Categorías
        function openCategoryModal() {
            document.getElementById('categoryModalTitle').textContent = 'Agregar Categoría';
            document.querySelector('#categoryModal form').reset();
            document.getElementById('category_id').value = '';
            document.getElementById('categorySubmitBtn').name = 'add_category';
            document.getElementById('categoryModal').classList.add('show');
        }
        
        function editCategory(category) {
            document.getElementById('categoryModalTitle').textContent = 'Editar Categoría';
            document.getElementById('category_id').value = category.id;
            document.getElementById('cat_name').value = category.name;
            document.getElementById('cat_description').value = category.description || '';
            document.getElementById('cat_is_active').checked = category.is_active == 1;
            document.getElementById('categorySubmitBtn').name = 'update_category';
            document.getElementById('categoryModal').classList.add('show');
        }
        
        function closeCategoryModal() {
            document.getElementById('categoryModal').classList.remove('show');
        }
        
        function deleteCategory(id, name) {
            if (confirm(`¿Eliminar categoría "${name}"?`)) {
                window.location.href = '?delete_category=' + id;
            }
        }
        
        // Cerrar modales al hacer clic fuera
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
