<?php
require_once __DIR__ . '/../config/config.php';

// Verify admin access
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = 'Category Management - Admin';
$success = '';
$error = '';

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (!empty($name)) {
        try {
            executeQuery(
                "INSERT INTO categories (name, description, is_active, created_at) VALUES (?, ?, ?, NOW())",
                [$name, $description, $is_active]
            );
            $success = 'Category added successfully';
        } catch (Exception $e) {
            $error = 'Error adding category';
        }
    } else {
        $error = 'Category name is required';
    }
}

// Update category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = intval($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (!empty($name)) {
        try {
            executeQuery(
                "UPDATE categories SET name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                [$name, $description, $is_active, $id]
            );
            $success = 'Category updated successfully';
        } catch (Exception $e) {
            $error = 'Error updating category';
        }
    }
}

// Delete category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        // Check if there are products using this category
        $stmt = executeQuery("SELECT COUNT(*) as count FROM products WHERE category = (SELECT name FROM categories WHERE id = ?)", [$id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            $error = "Cannot delete this category because it has $count associated product(s)";
        } else {
            executeQuery("DELETE FROM categories WHERE id = ?", [$id]);
            $success = 'Category deleted successfully';
        }
    } catch (Exception $e) {
        $error = 'Error deleting category';
    }
}

// Get all categories
try {
    $stmt = executeQuery("SELECT c.*, COUNT(p.id) as product_count 
                          FROM categories c 
                          LEFT JOIN products p ON c.name = p.category 
                          GROUP BY c.id 
                          ORDER BY c.name ASC");
    $categories = $stmt->fetchAll();
    
    // Statistics
    $stmt_stats = executeQuery("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
        FROM categories");
    $stats = $stmt_stats->fetch();
    
} catch (Exception $e) {
    $categories = [];
    $error = 'Error loading categories';
}

$pageTitle = 'Category Management';
include __DIR__ . '/header.php';
?>

<style>
.admin-page {
    background: var(--bg-light);
    min-height: calc(100vh - 200px);
    padding: 40px 0;
}

.admin-container {
    max-width: 1200px;
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

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.category-card {
    background: var(--white);
    border-radius: 12px;
    padding: 25px;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
    position: relative;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.category-name {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.category-description {
    color: var(--text-light);
    font-size: 14px;
    margin-bottom: 15px;
    line-height: 1.5;
}

.category-info {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
}

.info-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: var(--text-light);
}

.info-item i {
    color: var(--primary-cyan);
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

.category-actions {
    display: flex;
    gap: 10px;
}

.btn-action {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
}

.btn-edit {
    background: #17a2b8;
    flex: 1;
}

.btn-edit:hover {
    background: #138496;
}

.btn-delete {
    background: #dc3545;
    flex: 1;
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
    max-width: 500px;
    width: 90%;
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
.form-group textarea {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
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
    background: var(--white);
    border-radius: 12px;
}

.empty-state i {
    font-size: 64px;
    color: var(--primary-cyan);
    opacity: 0.3;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-tags"></i> Gestión de Categorías</h1>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>/admin" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button class="btn btn-add" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Nueva Categoría
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
                <div class="stat-label">Total Categorías</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['active']; ?></div>
                <div class="stat-label">Categorías Activas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($categories); ?></div>
                <div class="stat-label">Categorías con Productos</div>
            </div>
        </div>
        
        <!-- Grid de Categorías -->
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>No hay categorías</h3>
                <p>Comienza creando tu primera categoría para organizar tus productos</p>
            </div>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-header">
                            <span class="status-badge status-<?php echo $category['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $category['is_active'] ? 'Activa' : 'Inactiva'; ?>
                            </span>
                        </div>
                        
                        <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                        
                        <?php if (!empty($category['description'])): ?>
                            <div class="category-description">
                                <?php echo htmlspecialchars($category['description']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="category-info">
                            <div class="info-item">
                                <i class="fas fa-box"></i>
                                <span><?php echo $category['product_count']; ?> productos</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="category-actions">
                            <button class="btn-action btn-edit" onclick='editCategory(<?php echo json_encode($category); ?>)'>
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', <?php echo $category['product_count']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Agregar/Editar Categoría -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Nueva Categoría</h2>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="categoryForm" method="POST">
            <input type="hidden" name="category_id" id="category_id">
            
            <div class="form-group">
                <label>Nombre de la Categoría *</label>
                <input type="text" name="name" id="name" required placeholder="Ej: Suplementos, Vitaminas">
            </div>
            
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" id="description" placeholder="Descripción breve de la categoría"></textarea>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active" checked>
                    <label for="is_active" style="margin: 0;">Categoría Activa</label>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-back" onclick="closeModal()">Cancelar</button>
                <button type="submit" name="add_category" id="submitBtn" class="btn btn-add">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Nueva Categoría';
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    document.getElementById('submitBtn').name = 'add_category';
    document.getElementById('categoryModal').classList.add('show');
}

function editCategory(category) {
    document.getElementById('modalTitle').textContent = 'Editar Categoría';
    document.getElementById('category_id').value = category.id;
    document.getElementById('name').value = category.name;
    document.getElementById('description').value = category.description || '';
    document.getElementById('is_active').checked = category.is_active == 1;
    document.getElementById('submitBtn').name = 'update_category';
    document.getElementById('categoryModal').classList.add('show');
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('show');
}

function deleteCategory(id, name, productCount) {
    if (productCount > 0) {
        alert(`No se puede eliminar la categoría "${name}" porque tiene ${productCount} producto(s) asociado(s).\n\nPrimero debes eliminar o reasignar los productos.`);
        return;
    }
    
    if (confirm(`¿Estás seguro de eliminar la categoría "${name}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = '?delete=' + id;
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
