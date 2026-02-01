-- ========================================
-- VERIFICACIÓN Y REPARACIÓN TABLA CART
-- Version: 1.0 - 31/01/2026
-- ========================================

-- 1. Ver estructura actual de la tabla
DESCRIBE cart;

-- 2. Ver todos los registros actuales
SELECT * FROM cart;

-- 3. Verificar si hay productos huérfanos (sin producto asociado)
SELECT c.*, p.id as product_exists 
FROM cart c 
LEFT JOIN products p ON c.product_id = p.id 
WHERE p.id IS NULL;

-- 4. Limpiar productos huérfanos si existen
DELETE c FROM cart c 
LEFT JOIN products p ON c.product_id = p.id 
WHERE p.id IS NULL;

-- 5. Ver carrito por usuario
SELECT 
    c.id,
    c.user_id,
    u.full_name,
    c.product_id,
    p.name as product_name,
    c.quantity,
    c.created_at,
    c.updated_at
FROM cart c
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN products p ON c.product_id = p.id
ORDER BY c.user_id, c.created_at DESC;

-- 6. Estadísticas del carrito
SELECT 
    COUNT(DISTINCT user_id) as usuarios_con_carrito,
    COUNT(*) as total_items,
    SUM(quantity) as total_productos
FROM cart;

-- 7. Si necesitas recrear la tabla (¡CUIDADO! esto borra todos los datos)
-- DROP TABLE IF EXISTS cart;
-- CREATE TABLE IF NOT EXISTS cart (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     user_id INT,
--     session_id VARCHAR(255),
--     product_id INT NOT NULL,
--     quantity INT DEFAULT 1,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
--     FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
--     INDEX idx_user (user_id),
--     INDEX idx_session (session_id)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Limpiar carrito de un usuario específico (cambiar el 13 por el ID del usuario)
-- DELETE FROM cart WHERE user_id = 13;

-- 9. Agregar un producto manualmente para probar (cambiar IDs según necesites)
-- INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) 
-- VALUES (13, 3, 1, NOW(), NOW());

SELECT 'Verificación completada. Revisa los resultados arriba.' as mensaje;
