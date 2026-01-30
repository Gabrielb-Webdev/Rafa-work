-- Script para actualizar el sistema a modelo de cotización
-- Crear tabla para mensajes/chat de pedidos

CREATE TABLE IF NOT EXISTS order_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_proposal TINYINT(1) DEFAULT 0,
    proposal_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campos a la tabla orders para el sistema de propuestas
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS proposal_sent TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS proposal_date DATETIME NULL,
ADD COLUMN IF NOT EXISTS proposal_total DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS proposal_accepted TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS proposal_accepted_date DATETIME NULL;

-- Agregar campos a order_items para precios propuestos
ALTER TABLE order_items
ADD COLUMN IF NOT EXISTS proposed_price DECIMAL(10,2) NULL,
ADD COLUMN IF NOT EXISTS proposed_subtotal DECIMAL(10,2) NULL;
