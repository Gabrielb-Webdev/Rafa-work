<?php
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Panel Admin'; ?> - Forethink Health</title>
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
        
        .main-content {
            padding: 30px;
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
            text-decoration: none;
        }
        
        .btn-add {
            background: #28a745;
            color: white;
        }
        
        .btn-add:hover {
            background: #218838;
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
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
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
        
        .btn-edit {
            padding: 8px 16px;
            background: #17a2b8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-delete {
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
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
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo">
                <h2>Admin Panel</h2>
            </div>
            
            <ul class="admin-menu">
                <li><a href="<?php echo BASE_URL; ?>/admin/index.php" class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-dashboard"></i> <span>Dashboard</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/productos.php" class="<?php echo $current_page === 'productos' ? 'active' : ''; ?>">
                    <i class="fas fa-pills"></i> <span>Products</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/categorias.php" class="<?php echo $current_page === 'categorias' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i> <span>Categories</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/pedidos.php" class="<?php echo $current_page === 'pedidos' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i> <span>Orders</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/" target="_blank">
                    <i class="fas fa-globe"></i> <span>View Site</span>
                </a></li>
                <li><a href="<?php echo BASE_URL; ?>/logout">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a></li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <div class="admin-top-bar">
                <h1><?php echo $pageTitle ?? 'Admin Panel'; ?></h1>
                <div>
                    <span style="color: var(--text-light); font-size: 14px;"><?php echo date('m/d/Y'); ?> - <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <div class="main-content">
