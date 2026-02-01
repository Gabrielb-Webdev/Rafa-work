<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$order_id = $_GET['id'] ?? 0;
$success = '';
$error = '';

if (!$order_id) {
    redirect('/admin/pedidos.php');
}

// Obtener información del pedido
try {
    $stmt = executeQuery(
        "SELECT o.*, u.full_name as user_name, u.email as user_email, u.id as user_id
         FROM orders o 
         JOIN users u ON o.user_id = u.id 
         WHERE o.id = ?",
        [$order_id]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect('/admin/pedidos.php');
    }
    
    // Obtener items del pedido
    $stmt = executeQuery(
        "SELECT oi.*, p.name as product_name, p.image, p.price as current_price
         FROM order_items oi 
         LEFT JOIN products p ON oi.product_id = p.id 
         WHERE oi.order_id = ?",
        [$order_id]
    );
    $items = $stmt->fetchAll();
    
    // Obtener mensajes del chat (puede que la tabla no exista)
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
        // Tabla no existe, continuar sin mensajes
    }
    
} catch (Exception $e) {
    // Error al cargar pedido, mostrar mensaje
    die('Error al cargar el pedido: ' . $e->getMessage());
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span style="background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(255, 152, 0, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-hourglass-half"></i> PENDIENTE</span>',
        'processing' => '<span style="background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-cog fa-spin"></i> EN PROCESO</span>',
        'shipped' => '<span style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(23, 162, 184, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-truck"></i> ENVIADO</span>',
        'delivered' => '<span style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-check-circle"></i> ENTREGADO</span>',
        'cancelled' => '<span style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-times-circle"></i> CANCELADO</span>'
    ];
    return $badges[$status] ?? '<span style="background: #6c757d; color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700;">' . strtoupper($status) . '</span>';
}

$pageTitle = 'Detalle del Pedido - Admin';
include __DIR__ . '/header.php';
?>

<style>
.order-detail-page {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: calc(100vh - 200px);
    padding: 40px 0;
}

