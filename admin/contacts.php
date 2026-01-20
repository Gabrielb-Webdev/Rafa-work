<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = 'Mensajes de Contacto - Admin';
$success = '';
$error = '';

// Marcar como leído
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    try {
        executeQuery("UPDATE contacts SET is_read = 1 WHERE id = ?", [$id]);
        $success = 'Mensaje marcado como leído';
    } catch (Exception $e) {
        $error = 'Error al actualizar el mensaje';
    }
}

// Marcar como no leído
if (isset($_GET['mark_unread'])) {
    $id = intval($_GET['mark_unread']);
    try {
        executeQuery("UPDATE contacts SET is_read = 0 WHERE id = ?", [$id]);
        $success = 'Mensaje marcado como no leído';
    } catch (Exception $e) {
        $error = 'Error al actualizar el mensaje';
    }
}

// Eliminar mensaje
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        executeQuery("DELETE FROM contacts WHERE id = ?", [$id]);
        $success = 'Mensaje eliminado correctamente';
    } catch (Exception $e) {
        $error = 'Error al eliminar el mensaje';
    }
}

// Obtener todos los mensajes
$filter_read = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

try {
    $sql = "SELECT * FROM contacts WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if ($filter_read === 'unread') {
        $sql .= " AND is_read = 0";
    } elseif ($filter_read === 'read') {
        $sql .= " AND is_read = 1";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = executeQuery($sql, $params);
    $contacts = $stmt->fetchAll();
    
    // Estadísticas
    $stmt_stats = executeQuery("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read
        FROM contacts");
    $stats = $stmt_stats->fetch();
    
} catch (Exception $e) {
    $contacts = [];
    $error = 'Error al cargar los mensajes';
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

.btn-back {
    padding: 10px 20px;
    background: var(--primary-cyan);
    color: white;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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

.messages-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message-card {
    background: var(--white);
    border-radius: 12px;
    padding: 20px;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.message-card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.message-card.unread {
    border-left: 4px solid var(--primary-cyan);
    background: #f0ffff;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    gap: 20px;
}

.message-info {
    flex: 1;
}

.message-from {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.sender-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-cyan);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
}

.sender-details {
    flex: 1;
}

.sender-name {
    font-weight: 700;
    font-size: 16px;
    color: var(--text-dark);
}

.sender-email {
    font-size: 13px;
    color: var(--text-light);
}

.message-subject {
    font-weight: 600;
    font-size: 15px;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.message-preview {
    color: var(--text-light);
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 12px;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 13px;
    color: var(--text-light);
}

.message-actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    color: white;
    transition: all 0.3s ease;
}

.btn-view {
    background: #17a2b8;
}

.btn-view:hover {
    background: #138496;
}

.btn-delete {
    background: #dc3545;
}

.btn-delete:hover {
    background: #c82333;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-read {
    background: #d4edda;
    color: #155724;
}

.status-unread {
    background: #fff3cd;
    color: #856404;
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
    max-width: 700px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-color);
}

.modal-header h2 {
    font-size: 22px;
    color: var(--text-dark);
}

.btn-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: var(--text-light);
}

.message-detail {
    line-height: 1.8;
}

.detail-row {
    margin-bottom: 15px;
}

.detail-label {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.detail-value {
    color: var(--text-light);
}

.message-full-text {
    background: var(--bg-light);
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    line-height: 1.8;
    color: var(--text-dark);
}

@media (max-width: 768px) {
    .message-header {
        flex-direction: column;
    }
    
    .message-actions {
        width: 100%;
    }
    
    .btn-action {
        flex: 1;
    }
}
</style>

<section class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-envelope"></i> Mensajes de Contacto</h1>
            <a href="<?php echo BASE_URL; ?>/admin" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver
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
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Mensajes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #ff9800;"><?php echo $stats['unread']; ?></div>
                <div class="stat-label">Sin Leer</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #28a745;"><?php echo $stats['read']; ?></div>
                <div class="stat-label">Leídos</div>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <a href="?status=all" class="filter-btn <?php echo $filter_read === 'all' ? 'active' : ''; ?>">Todos</a>
                    <a href="?status=unread" class="filter-btn <?php echo $filter_read === 'unread' ? 'active' : ''; ?>">No Leídos</a>
                    <a href="?status=read" class="filter-btn <?php echo $filter_read === 'read' ? 'active' : ''; ?>">Leídos</a>
                </div>
                
                <div class="search-box">
                    <form method="GET">
                        <input type="text" name="search" placeholder="Buscar por nombre, email o asunto..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <?php if ($filter_read !== 'all'): ?>
                            <input type="hidden" name="status" value="<?php echo $filter_read; ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Lista de Mensajes -->
        <?php if (empty($contacts)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No hay mensajes</h3>
                <p>Los mensajes de contacto aparecerán aquí</p>
            </div>
        <?php else: ?>
            <div class="messages-list">
                <?php foreach ($contacts as $contact): ?>
                    <div class="message-card <?php echo $contact['is_read'] ? '' : 'unread'; ?>">
                        <div class="message-header">
                            <div class="message-info">
                                <div class="message-from">
                                    <div class="sender-avatar">
                                        <?php echo strtoupper(substr($contact['name'], 0, 1)); ?>
                                    </div>
                                    <div class="sender-details">
                                        <div class="sender-name"><?php echo htmlspecialchars($contact['name']); ?></div>
                                        <div class="sender-email"><?php echo htmlspecialchars($contact['email']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="message-subject">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($contact['subject']); ?>
                                </div>
                                
                                <div class="message-preview">
                                    <?php echo substr(htmlspecialchars($contact['message']), 0, 150); ?>...
                                </div>
                                
                                <div class="message-meta">
                                    <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></span>
                                    <span class="status-badge status-<?php echo $contact['is_read'] ? 'read' : 'unread'; ?>">
                                        <?php echo $contact['is_read'] ? 'Leído' : 'No Leído'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="message-actions">
                                <button class="btn-action btn-view" onclick='viewMessage(<?php echo json_encode($contact); ?>)'>
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if (!$contact['is_read']): ?>
                                    <a href="?mark_read=<?php echo $contact['id']; ?>" class="btn-action" style="background: #28a745;">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?mark_unread=<?php echo $contact['id']; ?>" class="btn-action" style="background: #ffc107;">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $contact['id']; ?>" class="btn-action btn-delete" 
                                   onclick="return confirm('¿Eliminar este mensaje?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Ver Mensaje -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-envelope-open"></i> Detalles del Mensaje</h2>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        
        <div class="message-detail">
            <div class="detail-row">
                <div class="detail-label">De:</div>
                <div class="detail-value" id="modal-name"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value" id="modal-email"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Asunto:</div>
                <div class="detail-value" id="modal-subject"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Fecha:</div>
                <div class="detail-value" id="modal-date"></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Mensaje:</div>
                <div class="message-full-text" id="modal-message"></div>
            </div>
        </div>
    </div>
</div>

<script>
function viewMessage(contact) {
    document.getElementById('modal-name').textContent = contact.name;
    document.getElementById('modal-email').textContent = contact.email;
    document.getElementById('modal-subject').textContent = contact.subject;
    document.getElementById('modal-date').textContent = new Date(contact.created_at).toLocaleString('es-ES');
    document.getElementById('modal-message').textContent = contact.message;
    document.getElementById('messageModal').classList.add('show');
    
    // Marcar como leído si no lo está
    if (!contact.is_read) {
        setTimeout(() => {
            window.location.href = '?mark_read=' + contact.id;
        }, 1000);
    }
}

function closeModal() {
    document.getElementById('messageModal').classList.remove('show');
}

// Cerrar modal al hacer clic fuera
document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
