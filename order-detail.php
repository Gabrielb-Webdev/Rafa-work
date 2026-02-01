<?php
require_once __DIR__ . '/config/config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

$order_id = $_GET['id'] ?? 0;
if (!$order_id) {
    redirect('/orders.php');
}

// Cargar pedido y verificar que pertenece al usuario
$stmt = executeQuery(
    "SELECT o.*, u.full_name as user_name, u.email as user_email 
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     WHERE o.id = ? AND o.user_id = ?",
    [$order_id, $_SESSION['user_id']]
);
$order = $stmt->fetch();

if (!$order) {
    die('Pedido no encontrado o no tienes permiso para verlo');
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
        "SELECT om.*, u.full_name as sender_name, u.user_role
         FROM order_messages om 
         JOIN users u ON om.user_id = u.id
         WHERE om.order_id = ? 
         ORDER BY om.created_at ASC",
        [$order_id]
    );
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    // Si la tabla no existe, continuar sin mensajes
}

$pageTitle = "Pedido #" . $order['order_number'];
require_once 'includes/header.php';
?>

<style>
.order-detail-container {
    max-width: 1200px;
    margin: 50px auto;
    padding: 0 20px;
}

.order-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    margin-bottom: 40px;
    color: white;
}

.order-header h1 {
    font-size: 32px;
    color: white;
    margin-bottom: 10px;
    font-weight: 700;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.info-item {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.info-item:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.info-item label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 1px;
    margin-bottom: 8px;
    display: block;
}

.info-item p {
    font-size: 18px;
    color: white;
    font-weight: 700;
    margin: 0;
}

.order-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

@media (max-width: 968px) {
    .order-content {
        grid-template-columns: 1fr;
    }
}

.section-card {
    background: var(--white);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 25px;
    border: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.section-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.section-card h2 {
    font-size: 22px;
    color: var(--text-dark);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 700;
    padding-bottom: 15px;
    border-bottom: 3px solid #00d4d4;
}

.product-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    margin-bottom: 15px;
    align-items: center;
    transition: all 0.3s ease;
    background: #fafafa;
}

.product-item:hover {
    border-color: #00d4d4;
    background: white;
    box-shadow: 0 4px 15px rgba(0, 212, 212, 0.1);
}

.product-item img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.product-info {
    flex: 1;
}

.product-info h3 {
    font-size: 16px;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.product-info p {
    font-size: 14px;
    color: var(--text-light);
    margin: 0;
}

.product-price {
    text-align: right;
}

.product-price .price {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-cyan);
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #d1ecf1;
    color: #0c5460;
}

.status-shipped {
    background: #cce5ff;
    color: #004085;
}

.status-delivered {
    background: #d4edda;
    color: #155724;
}

.proposal-alert {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 35px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3); }
    50% { box-shadow: 0 15px 50px rgba(16, 185, 129, 0.5); }
}

.proposal-alert h3 {
    font-size: 22px;
    margin-bottom: 15px;
    font-weight: 700;
}

