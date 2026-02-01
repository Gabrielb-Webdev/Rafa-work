<?php
/**
 * Ver Detalle de Pedido - DISEÑO ULTRA MODERNO
 * Version: 3.0 - COMPLETAMENTE REDISEÑADO
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$order_id = $_GET['id'] ?? 0;
if (!$order_id) {
    header('Location: pedidos.php');
    exit;
}

// Cargar pedido
$stmt = executeQuery(
    "SELECT o.*, 
     COALESCE(o.first_name, u.first_name) as first_name,
     COALESCE(o.last_name, u.last_name) as last_name,
     COALESCE(o.email, u.email) as email,
     COALESCE(o.phone, u.phone) as phone,
     COALESCE(o.address, u.address) as address,
     COALESCE(o.city, u.city) as city,
     COALESCE(o.state, u.state) as state,
     COALESCE(o.zip_code, u.zip_code) as zip_code
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     WHERE o.id = ?",
    [$order_id]
);
$order = $stmt->fetch();

if (!$order) {
    die('Pedido no encontrado');
}

// Cargar productos
$stmt = executeQuery(
    "SELECT oi.*, p.name as product_name, p.image, p.price as current_price
     FROM order_items oi 
     LEFT JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = ?",
    [$order_id]
);
$items = $stmt->fetchAll();

// Cargar mensajes del chat
$messages = [];
try {
    $stmt = executeQuery(
        "SELECT om.*, u.full_name as sender_name, 
         CASE WHEN u.user_role = 'admin' THEN 'admin' ELSE 'client' END as sender_type
         FROM order_messages om 
         JOIN users u ON om.user_id = u.id
         WHERE om.order_id = ? 
         ORDER BY om.created_at ASC",
        [$order_id]
    );
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    // Tabla no existe
}

// Estilos personalizados para esta página
$custom_styles = '
<style>
/* ===============================================
   DISEÑO COMPLETAMENTE NUEVO Y ULTRA MODERNO
   =============================================== */

body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%) !important;
    min-height: 100vh;
    font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif !important;
}

/* Hero Header con Gradiente Espectacular */
.hero-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 50px;
    border-radius: 30px;
    margin-bottom: 40px;
    box-shadow: 0 30px 80px rgba(102, 126, 234, 0.5);
    position: relative;
    overflow: hidden;
    animation: fadeInDown 0.6s ease;
}

.hero-header::before {
    content: "";
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.hero-header::after {
    content: "";
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.hero-content {
    position: relative;
    z-index: 2;
    color: white;
}

.hero-title {
    font-size: 52px;
    font-weight: 900;
    margin: 0 0 15px 0;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    letter-spacing: -1px;
}

.hero-subtitle {
    font-size: 18px;
    opacity: 0.95;
    font-weight: 500;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 30px;
    border-radius: 100px;
    font-size: 15px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    margin-top: 25px;
    margin-right: 15px;
}

.status-pending { 
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}
.status-processing { 
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}
.status-shipped { 
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}
.status-delivered { 
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.proposal-pill {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white !important;
}

/* Botón Back Moderno */
.btn-back-modern {
    background: white;
    color: #667eea;
    padding: 14px 35px;
    border-radius: 100px;
    text-decoration: none;
    font-weight: 800;
    font-size: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-back-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    color: #667eea;
}

/* Cards Modernas con Efecto Glass */
.glass-card {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(20px);
    border-radius: 25px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.8);
    margin-bottom: 35px;
    overflow: hidden;
    animation: fadeInUp 0.6s ease;
    transition: all 0.4s ease;
}

.glass-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 70px rgba(0,0,0,0.15);
}

.glass-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 28px 35px;
    color: white;
    border: none;
}

.glass-card-title {
    font-size: 26px;
    font-weight: 900;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.glass-card-title i {
    font-size: 32px;
}

.glass-card-body {
    padding: 35px;
}

/* Grid de Información Ultra Moderna */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}

.info-box {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
    padding: 25px;
    border-radius: 20px;
    border-left: 5px solid #667eea;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.info-box::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(102, 126, 234, 0.05);
    border-radius: 50%;
    transform: translate(30%, -30%);
}

