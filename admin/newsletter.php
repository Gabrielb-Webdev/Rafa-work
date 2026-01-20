<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$pageTitle = 'Newsletter - Suscriptores - Admin';
$success = '';
$error = '';

// Eliminar suscriptor
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        executeQuery("DELETE FROM newsletter WHERE id = ?", [$id]);
        $success = 'Suscriptor eliminado correctamente';
    } catch (Exception $e) {
        $error = 'Error al eliminar el suscriptor';
    }
}

// Exportar a CSV
if (isset($_GET['export'])) {
    try {
        $stmt = executeQuery("SELECT * FROM newsletter ORDER BY created_at DESC");
        $subscribers = $stmt->fetchAll();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=newsletter-subscribers-' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Encabezados
        fputcsv($output, ['Email', 'Fecha de Suscripción']);
        
        // Datos
        foreach ($subscribers as $sub) {
            fputcsv($output, [
                $sub['email'],
                date('d/m/Y H:i', strtotime($sub['created_at']))
            ]);
        }
        
        fclose($output);
        exit;
    } catch (Exception $e) {
        $error = 'Error al exportar los datos';
    }
}

// Obtener todos los suscriptores
$search = $_GET['search'] ?? '';

try {
    $sql = "SELECT * FROM newsletter WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND email LIKE ?";
        $params[] = '%' . $search . '%';
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = executeQuery($sql, $params);
    $subscribers = $stmt->fetchAll();
    
    // Estadísticas
    $stmt_stats = executeQuery("SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today,
        COUNT(CASE WHEN YEARWEEK(created_at) = YEARWEEK(NOW()) THEN 1 END) as this_week,
        COUNT(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 END) as this_month
        FROM newsletter");
    $stats = $stmt_stats->fetch();
    
} catch (Exception $e) {
    $subscribers = [];
    $error = 'Error al cargar los suscriptores';
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

.btn-export {
    background: #28a745;
    color: white;
}

.btn-export:hover {
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

.search-section {
    background: var(--white);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
}

.search-box {
    display: flex;
    gap: 10px;
}

.search-box input {
    flex: 1;
    padding: 10px 15px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
}

.subscribers-table {
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

.subscriber-email {
    display: flex;
    align-items: center;
    gap: 12px;
}

.email-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-cyan);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.btn-action {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    color: white;
    transition: all 0.3s ease;
    margin-right: 5px;
}

.btn-copy {
    background: #17a2b8;
}

.btn-copy:hover {
    background: #138496;
}

.btn-delete {
    background: #dc3545;
}

.btn-delete:hover {
    background: #c82333;
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

.info-box {
    background: #e7f3ff;
    border-left: 4px solid #2196F3;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.info-box p {
    margin: 0;
    color: #1976D2;
    display: flex;
    align-items: center;
    gap: 10px;
}

@media (max-width: 768px) {
    .subscribers-table {
        overflow-x: auto;
    }
    
    table {
        min-width: 600px;
    }
}
</style>

<section class="admin-page">
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-newspaper"></i> Newsletter - Suscriptores</h1>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>/admin" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="?export=1" class="btn btn-export">
                    <i class="fas fa-download"></i> Exportar CSV
                </a>
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
        
        <div class="info-box">
            <p>
                <i class="fas fa-info-circle"></i>
                <strong>Consejo:</strong> Puedes exportar todos los emails a CSV para importarlos en tu servicio de email marketing.
            </p>
        </div>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Suscriptores</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #28a745;"><?php echo $stats['today']; ?></div>
                <div class="stat-label">Hoy</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #17a2b8;"><?php echo $stats['this_week']; ?></div>
                <div class="stat-label">Esta Semana</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: #ffc107;"><?php echo $stats['this_month']; ?></div>
                <div class="stat-label">Este Mes</div>
            </div>
        </div>
        
        <!-- Búsqueda -->
        <div class="search-section">
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Buscar por email..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-back">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>
        </div>
        
        <!-- Tabla de Suscriptores -->
        <?php if (empty($subscribers)): ?>
            <div class="subscribers-table">
                <div class="empty-state">
                    <i class="fas fa-envelope-open"></i>
                    <h3>No hay suscriptores</h3>
                    <p>Los suscriptores del newsletter aparecerán aquí</p>
                </div>
            </div>
        <?php else: ?>
            <div class="subscribers-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Email</th>
                            <th>Fecha de Suscripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $index => $subscriber): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="subscriber-email">
                                        <div class="email-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <strong><?php echo htmlspecialchars($subscriber['email']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($subscriber['created_at'])); ?>
                                </td>
                                <td>
                                    <button class="btn-action btn-copy" onclick="copyEmail('<?php echo htmlspecialchars($subscriber['email']); ?>')">
                                        <i class="fas fa-copy"></i> Copiar
                                    </button>
                                    <a href="?delete=<?php echo $subscriber['id']; ?>" 
                                       class="btn-action btn-delete" 
                                       onclick="return confirm('¿Eliminar este suscriptor?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function copyEmail(email) {
    // Crear un elemento temporal
    const temp = document.createElement('textarea');
    temp.value = email;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
    
    // Mostrar feedback
    alert('Email copiado al portapapeles: ' + email);
}

// Copiar todos los emails
function copyAllEmails() {
    const emails = [];
    document.querySelectorAll('.subscriber-email strong').forEach(el => {
        emails.push(el.textContent);
    });
    
    const temp = document.createElement('textarea');
    temp.value = emails.join(', ');
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
    
    alert('Todos los emails copiados al portapapeles!');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
