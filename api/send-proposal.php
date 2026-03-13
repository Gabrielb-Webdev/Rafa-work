<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Verify admin access
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action !== 'send_proposal') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $items = $_POST['items'] ?? [];
    $shipping = (float)($_POST['shipping'] ?? 0);
    $discount = (float)($_POST['discount'] ?? 0);
    $proposal_message = trim($_POST['proposal_message'] ?? '');
    
    if (!$order_id || empty($items)) {
        throw new Exception('Incomplete data');
    }
    
    // Get order information
    $stmt = executeQuery(
        "SELECT o.*, u.email, u.full_name
         FROM orders o
         JOIN users u ON o.user_id = u.id
         WHERE o.id = ?",
        [$order_id]
    );
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    if ($order['proposal_sent']) {
        throw new Exception('A proposal has already been sent for this order');
    }
    
    // Calculate totals
    $subtotal = 0;
    $proposal_details = [];
    
    $conn = getConnection();
    $conn->beginTransaction();
    
    foreach ($items as $item_id => $item_data) {
        $price = (float)($item_data['price'] ?? 0);
        $quantity = (int)($item_data['quantity'] ?? 0);
        $item_subtotal = (float)($item_data['subtotal'] ?? 0);
        
        if ($price <= 0 || $quantity <= 0) {
            throw new Exception('All products must have valid price and quantity');
        }
        
        // Update order_items with proposed prices and quantities
        $stmt = $conn->prepare(
            "UPDATE order_items 
             SET proposed_price = ?, proposed_quantity = ?, proposed_subtotal = ?
             WHERE id = ? AND order_id = ?"
        );
        $stmt->execute([$price, $quantity, $item_subtotal, $item_id, $order_id]);
        
        $subtotal += $item_subtotal;
        
        // Get product name and original quantity for email
        $stmt_prod = $conn->prepare("SELECT product_name, quantity FROM order_items WHERE id = ?");
        $stmt_prod->execute([$item_id]);
        $prod = $stmt_prod->fetch();
        if ($prod) {
            $proposal_details[] = [
                'name' => $prod['product_name'],
                'quantity_requested' => $prod['quantity'],
                'quantity_proposed' => $quantity,
                'price' => $price,
                'subtotal' => $item_subtotal
            ];
        }
    }
    
    $total = $subtotal + $shipping - $discount;
    
    // Update order with proposal
    $stmt = $conn->prepare(
        "UPDATE orders 
         SET proposal_sent = 1,
             proposal_date = NOW(),
             proposal_total = ?,
             subtotal = ?,
             shipping = ?,
             total = ?
         WHERE id = ?"
    );
    $stmt->execute([$total, $subtotal, $shipping, $total, $order_id]);
    
    // Create chat message with proposal
    $proposal_text = "📋 QUOTATION PROPOSAL (USD)\n\n";
    
    if ($proposal_message) {
        $proposal_text .= $proposal_message . "\n\n";
    }
    
    $proposal_text .= "PRODUCT DETAILS:\n";
    $proposal_text .= str_repeat("-", 40) . "\n";
    
    foreach ($proposal_details as $detail) {
        $quantity_note = '';
        if ($detail['quantity_proposed'] != $detail['quantity_requested']) {
            $quantity_note = sprintf(
                " (you requested %d, we offer %d)",
                $detail['quantity_requested'],
                $detail['quantity_proposed']
            );
        }
        $proposal_text .= sprintf(
            "%s%s\n   %d units x USD $%.2f = USD $%.2f\n\n",
            $detail['name'],
            $quantity_note,
            $detail['quantity_proposed'],
            $detail['price'],
            $detail['subtotal']
        );
    }
    
    $proposal_text .= str_repeat("-", 40) . "\n";
    $proposal_text .= sprintf("Subtotal: USD $%.2f\n", $subtotal);
    $proposal_text .= sprintf("Shipping: USD $%.2f\n", $shipping);
    if ($discount > 0) {
        $proposal_text .= sprintf("Discount: -USD $%.2f\n", $discount);
    }
    $proposal_text .= sprintf("\n✅ TOTAL: USD $%.2f\n", $total);
    $proposal_text .= "\nYou can review the complete details in your order. If you have any questions, feel free to ask us through this chat.";
    
    // Insertar mensaje en el chat (sin columna is_proposal si no existe)
    try {
        $stmt = $conn->prepare(
            "INSERT INTO order_messages (order_id, user_id, message, created_at) 
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$order_id, $_SESSION['user_id'], $proposal_text]);
    } catch (Exception $e) {
        // If it fails, try without is_proposal column
        error_log("Error inserting message: " . $e->getMessage());
    }
    
    $conn->commit();
    
    // Send email to client
    try {
        $to = $order['email'];
        $subject = "Quotation Proposal - Order #" . $order['order_number'];
        
        $email_body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #00d4d4 0%, #00a0a0 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .product-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .product-table th, .product-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
                .product-table th { background: #00d4d4; color: white; }
                .total-row { font-size: 20px; font-weight: bold; color: #00d4d4; margin-top: 20px; }
                .button { display: inline-block; padding: 15px 30px; background: #00d4d4; color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Quotation Proposal</h1>
                    <p>Order #" . htmlspecialchars($order['order_number']) . "</p>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($order['full_name']) . "</strong>,</p>
                    
                    <p>We have prepared a personalized proposal for your order. Here are the details:</p>
                    
                    " . ($proposal_message ? "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>" . nl2br(htmlspecialchars($proposal_message)) . "</div>" : "") . "
                    
                    <table class='product-table'>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>";
        
        foreach ($proposal_details as $detail) {
            $email_body .= "
                            <tr>
                                <td>" . htmlspecialchars($detail['name']) . "</td>
                                <td>" . $detail['quantity'] . "</td>
                                <td>$" . number_format($detail['price'], 2) . "</td>
                                <td>$" . number_format($detail['subtotal'], 2) . "</td>
                            </tr>";
        }
        
        $email_body .= "
                        </tbody>
                    </table>
                    
                    <div style='background: white; padding: 20px; border-radius: 8px; margin-top: 20px;'>
                        <p style='margin: 10px 0;'><strong>Subtotal:</strong> $" . number_format($subtotal, 2) . "</p>
                        <p style='margin: 10px 0;'><strong>Shipping:</strong> $" . number_format($shipping, 2) . "</p>";
        
        if ($discount > 0) {
            $email_body .= "<p style='margin: 10px 0;'><strong>Discount:</strong> -$" . number_format($discount, 2) . "</p>";
        }
        
        $email_body .= "
                        <p class='total-row' style='margin-top: 20px; padding-top: 20px; border-top: 2px solid #00d4d4;'>
                            <strong>TOTAL:</strong> $" . number_format($total, 2) . "
                        </p>
                    </div>
                    
                    <p style='margin-top: 30px;'>To see more details or ask questions about this proposal, you can access the order chat in your account.</p>
                    
                    <center>
                        <a href='" . BASE_URL . "/order-detail?id=" . $order_id . "' class='button'>View Order and Chat</a>
                    </center>
                </div>
                <div class='footer'>
                    <p>Forethink Health - Order System</p>
                    <p>This is an automated email, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Forethink Health <noreply@" . $_SERVER['HTTP_HOST'] . ">" . "\r\n";
        
        @mail($to, $subject, $email_body, $headers);
        
    } catch (Exception $e) {
        // Log error pero no fallar la operación
        error_log("Error enviando email: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Propuesta enviada correctamente',
        'total' => $total
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
