-- Agregar columna proposed_quantity a order_items si no existe
ALTER TABLE order_items 
ADD COLUMN IF NOT EXISTS proposed_quantity INT NULL AFTER proposed_price;

-- Inicializar con las cantidades originales para pedidos existentes
UPDATE order_items 
SET proposed_quantity = quantity 
WHERE proposed_quantity IS NULL AND proposed_price IS NOT NULL;
