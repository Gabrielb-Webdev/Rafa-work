<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Debes ser administrador');
}

$order_id = $_GET['id'] ?? 0;
if (!$order_id) {
    die('ID de pedido requerido');
}

$stmt = executeQuery(
    "SELECT o.*, u.full_name as user_name, u.email as user_email 
     FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?",
    [$order_id]
);
$order = $stmt->fetch();

if (!$order) {
    die('Pedido no encontrado');
}

$stmt = executeQuery(
    "SELECT oi.*, p.image, p.price as current_price
     FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = ?",
    [$order_id]
);
$items = $stmt->fetchAll();

$stmt = executeQuery(
    "SELECT om.*, u.full_name as sender_name, u.user_role
     FROM order_messages om JOIN users u ON om.user_id = u.id
     WHERE om.order_id = ? ORDER BY om.created_at ASC",
    [$order_id]
);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Pedido #<?php echo $order['order_number']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .back { display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        h1 { margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        @media(max-width: 1000px) { .grid { grid-template-columns: 1fr; } }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .item { padding: 10px; background: #f8f9fa; border-radius: 5px; margin-bottom: 10px; }
        .chat { background: white; border-radius: 8px; padding: 20px; height: 600px; display: flex; flex-direction: column; }
        .messages { flex: 1; overflow-y: auto; margin-bottom: 15px; }
        .msg { padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .msg.admin { background: #e9ecef; }
        .msg.user { background: #00d4d4; color: white; }
        .inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 10px 0; }
        input, textarea { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .form-box { background: #fff3cd; padding: 20px; border-radius: 8px; border: 2px solid #ffc107; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="pedidos.php" class="back"><i class="fas fa-arrow-left"></i> Volver</a>
        
        <h1>Pedido #<?php echo $order['order_number']; ?></h1>
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($order['user_name']); ?> | <?php echo htmlspecialchars($order['user_email']); ?></p>
        
        <div class="grid">
            <div>
                <?php if (!$order['proposal_sent']): ?>
                <div class="form-box">
                    <h3>💰 Crear Propuesta</h3>
                    <form id="propForm">
                        <?php foreach ($items as $item): ?>
                        <div class="item">
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong> (x<?php echo $item['quantity']; ?>)
                            <div class="inputs">
                                <div>
                                    <label>Precio:</label>
                                    <input type="number" name="price_<?php echo $item['id']; ?>" 
                                           class="precio" data-id="<?php echo $item['id']; ?>" 
                                           data-qty="<?php echo $item['quantity']; ?>" 
                                           step="0.01" value="<?php echo $item['current_price'] ?? ''; ?>" required>
                                </div>
                                <div>
                                    <label>Subtotal:</label>
                                    <input type="number" class="sub" data-id="<?php echo $item['id']; ?>" 
                                           step="0.01" readonly style="background: #f8f9fa;">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="inputs">
                            <div><label>Envío:</label><input type="number" id="envio" step="0.01" value="0"></div>
                            <div><label>Descuento:</label><input type="number" id="desc" step="0.01" value="0"></div>
                        </div>
                        
                        <p style="font-size: 20px; margin: 10px 0;"><strong>Total: $<span id="total">0.00</span></strong></p>
                        
                        <label>Mensaje:</label>
                        <textarea name="proposal_message" rows="3"></textarea>
                        
                        <button type="submit" style="width: 100%; margin-top: 10px;">📤 Enviar Propuesta</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="card" style="background: #d4edda;">
                    <strong>✅ Propuesta enviada</strong>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <h3>📦 Productos</h3>
                    <?php foreach ($items as $item): ?>
                    <div class="item">
                        <?php echo htmlspecialchars($item['product_name']); ?> x<?php echo $item['quantity']; ?>
                        <?php if ($item['proposed_price']): ?>
                        - $<?php echo number_format($item['proposed_price'], 2); ?> = <strong>$<?php echo number_format($item['proposed_subtotal'], 2); ?></strong>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($order['proposal_total']): ?>
                    <p style="font-size: 20px; margin-top: 10px; color: #28a745;">
                        <strong>Total: $<?php echo number_format($order['proposal_total'], 2); ?></strong>
                    </p>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <h3>👤 Cliente</h3>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($order['street'] . ' ' . $order['street_number'] . ', ' . $order['city'] . ', CP ' . $order['postal_code']); ?></p>
                </div>
            </div>
            
            <div class="chat">
                <h3>💬 Chat</h3>
                <div class="messages" id="msgs">
                    <?php if (empty($messages)): ?>
                    <p style="text-align: center; color: #999;">No hay mensajes</p>
                    <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                    <div class="msg <?php echo $msg['user_role'] === 'admin' ? 'admin' : 'user'; ?>">
                        <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                        <small>(<?php echo date('d/m H:i', strtotime($msg['created_at'])); ?>)</small>
                        <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <form id="chatForm" style="display: flex; gap: 10px;">
                    <input type="text" id="msgInput" placeholder="Escribe..." required style="flex: 1;">
                    <button type="submit">Enviar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    const oid = <?php echo $order_id; ?>;
    const items = <?php echo json_encode($items); ?>;
    
    document.querySelectorAll('.precio').forEach(inp => {
        inp.oninput = function() {
            const qty = parseFloat(this.dataset.qty);
            const price = parseFloat(this.value) || 0;
            const sub = (price * qty).toFixed(2);
            document.querySelector('.sub[data-id="' + this.dataset.id + '"]').value = sub;
            calcTotal();
        };
        inp.oninput();
    });
    
    document.getElementById('envio').oninput = calcTotal;
    document.getElementById('desc').oninput = calcTotal;
    
    function calcTotal() {
        let sum = 0;
        document.querySelectorAll('.sub').forEach(s => sum += parseFloat(s.value) || 0);
        const env = parseFloat(document.getElementById('envio').value) || 0;
        const des = parseFloat(document.getElementById('desc').value) || 0;
        document.getElementById('total').textContent = (sum + env - des).toFixed(2);
    }
    
    document.getElementById('propForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button');
        btn.disabled = true;
        btn.textContent = 'Enviando...';
        
        const fd = new FormData(e.target);
        fd.append('action', 'send_proposal');
        fd.append('order_id', oid);
        
        items.forEach(item => {
            const price = document.querySelector('[name="price_' + item.id + '"]').value;
            const sub = document.querySelector('.sub[data-id="' + item.id + '"]').value;
            fd.append('items[' + item.id + '][price]', price);
            fd.append('items[' + item.id + '][subtotal]', sub);
        });
        
        try {
            const r = await fetch('<?php echo BASE_URL; ?>/api/send-proposal.php', { method: 'POST', body: fd });
            const d = await r.json();
            alert(d.success ? '✅ Propuesta enviada' : '❌ ' + d.message);
            if (d.success) location.reload();
        } catch(err) {
            alert('❌ Error de conexión');
        }
        btn.disabled = false;
        btn.textContent = '📤 Enviar Propuesta';
    });
    
    document.getElementById('chatForm').onsubmit = async (e) => {
        e.preventDefault();
        const msg = document.getElementById('msgInput').value.trim();
        if (!msg) return;
        
        const btn = e.target.querySelector('button');
        btn.disabled = true;
        
        try {
            const r = await fetch('<?php echo BASE_URL; ?>/api/order-messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=send&order_id=' + oid + '&message=' + encodeURIComponent(msg)
            });
            const d = await r.json();
            if (d.success) location.reload();
            else alert('Error: ' + d.message);
        } catch(err) {
            alert('Error de conexión');
        }
        btn.disabled = false;
    };
    
    document.getElementById('msgs').scrollTop = 999999;
    </script>
</body>
</html>
