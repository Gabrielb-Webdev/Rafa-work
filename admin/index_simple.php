<?php
/**
 * DASHBOARD ADMIN SIMPLE - MediCareOnline
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login_simple.php?error=access_denied');
    exit;
}

// Cargar base de datos
require_once __DIR__ . '/../config/database.php';

// Obtener estadísticas
try {
    // Total productos
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    
    // Total órdenes
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    
    // Total usuarios
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Órdenes pendientes
    $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pendiente'")->fetchColumn();
    
    // Productos recientes
    $recent_products = $pdo->query("SELECT id, name, price, stock_quantity, created_at FROM products ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
    // Órdenes recientes
    $recent_orders = $pdo->query("SELECT id, order_number, customer_name, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MediCareOnline Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Poppins', sans-serif; }
        .navbar { background: linear-gradient(135deg, #00D4FF, #0088AA); }
        .stat-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; }
        .icon-blue { background: linear-gradient(135deg, #00D4FF, #0088AA); }
        .icon-green { background: linear-gradient(135deg, #28a745, #20c997); }
        .icon-orange { background: linear-gradient(135deg, #fd7e14, #ffc107); }
        .icon-purple { background: linear-gradient(135deg, #6f42c1, #d63384); }
        .sidebar { background: white; min-height: calc(100vh - 56px); padding: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .sidebar a { color: #333; text-decoration: none; padding: 10px 15px; display: block; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s; }
        .sidebar a:hover { background: #f0f0f0; }
        .sidebar a.active { background: linear-gradient(135deg, #00D4FF, #0088AA); color: white; }
        .table-custom { background: white; border-radius: 10px; overflow: hidden; }
        .badge-pendiente { background: #ffc107; }
        .badge-confirmado { background: #17a2b8; }
        .badge-entregado { background: #28a745; }
        .badge-cancelado { background: #dc3545; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-pills me-2"></i> MediCareOnline Admin
            </a>
            <div class="text-white">
                <i class="fas fa-user-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrador'); ?>
                <a href="logout.php" class="btn btn-light btn-sm ms-3">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <a href="index_simple.php" class="active">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="products.php">
                    <i class="fas fa-pills me-2"></i> Productos
                </a>
                <a href="orders.php">
                    <i class="fas fa-shopping-cart me-2"></i> Pedidos
                </a>
                <a href="categories.php">
                    <i class="fas fa-folder me-2"></i> Categorías
                </a>
                <a href="brands.php">
                    <i class="fas fa-tags me-2"></i> Marcas
                </a>
                <a href="users.php">
                    <i class="fas fa-users me-2"></i> Usuarios
                </a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </h2>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Productos</h6>
                                    <h2 class="mb-0"><?php echo $total_products; ?></h2>
                                </div>
                                <div class="stat-icon icon-blue">
                                    <i class="fas fa-pills"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Pedidos</h6>
                                    <h2 class="mb-0"><?php echo $total_orders; ?></h2>
                                </div>
                                <div class="stat-icon icon-green">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Pedidos Pendientes</h6>
                                    <h2 class="mb-0"><?php echo $pending_orders; ?></h2>
                                </div>
                                <div class="stat-icon icon-orange">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Usuarios</h6>
                                    <h2 class="mb-0"><?php echo $total_users; ?></h2>
                                </div>
                                <div class="stat-icon icon-purple">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5 class="mb-3">
                                <i class="fas fa-pills me-2 text-primary"></i> Productos Recientes
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Precio</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td>S/ <?php echo number_format($product['price'], 2); ?></td>
                                            <td><?php echo $product['stock_quantity']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="products.php" class="btn btn-sm btn-primary">
                                Ver todos los productos <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5 class="mb-3">
                                <i class="fas fa-shopping-cart me-2 text-success"></i> Pedidos Recientes
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>N° Pedido</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td>S/ <?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="orders.php" class="btn btn-sm btn-success">
                                Ver todos los pedidos <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
