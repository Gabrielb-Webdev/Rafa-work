<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Verificar si el usuario está logueado
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? intval($_SESSION['user_id']) : null;

// Función para obtener el carrito del usuario desde BD
function getCartFromDB($userId) {
    try {
        $stmt = executeQuery(
            "SELECT c.*, p.name, p.price, p.stock, p.image, p.is_active 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ? AND p.is_active = 1",
            [$userId]
        );
        $items = $stmt->fetchAll();
        
        $cart = [];
        foreach ($items as $item) {
            $cart[(int)$item['product_id']] = [
                'id' => (int)$item['product_id'],
                'name' => $item['name'],
                'price' => (float)$item['price'],
                'quantity' => min((int)$item['quantity'], (int)$item['stock']),
                'image' => $item['image']
            ];
        }
        return $cart;
    } catch (Exception $e) {
        return [];
    }
}

// Función para guardar item en BD
function saveCartItemToDB($userId, $productId, $quantity) {
    try {
        // Verificar si ya existe
        $stmt = executeQuery("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Actualizar cantidad
            executeQuery("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", [$quantity, $existing['id']]);
        } else {
            // Insertar nuevo
            executeQuery(
                "INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
                [$userId, $productId, $quantity]
            );
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Función para eliminar item de BD
function removeCartItemFromDB($userId, $productId) {
    try {
        executeQuery("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Inicializar carrito
if ($isLoggedIn) {
    // Usuario logueado: cargar desde BD
    $_SESSION['cart'] = getCartFromDB($userId);
} else {
    // Usuario no logueado: usar sesión
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
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
                        
                        // Si está logueado, guardar también en BD
                        if ($isLoggedIn) {
                            saveCartItemToDB($userId, $productId, $newQty);
                        }
                        
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
                        
                        // Si está logueado, actualizar también en BD
                        if ($isLoggedIn) {
                            saveCartItemToDB($userId, $productId, $quantity);
                        }
                        
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
                
                // Si está logueado, eliminar también de BD
                if ($isLoggedIn) {
                    removeCartItemFromDB($userId, $productId);
                }
                
                $response['success'] = true;
                $response['message'] = 'Producto eliminado';
                $response['cartCount'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
            }
        }
        break;
        
    case 'remove':
        $productId = intval($_POST['product_id'] ?? 0);
        
        // Verificar si el producto existe en el carrito
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
        
        // Si está logueado, eliminar también de BD
        if ($isLoggedIn && $productExists) {
            removeCartItemFromDB($userId, $productId);
        }
        
        if ($productExists) {
            $response['success'] = true;
            $response['message'] = 'Producto eliminado del carrito';
            $response['cartCount'] = empty($_SESSION['cart']) ? 0 : array_sum(array_column($_SESSION['cart'], 'quantity'));
        } else {
            $response['message'] = 'Producto no encontrado en el carrito';
        }
        break;
        
    case 'get':
        // Si está logueado, recargar desde BD para estar sincronizado
        if ($isLoggedIn) {
            $_SESSION['cart'] = getCartFromDB($userId);
        }
        
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
        
        // Si está logueado, limpiar también de BD
        if ($isLoggedIn) {
            try {
                executeQuery("DELETE FROM cart WHERE user_id = ?", [$userId]);
            } catch (Exception $e) {
                // Continuar aunque falle
            }
        }
        
        $response['success'] = true;
        $response['message'] = 'Carrito vaciado';
        $response['cartCount'] = 0;
        break;
}

echo json_encode($response);
