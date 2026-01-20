# Panel de Administración - Forethink Health

## ✅ Sistema Completo Implementado

Todas las páginas del panel de administración han sido creadas y están funcionales.

## 📋 Páginas del Panel Admin

### 1. **Dashboard** (`admin/index.php`)
- **URL**: `/admin/index.php`
- **Funciones**:
  - Vista general con estadísticas principales
  - Total de productos, pedidos, usuarios
  - Pedidos pendientes destacados
  - Ventas totales en ARS
  - Tabla de pedidos recientes
- **Acceso**: Solo administradores

### 2. **Gestión de Productos** (`admin/products.php`) ✨ NUEVO
- **URL**: `/admin/products.php`
- **Funciones**:
  - ✅ Listar todos los productos con imagen, precio, stock
  - ✅ Agregar nuevo producto (con carga de imagen)
  - ✅ Editar producto existente
  - ✅ Eliminar producto
  - ✅ Filtrar por estado (Activos/Inactivos)
  - ✅ Buscar por nombre o categoría
  - ✅ Estadísticas: Total, Activos, Bajo Stock, Valor Inventario
  - ✅ Badge de stock (Verde/Amarillo/Rojo según cantidad)
  - ✅ Modal para agregar/editar con formulario completo
- **Campos del producto**:
  - Nombre, Descripción, Precio (ARS), Stock, Categoría
  - Imagen (subida a `/uploads/products/`)
  - Estado (Activo/Inactivo)

### 3. **Gestión de Categorías** (`admin/categories.php`) ✨ NUEVO
- **URL**: `/admin/categories.php`
- **Funciones**:
  - ✅ Listar categorías en tarjetas elegantes
  - ✅ Agregar nueva categoría
  - ✅ Editar categoría existente
  - ✅ Eliminar categoría (valida si tiene productos)
  - ✅ Ver cantidad de productos por categoría
  - ✅ Estado activo/inactivo
  - ✅ Estadísticas: Total categorías, Activas
- **Validaciones**:
  - No permite eliminar categorías con productos asociados
  - Nombres únicos de categoría

### 4. **Gestión de Pedidos** (`admin/orders.php`)
- **URL**: `/admin/orders.php`
- **Funciones**:
  - ✅ Listar todos los pedidos de clientes
  - ✅ Filtrar por estado (Pendiente, En Proceso, Enviado, Entregado)
  - ✅ Buscar por número de pedido, cliente o email
  - ✅ Actualizar estado del pedido con dropdown
  - ✅ Ver detalles completos de cada pedido
  - ✅ Estadísticas por estado
  - ✅ Ingresos totales en ARS
- **Estados de pedidos**:
  - 🟠 Pendiente
  - 🟡 En Proceso
  - 🔵 Enviado
  - 🟢 Entregado
  - 🔴 Cancelado

### 5. **Gestión de Usuarios** (`admin/users.php`) ✨ NUEVO
- **URL**: `/admin/users.php`
- **Funciones**:
  - ✅ Listar todos los usuarios (clientes y admins)
  - ✅ Agregar nuevo usuario
  - ✅ Editar información de usuario
  - ✅ Cambiar contraseña de usuario
  - ✅ Cambiar rol (Cliente/Administrador)
  - ✅ Eliminar usuario (valida si tiene pedidos)
  - ✅ Filtrar por rol (Todos/Clientes/Admins)
  - ✅ Buscar por nombre o email
  - ✅ Ver cantidad de pedidos y total gastado por usuario
- **Información de usuario**:
  - Nombre completo, Email, Teléfono
  - Rol (Cliente/Administrador)
  - Dirección, Contraseña
  - Estadísticas de compras
- **Validaciones**:
  - No permite eliminar usuario con pedidos
  - No permite que el admin se elimine a sí mismo
  - Contraseñas con hash bcrypt

### 6. **Mensajes de Contacto** (`admin/contacts.php`) ✨ NUEVO
- **URL**: `/admin/contacts.php`
- **Funciones**:
  - ✅ Ver todos los mensajes del formulario de contacto
  - ✅ Marcar como leído/no leído
  - ✅ Ver mensaje completo en modal
  - ✅ Eliminar mensaje
  - ✅ Filtrar por estado (Todos/No Leídos/Leídos)
  - ✅ Buscar por nombre, email o asunto
  - ✅ Estadísticas: Total, Sin Leer, Leídos
  - ✅ Auto-marcar como leído al visualizar
- **Vista de mensajes**:
  - Avatar con inicial
  - Nombre y email del remitente
  - Asunto y preview del mensaje
  - Fecha y hora
  - Estado visual (No leído con borde cyan)

### 7. **Newsletter - Suscriptores** (`admin/newsletter.php`) ✨ NUEVO
- **URL**: `/admin/newsletter.php`
- **Funciones**:
  - ✅ Listar todos los suscriptores del newsletter
  - ✅ Ver fecha de suscripción
  - ✅ Copiar email individual
  - ✅ Eliminar suscriptor
  - ✅ Exportar todos los emails a CSV
  - ✅ Buscar por email
  - ✅ Estadísticas: Total, Hoy, Esta Semana, Este Mes
- **Exportación CSV**:
  - Formato: Email, Fecha de Suscripción
  - Nombre archivo: `newsletter-subscribers-YYYY-MM-DD.csv`
  - Listo para importar en servicios de email marketing

## 🗄️ Base de Datos

### Tablas Creadas
El archivo `create-admin-tables.sql` contiene:

1. **`products`** - Productos del catálogo
   - id, name, description, price, stock, category, image, is_active, created_at, updated_at