.info-box:hover {
    transform: translateX(8px);
    background: linear-gradient(135deg, #e8f4ff 0%, #d6e9ff 100%);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
}

.info-label {
    font-size: 13px;
    text-transform: uppercase;
    color: #6c757d;
    font-weight: 800;
    letter-spacing: 1.5px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-label i {
    color: #667eea;
}

.info-value {
    font-size: 20px;
    font-weight: 800;
    color: #2c3e50;
}

.info-value a {
    color: #667eea;
    text-decoration: none;
    transition: all 0.3s ease;
}

.info-value a:hover {
    color: #764ba2;
    text-decoration: underline;
}

/* Productos con Diseño Espectacular */
.product-card {
    background: white;
    padding: 25px;
    border-radius: 20px;
    margin-bottom: 20px;
    border: 2px solid transparent;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    align-items: center;
    gap: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.product-card:hover {
    border-color: #667eea;
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.2);
    transform: scale(1.03);
}

.product-img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.product-details {
    flex: 1;
}

.product-name {
    font-size: 22px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 10px;
}

.quantity-badge {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 25px;
    border-radius: 100px;
    font-weight: 800;
    font-size: 17px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.product-price {
    font-size: 30px;
    font-weight: 900;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Total Display Épico */
.total-showcase {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    padding: 40px;
    border-radius: 25px;
    text-align: center;
    margin-top: 30px;
    box-shadow: 0 20px 60px rgba(40, 167, 69, 0.4);
    position: relative;
    overflow: hidden;
}

.total-showcase::before {
    content: '💰';
    font-size: 100px;
    position: absolute;
    top: -20px;
    right: -20px;
    opacity: 0.1;
}

.total-label {
    color: white;
    font-size: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 10px;
    opacity: 0.9;
}

.total-amount {
    font-size: 64px;
    font-weight: 900;
    color: white;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    letter-spacing: -2px;
}

/* Formulario de Propuesta Épico */
.proposal-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 45px;
    border-radius: 25px;
    color: white;
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
    position: sticky;
    top: 20px;
}

.proposal-title {
    font-size: 28px;
    font-weight: 900;
    margin-bottom: 10px;
}

.proposal-subtitle {
    opacity: 0.9;
    margin-bottom: 30px;
}

.form-group-modern {
    margin-bottom: 25px;
}

.form-label-modern {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: block;
}

.form-control-modern {
    background: white;
    border: none;
    border-radius: 15px;
    padding: 18px 22px;
    font-size: 17px;
    font-weight: 600;
    color: #2c3e50;
    width: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.form-control-modern:focus {
    outline: none;
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    transform: scale(1.02);
}

.btn-send-proposal {
    background: white;
    color: #667eea;
    border: none;
    padding: 20px 45px;
    border-radius: 100px;
    font-size: 19px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 12px 35px rgba(0,0,0,0.25);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    width: 100%;
}

.btn-send-proposal:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 50px rgba(0,0,0,0.35);
}

/* Chat Moderno Ultra */
.chat-container {
    background: white;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 15px 50px rgba(0,0,0,0.15);
    position: sticky;
    top: 20px;
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 28px 30px;
    color: white;
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-title {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
}

.chat-messages {
    height: 550px;
    overflow-y: auto;
    padding: 30px;
    background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
}

.message-bubble {
    max-width: 75%;
    padding: 18px 24px;
    border-radius: 25px;
    margin-bottom: 18px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    animation: slideUp 0.4s ease;
    position: relative;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-admin {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 8px;
}

.message-client {
    background: white;
    color: #2c3e50;
    border-bottom-left-radius: 8px;
}

.message-sender {
    font-weight: 800;
    font-size: 13px;
    margin-bottom: 6px;
    opacity: 0.85;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.message-text {
    font-size: 16px;
    line-height: 1.5;
}

.message-time {
    font-size: 12px;
    opacity: 0.65;
    margin-top: 8px;
    font-weight: 600;
}

.chat-input-area {
    padding: 25px;
    background: white;
    border-top: 2px solid #f0f2f5;
}

.chat-form {
    display: flex;
    gap: 15px;
}

.chat-input {
    flex: 1;
    background: #f5f7fa;
    border: 2px solid #e8ecf1;
    border-radius: 100px;
    padding: 15px 25px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.chat-input:focus {
    outline: none;
    border-color: #667eea;
    background: white;
}

.btn-send-msg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 15px 35px;
    border-radius: 100px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
}

.btn-send-msg:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

/* Scrollbar Moderna */
.chat-messages::-webkit-scrollbar {
    width: 10px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

/* Animations */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 36px;
    }
    
    .product-card {
        flex-direction: column;
        text-align: center;
    }
    
    .total-amount {
        font-size: 48px;
    }
}
</style>
';

$pageTitle = "Pedido #" . $order['order_number'];
require_once 'header.php';
?>

<!-- Inyectar estilos personalizados -->
<?php echo $custom_styles; ?>

<div class="container-fluid px-4 py-4">
    <!-- Hero Header -->
    <div class="hero-header">
        <div class="hero-content">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h1 class="hero-title">🛍️ <?php echo htmlspecialchars($order['order_number']); ?></h1>
                    <div class="hero-subtitle">
                        📅 Recibido el <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                    </div>
                </div>
                <a href="pedidos.php" class="btn-back-modern">
                    ← Volver
                </a>
            </div>
            
            <div>
                <span class="status-pill status-<?php echo $order['status']; ?>">
                    <?php
                    $status_text = [
                        'pending' => '⏳ Pendiente',
                        'processing' => '⚙️ En Proceso',
                        'shipped' => '🚚 Enviado',
                        'delivered' => '✅ Entregado'
                    ];
                    echo $status_text[$order['status']] ?? '📦 ' . $order['status'];
                    ?>
                </span>
                <?php if ($order['proposal_sent']): ?>
                <span class="status-pill proposal-pill">
                    ✓ Propuesta Enviada
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            
            <!-- Información del Cliente -->
            <div class="glass-card">
                <div class="glass-card-header">
                    <h5 class="glass-card-title">
                        <i class="fas fa-user-circle"></i>
                        Información del Cliente
                    </h5>
                </div>
                <div class="glass-card-body">
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-label">
                                <i class="fas fa-user"></i> NOMBRE
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                            </div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">
                                <i class="fas fa-envelope"></i> EMAIL
                            </div>
                            <div class="info-value">
                                <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>">
                                    <?php echo htmlspecialchars($order['email']); ?>
                                </a>
                            </div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">
                                <i class="fas fa-phone"></i> TELÉFONO
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($order['phone'] ?: 'No proporcionado'); ?>
                            </div>
                        </div>
                        <div class="info-box">
                            <div class="info-label">
                                <i class="fas fa-map-marker-alt"></i> DIRECCIÓN
                            </div>
                            <div class="info-value">
                                <?php 
                                $full_address = trim(
                                    implode(', ', array_filter([
                                        $order['address'],
                                        $order['city'],
                                        $order['state'],
                                        $order['zip_code']
                                    ]))
                                );
                                echo htmlspecialchars($full_address ?: 'No proporcionada');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos Solicitados -->
            <div class="glass-card">
                <div class="glass-card-header">
                    <h5 class="glass-card-title">
                        <i class="fas fa-shopping-cart"></i>
                        Productos Solicitados (<?php echo count($items); ?>)
                    </h5>
                </div>
                <div class="glass-card-body">
                    <?php foreach ($items as $item): ?>
                    <div class="product-card">
                        <?php if ($item['image']): ?>
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="" class="product-img">
                        <?php endif; ?>
                        <div class="product-details">
                            <div class="product-name">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </div>
                            <span class="quantity-badge">
                                <?php echo $item['quantity']; ?> unidades
                            </span>
                        </div>
                        <div class="product-price">
                            <?php if ($item['proposed_price']): ?>
                                $<?php echo number_format($item['proposed_price'], 2); ?>
                            <?php else: ?>
                                <span style="color: #dc3545;">Sin Precio</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="total-showcase">
                        <div class="total-label">TOTAL DEL PEDIDO</div>
                        <div class="total-amount">$<?php echo number_format($order['total'], 2); ?></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            
            <!-- Formulario de Propuesta -->
            <?php if (!$order['proposal_sent']): ?>
            <div class="proposal-section">
                <h3 class="proposal-title">💼 Enviar Propuesta</h3>
                <p class="proposal-subtitle">Configura los precios y envía la propuesta al cliente</p>
                
                <form method="post" action="../api/send-proposal.php">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    
                    <?php foreach ($items as $item): ?>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                            (<?php echo $item['quantity']; ?>x)
                        </label>
                        <input type="number" 
                               name="prices[<?php echo $item['id']; ?>]" 
                               class="form-control-modern"
                               placeholder="Precio unitario"
                               step="0.01"
                               value="<?php echo $item['proposed_price'] ?? ''; ?>"
                               required>
                    </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn-send-proposal">
                        🚀 Enviar Propuesta
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Chat -->
            <div class="chat-container mt-4">
                <div class="chat-header">
                    <i class="fas fa-comments"></i>
                    <h5 class="chat-title">Chat del Pedido</h5>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <?php if (empty($messages)): ?>
                    <p style="text-align: center; color: #6c757d; padding: 40px;">
                        No hay mensajes aún. ¡Inicia la conversación!
                    </p>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                        <div class="message-bubble message-<?php echo $msg['sender_type']; ?>">
                            <div class="message-sender">
                                <?php echo htmlspecialchars($msg['sender_name']); ?>
                            </div>
                            <div class="message-text">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                            <div class="message-time">
                                <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="chat-input-area">
                    <form class="chat-form" id="chatForm">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="text" 
                               name="message" 
                               class="chat-input" 
                               placeholder="Escribe un mensaje..."
                               required>
                        <button type="submit" class="btn-send-msg">
                            <i class="fas fa-paper-plane"></i> Enviar
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Auto-scroll chat to bottom
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Submit chat message
document.getElementById('chatForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('../api/order-messages.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Error al enviar mensaje');
        }
    } catch (error) {
        alert('Error de conexión');
    }
});
</script>

<?php require_once 'footer.php'; ?>
