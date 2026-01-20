# Sistema de Gestión de Pedidos - Forethink Health

## ✅ Sistema Completado

El sistema completo de gestión de pedidos está implementado y funcional. Los precios están en **pesos argentinos (ARS)**.

## 📋 Flujo del Cliente

### 1. **Navegación de Productos** (`products.php`)
- El cliente visualiza todos los productos disponibles
- Puede agregar productos al carrito usando el botón "BUY NOW"
- Los productos se guardan en `localStorage` del navegador

### 2. **Carrito de Compras** (`cart.php`)
- Muestra todos los productos agregados
- Permite modificar cantidades
- Permite eliminar productos
- Muestra resumen:
  - **Subtotal**: Suma de todos los productos
  - **Envío**: 
    - GRATIS si el subtotal > $500 ARS
    - $50 ARS si el subtotal ≤ $500 ARS
  - **Total**: Subtotal + Envío

### 3. **Checkout** (`checkout.php`)
- **Requisito**: Usuario debe estar logueado
- Formulario con:
  - Información de contacto (nombre, email, teléfono)
  - Dirección de envío completa
  - Método de pago
  - Notas adicionales (opcional)
- Procesa el pedido:
  - Genera número único de pedido: `FTH-XXXXX-YYYY`
  - Inserta en base de datos (tabla `orders` y `order_items`)
  - Limpia el carrito
  - Redirige a confirmación

### 4. **Confirmación de Pedido** (`order-confirmation.php`)
- Página de éxito con animación
- Muestra:
  - Número de pedido
  - Total pagado
  - Cantidad de items
  - Estado inicial: **Pendiente**
  - Fecha del pedido
- Botones de acción:
  - Ver todos mis pedidos
  - Continuar comprando

### 5. **Mis Pedidos** (`orders.php`)
- Lista todos los pedidos del usuario
- Muestra para cada pedido:
  - Número de pedido
  - Estado con badge de color
  - Fecha
  - Total
  - Items del pedido
  - Dirección de envío

## 🔐 Panel de Administrador

### 1. **Dashboard** (`admin/index.php`)
- **Estadísticas**:
  - Total de productos
  - Total de pedidos
  - Pedidos pendientes (destacado en naranja)
  - Total de usuarios
  - Ventas totales en ARS
- **Tabla de Pedidos Recientes**:
  - Últimos 10 pedidos
  - Número, cliente, total, estado, fecha

### 2. **Gestión de Pedidos** (`admin/orders.php`)
- **Estadísticas por Estado**:
  - Pendientes
  - En Proceso
  - Enviados
  - Entregados
  - Ingresos totales (ARS)

- **Filtros**:
  - Todos / Pendientes / En Proceso / Enviados / Entregados
  - Búsqueda por número de pedido, nombre de cliente o email

- **Tabla Completa**:
  - Número de pedido
  - Cliente (nombre y email)
  - Fecha y hora
  - Cantidad de items
  - Total en ARS
  - Estado actual con badge
  - **Actualizar Estado**:
    - Dropdown con opciones:
      - Pendiente (naranja)
      - En Proceso (amarillo)
      - Enviado (azul)
      - Entregado (verde)
      - Cancelado (rojo)
    - Botón guardar para actualizar

## 📊 Base de Datos