2. **`categories`** - Categorías de productos
   - id, name, description, is_active, created_at, updated_at

3. **`contacts`** - Mensajes del formulario de contacto
   - id, name, email, subject, message, is_read, created_at

4. **`newsletter`** - Suscriptores del newsletter
   - id, email, created_at

### Datos de Ejemplo
El archivo SQL incluye:
- 5 categorías de ejemplo (Suplementos, Vitaminas, Proteínas, etc.)
- 5 productos de ejemplo con precios en ARS

## 🚀 Instrucciones de Instalación

### Paso 1: Ejecutar SQL
1. Abrir phpMyAdmin
2. Seleccionar base de datos: `u851317150_fh`
3. Ir a pestaña "SQL"
4. Copiar y pegar contenido de `create-admin-tables.sql`
5. Hacer clic en "Continuar"

### Paso 2: Subir Archivos
Todos los archivos ya están creados en la carpeta `/admin/`:
- ✅ `index.php` - Dashboard
- ✅ `products.php` - Gestión de productos
- ✅ `categories.php` - Gestión de categorías
- ✅ `orders.php` - Gestión de pedidos
- ✅ `users.php` - Gestión de usuarios
- ✅ `contacts.php` - Mensajes de contacto
- ✅ `newsletter.php` - Suscriptores

### Paso 3: Crear Carpeta de Uploads
Asegurarse de que existe la carpeta:
```
/uploads/products/
```
Con permisos de escritura (777 o 755)

## 🎨 Características del Diseño

### Interfaz Unificada
- ✅ Diseño consistente en todas las páginas
- ✅ Color principal: Cyan (#00d4d4)
- ✅ Tarjetas con sombras suaves
- ✅ Badges de estado con colores
- ✅ Iconos Font Awesome 6
- ✅ Responsive design para móviles
- ✅ Modales elegantes para formularios

### Componentes Comunes
- **Header**: Título + botón volver
- **Stats Grid**: Estadísticas en tarjetas
- **Filtros**: Botones de filtro + búsqueda
- **Tablas**: Headers fijos, hover effects
- **Modales**: Formularios con validación
- **Alerts**: Mensajes de éxito/error
- **Empty States**: Mensaje cuando no hay datos

## 📊 Funcionalidades Destacadas

### 1. **Gestión de Productos**
- Subida de imágenes con preview
- Gestión de stock con alertas visuales
- Categorización automática
- Filtros y búsqueda avanzada

### 2. **Sistema de Pedidos**
- Estados actualizables en tiempo real
- Filtros por estado
- Búsqueda por múltiples criterios
- Estadísticas de ingresos

### 3. **Gestión de Usuarios**
- Roles (Admin/Cliente)
- Estadísticas de compras por usuario
- Cambio de contraseña seguro
- Validaciones de seguridad

### 4. **Mensajes de Contacto**
- Sistema de leído/no leído
- Vista previa y completa
- Auto-actualización de estado
- Filtros inteligentes

### 5. **Newsletter**
- Exportación a CSV
- Estadísticas temporales
- Copiar emails individuales
- Fecha de suscripción

## 🔐 Seguridad

- ✅ Verificación de rol administrador en todas las páginas
- ✅ Contraseñas con hash bcrypt
- ✅ Validación de entrada en formularios
- ✅ Prevención de SQL injection (PDO prepared statements)
- ✅ Escape de HTML (htmlspecialchars)
- ✅ Validación de permisos de archivo

## 🎯 Flujo de Trabajo

### Para Productos:
1. Admin crea categorías
2. Admin agrega productos con imágenes
3. Clientes ven productos en `/products.php`
4. Clientes agregan al carrito
5. Admin gestiona inventario

### Para Pedidos:
1. Cliente realiza pedido (checkout)
2. Admin ve pedido nuevo (Pendiente)
3. Admin cambia estado a "En Proceso"
4. Admin cambia estado a "Enviado"
5. Admin marca como "Entregado"
6. Cliente ve actualizaciones en tiempo real

### Para Comunicación:
1. Clientes envían mensajes desde `/contact.php`
2. Clientes se suscriben al newsletter
3. Admin ve mensajes en `/admin/contacts.php`
4. Admin exporta suscriptores para campañas
5. Admin usa emails para marketing

## 📱 Responsive Design

Todas las páginas son totalmente responsive:
- ✅ Desktop (1200px+)
- ✅ Tablet (768px - 1199px)
- ✅ Móvil (< 768px)

Características móviles:
- Scroll horizontal en tablas
- Menús colapsables
- Botones táctiles grandes
- Grid adaptativo

## 🔄 Próximas Mejoras Opcionales

1. **Dashboard con Gráficos**:
   - Gráfico de ventas por mes (Chart.js)
   - Productos más vendidos
   - Tendencias de pedidos

2. **Gestión de Productos Mejorada**:
   - Múltiples imágenes por producto
   - Galería de productos
   - Variantes (tallas, colores)

3. **Sistema de Notificaciones**:
   - Emails automáticos al recibir pedido
   - Notificaciones push para admin
   - SMS de confirmación

4. **Reportes Avanzados**:
   - Reporte de ventas PDF
   - Análisis de clientes
   - Productos con mejor rendimiento

5. **Integración de Pagos**:
   - MercadoPago
   - Stripe
   - PayPal

## 📞 Soporte

Para cualquier duda o problema:
- Revisar los archivos SQL en `/create-admin-tables.sql`
- Verificar permisos de carpetas
- Comprobar credenciales de base de datos en `/config/database.php`

---

**Panel Admin Completo** ✅
**Fecha**: 20 de Enero de 2026
**Versión**: 1.0
