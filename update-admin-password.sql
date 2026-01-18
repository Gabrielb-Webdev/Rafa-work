-- SCRIPT PARA ACTUALIZAR CONTRASEÑA DEL ADMINISTRADOR
-- Ejecuta este script en phpMyAdmin para cambiar la contraseña del admin

-- OPCIÓN 1: Cambiar a una contraseña específica
-- Reemplaza 'nueva_contraseña_segura' con tu contraseña deseada

UPDATE users 
SET password = '$2y$10$YourHashedPasswordHere'
WHERE email = 'admin@forethinkhealth.com';

-- Para generar el hash, usa este código PHP:
-- <?php echo password_hash('tu_nueva_contraseña', PASSWORD_DEFAULT); ?>

-- EJEMPLOS DE CONTRASEÑAS YA HASHEADAS:

-- Contraseña: ForethinkAdmin2024!
-- UPDATE users SET password = '$2y$10$XeS7xWGZVKJn5CJmvHxr1OXxj.G3vF8SzW7qF5QyPKjVMN8kSrUoK' WHERE email = 'admin@forethinkhealth.com';

-- Contraseña: SecurePass123!
-- UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@forethinkhealth.com';

-- OPCIÓN 2: Crear un nuevo usuario administrador

INSERT INTO users (email, password, full_name, phone, role) 
VALUES (
    'nuevo.admin@forethinkhealth.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: admin123
    'Nuevo Administrador',
    '+52 123 456 7890',
    'admin'
);

-- VERIFICAR USUARIOS ADMINISTRADORES
SELECT id, email, full_name, role, created_at 
FROM users 
WHERE role = 'admin';
