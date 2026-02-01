<?php
require_once __DIR__ . '/config/config.php';

// Check if logged in
if (!isLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'My Profile - Forethink Health';
$success = '';
$error = '';

// Get user data
try {
    $stmt = executeQuery("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirect('/logout.php');
    }
} catch (Exception $e) {
    $error = 'Error loading profile data.';
}

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    
    if (empty($full_name)) {
        $error = 'Full name is required';
    } else {
        try {
            executeQuery(
                "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?",
                [$full_name, $phone, $address, $_SESSION['user_id']]
            );
            
            $_SESSION['user_name'] = $full_name;
            $success = 'Profile updated successfully!';
            
            // Reload data
            $stmt = executeQuery("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } catch (Exception $e) {
            $error = 'Error updating profile.';
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        if (password_verify($current_password, $user['password'])) {
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            try {
                executeQuery("UPDATE users SET password = ? WHERE id = ?", [$new_hash, $_SESSION['user_id']]);
                $success = 'Password updated successfully!';
            } catch (Exception $e) {
                $error = 'Error updating password.';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
.profile-page {
    background: var(--bg-light);
    min-height: calc(100vh - 200px);
    padding: 60px 0;
}

.profile-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px;
}

.profile-header {
    text-align: center;
    margin-bottom: 50px;
}

.profile-header h1 {
    font-size: 36px;
    color: var(--text-dark);
    margin-bottom: 10px;
}

.profile-header p {
    color: var(--text-light);
    font-size: 16px;
}

.profile-content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
}

.profile-sidebar {
    background: var(--white);
    border-radius: 16px;
    padding: 30px;
    box-shadow: var(--shadow-sm);
    height: fit-content;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, var(--primary-cyan), #00b8e6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 48px;
    color: var(--white);
}

.profile-name {
    text-align: center;
    margin-bottom: 10px;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-dark);
}

.profile-email {
    text-align: center;
    color: var(--text-light);
    font-size: 14px;
    margin-bottom: 20px;
}

.profile-role {
    text-align: center;
    display: inline-block;
    width: 100%;
    padding: 8px 16px;
    background: rgba(0, 212, 212, 0.1);
    color: var(--primary-cyan);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.profile-stats {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid var(--border-color);
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
}

.stat-label {
    color: var(--text-light);
    font-size: 14px;
}

.stat-value {
    color: var(--text-dark);
    font-weight: 600;
}

.profile-main {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.profile-card {
    background: var(--white);
    border-radius: 16px;
    padding: 30px;
    box-shadow: var(--shadow-sm);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--bg-light);
}

.card-header i {
    font-size: 24px;
    color: var(--primary-cyan);
}

.card-header h2 {
    font-size: 22px;
    color: var(--text-dark);
    margin: 0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-dark);
    font-size: 14px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-cyan);
    box-shadow: 0 0 0 4px rgba(0, 212, 212, 0.08);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.btn-primary {
    background: var(--primary-cyan);
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #00bfbf;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 212, 212, 0.3);
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

@media (max-width: 768px) {
    .profile-content {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="profile-page">
    <div class="profile-container">
        <div class="profile-header">
            <h1>Mi Perfil</h1>
            <p>Administra tu información personal y configuración de cuenta</p>
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
        
        <div class="profile-content">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
                <div class="profile-role">
                    <?php echo $user['role'] === 'admin' ? 'Administrador' : 'Cliente'; ?>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-label">Miembro desde</span>
                        <span class="stat-value"><?php echo date('Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Estado</span>
                        <span class="stat-value" style="color: #28a745;">Activo</span>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="profile-main">
                <!-- Información Personal -->
                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-user-edit"></i>
                        <h2>Información Personal</h2>
                    </div>
                    
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full_name">Nombre Completo *</label>
                                <input type="text" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Teléfono</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Rol</label>
                                <input type="text" value="<?php echo $user['role'] === 'admin' ? 'Administrador' : 'Cliente'; ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Dirección</label>
                            <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
                
                <!-- Cambiar Contraseña -->
                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-lock"></i>
                        <h2>Cambiar Contraseña</h2>
                    </div>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="current_password">Contraseña Actual *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">Nueva Contraseña *</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Contraseña *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn-primary">
                            <i class="fas fa-key"></i> Actualizar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
