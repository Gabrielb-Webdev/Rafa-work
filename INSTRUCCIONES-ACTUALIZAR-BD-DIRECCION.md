# INSTRUCCIONES PARA ACTUALIZAR LA BASE DE DATOS

## Paso 1: Ejecutar el script SQL

1. Abre phpMyAdmin
2. Selecciona tu base de datos (u851317150_fh)
3. Ve a la pestaña "SQL"
4. Copia y pega el siguiente código:

```sql
-- Agregar nuevas columnas para dirección separada
ALTER TABLE `orders` 
ADD COLUMN `street` VARCHAR(255) AFTER `phone`,
ADD COLUMN `street_number` VARCHAR(50) AFTER `street`,
ADD COLUMN `neighborhood` VARCHAR(255) AFTER `street_number`,
ADD COLUMN `city` VARCHAR(255) AFTER `neighborhood`,
ADD COLUMN `postal_code` VARCHAR(20) AFTER `city`;

-- Verificar que se agregaron correctamente
DESCRIBE `orders`;
```

5. Haz clic en "Continuar"

## Paso 2: Opcional - Eliminar columna antigua

**IMPORTANTE:** Solo ejecuta esto DESPUÉS de verificar que todo funciona correctamente.

```sql
-- Eliminar la columna antigua de address (solo si ya no la necesitas)
ALTER TABLE `orders` DROP COLUMN `address`;
```

## Paso 3: Verificación

Después de ejecutar el SQL, los pedidos nuevos se guardarán con:
- `street` (Calle)
- `street_number` (Número)
- `neighborhood` (Colonia)
- `city` (Ciudad)
- `postal_code` (Código Postal)

## Nota Importante

Los pedidos existentes pueden tener la columna `address` con datos antiguos. Si necesitas migrar esos datos a las nuevas columnas, tendrías que hacerlo manualmente o crear un script específico para tu caso.

Para pedidos nuevos, el formulario ahora solicita cada campo por separado y funcionará perfectamente.
