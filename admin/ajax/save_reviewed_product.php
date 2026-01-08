<?php
/**
 * Guardar producto revisado y completado
 */

require_once '../../config/database_production.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$productData = json_decode(file_get_contents('php://input'), true);

if (!$productData) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos del producto.']);
    exit;
}

try {
    // Validar campos obligatorios
    $required = ['title', 'price_cop', 'category_id', 'brand_id', 'console_id'];
    foreach ($required as $field) {
        if (empty($productData[$field])) {
            echo json_encode(['success' => false, 'message' => "El campo {$field} es obligatorio."]);
            exit;
        }
    }
    
    // Preparar datos para inserción
    $sql = "INSERT INTO products (
        title, sku, status, description, short_description,
        price_cop, price_usd, stock, category_id, brand_id,
        console_id, product_type, is_featured, is_new, on_sale, is_active,
        `condition`, tags, meta_title, meta_description,
        created_at, updated_at
    ) VALUES (
        :title, :sku, :status, :description, :short_description,
        :price_cop, :price_usd, :stock, :category_id, :brand_id,
        :console_id, :product_type, :is_featured, :is_new, :on_sale, :is_active,
        :condition, :tags, :meta_title, :meta_description,
        NOW(), NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $productData['title'],
        ':sku' => $productData['sku'] ?: 'AUTO-' . time(),
        ':status' => $productData['status'] ?? 1,
        ':description' => $productData['description'] ?? '',
        ':short_description' => $productData['short_description'] ?? '',
        ':price_cop' => $productData['price_cop'],
        ':price_usd' => $productData['price_usd'] ?? 0,
        ':stock' => $productData['stock'] ?? 0,
        ':category_id' => $productData['category_id'],
        ':brand_id' => $productData['brand_id'],
        ':console_id' => $productData['console_id'],
        ':product_type' => $productData['product_type'] ?? 'game',
        ':is_featured' => $productData['is_featured'] ?? 0,
        ':is_new' => $productData['is_new'] ?? 0,
        ':on_sale' => $productData['on_sale'] ?? 0,
        ':is_active' => $productData['is_active'] ?? 1,
        ':condition' => $productData['condition'] ?? 'nuevo',
        ':tags' => $productData['tags'] ?? '',
        ':meta_title' => $productData['meta_title'] ?? $productData['title'],
        ':meta_description' => $productData['meta_description'] ?? ''
    ]);
    
    $productId = $pdo->lastInsertId();
    
    // Insertar géneros
    if (!empty($productData['genres']) && is_array($productData['genres'])) {
        $genreStmt = $pdo->prepare("INSERT INTO product_genres (product_id, genre_id) VALUES (:product_id, :genre_id)");
        foreach ($productData['genres'] as $genreId) {
            $genreStmt->execute([
                ':product_id' => $productId,
                ':genre_id' => $genreId
            ]);
        }
    }
    
    // Procesar imágenes si se subieron
    if (!empty($productData['images']) && is_array($productData['images'])) {
        $imageStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, display_order) VALUES (:product_id, :image_path, :is_primary, :display_order)");
        
        foreach ($productData['images'] as $index => $imagePath) {
            $imageStmt->execute([
                ':product_id' => $productId,
                ':image_path' => $imagePath,
                ':is_primary' => $index === 0 ? 1 : 0,
                ':display_order' => $index + 1
            ]);
        }
    }
    
    echo json_encode([
        'success' => true,
        'product_id' => $productId,
        'message' => 'Producto guardado exitosamente.'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar el producto: ' . $e->getMessage()
    ]);
}
