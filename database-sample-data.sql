-- DATOS DE EJEMPLO ADICIONALES PARA FORETHINK HEALTH
-- Este archivo es OPCIONAL - solo si quieres más datos de prueba

-- Insertar más productos de ejemplo
INSERT INTO products (category_id, name, slug, description, price, discount_price, stock, rating, is_featured) VALUES 
-- Medicina
(1, 'Paracetamol 500mg', 'paracetamol-500mg', 'Analgésico y antipirético de uso común', 12.00, 10.80, 200, 4.5, FALSE),
(1, 'Ibuprofeno 400mg', 'ibuprofeno-400mg', 'Antiinflamatorio no esteroideo', 15.00, NULL, 150, 4.0, TRUE),
(1, 'Amoxicilina 500mg', 'amoxicilina-500mg', 'Antibiótico de amplio espectro', 28.00, 25.20, 100, 4.5, FALSE),
(1, 'Omeprazol 20mg', 'omeprazol-20mg', 'Inhibidor de la bomba de protones', 22.00, NULL, 120, 4.0, FALSE),

-- Vitaminas y Suplementos
(2, 'Multivitamínico Completo', 'multivitaminico-completo', 'Complejo vitamínico para toda la familia', 35.00, 31.50, 180, 5.0, TRUE),
(2, 'Omega 3 Fish Oil', 'omega-3-fish-oil', 'Aceite de pescado rico en EPA y DHA', 42.00, NULL, 90, 4.5, FALSE),
(2, 'Vitamina D3 5000 UI', 'vitamina-d3-5000ui', 'Fortalece huesos y sistema inmune', 28.00, 25.20, 150, 4.5, TRUE),
(2, 'Magnesio 400mg', 'magnesio-400mg', 'Suplemento de magnesio quelado', 24.00, NULL, 130, 4.0, FALSE),
(2, 'Probióticos 10 Billones', 'probioticos-10-billones', 'Mejora la salud digestiva', 38.00, 34.20, 70, 5.0, TRUE),
(2, 'Zinc 50mg', 'zinc-50mg', 'Fortalece el sistema inmunológico', 18.00, NULL, 160, 4.0, FALSE),
(2, 'Colágeno Hidrolizado', 'colageno-hidrolizado', 'Para piel, cabello y articulaciones', 45.00, 40.50, 85, 4.5, TRUE),
(2, 'Coenzima Q10 100mg', 'coenzima-q10-100mg', 'Antioxidante celular potente', 52.00, NULL, 60, 5.0, FALSE);

-- Insertar más usuarios de prueba (clientes)
INSERT INTO users (email, password, full_name, phone, role) VALUES 
('cliente1@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González', '+52 123 456 7890', 'customer'),
('cliente2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', '+52 123 456 7891', 'customer'),
('cliente3@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Martínez', '+52 123 456 7892', 'customer');

-- Nota: Todos los usuarios de prueba tienen la contraseña: admin123

-- Insertar pedidos de ejemplo
INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, shipping_address, shipping_phone) VALUES
(2, 'ORD-2024-0001', 156.80, 'delivered', 'credit_card', 'Calle Principal 123, Col. Centro, Ciudad, CP 12345', '+52 123 456 7890'),
(3, 'ORD-2024-0002', 89.50, 'processing', 'paypal', 'Av. Reforma 456, Col. Norte, Ciudad, CP 12346', '+52 123 456 7891'),
(4, 'ORD-2024-0003', 234.00, 'shipped', 'cash_on_delivery', 'Calle 5 de Mayo 789, Col. Sur, Ciudad, CP 12347', '+52 123 456 7892'),
(2, 'ORD-2024-0004', 67.20, 'pending', 'credit_card', 'Calle Principal 123, Col. Centro, Ciudad, CP 12345', '+52 123 456 7890');

-- Insertar items de pedidos
INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES
-- Pedido 1
(1, 1, 'Health Medicine Red', 32.40, 2, 64.80),
(1, 7, 'Multivitamínico Completo', 31.50, 2, 63.00),
(1, 5, 'Paracetamol 500mg', 10.80, 1, 10.80),

-- Pedido 2
(2, 8, 'Omega 3 Fish Oil', 42.00, 1, 42.00),
(2, 10, 'Magnesio 400mg', 24.00, 2, 48.00),

-- Pedido 3
(3, 11, 'Probióticos 10 Billones', 34.20, 3, 102.60),
(3, 13, 'Colágeno Hidrolizado', 40.50, 2, 81.00),
(3, 6, 'Ibuprofeno 400mg', 15.00, 1, 15.00),

-- Pedido 4
(4, 9, 'Vitamina D3 5000 UI', 25.20, 2, 50.40),
(4, 12, 'Zinc 50mg', 18.00, 1, 18.00);

-- Insertar solicitudes de contacto de ejemplo
INSERT INTO contact_requests (name, phone, email, medicine, message, status) VALUES
('Roberto Sánchez', '+52 555 111 2222', 'roberto@example.com', 'medicine-1', '¿Tienen disponibilidad de este medicamento?', 'pending'),
('Laura Torres', '+52 555 333 4444', 'laura@example.com', 'general', 'Necesito información sobre envíos a provincia', 'contacted'),
('Carlos Ramírez', '+52 555 555 6666', 'carlos@example.com', 'prescription', 'Quiero hacer un pedido con receta médica', 'pending');

-- Insertar suscripciones al newsletter
INSERT INTO newsletter_subscriptions (email) VALUES
('subscriber1@example.com'),
('subscriber2@example.com'),
('subscriber3@example.com'),
('subscriber4@example.com'),
('subscriber5@example.com');

-- Actualizar estadísticas de productos (simulando ventas)
UPDATE products SET stock = stock - 5 WHERE id IN (1, 7, 9, 11);

-- Consultas útiles para verificar los datos

-- Ver todos los productos con sus categorías
-- SELECT p.name, c.name as category, p.price, p.stock FROM products p JOIN categories c ON p.category_id = c.id;

-- Ver todos los pedidos con información del cliente
-- SELECT o.order_number, u.full_name, o.total_amount, o.status, o.created_at FROM orders o JOIN users u ON o.user_id = u.id;

-- Ver productos más vendidos
-- SELECT p.name, COUNT(oi.id) as total_vendido FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY p.id ORDER BY total_vendido DESC;

-- Ver usuarios con más pedidos
-- SELECT u.full_name, COUNT(o.id) as total_pedidos FROM users u LEFT JOIN orders o ON u.id = o.user_id WHERE u.role = 'customer' GROUP BY u.id ORDER BY total_pedidos DESC;
