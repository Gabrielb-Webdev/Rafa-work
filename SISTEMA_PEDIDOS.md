# 📦 Sistema de Pedidos MediCareOnline

## 🔗 Link de Configuración de Base de Datos

**URL:** https://mediumvioletred-lobster-199641.hostingersite.com/setup_pharmacy.php

**Contraseña:** `MediCare2026`

---

## 📋 ¿Cómo funciona el Sistema de Pedidos?

### 1. **Proceso del Cliente:**
```
1. El cliente navega y selecciona productos
2. Agrega productos al carrito
3. Revisa el carrito y selecciona método de envío
4. Completa el formulario de checkout con:
   - Nombre y apellido
   - Email
   - Teléfono
   - Dirección (si es envío a domicilio)
   - Notas adicionales (opcional)
5. Confirma el PEDIDO (no hay pago online)
6. Recibe número de pedido único
```

### 2. **NO hay pago en línea:**
- El sistema NO procesa pagos
- El sistema SOLO genera pedidos
- Los clientes NO pagan al hacer el pedido
- Los pedidos quedan en estado "Pendiente"

### 3. **Gestión del Administrador:**
```
Admin Panel > Pedidos (Orders)
```

**Estados de Pedidos:**
- ✅ `pending` - Pendiente (nuevo pedido)
- ✅ `processing` - En proceso (confirmado por admin)
- ✅ `shipped` - Enviado
- ✅ `delivered` - Entregado
- ❌ `cancelled` - Cancelado

---

## 🔧 Mejoras Implementadas

### ✅ 1. **Proceso de Checkout Simplificado**
- Eliminada la selección de método de pago
- Solo se requiere información de contacto y envío
- Campo de notas adicionales para instrucciones especiales

### ✅ 2. **Sistema de Pedidos (No Compras)**
- Los pedidos se guardan con estado "pending"
- NO se descuenta stock automáticamente
- El admin debe confirmar y procesar cada pedido
- Número de pedido único: `PEDIDO-YYYYMMDD-XXXXXX`

### ✅ 3. **Página de Confirmación Actualizada**
- Muestra mensaje claro: "Tu pedido ha sido recibido"
- Indica que el equipo contactará al cliente
- Muestra número de pedido para seguimiento
- Detalle completo del pedido

### ✅ 4. **Base de Datos Actualizada**
```sql
-- Tabla orders
ALTER TABLE orders ADD COLUMN notes TEXT AFTER status;

-- Campo payment_method ahora se usa como "estado de confirmación"
-- payment_status siempre será "pending" para pedidos
```

---

## 📊 Tablas de Base de Datos Requeridas

El script `setup_pharmacy.php` creará automáticamente:

### 1. **products** - Productos del catálogo
- Medicamentos, vitaminas, suplementos
- Precios, stock, descripciones
- Imágenes y categorías

### 2. **orders** - Pedidos de clientes
- Información del cliente
- Dirección de envío
- Estado del pedido
- Totales y descuentos

### 3. **order_items** - Items de cada pedido
- Productos del pedido
- Cantidades y precios
- Subtotales

### 4. **categories** - Categorías farmacéuticas
- Medicine & Health
- Vitamins & Supplements
- Personal Care
- etc.

### 5. **brands** - Marcas farmacéuticas
- Pfizer, Bayer, Johnson & Johnson, etc.

### 6. **cart_sessions** - Carritos activos
- Carritos temporales de usuarios
- Persistencia de sesión

### 7. **users** - Usuarios registrados
- Datos de clientes
- Historial de pedidos

### 8. **coupons** - Cupones de descuento
- Descuentos porcentuales o fijos
- Fecha de vigencia
- Límites de uso

---

## 🚀 Pasos para Configurar

### **Paso 1: Ejecutar Setup**
1. Abre: https://mediumvioletred-lobster-199641.hostingersite.com/setup_pharmacy.php
2. Ingresa contraseña: `MediCare2026`
3. Haz clic en "Ejecutar Migración"
4. Espera confirmación de éxito

### **Paso 2: Verificar Creación**
1. Ve a phpMyAdmin
2. Selecciona la base de datos `u851317150_mg360_db`
3. Verifica que existan las tablas:
   - products
   - orders
   - order_items
   - categories
   - brands
   - users
   - coupons
   - cart_sessions

### **Paso 3: Revisar Productos de Ejemplo**
1. Abre: https://mediumvioletred-lobster-199641.hostingersite.com/
2. Verás productos de ejemplo en:
   - MEDICINE & HEALTH
   - VITAMINS & SUPPLEMENTS

### **Paso 4: Probar el Sistema**
1. Agrega productos al carrito
2. Ve a checkout
3. Completa el formulario
4. Confirma el pedido
5. Recibirás número de pedido

### **Paso 5: Panel de Administración**
1. Accede a: https://mediumvioletred-lobster-199641.hostingersite.com/admin/
2. Inicia sesión con las credenciales que creates
3. Ve a "Pedidos" (Orders)
4. Gestiona los pedidos recibidos

---

## 💡 Flujo de Trabajo Recomendado

### **Para el Administrador:**

1. **Recibe Pedido Nuevo** → Estado: `pending`
   - Revisa detalles del pedido
   - Verifica disponibilidad de stock
   - Contacta al cliente si es necesario

2. **Confirma Pedido** → Estado: `processing`
   - Prepara los productos
   - Genera factura/comprobante
   - Descuenta stock manualmente

3. **Envía Pedido** → Estado: `shipped`
   - Coordina envío o retiro
   - Notifica al cliente
   - Proporciona tracking (si aplica)

4. **Completa Pedido** → Estado: `delivered`
   - Confirma recepción
   - Cobra pago (si aplica)
   - Cierra el pedido

### **Para el Cliente:**

1. **Hace Pedido Online**
   - Sin necesidad de pago inmediato
   - Recibe número de seguimiento

2. **Espera Confirmación**
   - Admin contacta para confirmar
   - Se coordina forma de pago

3. **Recibe o Retira Pedido**
   - Según método de envío elegido
   - Paga en ese momento (o según acuerdo)

---

## 📧 Notificaciones (Futuro)

### Sugerencias de Notificaciones Automáticas:

- ✉️ Email al cliente cuando se crea el pedido
- ✉️ Email al admin cuando hay nuevo pedido
- ✉️ Email al cliente cuando cambia estado del pedido
- 📱 WhatsApp API para notificaciones rápidas

---

## ⚙️ Configuración Adicional

### Métodos de Envío Disponibles:
- 🏪 **Retiro en tienda** - Sin costo
- 📦 **Envío a domicilio** - Costo variable según zona
- 🚚 **Correo** - Envío por correo argentino
- 🏍️ **Moto en CABA** - Entrega rápida en Capital Federal

### Personalización:
Puedes modificar los métodos de envío en:
- `carrito.php` - Sección de métodos de envío
- `ajax/set-shipping.php` - Cálculo de costos

---

## 🔒 Seguridad

### Recomendaciones:
1. **Eliminar setup_pharmacy.php** después de ejecutarlo
2. **Cambiar contraseña** del admin inmediatamente
3. **Usar HTTPS** (ya está en Hostinger)
4. **Backup periódico** de la base de datos

---

## 📞 Soporte

Si tienes problemas:
1. Verifica que todas las tablas se crearon correctamente
2. Revisa los logs de PHP en Hostinger
3. Comprueba permisos de archivos y directorios
4. Verifica conexión a base de datos en `config/database.php`

---

**Última actualización:** 8 de Enero de 2026
**Versión:** 2.0 - Sistema de Pedidos MediCareOnline
