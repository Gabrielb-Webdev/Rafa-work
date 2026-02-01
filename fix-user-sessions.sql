-- ========================================
-- VERIFICAR Y CORREGIR USUARIOS
-- Version: 1.0 - 31/01/2026
-- ========================================

-- 1. Ver todos los usuarios
SELECT id, full_name, email, user_role, created_at 
FROM users 
ORDER BY id;

-- 2. Ver el ID más alto de usuarios
SELECT MAX(id) as max_user_id FROM users;

-- 3. Ver carritos con usuarios inexistentes
SELECT c.*, u.id as user_exists
FROM cart c
LEFT JOIN users u ON c.user_id = u.id
WHERE u.id IS NULL;

-- 4. Limpiar carritos de usuarios que ya no existen
DELETE c FROM cart c
LEFT JOIN users u ON c.user_id = u.id
WHERE u.id IS NULL;

-- 5. Verificar si existe el usuario con ID 12
SELECT * FROM users WHERE id = 12;

-- 6. Si no existe el usuario 12, ver qué usuarios sí existen
SELECT id, full_name, email FROM users WHERE user_role = 'admin';

-- 7. Ver todos los carritos actuales
SELECT 
    c.*,
    u.full_name,
    p.name as product_name
FROM cart c
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN products p ON c.product_id = p.id;

SELECT '✓ Verificación completada. Si el usuario ID 12 no existe, debes iniciar sesión con un usuario válido.' as mensaje;
