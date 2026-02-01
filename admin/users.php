<?php
require_once __DIR__ . '/../config/config.php';

// Verify admin access
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = 'User Management - Admin';
$success = '';
$error = '';

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (!empty($full_name) && !empty($email) && !empty($password)) {
        try {
            // Check if email already exists
            $stmt = executeQuery("SELECT id FROM users WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                $error = 'Email is already registered';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                executeQuery(
                    "INSERT INTO users (full_name, email, password, role, phone, address, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW())",
                    [$full_name, $email, $hashed_password, $role, $phone, $address]
                );
                $success = 'User added successfully';
            }
        } catch (Exception $e) {
            $error = 'Error adding user';
        }
    } else {
        $error = 'Please complete all required fields';
    }
}

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = intval($_POST['user_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'customer';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (!empty($full_name) && !empty($email)) {
        try {
            // If there's a new password, update it
            if (!empty($_POST['password'])) {
                $hashed_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                executeQuery(
                    "UPDATE users SET full_name = ?, email = ?, password = ?, role = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?",
                    [$full_name, $email, $hashed_password, $role, $phone, $address, $id]
                );
            } else {
                executeQuery(
                    "UPDATE users SET full_name = ?, email = ?, role = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?",
                    [$full_name, $email, $role, $phone, $address, $id]
                );
            }
            $success = 'User updated successfully';
        } catch (Exception $e) {
            $error = 'Error updating user';
        }
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Don't allow deleting current user
    if ($id == $_SESSION['user_id']) {
        $error = 'You cannot delete your own user account';
    } else {
        try {
            // Check if user has orders
            $stmt = executeQuery("SELECT COUNT(*) as count FROM orders WHERE user_id = ?", [$id]);
            $orderCount = $stmt->fetch()['count'];
            
            if ($orderCount > 0) {
                $error = "Cannot delete this user because they have $orderCount associated order(s)";
            } else {
                executeQuery("DELETE FROM users WHERE id = ?", [$id]);
                $success = 'User deleted successfully';
            }
        } catch (Exception $e) {
            $error = 'Error deleting user';
        }
    }
}

// Obtener todos los usuarios
$search = $_GET['search'] ?? '';
$filter_role = $_GET['role'] ?? 'all';

try {
    $sql = "SELECT u.*, COUNT(o.id) as order_count, SUM(o.total) as total_spent 
            FROM users u 
            LEFT JOIN orders o ON u.id = o.user_id 
            WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if ($filter_role !== 'all') {
        $sql .= " AND u.role = ?";
        $params[] = $filter_role;
    }
    
    $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
    
    $stmt = executeQuery($sql, $params);
    $users = $stmt->fetchAll();
    
    // Estadísticas
    $stmt_stats = executeQuery("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customers
        FROM users");
    $stats = $stmt_stats->fetch();
    
} catch (Exception $e) {
    $users = [];
    $error = 'Error al cargar los usuarios';
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

.users-table {
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

.role-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.role-admin {
    background: #dc3545;
    color: white;
}

.role-customer {
    background: #17a2b8;
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
    min-height: 80px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
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

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary-cyan);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
}

@media (max-width: 768px) {
    .users-table {
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
            <h1><i class="fas fa-users"></i> Gestión de Usuarios</h1>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>/admin" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button class="btn btn-add" onclick="openAddModal()">
                    <i class="fas fa-user-plus"></i> Agregar Usuario
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
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['customers']; ?></div>
                <div class="stat-label">Clientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['admins']; ?></div>
                <div class="stat-label">Administradores</div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <a href="?role=all" class="filter-btn <?php echo $filter_role === 'all' ? 'active' : ''; ?>">Todos</a>
                    <a href="?role=customer" class="filter-btn <?php echo $filter_role === 'customer' ? 'active' : ''; ?>">Clientes</a>
                    <a href="?role=admin" class="filter-btn <?php echo $filter_role === 'admin' ? 'active' : ''; ?>">Admins</a>
                </div>
                
                <div class="search-box">
                    <form method="GET">
                        <input type="text" name="search" placeholder="Buscar por nombre o email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <?php if ($filter_role !== 'all'): ?>
                            <input type="hidden" name="role" value="<?php echo $filter_role; ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tabla de Usuarios -->
        <?php if (empty($users)): ?>
            <div class="users-table">
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No hay usuarios</h3>
                    <p>Los usuarios aparecerán aquí</p>
                </div>
            </div>
        <?php else: ?>
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Teléfono</th>
                            <th>Pedidos</th>
                            <th>Total Gastado</th>
                            <th>Registrado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'ADMIN' : 'CLIENTE'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td><?php echo $user['order_count']; ?></td>
                                <td>$<?php echo number_format($user['total_spent'] ?? 0, 2); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn-action btn-edit" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', <?php echo $user['order_count']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Agregar/Editar Usuario -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Agregar Usuario</h2>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        
        <form id="userForm" method="POST">
            <input type="hidden" name="user_id" id="user_id">
            
            <div class="form-group">
                <label>Nombre Completo *</label>
                <input type="text" name="full_name" id="full_name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="phone" id="phone">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label id="passwordLabel">Contraseña *</label>
                    <input type="password" name="password" id="password">
                </div>
                
                <div class="form-group">
                    <label>Rol *</label>
                    <select name="role" id="role" required>
                        <option value="customer">Cliente</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Dirección</label>
                <textarea name="address" id="address"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-back" onclick="closeModal()">Cancelar</button>
                <button type="submit" name="add_user" id="submitBtn" class="btn btn-add">
                    <i class="fas fa-save"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Agregar Usuario';
    document.getElementById('userForm').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordLabel').textContent = 'Contraseña *';
    document.getElementById('submitBtn').name = 'add_user';
    document.getElementById('userModal').classList.add('show');
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Editar Usuario';
    document.getElementById('user_id').value = user.id;
    document.getElementById('full_name').value = user.full_name;
    document.getElementById('email').value = user.email;
    document.getElementById('phone').value = user.phone || '';
    document.getElementById('role').value = user.role;
    document.getElementById('address').value = user.address || '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('passwordLabel').textContent = 'Contraseña (dejar en blanco para no cambiar)';
    document.getElementById('submitBtn').name = 'update_user';
    document.getElementById('userModal').classList.add('show');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('show');
}

function deleteUser(id, name, orderCount) {
    if (orderCount > 0) {
        alert(`No se puede eliminar el usuario "${name}" porque tiene ${orderCount} pedido(s) asociado(s).`);
        return;
    }
    
    if (confirm(`¿Estás seguro de eliminar el usuario "${name}"?\n\nEsta acción no se puede deshacer.`)) {
        window.location.href = '?delete=' + id;
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
