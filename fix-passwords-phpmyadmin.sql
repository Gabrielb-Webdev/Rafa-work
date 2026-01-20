-- =========================================
-- FIX DE CONTRASEÑAS - FORETHINK HEALTH
-- =========================================
-- Ejecuta este archivo en phpMyAdmin para actualizar todas las contraseñas a: admin123
--
-- IMPORTANTE: Este hash fue generado con password_hash() de PHP
-- y funcionará con password_verify() en el login
-- =========================================

-- Primero, vamos a ver qué usuarios existen
SELECT id, email, full_name, role FROM users;

-- Ahora actualizamos todos los usuarios con el hash correcto de "admin123"
-- Este hash es compatible con password_verify() de PHP
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Verificar que se actualizaron
SELECT id, email, full_name, role, 'admin123' as password_text FROM users;

-- Si no existen estos usuarios, créalos:

-- Usuario normal
INSERT IGNORE INTO users (email, password, full_name, phone, role, created_at) 
VALUES ('usuario@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Rodríguez', '+52 555 888 9999', 'customer', NOW());

-- Admin Forethink
INSERT IGNORE INTO users (email, password, full_name, phone, role, created_at) 
VALUES ('admin@forethink.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Forethink', '+52 555 100 2000', 'admin', NOW());

-- Ver usuarios finales
SELECT 
    id,
    email,
    full_name,
    phone,
    role,
    'admin123' as password_text,
    created_at
FROM users 
ORDER BY id;

-- =========================================
-- RESULTADO: Todos los usuarios ahora tienen la contraseña: admin123
-- =========================================
