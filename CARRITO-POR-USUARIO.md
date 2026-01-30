# Sistema de Carrito Individual por Usuario

## 📋 Descripción

Se ha implementado un sistema de carrito de compras personalizado donde cada usuario tiene su propio carrito independiente, vinculado a su cuenta en la base de datos.

## ✅ Características Implementadas

### 1. **Carrito Persistente en Base de Datos**
- Cada producto agregado al carrito se guarda en la tabla `cart` vinculado al `user_id`
- El carrito se mantiene aunque el usuario cierre sesión y vuelva a entrar
- Los usuarios no autenticados mantienen su carrito en sesión temporalmente

### 2. **Sincronización Automática**
- **Al Login**: Si el usuario tenía productos en el carrito de sesión (antes de loguearse), estos se sincronizan con su carrito en BD
- **Al Registro**: El carrito de sesión se transfiere automáticamente a la BD cuando el usuario crea una cuenta
- **En Cada Página**: El carrito se carga automáticamente desde la BD para usuarios autenticados

### 3. **Gestión de Stock**
- Verificación de stock disponible antes de agregar productos
- Reducción automática de stock al completar un pedido
- Validación de stock en tiempo real al actualizar cantidades

### 4. **Operaciones del Carrito**
Todas las operaciones se sincronizan entre sesión y BD:
- ✅ Agregar productos
- ✅ Actualizar cantidades
- ✅ Eliminar productos
- ✅ Vaciar carrito completo

## 🔧 Archivos Modificados

### 1. `api/cart.php`
**Cambios principales:**
- Detecta si el usuario está logueado (`$_SESSION['user_id']`)
- Nuevas funciones:
  - `getCartFromDB($userId)`: Carga el carrito del usuario desde BD
  - `saveCartItemToDB($userId, $productId, $quantity)`: Guarda/actualiza items en BD
  - `removeCartItemFromDB($userId, $productId)`: Elimina items de BD
- Todas las operaciones (add, update, remove, clear) ahora sincronizan con BD

### 2. `cart.php`
**Cambios principales:**
- Carga el carrito desde BD automáticamente si el usuario está logueado
- Mantiene sincronización entre sesión y BD

### 3. `login.php`
**Cambios principales:**
- Sincroniza carrito de sesión con BD al hacer login
- Si hay productos duplicados, suma las cantidades
- Carga el carrito completo desde BD después del login

### 4. `register.php`
**Cambios principales:**
- Transfiere automáticamente el carrito de sesión a BD al crear cuenta
- El usuario no pierde productos en el carrito al registrarse

### 5. `config/config.php`
**Cambios principales:**
- Nueva función: `loadCartFromDB()` para cargar carrito del usuario
- Carga automática del carrito al iniciar sesión (con marca `cart_loaded`)

### 6. `checkout.php`
**Cambios principales:**
- Limpia el carrito de BD al completar el pedido
- Reduce el stock de productos automáticamente
- Transacción completa para garantizar integridad

## 🗄️ Estructura de la Tabla Cart

```sql
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

## 🔄 Flujo de Funcionamiento

### Usuario No Autenticado
1. Agrega productos → Se guardan en `$_SESSION['cart']`
2. Los productos persisten mientras la sesión esté activa

### Usuario Se Registra/Loguea
1. Sistema detecta carrito en sesión
2. Productos se transfieren a la tabla `cart` en BD con su `user_id`
3. Carrito queda vinculado permanentemente al usuario

### Usuario Autenticado
1. Todas las operaciones se guardan en BD automáticamente
2. Al recargar cualquier página, el carrito se carga desde BD
3. El carrito está disponible en cualquier dispositivo donde se loguee

### Usuario Completa Pedido
1. Se crea el pedido en tabla `orders`
2. Se reducen las cantidades de stock de productos
3. Se limpia el carrito de sesión y BD
4. Usuario puede empezar un nuevo carrito

## ⚠️ Consideraciones Importantes

### Usuarios Múltiples
- ✅ Cada usuario tiene su carrito completamente independiente
- ✅ No hay interferencia entre carritos de diferentes usuarios
- ✅ El `user_id` garantiza la separación de datos

### Validación de Stock
- ✅ Se valida stock antes de agregar productos
- ✅ Se ajusta cantidad si excede el stock disponible
- ✅ Se reduce stock al confirmar pedido

### Seguridad
- ✅ Todas las consultas usan prepared statements
- ✅ Validación de `user_id` en cada operación
- ✅ Sanitización de entradas

## 🧪 Pruebas Recomendadas

1. **Carrito como Invitado**
   - Agregar productos sin estar logueado
   - Verificar que se mantienen en sesión

2. **Registro con Carrito**
   - Agregar productos como invitado
   - Registrarse
   - Verificar que productos se transfieren a BD

3. **Login con Carrito**
   - Agregar productos como invitado
   - Loguearse con cuenta existente
   - Verificar fusión de carritos

4. **Carrito Persistente**
   - Loguearse y agregar productos
   - Cerrar sesión
   - Volver a loguearse
   - Verificar que el carrito se mantiene

5. **Stock Management**
   - Verificar límite de stock al agregar
   - Completar pedido
   - Verificar reducción de stock en BD

6. **Múltiples Usuarios**
   - Usuario A agrega productos
   - Usuario B agrega productos diferentes
   - Verificar que cada uno ve solo sus productos

## 📝 Notas Técnicas

- El sistema mantiene retrocompatibilidad con usuarios no autenticados
- La carga del carrito desde BD ocurre solo una vez por sesión (optimización)
- Los errores de BD no bloquean el flujo, mantiene carrito de sesión como fallback
- La eliminación de usuario (CASCADE) limpia automáticamente su carrito
