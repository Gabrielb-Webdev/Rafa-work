<?php
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
    "SELECT o.*, u.full_name as user_name, u.email as user_email 
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
require_once 'header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo htmlspecialchars($order['order_number']); ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="pedidos.php">Pedidos</a></li>
        <li class="breadcrumb-item active">Detalle</li>
    </ol>

    <div class="row">
        <!-- Columna izquierda: Información del pedido -->
        <div class="col-lg-8">
            
            <!-- Datos del cliente -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i> Información del Cliente
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($order['phone'] ?: 'No proporcionado'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Calle:</strong> <?php echo htmlspecialchars($order['street'] ?: 'No proporcionado'); ?></p>
                            <p><strong>Número:</strong> <?php echo htmlspecialchars($order['street_number'] ?: 'No proporcionado'); ?></p>
                            <p><strong>Colonia:</strong> <?php echo htmlspecialchars($order['neighborhood'] ?: 'No proporcionado'); ?></p>
                            <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($order['city'] ?: 'No proporcionado'); ?></p>
                            <p><strong>CP:</strong> <?php echo htmlspecialchars($order['postal_code'] ?: 'No proporcionado'); ?></p>
                        </div>
                    </div>
                    <?php if ($order['notes']): ?>
                    <div class="alert alert-info mt-3">
                        <strong>Notas:</strong> <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Productos del pedido -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-box me-1"></i> Productos Solicitados
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Propuesto</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if ($item['image']): ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>
                                        <?php if ($item['proposed_price']): ?>
                                            $<?php echo number_format($item['proposed_price'], 2); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin definir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['proposed_subtotal']): ?>
                                            $<?php echo number_format($item['proposed_subtotal'], 2); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if ($order['proposal_total']): ?>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>$<?php echo number_format($order['proposal_total'], 2); ?></th>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Formulario de propuesta -->
            <?php if (!$order['proposal_sent']): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Crear Propuesta Comercial
                </div>
                <div class="card-body">
                    <form id="proposalForm">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr data-item-id="<?php echo $item['id']; ?>">
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>
                                            <input type="number" 
                                                   name="price_<?php echo $item['id']; ?>" 
                                                   class="form-control item-price" 
                                                   step="0.01" 
                                                   min="0" 
                                                   value="<?php echo $item['current_price'] ?? 0; ?>"
                                                   data-quantity="<?php echo $item['quantity']; ?>"
                                                   required>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control item-subtotal" 
                                                   readonly 
                                                   value="0">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td><strong>$<span id="subtotalDisplay">0.00</span></strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">Envío:</td>
                                        <td>
                                            <input type="number" id="shippingCost" class="form-control" step="0.01" value="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">Descuento:</td>
                                        <td>
                                            <input type="number" id="discountAmount" class="form-control" step="0.01" value="0">
                                        </td>
                                    </tr>
                                    <tr class="table-success">
                                        <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                        <td><strong>$<span id="totalDisplay">0.00</span></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mensaje para el cliente:</label>
                            <textarea name="proposal_message" class="form-control" rows="3" 
                                      placeholder="Escribe un mensaje personalizado para el cliente..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Propuesta al Cliente
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Propuesta enviada el <?php echo date('d/m/Y H:i', strtotime($order['proposal_date'])); ?></strong>
                <?php if ($order['proposal_accepted']): ?>
                <br>✅ Aceptada el <?php echo date('d/m/Y H:i', strtotime($order['proposal_accepted_date'])); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Columna derecha: Chat -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-comments me-1"></i> Chat con el Cliente
                </div>
                <div class="card-body p-0" style="height: 600px; display: flex; flex-direction: column;">
                    <div id="messagesContainer" style="flex: 1; overflow-y: auto; padding: 15px;">
                        <?php if (empty($messages)): ?>
                        <p class="text-muted text-center">No hay mensajes aún</p>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                            <div class="mb-3 <?php echo $msg['user_role'] === 'admin' ? 'text-end' : ''; ?>">
                                <div class="d-inline-block px-3 py-2 rounded <?php echo $msg['user_role'] === 'admin' ? 'bg-primary text-white' : 'bg-light'; ?>" 
                                     style="max-width: 80%;">
                                    <small class="d-block fw-bold"><?php echo htmlspecialchars($msg['sender_name']); ?></small>
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    <small class="d-block text-muted mt-1" style="font-size: 0.75rem;">
                                        <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 border-top">
                        <form id="chatForm">
                            <div class="input-group">
                                <input type="text" id="messageInput" class="form-control" 
                                       placeholder="Escribe un mensaje..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const orderId = <?php echo $order_id; ?>;
const items = <?php echo json_encode($items); ?>;

// Calcular totales
function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-price').forEach(input => {
        const price = parseFloat(input.value) || 0;
        const quantity = parseInt(input.dataset.quantity) || 0;
        const itemSubtotal = price * quantity;
        
        const subtotalInput = input.closest('tr').querySelector('.item-subtotal');
        subtotalInput.value = itemSubtotal.toFixed(2);
        
        subtotal += itemSubtotal;
    });
    
    const shipping = parseFloat(document.getElementById('shippingCost').value) || 0;
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const total = subtotal + shipping - discount;
    
    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
    document.getElementById('totalDisplay').textContent = total.toFixed(2);
}

// Event listeners para cálculos
document.querySelectorAll('.item-price, #shippingCost, #discountAmount').forEach(input => {
    input.addEventListener('input', calculateTotals);
});

// Calcular al cargar
calculateTotals();

// Enviar propuesta
document.getElementById('proposalForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
    
    const formData = new FormData(e.target);
    formData.append('action', 'send_proposal');
    formData.append('order_id', orderId);
    
    items.forEach(item => {
        const priceInput = document.querySelector(`[name="price_${item.id}"]`);
        const subtotalInput = priceInput.closest('tr').querySelector('.item-subtotal');
        formData.append(`items[${item.id}][price]`, priceInput.value);
        formData.append(`items[${item.id}][subtotal]`, subtotalInput.value);
    });
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/send-proposal.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Propuesta enviada correctamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    } catch (error) {
        alert('❌ Error de conexión');
    }
    
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Enviar Propuesta al Cliente';
});

// Enviar mensaje de chat
document.getElementById('chatForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    
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
        alert('Error de conexión');
    }
    
    submitBtn.disabled = false;
});

// Auto-scroll del chat
const messagesContainer = document.getElementById('messagesContainer');
messagesContainer.scrollTop = messagesContainer.scrollHeight;
</script>

<?php require_once 'footer.php'; ?>
