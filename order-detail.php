<?php
require_once __DIR__ . '/config/config.php';

// Verificar si está logueado
if (!isLoggedIn()) {
    redirect('/login.php');
}

$pageTitle = 'Detalle del Pedido - Forethink Health';
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener información del pedido
try {
    $stmt = executeQuery(
        "SELECT o.*, u.email, u.full_name as user_full_name
         FROM orders o 
         JOIN users u ON o.user_id = u.id 
         WHERE o.id = ? AND o.user_id = ?",
        [$order_id, $_SESSION['user_id']]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect('/orders.php');
    }
    
    // Obtener items del pedido
    $stmt = executeQuery(
        "SELECT oi.*, p.image, p.description
         FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?",
        [$order_id]
    );
    $orderItems = $stmt->fetchAll();
    
    // Obtener mensajes del chat
    $stmt = executeQuery(
        "SELECT om.*, u.full_name as sender_name, u.user_role
         FROM order_messages om
         JOIN users u ON om.user_id = u.id
         WHERE om.order_id = ?
         ORDER BY om.created_at ASC",
        [$order_id]
    );
    $messages = $stmt->fetchAll();
    
} catch (Exception $e) {
    redirect('/orders.php');
}

function getStatusText($status) {
    $statuses = [
        'pending' => 'Pendiente de Cotización',
        'processing' => 'En Proceso',
        'shipped' => 'Enviado',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado'
    ];
    return $statuses[$status] ?? 'Desconocido';
}

function getStatusColor($status) {
    $colors = [
        'pending' => '#ff9800',
        'processing' => '#ffc107',
        'shipped' => '#17a2b8',
        'delivered' => '#28a745',
        'cancelled' => '#dc3545'
    ];
    return $colors[$status] ?? '#666';
}

include __DIR__ . '/includes/header.php';
?>

<style>
.order-detail-page {
    background: #f8f9fa;
    min-height: calc(100vh - 200px);
    padding: 60px 0;
}

.detail-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    text-decoration: none;
    margin-bottom: 20px;
    font-weight: 600;
    transition: color 0.3s;
}

.back-link:hover {
    color: #00d4d4;
}

.detail-header {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.detail-header h1 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 15px;
}

.order-meta {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #6c757d;
}

.meta-item i {
    color: #00d4d4;
    font-size: 18px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 30px;
    margin-bottom: 30px;
}

@media (max-width: 1024px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}

.detail-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #00d4d4;
}

.info-row {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 15px;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
}

.info-value {
    color: #2c3e50;
}

.product-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 15px;
}

.product-image {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image i {
    font-size: 30px;
    color: #00d4d4;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.product-qty {
    color: #6c757d;
    font-size: 14px;
}

.product-price {
    text-align: right;
    font-weight: 700;
    color: #00d4d4;
}

.price-pending {
    color: #ffc107;
    font-style: italic;
}

.proposal-alert {
    padding: 20px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 8px;
    margin-bottom: 20px;
}

.proposal-alert.success {
    background: #d4edda;
    border-left-color: #28a745;
}

.proposal-alert strong {
    color: #856404;
}

.proposal-alert.success strong {
    color: #155724;
}

/* Chat */
.chat-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    height: 700px;
}

.chat-header {
    padding: 20px 30px;
    border-bottom: 2px solid #f0f0f0;
}

