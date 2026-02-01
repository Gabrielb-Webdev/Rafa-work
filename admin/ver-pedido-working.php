<?php
/**
 * Ver Detalle de Pedido - Panel Admin
 * Version: 2.0 - UI/UX Mejorado
 * Fecha: 31/01/2026
 * Cambios: Diseño moderno, chat mejorado, animaciones fluidas
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

<style>
.order-status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
}
.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cfe2ff; color: #084298; }
.status-shipped { background: #cff4fc; color: #055160; }
.status-delivered { background: #d1e7dd; color: #0f5132; }

.proposal-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}
.proposal-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    color: white;
}
.item-price-input {
    font-weight: 600;
    font-size: 15px;
}
.total-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 15px;
}
.chat-message {
    animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.product-image-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.info-badge {
    background: #e7f3ff;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    display: inline-block;
    margin: 5px;
}
</style>

<div class="container-fluid px-4">
    <!-- Header mejorado -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-shopping-bag text-primary me-2"></i>
                <?php echo htmlspecialchars($order['order_number']); ?>
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i>
                Recibido el <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
            </p>
        </div>
        <a href="pedidos.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver a Pedidos
        </a>
    </div>

    <!-- Badges de estado -->
    <div class="mb-4">
        <span class="order-status-badge status-<?php echo $order['status']; ?> me-2">
            <?php
            $status_icons = [
                'pending' => '⏳',
                'processing' => '⚙️',
                'shipped' => '🚚',
                'delivered' => '✅'
            ];
            echo ($status_icons[$order['status']] ?? '📦') . ' ' . ucfirst($order['status']);
            ?>
        </span>
        <?php if ($order['proposal_sent']): ?>
        <span class="info-badge">
            <i class="fas fa-check-circle text-success"></i> Propuesta Enviada
        </span>
        <?php else: ?>
        <span class="info-badge">
            <i class="fas fa-clock text-warning"></i> Esperando Propuesta
        </span>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Columna izquierda: Información del pedido -->
        <div class="col-lg-8">
            
            <!-- Datos del cliente con diseño mejorado -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle text-primary me-2"></i>
                        Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="fas fa-user me-1"></i>Nombre Completo</small>
                                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="fas fa-envelope me-1"></i>Email</small>
                                <strong><?php echo htmlspecialchars($order['email']); ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="fas fa-phone me-1"></i>Teléfono</small>
                                <strong><?php echo htmlspecialchars($order['phone'] ?: 'No proporcionado'); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt me-1"></i>Dirección</small>
                                <strong>
                                    <?php echo htmlspecialchars($order['street'] ?: 'No proporcionado'); ?>
                                    <?php echo htmlspecialchars($order['street_number'] ? ' #' . $order['street_number'] : ''); ?>
                                </strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="fas fa-city me-1"></i>Colonia</small>
                                <strong><?php echo htmlspecialchars($order['neighborhood'] ?: 'No proporcionado'); ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="fas fa-map me-1"></i>Ciudad y CP</small>
                                <strong>
                                    <?php echo htmlspecialchars($order['city'] ?: 'No proporcionado'); ?>
                                    <?php echo $order['postal_code'] ? ', CP ' . htmlspecialchars($order['postal_code']) : ''; ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                    <?php if ($order['notes']): ?>
                    <div class="alert alert-info mt-3 border-0" style="background: #e7f3ff;">
                        <strong><i class="fas fa-sticky-note me-2"></i>Notas del Cliente:</strong><br>
                        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Productos del pedido con diseño mejorado -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-box-open text-success me-2"></i>
                        Productos Solicitados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60%">Producto</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">Precio Propuesto</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['image']): ?>
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="" class="product-image-thumb me-3">
                                            <?php endif; ?>
                                            <span class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?php echo $item['quantity']; ?></span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($item['proposed_price']): ?>
                                            <span class="text-success fw-bold">$<?php echo number_format($item['proposed_price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($item['proposed_subtotal']): ?>
                                            <strong class="text-primary">$<?php echo number_format($item['proposed_subtotal'], 2); ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if ($order['proposal_total']): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">TOTAL:</th>
                                    <th class="text-end text-success fs-5">$<?php echo number_format($order['proposal_total'], 2); ?></th>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Formulario de propuesta mejorado -->
            <?php if (!$order['proposal_sent']): ?>
            <div class="card proposal-card mb-4 border-0 shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>
                        Crear Propuesta Comercial
                    </h5>
                    <small class="d-block mt-1 opacity-75">Define los precios para cada producto</small>
                </div>
                <div class="card-body">
                    <form id="proposalForm">
                        <div class="table-responsive">
                            <table class="table table-borderless text-white">
                                <thead style="border-bottom: 2px solid rgba(255,255,255,0.2)">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center" style="width: 80px">Cant.</th>
                                        <th class="text-end" style="width: 150px">Precio Unit.</th>
                                        <th class="text-end" style="width: 150px">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr data-item-id="<?php echo $item['id']; ?>">
                                        <td class="align-middle">
                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge bg-light text-dark"><?php echo $item['quantity']; ?></span>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-0">$</span>
                                                <input type="number" 
                                                       name="price_<?php echo $item['id']; ?>" 
                                                       class="form-control form-control-sm item-price item-price-input border-0 text-end fw-bold" 
                                                       step="0.01" 
                                                       min="0" 
                                                       value="<?php echo $item['current_price'] ?? 0; ?>"
                                                       data-quantity="<?php echo $item['quantity']; ?>"
                                                       required>
                                            </div>
                                        </td>
                                        <td class="text-end align-middle">
                                            <strong style="font-size: 16px;">$<span class="item-subtotal-display">0.00</span></strong>
                                            <input type="hidden" class="item-subtotal" value="0">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="total-section bg-white text-dark rounded">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">
                                        <i class="fas fa-truck me-1"></i>Costo de Envío
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="shippingCost" class="form-control" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">
                                        <i class="fas fa-tag me-1"></i>Descuento
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="discountAmount" class="form-control" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">TOTAL PROPUESTA</label>
                                    <div class="fs-3 fw-bold text-success">
                                        $<span id="totalDisplay">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label text-white fw-bold">
                                <i class="fas fa-comment-dots me-2"></i>Mensaje para el Cliente
                            </label>
                            <textarea name="proposal_message" class="form-control" rows="3" 
                                      placeholder="Escribe un mensaje personalizado para acompañar tu propuesta..."
                                      style="resize: none;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-light btn-lg w-100 mt-3 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Propuesta al Cliente
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-success border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle fs-3 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Propuesta Enviada</h5>
                        <p class="mb-0">
                            Enviada el <?php echo date('d/m/Y H:i', strtotime($order['proposal_date'])); ?>
                            <?php if ($order['proposal_accepted']): ?>
                            <br><strong class="text-success">
                                <i class="fas fa-check-double me-1"></i>
                                Aceptada el <?php echo date('d/m/Y H:i', strtotime($order['proposal_accepted_date'])); ?>
                            </strong>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Columna derecha: Chat mejorado -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 20px;">
                <div class="card-header bg-primary text-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        Chat con el Cliente
                    </h5>
                    <small class="opacity-75">Comunicación en tiempo real</small>
                </div>
                <div class="card-body p-0" style="height: 500px; display: flex; flex-direction: column;">
                    <div id="messagesContainer" style="flex: 1; overflow-y: auto; padding: 20px; background: #f8f9fa;">
                        <?php if (empty($messages)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fs-1 mb-3 d-block opacity-25"></i>
                            <p class="mb-0">No hay mensajes aún</p>
                            <small>Inicia la conversación</small>
                        </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                            <div class="mb-3 <?php echo $msg['user_role'] === 'admin' ? 'text-end' : ''; ?> chat-message">
                                <div class="d-inline-block px-3 py-2 rounded-3 <?php echo $msg['user_role'] === 'admin' ? 'bg-primary text-white' : 'bg-white shadow-sm'; ?>" 
                                     style="max-width: 80%; word-wrap: break-word;">
                                    <small class="d-block fw-bold mb-1 <?php echo $msg['user_role'] === 'admin' ? 'opacity-75' : 'text-primary'; ?>">
                                        <?php echo $msg['user_role'] === 'admin' ? 'Tú' : htmlspecialchars($msg['sender_name']); ?>
                                    </small>
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    <small class="d-block mt-1 opacity-50" style="font-size: 0.7rem;">
                                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 border-top bg-white">
                        <form id="chatForm">
                            <div class="input-group">
                                <input type="text" id="messageInput" class="form-control border-0 bg-light" 
                                       placeholder="Escribe un mensaje..." required style="resize: none;">
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
        
        const row = input.closest('tr');
        const subtotalDisplay = row.querySelector('.item-subtotal-display');
        const subtotalInput = row.querySelector('.item-subtotal');
        
        if (subtotalDisplay) subtotalDisplay.textContent = itemSubtotal.toFixed(2);
        if (subtotalInput) subtotalInput.value = itemSubtotal.toFixed(2);
        
        subtotal += itemSubtotal;
    });
    
    const shipping = parseFloat(document.getElementById('shippingCost').value) || 0;
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const total = subtotal + shipping - discount;
    
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
