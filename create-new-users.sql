-- CREAR NUEVOS USUARIOS PARA FORETHINK HEALTH
-- Ejecutar este archivo para agregar los nuevos usuarios

-- Usuario normal (cliente)
INSERT INTO users (email, password, full_name, phone, role, created_at) VALUES 
('usuario@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Rodríguez', '+52 555 888 9999', 'customer', NOW());

-- Usuario administrador
INSERT INTO users (email, password, full_name, phone, role, created_at) VALUES 
('admin@forethink.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Forethink', '+52 555 100 2000', 'admin', NOW());

-- INFORMACIÓN DE USUARIOS:
-- =====================================================
-- USUARIO EXISTENTE (del database-sample-data.sql):
-- Email: admin@test.com
-- Password: admin123
-- Rol: admin
-- Nombre: Administrator
--
-- Email: cliente1@test.com
-- Password: admin123
-- Rol: customer
-- Nombre: María González
--
-- Email: cliente2@test.com
-- Password: admin123
-- Rol: customer
-- Nombre: Juan Pérez
--
-- Email: cliente3@test.com
-- Password: admin123
-- Rol: customer
-- Nombre: Ana Martínez
-- =====================================================
--
-- NUEVOS USUARIOS:
-- =====================================================
-- USUARIO NORMAL:
-- Email: usuario@test.com
-- Password: admin123
-- Rol: customer
-- Nombre: Carlos Rodríguez
-- Teléfono: +52 555 888 9999
--
-- USUARIO ADMIN:
-- Email: admin@forethink.com
-- Password: admin123
-- Rol: admin
-- Nombre: Admin Forethink
-- Teléfono: +52 555 100 2000
-- =====================================================
--
-- NOTA: Todos los usuarios tienen la misma contraseña: admin123
-- Se recomienda cambiar las contraseñas en producción
