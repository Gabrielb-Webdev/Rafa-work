<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = 'Gestión de Productos - Admin';
$success = '';
$error = '';

// Agregar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Manejo de imagen
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
    
    if (!empty($name)) {
        try {
            // Generar slug único a partir del nombre
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            
            // Verificar si el slug ya existe y hacerlo único
            $existing = fetchOne("SELECT COUNT(*) as count FROM products WHERE slug = ?", [$slug]);
            if ($existing['count'] > 0) {
                $slug = $slug . '-' . time();
            }
            
            // Los precios y stock se asignan 0 por defecto (se manejan en cotizaciones)
            executeQuery(
                "INSERT INTO products (name, slug, description, price, stock, category_id, image, is_active, created_at) 
                 VALUES (?, ?, ?, 0, 0, NULL, ?, ?, NOW())",
                [$name, $slug, $description, $image, $is_active]
            );
            $success = 'Producto agregado correctamente';
        } catch (Exception $e) {
            $error = 'Error al agregar el producto: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor completa el nombre del producto';
    }
}

// Actualizar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = intval($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Generar slug único a partir del nombre
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Verificar si el slug ya existe (excluyendo el producto actual)
    $existing = fetchOne("SELECT COUNT(*) as count FROM products WHERE slug = ? AND id != ?", [$slug, $id]);
    if ($existing['count'] > 0) {
        $slug = $slug . '-' . time();
    }
    
    // Manejo de imagen
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/products/';
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        
        executeQuery(
            "UPDATE products SET name = ?, slug = ?, description = ?, category_id = NULL, image = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
            [$name, $slug, $description, $image, $is_active, $id]
        );
    } else {
        executeQuery(
            "UPDATE products SET name = ?, slug = ?, description = ?, category_id = NULL, is_active = ?, updated_at = NOW() WHERE id = ?",
            [$name, $slug, $description, $is_active, $id]
        );
    }
    
    $success = 'Producto actualizado correctamente';
}