### Tabla `orders`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- user_id (INT, FOREIGN KEY a users)
- order_number (VARCHAR, único, ej: FTH-12345-2024)
- full_name (VARCHAR)
- email (VARCHAR)
- phone (VARCHAR)
- address (TEXT)
- subtotal (DECIMAL)
- shipping (DECIMAL)
- total (DECIMAL)
- status (ENUM: pending, processing, shipped, delivered, cancelled)
- payment_method (VARCHAR)
- notes (TEXT, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Tabla `order_items`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- order_id (INT, FOREIGN KEY a orders)
- product_name (VARCHAR)
- product_price (DECIMAL)
- quantity (INT)
- subtotal (DECIMAL)
```

## 💰 Lógica de Precios

- **Moneda**: Pesos Argentinos (ARS)
- **Envío Gratis**: Compras mayores a $500 ARS
- **Costo de Envío**: $50 ARS para compras menores a $500 ARS
- **IVA**: No se aplica (eliminado del sistema)

## 🎨 Estados de Pedidos

| Estado | Color | Icono | Descripción |
|--------|-------|-------|-------------|
| Pendiente | Naranja (#ff9800) | Reloj de arena | Pedido recibido, esperando procesamiento |
| En Proceso | Amarillo (#ffc107) | Reloj | Pedido siendo preparado |
| Enviado | Azul (#17a2b8) | Camión | Pedido en tránsito |
| Entregado | Verde (#28a745) | Check | Pedido completado |
| Cancelado | Rojo (#dc3545) | X | Pedido cancelado |

## 🔄 Flujo Completo

```
CLIENTE                          ADMINISTRADOR
   │                                    │
   ├─► Ver productos                   │
   ├─► Agregar al carrito              │
   ├─► Ver carrito                     │
   ├─► Iniciar checkout                │
   ├─► Completar formulario            │
   ├─► Enviar pedido                   │
   │   (Estado: Pendiente)             │
   │                                    │
   │                          ◄─────── Ver nuevo pedido
   │                          ◄─────── Cambiar a "En Proceso"
   │                          ◄─────── Preparar pedido
   │                          ◄─────── Cambiar a "Enviado"
   ├─► Ver actualización               │
   │   (Estado: Enviado)               │
   │                                    │
   │                          ◄─────── Pedido entregado
   │                          ◄─────── Cambiar a "Entregado"
   ├─► Ver actualización               │
   │   (Estado: Entregado)             │
   └─► ✅ Proceso completo             └─► ✅ Pedido completado
```

## 🚀 Cómo Usar el Sistema

### Para Clientes:
1. Navegar a **ONLINE BUY** en el menú
2. Hacer clic en **BUY NOW** en los productos deseados
3. Ir al **carrito** (icono en la esquina superior derecha)
4. Revisar productos y hacer clic en **Proceder al Pago**
5. Si no está logueado, el sistema pedirá iniciar sesión
6. Completar el formulario de checkout
7. Hacer clic en **Realizar Pedido**
8. Ver confirmación con número de pedido
9. Ir a **Mis Pedidos** desde el menú de usuario para ver el estado

### Para Administradores:
1. Iniciar sesión con cuenta de administrador
2. Ir a **Panel Admin** desde el menú de usuario
3. Ver estadísticas en el dashboard
4. Hacer clic en **Pedidos** en el menú lateral
5. Filtrar pedidos por estado si es necesario
6. Seleccionar nuevo estado en el dropdown
7. Hacer clic en el botón guardar (💾)
8. El cliente verá el estado actualizado en su página de pedidos

## ✨ Características Especiales

- ✅ **Números de Pedido Únicos**: Generación automática con formato FTH-XXXXX-YYYY
- ✅ **Transacciones Seguras**: Uso de transacciones SQL para garantizar integridad
- ✅ **LocalStorage para Carrito**: Carrito persiste entre sesiones
- ✅ **Responsive Design**: Funciona en móviles, tablets y desktop
- ✅ **Búsqueda y Filtros**: Administrador puede encontrar pedidos rápidamente
- ✅ **Estadísticas en Tiempo Real**: Dashboard con métricas actualizadas
- ✅ **Badges de Estado**: Indicadores visuales claros con colores
- ✅ **Validación de Formularios**: Campos requeridos en checkout
- ✅ **Confirmación Visual**: Página de éxito con animación

## 📝 Notas Importantes

1. **SQL Ejecutado**: Las tablas `orders` y `order_items` ya están creadas en la base de datos
2. **Versiones Actuales**:
   - CSS: v6.3
   - JavaScript: v5.9
3. **Próximas Mejoras Opcionales**:
   - Notificaciones por email al cliente
   - Notificaciones por email al administrador
   - Historial de cambios de estado
   - Página de detalle del pedido para el admin
   - Impresión de pedidos
   - Exportación a Excel/PDF

## 🎯 Accesos Rápidos

- **Sitio Web**: https://mediumvioletred-lobster-199641.hostingersite.com
- **Base de Datos**: u851317150_fh
- **Panel Admin**: `/admin/index.php`
- **Gestión de Pedidos**: `/admin/orders.php`

---

**Sistema completado el**: <?php echo date('d/m/Y'); ?>
**Desarrollado para**: Forethink Health
**Moneda**: Pesos Argentinos (ARS)
