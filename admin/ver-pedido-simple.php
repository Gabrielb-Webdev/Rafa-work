<?php
require_once __DIR__ . '/../config/config.php';

// Verificar admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('/admin/pedidos.php');
}

$order_id = $_GET['id'] ?? 0;
if (!$order_id) {
    redirect('/admin/pedidos.php');
}

// Obtener pedido
$stmt = executeQuery(
    "SELECT o.*, u.full_name as user_name, u.email as user_email 
     FROM orders o JOIN users u ON o.user_id = u.id 
     WHERE o.id = ?",
    [$order_id]
);
$order = $stmt->fetch();

if (!$order) {
    redirect('/admin/pedidos.php');
}

// Obtener items
$stmt = executeQuery(
    "SELECT oi.*, p.image, p.price as current_price
     FROM order_items oi 
     LEFT JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = ?",
    [$order_id]
);
$items = $stmt->fetchAll();

// Obtener mensajes
$stmt = executeQuery(
    "SELECT om.*, u.full_name as sender_name, u.user_role
     FROM order_messages om
     JOIN users u ON om.user_id = u.id
     WHERE om.order_id = ?
     ORDER BY om.created_at ASC",
    [$order_id]
);
$messages = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>

<style>
.container { max-width: 1400px; margin: 40px auto; padding: 20px; }
.back-btn { display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
.grid { display: grid; grid-template-columns: 1fr 400px; gap: 30px; }
@media(max-width: 1000px) { .grid { grid-template-columns: 1fr; } }
.card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
.product-item { padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; }
.chat-box { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 600px; display: flex; flex-direction: column; }
.chat-messages { flex: 1; overflow-y: auto; padding: 20px; }
.message { margin-bottom: 15px; padding: 10px 15px; border-radius: 8px; max-width: 80%; }
.message.admin { background: #e9ecef; margin-right: auto; }
.message.user { background: #00d4d4; color: white; margin-left: auto; }
.chat-input { padding: 15px; border-top: 1px solid #dee2e6; }
.chat-input form { display: flex; gap: 10px; }
.chat-input input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
.chat-input button { padding: 10px 20px; background: #00d4d4; color: white; border: none; border-radius: 5px; cursor: pointer; }
.proposal-form { background: #fff3cd; padding: 20px; border-radius: 10px; border: 2px solid #ffc107; margin-bottom: 20px; }
.product-proposal { background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
.price-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
.price-inputs input { padding: 8px; border: 1px solid #ddd; border-radius: 5px; width: 100%; }
.btn-send-proposal { width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 15px; }
</style>

<div class="container">
    <a href="pedidos.php" class="back-btn">← Volver a Pedidos</a>
    
    <h1>Pedido #<?php echo $order['order_number']; ?></h1>
    <p>Cliente: <?php echo htmlspecialchars($order['user_name']); ?> | Email: <?php echo htmlspecialchars($order['user_email']); ?></p>
    
    <div class="grid">
        <div>
            <?php if (!$order['proposal_sent']): ?>
                <div class="proposal-form">
                    <h3>💰 Crear Propuesta de Cotización</h3>
                    <form id="proposalForm">
                        <?php foreach ($items as $item): ?>
                            <div class="product-proposal">
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong> (Cantidad: <?php echo $item['quantity']; ?>)
                                <div class="price-inputs">
                                    <div>
                                        <label>Precio Unitario:</label>
                                        <input type="number" name="price_<?php echo $item['id']; ?>" class="item-price" 
                                               data-item-id="<?php echo $item['id']; ?>" data-quantity="<?php echo $item['quantity']; ?>"
                                               step="0.01" value="<?php echo $item['current_price'] ?? ''; ?>" required>
                                    </div>
                                    <div>
                                        <label>Subtotal:</label>
                                        <input type="number" class="item-subtotal" data-item-id="<?php echo $item['id']; ?>" 
                                               step="0.01" readonly style="background: #f8f9fa;">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="price-inputs">
                            <div>
                                <label>Envío:</label>
                                <input type="number" id="shipping" step="0.01" value="0">
                            </div>
                            <div>
                                <label>Descuento:</label>
                                <input type="number" id="discount" step="0.01" value="0">
                            </div>
                        </div>
                        
                        <p style="font-size: 20px; font-weight: bold; margin-top: 15px;">
                            Total: <span id="proposalTotal">$0.00</span>
                        </p>
                        
                        <label>Mensaje para el cliente:</label>
                        <textarea name="proposal_message" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ffc107; border-radius: 5px; margin-top: 5px;"></textarea>
                        
                        <button type="submit" class="btn-send-proposal">📤 Enviar Propuesta</button>
                    </form>
                </div>
            <?php else: ?>
                <div style="padding: 20px; background: #d4edda; border-radius: 10px; margin-bottom: 20px;">
                    <strong>✅ Propuesta enviada el <?php echo date('d/m/Y H:i', strtotime($order['proposal_date'])); ?></strong>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h3>📦 Productos</h3>
                <?php foreach ($items as $item): ?>
                    <div class="product-item">
                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                        Cantidad: <?php echo $item['quantity']; ?>
                        <?php if ($item['proposed_price'] && $item['proposed_price'] > 0): ?>
                            | Precio: $<?php echo number_format($item['proposed_price'], 2); ?>
                            = <strong>$<?php echo number_format($item['proposed_subtotal'], 2); ?></strong>
                        <?php else: ?>
                            | <em style="color: #ffc107;">Pendiente cotización</em>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($order['proposal_total'] && $order['proposal_total'] > 0): ?>
                    <p style="font-size: 24px; font-weight: bold; color: #28a745; margin-top: 20px;">
                        Total Propuesta: $<?php echo number_format($order['proposal_total'], 2); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h3>👤 Información del Cliente</h3>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($order['street'] . ' ' . $order['street_number']); ?>, 
                   <?php echo htmlspecialchars($order['city']); ?>, CP <?php echo htmlspecialchars($order['postal_code']); ?></p>
            </div>
        </div>
        
        <div>
            <div class="chat-box">
                <div style="padding: 15px; border-bottom: 1px solid #dee2e6;">
                    <h3>💬 Chat del Pedido</h3>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <?php if (empty($messages)): ?>
                        <p style="text-align: center; color: #6c757d; padding: 40px;">No hay mensajes</p>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['user_role'] === 'admin' ? 'admin' : 'user'; ?>">
                                <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                <small style="color: #6c757d;">(<?php echo date('d/m H:i', strtotime($msg['created_at'])); ?>)</small>
                                <p style="margin: 5px 0 0 0;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="chat-input">
                    <form id="chatForm">
                        <input type="text" id="messageInput" placeholder="Escribe un mensaje..." required>
                        <button type="submit">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const orderId = <?php echo $order_id; ?>;
const itemsData = <?php echo json_encode($items); ?>;

// Calcular subtotales
document.querySelectorAll('.item-price').forEach(input => {
    input.addEventListener('input', function() {
        const quantity = parseFloat(this.dataset.quantity);
        const price = parseFloat(this.value) || 0;
        const subtotal = (price * quantity).toFixed(2);
        document.querySelector('.item-subtotal[data-item-id="' + this.dataset.itemId + '"]').value = subtotal;
        calculateTotal();
    });
    input.dispatchEvent(new Event('input'));
});

document.getElementById('shipping')?.addEventListener('input', calculateTotal);
document.getElementById('discount')?.addEventListener('input', calculateTotal);

function calculateTotal() {
    let subtotal = 0;
    document.querySelectorAll('.item-subtotal').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    const shipping = parseFloat(document.getElementById('shipping')?.value || 0);
    const discount = parseFloat(document.getElementById('discount')?.value || 0);
    const total = subtotal + shipping - discount;
    document.getElementById('proposalTotal').textContent = '$' + total.toFixed(2);
}

// Enviar propuesta
document.getElementById('proposalForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    
    const formData = new FormData(e.target);
    formData.append('action', 'send_proposal');
    formData.append('order_id', orderId);
    
    itemsData.forEach(item => {
        const price = document.querySelector('[name="price_' + item.id + '"]').value;
        const subtotal = document.querySelector('.item-subtotal[data-item-id="' + item.id + '"]').value;
        formData.append('items[' + item.id + '][price]', price);
        formData.append('items[' + item.id + '][subtotal]', subtotal);
    });
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/send-proposal.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            alert('✅ Propuesta enviada exitosamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    } catch (error) {
        alert('❌ Error de conexión');
    } finally {
        btn.disabled = false;
        btn.textContent = '📤 Enviar Propuesta';
    }
});

// Chat
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = messageInput.value.trim();
    if (!message) return;
    
    const btn = chatForm.querySelector('button');
    btn.disabled = true;
    btn.textContent = '...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/order-messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=send&order_id=' + orderId + '&message=' + encodeURIComponent(message)
        });
        const data = await response.json();
        if (data.success) {
            messageInput.value = '';
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error de conexión');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Enviar';
    }
});

// Scroll al final del chat
document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
</script>

<?php include __DIR__ . '/footer.php'; ?>