.proposal-total {
    font-size: 48px;
    font-weight: 900;
    margin: 20px 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.btn-accept {
    background: white;
    color: #059669;
    padding: 16px 40px;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.btn-accept:hover {
    background: #f0fdf4;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

/* Chat */
.chat-container {
    background: var(--white);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    height: 650px;
    display: flex;
    flex-direction: column;
    border: 1px solid #f0f0f0;
    position: sticky;
    top: 20px;
}

.chat-header {
    padding: 25px;
    border-bottom: 2px solid #00d4d4;
    background: linear-gradient(135deg, #00d4d4 0%, #00bfbf 100%);
    border-radius: 16px 16px 0 0;
}

.chat-header h2 {
    font-size: 20px;
    color: white;
    margin: 0;
    font-weight: 700;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 25px;
    background: #f8f9fa;
}

.message {
    margin-bottom: 20px;
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.mine {
    text-align: right;
}

.message-bubble {
    display: inline-block;
    max-width: 75%;
    padding: 14px 18px;
    border-radius: 16px;
    margin-bottom: 5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    word-wrap: break-word;
}

.message.mine .message-bubble {
    background: linear-gradient(135deg, #00d4d4 0%, #00bfbf 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message.theirs .message-bubble {
    background: white;
    color: var(--text-dark);
    border-bottom-left-radius: 4px;
    border: 1px solid #e9ecef;
}

.message-sender {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-light);
}

.message-time {
    font-size: 11px;
    color: var(--text-light);
}

.chat-input {
    padding: 20px;
    border-top: 1px solid var(--border-light);
    background: white;
}

.chat-input form {
    display: flex;
    gap: 12px;
}

.chat-input input {
    flex: 1;
    padding: 14px 18px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s ease;
}

.chat-input input:focus {
    outline: none;
    border-color: #00d4d4;
    box-shadow: 0 0 0 4px rgba(0, 212, 212, 0.1);
}

.chat-input button {
    padding: 14px 28px;
    background: linear-gradient(135deg, #00d4d4 0%, #00bfbf 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 212, 212, 0.3);
}

.chat-input button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 212, 212, 0.4);
}
</style>

<div class="order-detail-container">
    <!-- Header del pedido -->
    <div class="order-header">
        <h1>Pedido #<?php echo htmlspecialchars($order['order_number']); ?></h1>
        <p style="color: var(--text-light); margin-bottom: 0;">
            Realizado el <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
        </p>
        
        <div class="order-info-grid">
            <div class="info-item">
                <label>Estado</label>
                <p>
                    <?php
                    $status_text = [
                        'pending' => '⏳ Pendiente',
                        'processing' => '🔄 En Proceso',
                        'shipped' => '🚚 Enviado',
                        'delivered' => '✅ Entregado',
                        'cancelled' => '❌ Cancelado'
                    ];
                    echo $status_text[$order['status']] ?? $order['status'];
                    ?>
                </p>
            </div>
            
            <div class="info-item">
                <label>Productos</label>
                <p><?php echo count($items); ?> artículos</p>
            </div>
            
            <div class="info-item">
                <label>Estado de Propuesta</label>
                <p>
                    <?php if ($order['proposal_sent']): ?>
                        ✅ Propuesta recibida
                    <?php else: ?>
                        ⏳ En espera de cotización
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="order-content">
        <div>
            <!-- Propuesta recibida -->
            <?php if ($order['proposal_sent'] && !$order['proposal_accepted']): ?>
            <div class="proposal-alert">
                <h3>🎉 ¡Hemos preparado tu propuesta!</h3>
                <p>Revisa los detalles a continuación y acepta la propuesta si estás de acuerdo.</p>
                <div class="proposal-total">
                    $<?php echo number_format($order['proposal_total'], 2); ?>
                </div>
                <button class="btn-accept" onclick="acceptProposal()">
                    ✓ Aceptar Propuesta
                </button>
            </div>
            <?php elseif ($order['proposal_accepted']): ?>
            <div class="section-card" style="background: #d4edda; border: 2px solid #28a745;">
                <h2 style="color: #155724;">✅ Propuesta Aceptada</h2>
                <p style="color: #155724; margin: 0;">
                    Aceptaste esta propuesta el <?php echo date('d/m/Y H:i', strtotime($order['proposal_accepted_date'])); ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Productos -->
            <div class="section-card">
                <h2>📦 Productos Solicitados</h2>
                <?php foreach ($items as $item): ?>
                <div class="product-item">
                    <?php if ($item['image']): ?>
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <?php endif; ?>
                    
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        <p>Cantidad: <?php echo $item['quantity']; ?></p>
                    </div>
                    
                    <div class="product-price">
                        <?php if ($item['proposed_price']): ?>
                        <div class="price">$<?php echo number_format($item['proposed_subtotal'], 2); ?></div>
                        <small style="color: var(--text-light);">
                            $<?php echo number_format($item['proposed_price'], 2); ?> c/u
                        </small>
                        <?php else: ?>
                        <small style="color: var(--text-light);">Pendiente de cotización</small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if ($order['proposal_total']): ?>
                <div style="text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--border-light);">
                    <span style="font-size: 24px; font-weight: 700; color: var(--primary-cyan);">
                        Total: $<?php echo number_format($order['proposal_total'], 2); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Información de envío -->
            <div class="section-card">
                <h2>📍 Dirección de Envío</h2>
                <p>
                    <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                    <?php echo htmlspecialchars($order['street'] . ' ' . $order['street_number']); ?><br>
                    <?php echo htmlspecialchars($order['neighborhood']); ?><br>
                    <?php echo htmlspecialchars($order['city'] . ', CP ' . $order['postal_code']); ?><br>
                    <br>
                    📧 <?php echo htmlspecialchars($order['email']); ?><br>
                    <?php if ($order['phone']): ?>
                    📱 <?php echo htmlspecialchars($order['phone']); ?>
                    <?php endif; ?>
                </p>
                
                <?php if ($order['notes']): ?>
                <div style="background: var(--bg-light); padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <strong>Notas:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat -->
        <div>
            <div class="chat-container">
                <div class="chat-header">
                    <h2>💬 Chat con el Vendedor</h2>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <?php if (empty($messages)): ?>
                    <p style="text-align: center; color: var(--text-light); margin-top: 50px;">
                        No hay mensajes aún.<br>
                        Escribe algo para iniciar la conversación.
                    </p>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['user_id'] == $_SESSION['user_id'] ? 'mine' : 'theirs'; ?>">
                            <div class="message-sender">
                                <?php echo htmlspecialchars($msg['sender_name']); ?>
                            </div>
                            <div class="message-bubble">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                            <div class="message-time">
                                <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="chat-input">
                    <form id="chatForm">
                        <input type="text" 
                               id="messageInput" 
                               placeholder="Escribe tu mensaje..." 
                               required>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i> Enviar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const orderId = <?php echo $order_id; ?>;
const chatMessages = document.getElementById('chatMessages');

// Función para cargar mensajes
async function loadMessages() {
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/order-messages.php?order_id=' + orderId);
        const data = await response.json();
        
        if (data.success && data.messages) {
            updateChatMessages(data.messages);
        }
    } catch (error) {
        console.error('Error al cargar mensajes:', error);
    }
}

function updateChatMessages(messages) {
    if (!chatMessages) return;
    
    const wasAtBottom = chatMessages.scrollHeight - chatMessages.scrollTop <= chatMessages.clientHeight + 50;
    
    chatMessages.innerHTML = '';
    
    if (messages.length === 0) {
        chatMessages.innerHTML = '<p style="text-align: center; color: #999; padding: 40px;">No hay mensajes aún. ¡Inicia la conversación!</p>';
    } else {
        messages.forEach(msg => {
            const isAdmin = msg.user_role === 'admin';
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message ' + (isAdmin ? 'admin-message' : 'user-message');
            
            messageDiv.innerHTML = `
                <div style="font-weight: 700; font-size: 13px; margin-bottom: 5px; opacity: 0.8;">
                    ${msg.sender_name || 'Usuario'}
                </div>
                <div style="white-space: pre-wrap; line-height: 1.5;">
                    ${msg.message.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
                </div>
                <div style="font-size: 11px; margin-top: 8px; opacity: 0.7;">
                    ${new Date(msg.created_at).toLocaleString('es-MX')}
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
        });
    }
    
    if (wasAtBottom) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Actualizar mensajes cada 3 segundos
setInterval(loadMessages, 3000);

// Auto-scroll inicial
chatMessages.scrollTop = chatMessages.scrollHeight;

// Enviar mensaje de chat
document.getElementById('chatForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/order-messages.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=${orderId}&message=${encodeURIComponent(message)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            // Recargar mensajes inmediatamente
            await loadMessages();
        } else {
            showNotification('error', 'Error', data.message);
        }
    } catch (error) {
        showNotification('error', 'Error de Conexión', 'Por favor intenta de nuevo más tarde.');
    }
    
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
});

// Aceptar propuesta
async function acceptProposal() {
    showConfirmModal();
}
</script>

<!-- Modal de Confirmación Moderno -->
<div id="confirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; backdrop-filter: blur(5px); align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 25px; padding: 40px; max-width: 500px; width: 90%; box-shadow: 0 20px 80px rgba(0,0,0,0.3); animation: slideUp 0.3s ease;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 40px; margin-bottom: 20px;">
                ⚡
            </div>
            <h2 style="font-size: 28px; color: #2c3e50; margin-bottom: 10px; font-weight: 800;">¿Aceptar Propuesta?</h2>
            <p style="color: #6c757d; font-size: 16px; line-height: 1.6;">
                Estás a punto de aceptar esta propuesta. Una vez confirmado, procesaremos tu pedido.
            </p>
        </div>
        <div style="display: flex; gap: 15px;">
            <button onclick="closeConfirmModal()" style="flex: 1; padding: 15px; background: #f0f0f0; border: none; border-radius: 50px; font-weight: 700; cursor: pointer; font-size: 16px; transition: all 0.3s;">
                Cancelar
            </button>
            <button onclick="confirmAccept()" style="flex: 1; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 50px; font-weight: 800; cursor: pointer; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: all 0.3s;">
                ✓ Confirmar
            </button>
        </div>
    </div>
</div>

<!-- Modal de Notificación -->
<div id="notificationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; backdrop-filter: blur(5px); align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 25px; padding: 40px; max-width: 450px; width: 90%; box-shadow: 0 20px 80px rgba(0,0,0,0.3); text-align: center;">
        <div id="notifIcon" style="width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 40px; margin-bottom: 20px;"></div>
        <h2 id="notifTitle" style="font-size: 24px; margin-bottom: 10px; font-weight: 800;"></h2>
        <p id="notifMessage" style="color: #6c757d; font-size: 16px; line-height: 1.6; margin-bottom: 30px;"></p>
        <button onclick="closeNotificationModal()" style="padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 50px; font-weight: 800; cursor: pointer; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
            Entendido
        </button>
    </div>
</div>

<style>
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>

<script>
function showConfirmModal() {
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function showNotification(type, title, message, reload = false) {
    const modal = document.getElementById('notificationModal');
    const icon = document.getElementById('notifIcon');
    const titleEl = document.getElementById('notifTitle');
    const messageEl = document.getElementById('notifMessage');
    
    if (type === 'success') {
        icon.style.background = 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)';
        icon.textContent = '✓';
        titleEl.style.color = '#11998e';
    } else {
        icon.style.background = 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
        icon.textContent = '✕';
        titleEl.style.color = '#f5576c';
    }
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    modal.style.display = 'flex';
    
    if (reload) {
        setTimeout(() => location.reload(), 2000);
    }
}

function closeNotificationModal() {
    document.getElementById('notificationModal').style.display = 'none';
}

async function confirmAccept() {
    closeConfirmModal();
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/accept-proposal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=${orderId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', '¡Propuesta Aceptada!', 'Tu pedido está siendo procesado. Nos pondremos en contacto pronto.', true);
        } else {
            showNotification('error', 'Error', data.message);
        }
    } catch (error) {
        showNotification('error', 'Error de Conexión', 'Por favor intenta de nuevo más tarde.');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