.chat-header h3 {
    font-size: 20px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 30px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.message {
    display: flex;
    gap: 12px;
    max-width: 85%;
}

.message.user {
    margin-left: auto;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.message.admin .message-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.message.user .message-avatar {
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
}

.message-content {
    flex: 1;
}

.message-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
}

.message-sender {
    font-weight: 700;
    font-size: 14px;
    color: #2c3e50;
}

.message-time {
    font-size: 12px;
    color: #6c757d;
}

.message-bubble {
    padding: 12px 16px;
    border-radius: 12px;
    line-height: 1.5;
}

.message.admin .message-bubble {
    background: #f0f0f0;
    color: #2c3e50;
    border-bottom-left-radius: 4px;
}

.message.user .message-bubble {
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.proposal-message {
    background: #fff3cd !important;
    border: 2px solid #ffc107;
    color: #856404 !important;
    padding: 20px;
}

.proposal-message .proposal-title {
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.proposal-items {
    margin: 15px 0;
}

.proposal-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(133, 100, 4, 0.2);
}

.proposal-total {
    font-size: 18px;
    font-weight: 700;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid rgba(133, 100, 4, 0.3);
    display: flex;
    justify-content: space-between;
}

.chat-input-area {
    padding: 20px 30px;
    border-top: 2px solid #f0f0f0;
    background: #fafafa;
    border-radius: 0 0 15px 15px;
}

.chat-input-form {
    display: flex;
    gap: 12px;
}

.chat-input {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #eee;
    border-radius: 25px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.chat-input:focus {
    outline: none;
    border-color: #00d4d4;
}

.chat-send-btn {
    padding: 12px 30px;
    background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%);
    color: white;
    border: none;
    border-radius: 25px;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.2s;
}

.chat-send-btn:hover {
    transform: translateY(-2px);
}

.chat-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty-chat {
    text-align: center;
    color: #6c757d;
    padding: 40px;
}

.empty-chat i {
    font-size: 60px;
    color: #dee2e6;
    margin-bottom: 15px;
}
</style>

<section class="order-detail-page">
    <div class="detail-container">
        <a href="<?php echo BASE_URL; ?>/orders.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver a Mis Pedidos
        </a>
        
        <div class="detail-header">
            <h1>Pedido #<?php echo $order['order_number']; ?></h1>
            <div class="order-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-box"></i>
                    <span><?php echo count($orderItems); ?> producto(s)</span>
                </div>
                <div class="meta-item">
                    <span class="status-badge" style="background: <?php echo getStatusColor($order['status']); ?>; color: white;">
                        <?php echo getStatusText($order['status']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="detail-grid">
            <div>
                <?php if (!$order['proposal_sent']): ?>
                    <div class="proposal-alert">
                        <strong><i class="fas fa-clock"></i> Pendiente de Cotización</strong>
                        <p style="margin: 10px 0 0 0; color: #856404;">
                            Tu pedido ha sido recibido. Pronto recibirás una propuesta personalizada con los precios y disponibilidad de cada producto.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="proposal-alert success">
                        <strong><i class="fas fa-check-circle"></i> ¡Propuesta Recibida!</strong>
                        <p style="margin: 10px 0 0 0; color: #155724;">
                            Revisa la propuesta en el chat y los detalles de cada producto abajo.
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="detail-section">
                    <h2 class="section-title">
                        <i class="fas fa-box"></i> Productos Solicitados
                    </h2>
                    
                    <?php foreach ($orderItems as $item): ?>
                        <div class="product-item">
                            <div class="product-image">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-pills"></i>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="product-qty">Cantidad solicitada: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="product-price">
                                <?php if ($item['proposed_price'] !== null): ?>
                                    $<?php echo number_format($item['proposed_price'], 2); ?> c/u<br>
                                    <small style="color: #6c757d;">Total: $<?php echo number_format($item['proposed_subtotal'], 2); ?></small>
                                <?php else: ?>
                                    <span class="price-pending">Pendiente</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if ($order['proposal_total']): ?>
                        <div style="margin-top: 20px; padding: 20px; background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%); border-radius: 12px; color: white;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 18px; font-weight: 600;">Total de la Propuesta:</span>
                                <span style="font-size: 32px; font-weight: 700;">$<?php echo number_format($order['proposal_total'], 2); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="detail-section" style="margin-top: 30px;">
                    <h2 class="section-title">
                        <i class="fas fa-user"></i> Información de Contacto
                    </h2>
                    <div class="info-row">
                        <div class="info-label">Nombre:</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['email']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Teléfono:</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['phone']); ?></div>
                    </div>
                </div>
                
                <div class="detail-section" style="margin-top: 30px;">
                    <h2 class="section-title">
                        <i class="fas fa-map-marker-alt"></i> Dirección de Envío
                    </h2>
                    <div class="info-row">
                        <div class="info-label">Calle:</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['street']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Número:</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['street_number']); ?></div>
                    </div>
                    <?php if ($order['neighborhood']): ?>
                        <div class="info-row">
                            <div class="info-label">Colonia:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['neighborhood']); ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Ciudad:</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['city']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Código Postal:</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['postal_code']); ?></div>
                    </div>
                    <?php if ($order['notes']): ?>
                        <div class="info-row">
                            <div class="info-label">Notas:</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <div class="chat-container">
                    <div class="chat-header">
                        <h3>
                            <i class="fas fa-comments"></i>
                            Chat del Pedido
                        </h3>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <?php if (empty($messages)): ?>
                            <div class="empty-chat">
                                <i class="fas fa-comments"></i>
                                <p>Aún no hay mensajes</p>
                                <small>Escribe un mensaje para comunicarte con el administrador</small>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?php echo $msg['user_role'] === 'admin' ? 'admin' : 'user'; ?>">
                                    <div class="message-avatar">
                                        <?php echo strtoupper(substr($msg['sender_name'], 0, 1)); ?>
                                    </div>
                                    <div class="message-content">
                                        <div class="message-header">
                                            <span class="message-sender"><?php echo htmlspecialchars($msg['sender_name']); ?></span>
                                            <span class="message-time"><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></span>
                                        </div>
                                        <?php if ($msg['is_proposal']): ?>
                                            <div class="message-bubble proposal-message">
                                                <div class="proposal-title">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                    Propuesta de Cotización
                                                </div>
                                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="message-bubble">
                                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="chat-input-area">
                        <form class="chat-input-form" id="chatForm">
                            <input type="text" 
                                   class="chat-input" 
                                   id="messageInput" 
                                   placeholder="Escribe tu mensaje..."
                                   required>
                            <button type="submit" class="chat-send-btn" id="sendBtn">
                                <i class="fas fa-paper-plane"></i> Enviar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const orderId = <?php echo $order_id; ?>;
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');

// Auto-scroll al final del chat
function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

scrollToBottom();

// Enviar mensaje
chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const message = messageInput.value.trim();
    if (!message) return;
    
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/order-messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=send&order_id=${orderId}&message=${encodeURIComponent(message)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            location.reload(); // Recargar para mostrar el nuevo mensaje
        } else {
            alert('Error al enviar el mensaje: ' + data.message);
        }
    } catch (error) {
        alert('Error de conexión al enviar el mensaje');
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
    }
});

// Recargar mensajes cada 30 segundos
setInterval(() => {
    location.reload();
}, 30000);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