// Eliminar producto
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        // Primero verificar si el producto está en pedidos
        $check = fetchOne("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?", [$id]);
        
        if ($check['count'] > 0) {
            $error = 'No se puede eliminar: el producto está asociado a pedidos existentes. Mejor márcalo como inactivo.';
        } else {
            // Obtener imagen para eliminarla
            $product = fetchOne("SELECT image FROM products WHERE id = ?", [$id]);
            
            // Eliminar producto
            executeQuery("DELETE FROM products WHERE id = ?", [$id]);
            
            // Eliminar imagen física si existe
            if ($product && !empty($product['image'])) {
                $image_path = __DIR__ . '/../uploads/products/' . $product['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $success = 'Producto eliminado correctamente';
            header('Location: ' . BASE_URL . '/admin/productos.php');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Error al eliminar el producto: ' . $e->getMessage();
    }
}

// Obtener todos los productos
$search = $_GET['search'] ?? '';
$filter_active = $_GET['active'] ?? 'all';

try {
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR category LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if ($filter_active === '1') {
        $sql .= " AND is_active = 1";
    } elseif ($filter_active === '0') {
        $sql .= " AND is_active = 0";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = executeQuery($sql, $params);
    $products = $stmt->fetchAll();
    
    // Estadísticas
    $stats = [
        'total' => 0,
        'active' => 0,
        'low_stock' => 0,
        'total_value' => 0
    ];
    
    $stmt_stats = executeQuery("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END) as low_stock,
        SUM(price * stock) as total_value
        FROM products");
    $stats = $stmt_stats->fetch();
    
} catch (Exception $e) {
    $products = [];
    $error = 'Error al cargar los productos';
}

$pageTitle = 'Gestión de Productos';
include __DIR__ . '/header.php';
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
    flex-wrap: wrap;
    gap: 20px;
}

.admin-header h1 {
    font-size: 32px;
    color: var(--text-dark);
}

.header-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back {
    background: var(--primary-cyan);
    color: white;
}

.btn-back:hover {
    background: #00bfbf;
}

.btn-add {
    background: #28a745;
    color: white;
}

.btn-add:hover {
    background: #218838;
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

.products-table {
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

.product-image {
    width: 60px;
    height: 60px;
    object-fit: contain;
    border-radius: 8px;
    background: var(--bg-light);
    padding: 5px;
}

.stock-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.stock-high {
    background: #d4edda;
    color: #155724;
}

.stock-low {
    background: #fff3cd;
    color: #856404;
}

.stock-out {
    background: #f8d7da;
    color: #721c24;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #28a745;
    color: white;
}

.status-inactive {
    background: #dc3545;
    color: white;
}

.btn-action {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    margin-right: 5px;
    color: white;
}

.btn-edit {
    background: #17a2b8;
}

.btn-edit:hover {
    background: #138496;
}

.btn-delete {
    background: #dc3545;
}

.btn-delete:hover {
    background: #c82333;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
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

.modal-header h2 {
    font-size: 24px;
    color: var(--text-dark);
}

.btn-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-light);
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
    resize: vertical;
    min-height: 100px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
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
    .products-table {
        overflow-x: auto;
    }
    
    table {
        min-width: 900px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-pills"></i> Gestión de Productos</h1>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>/admin" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button class="btn btn-add" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Agregar Producto
                </button>
            </div>
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
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Productos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['active']; ?></div>
                <div class="stat-label">Productos Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #ff9800;"><?php echo $stats['low_stock']; ?></div>
                <div class="stat-label">Bajo Stock (&lt;10)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">$<?php echo number_format($stats['total_value'], 2); ?></div>
                <div class="stat-label">Valor Total Inventario</div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <a href="?active=all" class="filter-btn <?php echo $filter_active === 'all' ? 'active' : ''; ?>">Todos</a>
                    <a href="?active=1" class="filter-btn <?php echo $filter_active === '1' ? 'active' : ''; ?>">Activos</a>
                    <a href="?active=0" class="filter-btn <?php echo $filter_active === '0' ? 'active' : ''; ?>">Inactivos</a>
                </div>
                
                <div class="search-box">
                    <form method="GET">
                        <input type="text" name="search" placeholder="Buscar por nombre o categoría..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <?php if ($filter_active !== 'all'): ?>
                            <input type="hidden" name="active" value="<?php echo $filter_active; ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tabla de Productos -->
        <?php if (empty($products)): ?>
            <div class="products-table">
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No hay productos</h3>
                    <p>Comienza agregando tu primer producto</p>
                </div>
            </div>
        <?php else: ?>
            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo $product['image']; ?>" 
                                             class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div class="product-image" style="display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: var(--text-light);"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <div style="font-size: 12px; color: var(--text-light); margin-top: 4px;">
                                        <?php echo substr(htmlspecialchars($product['description']), 0, 50); ?>...
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($product['category'] ?? 'Sin categoría'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $product['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action btn-edit" onclick='editProduct(<?php echo json_encode($product); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Agregar/Editar Producto -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Agregar Producto</h2>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="product_id">
            
            <div class="form-group">
                <label>Nombre del Producto *</label>
                <input type="text" name="name" id="name" required>
            </div>
            
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" id="description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Imagen del Producto</label>
                <input type="file" name="image" id="image" accept="image/*">
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active" checked>
                    <label for="is_active" style="margin: 0;">Producto Activo</label>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-back" onclick="closeModal()">Cancelar</button>
                <button type="submit" name="add_product" id="submitBtn" class="btn btn-add">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Agregar Producto';
    document.getElementById('productForm').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('submitBtn').name = 'add_product';
    document.getElementById('productModal').classList.add('show');
}

function editProduct(product) {
    document.getElementById('modalTitle').textContent = 'Editar Producto';
    document.getElementById('product_id').value = product.id;
    document.getElementById('name').value = product.name;
    document.getElementById('description').value = product.description || '';
    document.getElementById('is_active').checked = product.is_active == 1;
    document.getElementById('submitBtn').name = 'update_product';
    document.getElementById('productModal').classList.add('show');
}

function closeModal() {
    document.getElementById('productModal').classList.remove('show');
}

function deleteProduct(id, name) {
    if (confirm(`¿Estás seguro de eliminar el producto "${name}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = '?delete=' + id;
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
