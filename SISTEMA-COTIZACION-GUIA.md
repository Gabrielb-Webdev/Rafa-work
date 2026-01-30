# Sistema de Cotización - Guía de Uso

## 📋 Resumen de Cambios

El sistema ha sido transformado de un e-commerce tradicional a un **sistema de cotización**, donde:

- ❌ Los productos NO muestran precios públicos
- 📦 Los usuarios hacen pedidos sin ver costos
- 💬 Cada pedido tiene un chat integrado
- 💰 El admin envía propuestas personalizadas con precios
- 📧 El usuario recibe email cuando hay una propuesta

---

## 🔧 Instalación de la Base de Datos

### Paso 1: Ejecutar el Script de Actualización

Ve a tu navegador y accede a:
```
http://tu-dominio.com/update-quotation-database.php
```

Esto creará automáticamente:
- Tabla `order_messages` para el chat de pedidos
- Campos nuevos en tabla `orders`: 
  - `proposal_sent`
  - `proposal_date`
  - `proposal_total`
  - `proposal_accepted`
  - `proposal_accepted_date`
- Campos nuevos en tabla `order_items`:
  - `proposed_price`
  - `proposed_subtotal`

✅ Verás un resumen visual de las operaciones realizadas.

---

## 👥 Flujo para el Usuario (Cliente)

### 1. Navegar Catálogo
- Los productos muestran el mensaje: **"📬 Solicita cotización"**
- No se muestran precios en la lista ni en el modal de detalle
- Pueden agregar productos al carrito normalmente

### 2. Carrito de Compras
- El carrito muestra los productos agregados
- En lugar de precios y totales, muestra:
  - "🔔 Pendiente de cotización"
  - Cantidad de cada producto
  - Mensaje informativo sobre el sistema de cotización

### 3. Finalizar Pedido (Checkout)
- El formulario captura:
  - Nombre completo
  - Teléfono
  - Dirección detallada (calle, número, colonia, ciudad, CP)
  - Notas adicionales
- **No se muestran totales ni costos**
- El pedido se guarda con estado "Pendiente"

### 4. Ver Mis Pedidos
- En `/orders.php` se listan todos los pedidos
- Si aún no hay propuesta: muestra "Pendiente cotización"
- Si ya hay propuesta: muestra el total propuesto
- Botón **"Ver Detalles"** para acceder al pedido

### 5. Detalle del Pedido y Chat
- Acceden a `/order-detail.php?id=X`
- Ven:
  - Lista de productos solicitados con cantidades
  - Estado del pedido
  - Información de contacto y envío
  - **Chat integrado** para comunicarse con el admin
  - Si hay propuesta: se muestra el precio de cada producto y el total

#### Características del Chat:
- Mensajes en tiempo real
- Distingue mensajes del usuario vs admin
- Los mensajes de propuesta se destacan visualmente
- Se puede escribir consultas sobre el pedido

---

## 👨‍💼 Flujo para el Admin

### 1. Ver Lista de Pedidos
- En `/admin/pedidos.php` se listan todos los pedidos
- Indicador visual si ya se envió propuesta
- Botón del ojo (👁️) para ver detalles

### 2. Detalle del Pedido (`/admin/ver-pedido.php`)

#### Panel Izquierdo:

**Si aún NO se envió propuesta:**
- Formulario para crear cotización con:
  - Precio unitario para cada producto
  - Cálculo automático de subtotales
  - Campo de costo de envío
  - Campo de descuento
  - Cálculo automático del total
  - Área de texto para mensaje personalizado
  - Botón **"Enviar Propuesta al Cliente"**

**Después de enviar propuesta:**
- Mensaje de confirmación con fecha de envío
- Lista de productos con precios propuestos
- Total de la propuesta destacado

#### Panel Derecho:
- **Chat integrado** del pedido
- Se pueden enviar mensajes al cliente
- Los mensajes de propuesta se destacan automáticamente

#### Secciones Informativas:
- Datos del cliente (nombre, email, teléfono)
- Dirección de envío completa
- Notas del pedido (si las hay)

### 3. Enviar Propuesta

Cuando el admin completa el formulario y hace clic en **"Enviar Propuesta"**:

1. Se actualizan los precios en la base de datos
2. Se calcula el total de la propuesta
3. Se crea un mensaje en el chat con el detalle
4. Se marca el pedido como "propuesta enviada"
5. **Se envía un EMAIL automático al cliente** con:
   - Tabla de productos y precios
   - Subtotal, envío, descuentos
   - Total de la propuesta
   - Link directo al pedido para ver el chat
   - Diseño profesional en HTML

---

## 📧 Sistema de Emails

### Configuración del Email

Los emails se envían usando la función `mail()` de PHP. Para que funcionen correctamente:

#### En Hostinger:
1. El email se envía desde: `noreply@tu-dominio.com`
2. Asegúrate de que tu hosting permita envío de emails
3. Los emails incluyen:
   - HTML estilizado
   - Logo y colores de marca
   - Tabla de productos
   - Total destacado
   - Link al pedido

#### Personalización:
El archivo `/api/send-proposal.php` contiene la plantilla del email.
Puedes modificar:
- Colores (línea 159: `#00d4d4`)
- Texto del encabezado
- Pie de página
- Estilos CSS

---

## 🗄️ Estructura de Archivos Modificados

