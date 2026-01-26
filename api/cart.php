<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Inicializar carrito si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($productId > 0) {
            try {
                // Verificar que el producto existe y está activo
                $stmt = executeQuery("SELECT * FROM products WHERE id = ? AND is_active = 1", [$productId]);
                $product = $stmt->fetch();
                
                if ($product) {
                    // Inicializar carrito como array con claves enteras
                    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                        $_SESSION['cart'] = [];
                    }
                    
                    // Verificar stock
                    $currentQty = isset($_SESSION['cart'][$productId]) ? intval($_SESSION['cart'][$productId]['quantity']) : 0;
                    $newQty = $currentQty + $quantity;
                    
                    if ($newQty <= $product['stock']) {
                        // Asegurar que la clave sea entero explícitamente
                        $_SESSION['cart'][(int)$productId] = [
                            'id' => (int)$product['id'],
                            'name' => $product['name'],
                            'price' => (float)$product['price'],
                            'quantity' => (int)$newQty,
                            'image' => $product['image']
                        ];
                        
                        $response['success'] = true;
                        $response['message'] = 'Producto agregado al carrito';
                        $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
                    } else {
                        $response['message'] = 'No hay suficiente stock disponible';
                    }
                } else {
                    $response['message'] = 'Producto no encontrado';
                }
            } catch (Exception $e) {
                $response['message'] = 'Error al agregar al carrito';
            }
        }
        break;
        
    case 'update':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            if ($quantity > 0) {
                try {
                    // Verificar stock
                    $stmt = executeQuery("SELECT stock FROM products WHERE id = ?", [$productId]);
                    $product = $stmt->fetch();
                    
                    if ($product && $quantity <= $product['stock']) {
                        $_SESSION['cart'][$productId]['quantity'] = $quantity;
                        $response['success'] = true;
                        $response['message'] = 'Cantidad actualizada';
                        $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
                    } else {
                        $response['message'] = 'No hay suficiente stock';
                    }
                } catch (Exception $e) {
                    $response['message'] = 'Error al actualizar';
                }
            } else {
                unset($_SESSION['cart'][$productId]);
                $response['success'] = true;
                $response['message'] = 'Producto eliminado';
                $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
            }
        }
        break;
        
    case 'remove':
        $productId = intval($_POST['product_id'] ?? 0);
        
        // Depuración: verificar si el producto existe en el carrito
        $cartKeys = array_keys($_SESSION['cart']);
        $productExists = false;
        
        // Buscar el producto tanto por clave numérica como string
        foreach ($cartKeys as $key) {
            if ($key == $productId || intval($key) == $productId) {
                unset($_SESSION['cart'][$key]);
                $productExists = true;
                break;
            }
        }
        
        if ($productExists) {
            $response['success'] = true;
            $response['message'] = 'Producto eliminado del carrito';
            $response['cartCount'] = empty($_SESSION['cart']) ? 0 : array_sum(array_column($_SESSION['cart'], 'quantity'));
        } else {
            $response['message'] = 'Producto no encontrado en el carrito (ID: ' . $productId . ', Keys: ' . implode(',', $cartKeys) . ')';
        }
        break;
        
    case 'get':
        $response['success'] = true;
        $response['cart'] = $_SESSION['cart'];
        $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $response['total'] = $total;
        break;
        
    case 'clear':
        $_SESSION['cart'] = [];
        $response['success'] = true;
        $response['message'] = 'Carrito vaciado';
        $response['cartCount'] = 0;
        break;
}

echo json_encode($response);