.detail-container {
    max-width: 1600px;
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
    font-weight: 700;
    padding: 12px 25px;
    background: white;
    border-radius: 50px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.back-link:hover {
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.detail-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 25px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
    color: white;
}

.detail-header h1 {
    font-size: 36px;
    color: white;
    margin-bottom: 15px;
    font-weight: 900;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
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
    color: rgba(255,255,255,0.95);
    font-weight: 600;
}

.meta-item i {
    color: #ffd700;
    font-size: 20px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 500px;
    gap: 30px;
}

@media (max-width: 1200px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}

.detail-section {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    border: 1px solid rgba(255,255,255,0.8);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.detail-section:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 50px rgba(0,0,0,0.12);
}

.section-title {
    font-size: 24px;
    font-weight: 900;
    color: #2c3e50;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 15px;
    border-bottom: 3px solid #667eea;
}

.section-title i {
    color: #667eea;
    font-size: 28px;
}

.info-row {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-label {
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}

.info-value {
    color: #2c3e50;
    font-weight: 600;
    font-size: 16px;
}

/* Formulario de propuesta */
.proposal-form {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 20px;
    padding: 35px;
    margin-bottom: 30px;
    box-shadow: 0 15px 50px rgba(102, 126, 234, 0.4);
    color: white;
}

.proposal-form h3 {
    color: white;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: 900;
}

.product-proposal {
    background: rgba(255,255,255,0.95);
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.product-proposal-header {
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 12px;
    font-size: 17px;
}

.price-input-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 12px;
}

.price-input-group label {
    display: block;
    font-size: 13px;
    color: #2c3e50;
    margin-bottom: 6px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.price-input-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid rgba(255,255,255,0.9);
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.price-input-group input:focus {
    outline: none;
    border-color: #ffd700;
    box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
}

.proposal-total-row {
    display: flex;
    justify-content: space-between;
    padding: 20px;
    margin-top: 20px;
    border-top: 3px solid rgba(255,255,255,0.3);
    font-size: 22px;
    font-weight: 900;
    color: white;
    background: rgba(0,0,0,0.1);
    border-radius: 10px;
}

.btn-send-proposal {
    width: 100%;
    padding: 18px;
    background: white;
    color: #667eea;
    border: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 900;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.btn-send-proposal:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.3);
}

.btn-send-proposal:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Chat */
.chat-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    height: 600px;
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
    border-color: #667eea;
}

.chat-send-btn {
    padding: 12px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}
</style>

<section class="order-detail-page">
    <div class="detail-container">
        <a href="<?php echo BASE_URL; ?>/admin/pedidos.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver a Pedidos
        </a>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="detail-header">
            <h1>Pedido #<?php echo $order['order_number']; ?></h1>
            <div class="order-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($order['user_name']); ?></span>
                </div>
                <div class="meta-item" id="orderStatusBadge">
                    <?php echo getStatusBadge($order['status']); ?>
                </div>
                <?php if ($order['proposal_sent']): ?>
                    <div class="meta-item">
                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        <span style="color: #28a745; font-weight: 700;">Propuesta Enviada</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="detail-grid">
            <div>
                <?php if (!$order['proposal_sent']): ?>
                    <div class="proposal-form">
                        <h3>
                            <i class="fas fa-file-invoice-dollar"></i>
                            Crear Propuesta de Cotización
                        </h3>
                        <p style="color: #856404; margin-bottom: 20px;">
                            Ingresa el precio propuesto para cada producto y envía la cotización al cliente
                        </p>
                        
                        <form id="proposalForm">
                            <?php foreach ($items as $index => $item): ?>
                                <div class="product-proposal">
                                    <div class="product-proposal-header">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                        <small style="color: #6c757d; font-weight: normal;">(Cantidad: <?php echo $item['quantity']; ?>)</small>
                                    </div>
                                    <div class="price-input-group">
                                        <div>
                                            <label>Precio Unitario:</label>
                                            <input type="number" 
                                                   name="price_<?php echo $item['id']; ?>" 
                                                   class="item-price" 
                                                   data-item-id="<?php echo $item['id']; ?>"
                                                   data-quantity="<?php echo $item['quantity']; ?>"
                                                   step="0.01" 
                                                   min="0"
                                                   placeholder="0.00"
                                                   value="<?php echo $item['current_price'] ?? ''; ?>"
                                                   required>
                                        </div>
                                        <div>
                                            <label>Subtotal:</label>
                                            <input type="number" 
                                                   name="subtotal_<?php echo $item['id']; ?>" 
                                                   class="item-subtotal" 
                                                   data-item-id="<?php echo $item['id']; ?>"
                                                   step="0.01" 
                                                   readonly 
                                                   placeholder="0.00"
                                                   style="background: #f8f9fa;">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="product-proposal" style="background: #f8f9fa;">
                                <div class="price-input-group">
                                    <div>
                                        <label>Costo de Envío:</label>
                                        <input type="number" 
                                               name="shipping" 
                                               id="shipping" 
                                               step="0.01" 
                                               min="0" 
                                               placeholder="0.00"
                                               value="0">
                                    </div>
                                    <div>
                                        <label>Descuento:</label>
                                        <input type="number" 
                                               name="discount" 
                                               id="discount" 
                                               step="0.01" 
                                               min="0" 
                                               placeholder="0.00"
                                               value="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="proposal-total-row">
                                <span>Total de la Propuesta:</span>
                                <span id="proposalTotal">$0.00</span>
                            </div>
                            
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: #856404; font-weight: 600; margin-bottom: 8px;">
                                    Mensaje para el cliente:
                                </label>
                                <textarea name="proposal_message" 
                                          id="proposalMessage" 
                                          rows="4" 
                                          style="width: 100%; padding: 12px; border: 1px solid #ffc107; border-radius: 8px; font-size: 14px;"
                                          placeholder="Escribe un mensaje personalizado para acompañar la propuesta..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn-send-proposal" id="sendProposalBtn">
                                <i class="fas fa-paper-plane"></i> Enviar Propuesta al Cliente
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Propuesta Enviada -->
                    <?php if (isset($order['proposal_accepted']) && $order['proposal_accepted']): ?>
                        <!-- Propuesta Aceptada -->
                        <div style="padding: 25px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 15px; margin-bottom: 30px; color: white; box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);">
                            <strong style="display: flex; align-items: center; gap: 10px; font-size: 20px; margin-bottom: 10px;">
                                <i class="fas fa-check-double" style="font-size: 28px;"></i> ¡Propuesta Aceptada por el Cliente!
                            </strong>
                            <p style="margin: 5px 0 0 0; opacity: 0.95; font-size: 15px;">
                                ✅ Propuesta enviada el <?php echo date('d/m/Y H:i', strtotime($order['proposal_date'])); ?><br>
                                ✅ Aceptada el <?php echo date('d/m/Y H:i', strtotime($order['proposal_accepted_date'])); ?>
                            </p>
                            <div style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 10px; font-weight: 600;">
                                💰 Total Aceptado: $<?php echo number_format($order['proposal_total'], 2); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Propuesta Enviada pero no aceptada -->
                        <div style="padding: 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; margin-bottom: 30px; color: white; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
                            <strong style="display: flex; align-items: center; gap: 10px; font-size: 20px; margin-bottom: 10px;">
                                <i class="fas fa-paper-plane"></i> Propuesta Enviada - Esperando Respuesta
                            </strong>
                            <p style="margin: 5px 0 0 0; opacity: 0.95; font-size: 15px;">
                                📨 La propuesta fue enviada el <?php echo date('d/m/Y H:i', strtotime($order['proposal_date'])); ?><br>
                                ⏳ El cliente aún no ha aceptado la propuesta
                            </p>
                            <div style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 10px; font-weight: 600;">
                                💰 Total Propuesto: $<?php echo number_format($order['proposal_total'], 2); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="detail-section">
                    <h2 class="section-title">
                        <i class="fas fa-box"></i> Productos Solicitados
                    </h2>
                    
                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; gap: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                            <div style="width: 60px; height: 60px; background: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/uploads/products/<?php echo $item['image']; ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <i class="fas fa-pills" style="color: #00d4d4; font-size: 24px;"></i>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #2c3e50;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div style="color: #6c757d; font-size: 14px;">Cantidad: <?php echo $item['quantity']; ?></div>
                                <?php if ($item['proposed_price'] !== null): ?>
                                    <div style="color: #00d4d4; font-weight: 600; margin-top: 5px;">
                                        $<?php echo number_format($item['proposed_price'], 2); ?> c/u = $<?php echo number_format($item['proposed_subtotal'], 2); ?>
                                    </div>
                                <?php else: ?>
                                    <div style="color: #ffc107; font-style: italic; margin-top: 5px;">Pendiente de cotización</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if ($order['proposal_total']): ?>
                        <div style="margin-top: 20px; padding: 20px; background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%); border-radius: 12px; color: white;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 18px; font-weight: 600;">Total Propuesta:</span>
                                <span style="font-size: 32px; font-weight: 700;">$<?php echo number_format($order['proposal_total'], 2); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="detail-section">
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
                
                <div class="detail-section">
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
                            <div style="text-align: center; color: #6c757d; padding: 40px;">
                                <i class="fas fa-comments" style="font-size: 60px; color: #dee2e6; margin-bottom: 15px;"></i>
                                <p>Aún no hay mensajes</p>
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
                                                <strong style="display: block; margin-bottom: 10px;">
                                                    <i class="fas fa-file-invoice-dollar"></i> Propuesta de Cotización
                                                </strong>
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
                                   placeholder="Escribe un mensaje..."
                                   required>
                            <button type="submit" class="chat-send-btn" id="sendChatBtn">
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
const orderData = <?php echo json_encode($order); ?>;
const itemsData = <?php echo json_encode($items); ?>;

// Calcular subtotales automáticamente (solo si existe formulario de propuesta)
const itemPriceInputs = document.querySelectorAll('.item-price');
if (itemPriceInputs.length > 0) {
    itemPriceInputs.forEach(input => {
        input.addEventListener('input', function() {
            const quantity = parseFloat(this.dataset.quantity);
            const price = parseFloat(this.value) || 0;
            const subtotal = (price * quantity).toFixed(2);
            
            const itemId = this.dataset.itemId;
            const subtotalEl = document.querySelector(`.item-subtotal[data-item-id="${itemId}"]`);
            if (subtotalEl) {
                subtotalEl.value = subtotal;
            }
            
            calculateTotal();
        });
        
        // Trigger inicial
        input.dispatchEvent(new Event('input'));
    });
}

const shippingInput = document.getElementById('shipping');
const discountInput = document.getElementById('discount');

if (shippingInput) {
    shippingInput.addEventListener('input', calculateTotal);
}

if (discountInput) {
    discountInput.addEventListener('input', calculateTotal);
}

function calculateTotal() {
    let subtotal = 0;
    
    document.querySelectorAll('.item-subtotal').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const shippingEl = document.getElementById('shipping');
    const discountEl = document.getElementById('discount');
    const totalEl = document.getElementById('proposalTotal');
    
    const shipping = shippingEl ? (parseFloat(shippingEl.value) || 0) : 0;
    const discount = discountEl ? (parseFloat(discountEl.value) || 0) : 0;
    const total = subtotal + shipping - discount;
    
    if (totalEl) {
        totalEl.textContent = '$' + total.toFixed(2);
    }
}

// Enviar propuesta
const proposalForm = document.getElementById('proposalForm');
if (proposalForm) {
    proposalForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btn = document.getElementById('sendProposalBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando propuesta...';
        
        const formData = new FormData(e.target);
        formData.append('action', 'send_proposal');
        formData.append('order_id', orderId);
        
        // Agregar datos de items si existen
        const itemPrices = document.querySelectorAll('.item-price');
        itemPrices.forEach(input => {
            const itemId = input.dataset.itemId;
            const price = input.value;
            const subtotalEl = document.querySelector(`.item-subtotal[data-item-id="${itemId}"]`);
            const subtotal = subtotalEl ? subtotalEl.value : 0;
            formData.append(`items[${itemId}][price]`, price);
            formData.append(`items[${itemId}][subtotal]`, subtotal);
        });
        
        try {
            const response = await fetch('<?php echo BASE_URL; ?>/api/send-proposal.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('✅ Propuesta enviada exitosamente. Se ha notificado al cliente por email y por chat.');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        } catch (error) {
            alert('❌ Error de conexión al enviar la propuesta');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
}
</script>

<style>
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
</style>

<?php include __DIR__ . '/footer.php'; ?>
// Chat en tiempo real
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');
const orderId = <?php echo $order_id; ?>;

function scrollToBottom() {
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

scrollToBottom();

// Función para cargar mensajes sin recargar la página
async function loadMessages() {
    try {
        console.log('Admin: Cargando mensajes para order_id:', orderId);
        const response = await fetch('<?php echo BASE_URL; ?>/api/order-messages.php?order_id=' + orderId);
        console.log('Admin: Respuesta recibida:', response.status);
        const data = await response.json();
        console.log('Admin: Mensajes recibidos:', data);
        
        if (data.success && data.messages) {
            console.log('Admin: Actualizando chat con', data.messages.length, 'mensajes');
            updateChatMessages(data.messages);
        } else {
            console.error('Admin: Error en respuesta:', data);
        }
    } catch (error) {
        console.error('Admin: Error al cargar mensajes:', error);
    }
}

function updateChatMessages(messages) {
    if (!chatMessages) {
        console.error('Admin: chatMessages element not found');
        return;
    }
    
    console.log('Admin: Renderizando', messages.length, 'mensajes');
    
    const currentScroll = chatMessages.scrollHeight - chatMessages.scrollTop;
    const wasAtBottom = currentScroll <= chatMessages.clientHeight + 50;
    
    chatMessages.innerHTML = '';
    
    if (messages.length === 0) {
        chatMessages.innerHTML = '<p style="text-align: center; color: #999; padding: 40px;">No hay mensajes aún. ¡Inicia la conversación!</p>';
    } else {
        messages.forEach((msg, index) => {
            console.log('Admin: Renderizando mensaje', index, ':', msg);
            const isAdmin = msg.user_role === 'admin';
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message';
            messageDiv.style.cssText = `
                background: ${isAdmin ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : '#f8f9fa'};
                color: ${isAdmin ? 'white' : '#2c3e50'};
                padding: 15px 20px;
                border-radius: 20px;
                margin-bottom: 15px;
                max-width: 70%;
                ${isAdmin ? 'margin-left: auto;' : 'margin-right: auto;'}
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                animation: slideIn 0.3s ease;
            `;
            
            messageDiv.innerHTML = `
                <div style="font-weight: 700; font-size: 12px; margin-bottom: 5px; opacity: 0.8; text-transform: uppercase;">
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
        console.log('Admin: Mensajes renderizados correctamente');
    }
    
    if (wasAtBottom) {
        scrollToBottom();
    }
}

// Polling cada 3 segundos para actualizar mensajes
setInterval(loadMessages, 3000);

// Actualizar estado del pedido en tiempo real
async function updateOrderStatus() {
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/api/get-order-status.php?order_id=' + orderId);
        const data = await response.json();
        
        if (data.success) {
            updateStatusBadge(data.status, data.proposal_sent);
        }
    } catch (error) {
        console.error('Error al actualizar estado:', error);
    }
}

function updateStatusBadge(status, proposalSent) {
    const badges = {
        'pending': '<span style="background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(255, 152, 0, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-hourglass-half"></i> PENDIENTE</span>',
        'processing': '<span style="background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(33, 150, 243, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-cog fa-spin"></i> EN PROCESO</span>',
        'shipped': '<span style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(23, 162, 184, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-truck"></i> ENVIADO</span>',
        'delivered': '<span style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-check-circle"></i> ENTREGADO</span>',
        'cancelled': '<span style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 10px 20px; border-radius: 50px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4); display: inline-flex; align-items: center; gap: 8px;"><i class="fas fa-times-circle"></i> CANCELADO</span>'
    };
    
    const badgeEl = document.getElementById('orderStatusBadge');
    if (badgeEl && badges[status]) {
        badgeEl.innerHTML = badges[status];
    }
}

// Actualizar estado cada 5 segundos
setInterval(updateOrderStatus, 5000);

// Enviar mensaje sin recargar
if (chatForm) {
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Admin: Formulario de chat enviado');
        
        const message = messageInput.value.trim();
        console.log('Admin: Mensaje a enviar:', message);
        
        if (!message) {
            console.log('Admin: Mensaje vacío, no se envía');
            return;
        }
        
        const btn = document.getElementById('sendChatBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            console.log('Admin: Enviando mensaje a API...');
            const response = await fetch('<?php echo BASE_URL; ?>/api/order-messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `order_id=${orderId}&message=${encodeURIComponent(message)}`
            });
            
            console.log('Admin: Respuesta recibida:', response.status);
            const data = await response.json();
            console.log('Admin: Datos:', data);
            
            if (data.success) {
                messageInput.value = '';
                console.log('Admin: Mensaje enviado exitosamente');
                // Cargar mensajes inmediatamente
                await loadMessages();
            } else {
                console.error('Admin: Error al enviar:', data);
                alert('Error al enviar el mensaje: ' + data.message);
            }
        } catch (error) {
            console.error('Admin: Error de conexión:', error);
            alert('Error de conexión al enviar el mensaje: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
} else {
    console.error('Admin: chatForm element not found');
}

// Calcular subtotales automáticamente
document.querySelectorAll('.item-price').forEach(input => {