### Archivos del Usuario:
```
products.php          → Catálogo sin precios
cart.php              → Carrito sin totales
checkout.php          → Checkout sin costos
orders.php            → Lista de pedidos
order-detail.php      → [NUEVO] Detalle con chat
```

### Archivos del Admin:
```
admin/ver-pedido.php  → Formulario de propuesta + chat
```

### APIs:
```
api/order-messages.php  → [NUEVO] Gestión de mensajes de chat
api/send-proposal.php   → [NUEVO] Envío de propuestas y emails
```

### Base de Datos:
```
update-quotation-database.php  → Script de actualización
update-quotation-system.sql    → SQL manual (si prefieres)
```

---

## 🎨 Características Visuales

### Indicadores de Estado:
- 🕐 **Amarillo**: Pendiente de cotización
- ✅ **Verde**: Propuesta enviada
- 📧 **Notificación**: Email enviado automáticamente

### Chat:
- Mensajes del admin: fondo gris, avatar morado
- Mensajes del usuario: fondo cyan, avatar cyan
- Mensajes de propuesta: fondo amarillo destacado

### Formulario de Propuesta (Admin):
- Cálculo automático de subtotales
- Total actualizado en tiempo real
- Validación de campos numéricos
- Confirmación antes de enviar

---

## 🔍 Casos de Uso

### Caso 1: Cliente Nuevo Solicita Productos
1. Cliente navega el catálogo
2. Agrega 3 productos diferentes
3. Va al carrito y ve los productos sin precios
4. Completa el checkout con sus datos
5. Recibe confirmación: "Pedido recibido"

### Caso 2: Admin Recibe y Cotiza
1. Admin ve nuevo pedido en panel de administración
2. Hace clic en "Ver detalles"
3. Revisa productos solicitados
4. Completa formulario con precios:
   - Producto A: $150 x 2 = $300
   - Producto B: $80 x 1 = $80
   - Producto C: $200 x 3 = $600
   - Envío: $50
   - **Total: $1,030**
5. Escribe mensaje: "Te conseguimos todo con 10% descuento!"
6. Hace clic en "Enviar Propuesta"

### Caso 3: Cliente Recibe y Consulta
1. Cliente recibe email con la propuesta
2. Hace clic en el link del email
3. Ve los precios propuestos en su pedido
4. Usa el chat para preguntar: "¿Cuándo me lo pueden enviar?"
5. Admin responde por el chat
6. Cliente acepta y se procede con el envío

---

## 🔐 Seguridad

- ✅ Verificación de sesión para chat y propuestas
- ✅ Usuarios solo ven sus propios pedidos
- ✅ Admin puede ver todos los pedidos
- ✅ Validación de permisos en todas las APIs
- ✅ Transacciones de BD para integridad de datos
- ✅ Escape de HTML para prevenir XSS

---

## 📊 Tablas de Base de Datos

### `orders`
Campos nuevos:
```sql
proposal_sent (TINYINT)          -- 0=No, 1=Sí
proposal_date (DATETIME)         -- Fecha de envío
proposal_total (DECIMAL)         -- Total propuesto
proposal_accepted (TINYINT)      -- Para futuras mejoras
proposal_accepted_date (DATETIME)
```

### `order_items`
Campos nuevos:
```sql
proposed_price (DECIMAL)     -- Precio propuesto por el admin
proposed_subtotal (DECIMAL)  -- Precio * cantidad
```

### `order_messages` [NUEVA TABLA]
```sql
id (INT)                -- ID del mensaje
order_id (INT)          -- ID del pedido
user_id (INT)           -- Quién envió (admin o usuario)
message (TEXT)          -- Contenido del mensaje
is_proposal (TINYINT)   -- 1 si es mensaje de propuesta
proposal_data (JSON)    -- Datos extra (opcional)
created_at (TIMESTAMP)  -- Fecha y hora
```

---

## 🚀 Próximas Mejoras Sugeridas

1. **Aceptación de Propuesta**
   - Botón "Aceptar Propuesta" en el chat
   - Cambio de estado automático
   - Notificación al admin

2. **Historial de Propuestas**
   - Si el admin modifica una propuesta
   - Guardar versiones anteriores

3. **Notificaciones Push**
   - Alertas cuando hay nuevos mensajes en el chat

4. **Dashboard de Estadísticas**
   - Tasa de aceptación de propuestas
   - Tiempo promedio de respuesta
   - Valor promedio de pedidos

5. **Integración con WhatsApp**
   - Enviar notificación de propuesta por WhatsApp
   - Link directo al chat del pedido

---

## 📞 Soporte

Si tienes dudas o necesitas ayuda con:
- Configuración de emails
- Personalización de diseños
- Agregar funcionalidades
- Debugging de errores

Puedes consultar los archivos de código que tienen comentarios explicativos.

---

## ✅ Checklist de Implementación

- [x] Ejecutar `update-quotation-database.php`
- [ ] Probar flujo completo como usuario
- [ ] Probar envío de propuesta como admin
- [ ] Verificar recepción de emails
- [ ] Probar chat en ambos lados
- [ ] Revisar diseño responsive en móvil
- [ ] Configurar email del dominio (opcional)

---

**¡Sistema listo para usar! 🎉**

El sistema de cotización está completamente operativo. Los usuarios pueden hacer pedidos, los admins pueden enviar propuestas personalizadas, y ambos pueden comunicarse por chat.
