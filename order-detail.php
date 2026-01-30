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
    background: var(--white);
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 30px;
}

.order-header h1 {
    font-size: 28px;
    color: var(--text-dark);
    margin-bottom: 10px;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.info-item {
    background: var(--bg-light);
    padding: 15px;
    border-radius: 8px;
}

.info-item label {
    font-size: 12px;
    color: var(--text-light);
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

.info-item p {
    font-size: 16px;
    color: var(--text-dark);
    font-weight: 600;
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
    padding: 25px;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 20px;
}

.section-card h2 {
    font-size: 20px;
    color: var(--text-dark);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.product-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    margin-bottom: 15px;
    align-items: center;
}

.product-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.proposal-alert h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.proposal-total {
    font-size: 32px;
    font-weight: 700;
    margin: 10px 0;
}

.btn-accept {
    background: #28a745;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-accept:hover {
    background: #218838;
}

/* Chat */
.chat-container {
    background: var(--white);
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    height: 600px;
    display: flex;
    flex-direction: column;
}

.chat-header {
    padding: 20px;
    border-bottom: 1px solid var(--border-light);
}

.chat-header h2 {
    font-size: 18px;
    color: var(--text-dark);
    margin: 0;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.message {
    margin-bottom: 20px;
}

.message.mine {
    text-align: right;
}

.message-bubble {
    display: inline-block;
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 5px;
}

.message.mine .message-bubble {
    background: var(--primary-cyan);
    color: white;
    border-bottom-right-radius: 0;
}

.message.theirs .message-bubble {
    background: var(--bg-light);
    color: var(--text-dark);
    border-bottom-left-radius: 0;
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
}

.chat-input form {
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex: 1;
    padding: 12px;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    font-size: 14px;
}

.chat-input button {
    padding: 12px 24px;
    background: var(--primary-cyan);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.chat-input button:hover {
    background: #00bfbf;
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
            body: `action=send&order_id=${orderId}&message=${encodeURIComponent(message)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error de conexión. Por favor intenta de nuevo.');
    }
    
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
});

// Aceptar propuesta
async function acceptProposal() {
    if (!confirm('¿Estás seguro de que deseas aceptar esta propuesta?')) {
        return;
    }
    
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
            alert('✅ ¡Propuesta aceptada! Nos pondremos en contacto contigo pronto.');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    } catch (error) {
        alert('Error de conexión. Por favor intenta de nuevo.');
    }
}

// Auto-scroll del chat
const chatMessages = document.getElementById('chatMessages');
chatMessages.scrollTop = chatMessages.scrollHeight;
</script>

<?php require_once 'includes/footer.php'; ?>
