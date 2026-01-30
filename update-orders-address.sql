-- Script para actualizar la tabla orders con campos de dirección separados
-- Ejecutar este script en phpMyAdmin

-- Primero, agregar las nuevas columnas
ALTER TABLE `orders` 
ADD COLUMN `street` VARCHAR(255) AFTER `shipping_phone`,
ADD COLUMN `street_number` VARCHAR(50) AFTER `street`,
ADD COLUMN `neighborhood` VARCHAR(255) AFTER `street_number`,
ADD COLUMN `city` VARCHAR(255) AFTER `neighborhood`,
ADD COLUMN `postal_code` VARCHAR(20) AFTER `city`;

-- Migrar datos existentes si los hay (intentar extraer de shipping_address)
-- UPDATE `orders` SET 
--     `street` = SUBSTRING_INDEX(`shipping_address`, ',', 1),
--     `city` = TRIM(SUBSTRING_INDEX(`shipping_address`, ',', -1))
-- WHERE `shipping_address` IS NOT NULL AND `shipping_address` != '';

-- Opcional: eliminar la columna antigua después de verificar que todo funciona
-- ALTER TABLE `orders` DROP COLUMN `shipping_address`;

-- Verificar la estructura
DESCRIBE `orders`;
