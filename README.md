# Forethink Health - Ecommerce de Medicinas

Sistema completo de ecommerce para venta de medicinas, vitaminas y suplementos con panel de administración.

## 🚀 Características

- ✅ Sistema de registro y login de usuarios
- ✅ Carrito de compras con almacenamiento local
- ✅ Panel de administración completo
- ✅ Gestión de productos y categorías
- ✅ Sistema de pedidos
- ✅ Formulario de contacto
- ✅ Newsletter
- ✅ Diseño responsive
- ✅ Base de datos MySQL

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx) - **Hostinger incluye esto**
- phpMyAdmin (para gestión de BD)

## 🛠️ Instalación en Hostinger

### Paso 1: Configurar la Base de Datos

1. Ve al panel de Hostinger
2. Abre **phpMyAdmin**
3. Selecciona tu base de datos (la que viste en la captura: `u851317150_...`)
4. Abre el archivo `database.sql` de este proyecto
5. Copia todo el contenido y pégalo en la pestaña SQL de phpMyAdmin
6. Haz clic en "Ejecutar"

### Paso 2: Configurar la Conexión a la Base de Datos

Edita el archivo `config/database.php`:

```php
define('DB_HOST', 'localhost'); // Normalmente es 'localhost'
define('DB_NAME', 'u851317150_forethink'); // Tu nombre de base de datos
define('DB_USER', 'u851317150_fh'); // Tu usuario de MySQL
define('DB_PASS', 'TU_CONTRASEÑA_AQUI'); // Tu contraseña de MySQL
```

**Para obtener estos datos en Hostinger:**
1. Panel de Hostinger → Sitios Web → tu sitio
2. Busca "Bases de datos MySQL"
3. Ahí verás el nombre de la base de datos, usuario y podrás ver/cambiar la contraseña

### Paso 3: Configurar la URL del Sitio

Edita el archivo `config/config.php`:

```php
define('BASE_URL', 'https://mediumvioletred-lobster-199641.hostingersite.com');
```

### Paso 4: Subir Archivos

#### Opción A: A través de GitHub (Recomendado)

1. Crea un repositorio en GitHub
2. Sube todos los archivos del proyecto
3. En Hostinger:
   - Ve a **Git** en el panel
   - Conecta tu repositorio de GitHub
   - Selecciona la rama (main/master)
   - Define la ruta de deployment: `/public_html`
   - Haz clic en "Deploy"

Ahora cada vez que hagas push a GitHub, se actualizará automáticamente en Hostinger.

#### Opción B: FTP Manual

1. Descarga un cliente FTP como FileZilla
2. Conecta usando las credenciales FTP de Hostinger
3. Sube todos los archivos a la carpeta `/public_html`

### Paso 5: Configurar Permisos

Asegúrate de que la carpeta `uploads/` tenga permisos de escritura (755 o 777):

```bash
chmod -R 755 uploads/
```

## 👤 Credenciales de Administrador por Defecto

Después de ejecutar el archivo SQL, puedes iniciar sesión como administrador:

- **Email:** `admin@forethinkhealth.com`
- **Contraseña:** `admin123`

**⚠️ IMPORTANTE: Cambia esta contraseña inmediatamente después del primer login.**

## 📁 Estructura del Proyecto

```
forethink-health/
├── admin/              # Panel de administración
│   └── index.php       # Dashboard del admin
├── api/                # APIs para AJAX
│   ├── contact.php     # Manejo de formulario de contacto
│   └── newsletter.php  # Suscripción al newsletter
├── assets/             # Recursos estáticos
│   ├── css/
│   │   └── style.css   # Estilos principales
│   ├── js/
│   │   └── main.js     # JavaScript principal
│   └── images/         # Imágenes del sitio
├── config/             # Configuración
│   ├── config.php      # Configuración general
│   └── database.php    # Conexión a base de datos
├── includes/           # Componentes reutilizables
│   ├── header.php      # Encabezado del sitio
│   └── footer.php      # Pie de página
├── uploads/            # Archivos subidos
│   └── products/       # Imágenes de productos
├── cart.php            # Página del carrito
├── index.php           # Página principal
├── login.php           # Inicio de sesión
├── register.php        # Registro de usuarios
├── products.php        # Catálogo de productos
├── logout.php          # Cerrar sesión
├── database.sql        # Script de base de datos
└── README.md           # Este archivo
```

## 🔧 Configuración de GitHub para Deployment Automático

### 1. Crear el repositorio

```bash
cd "e:\Users\gabri\Documentos\Brodev Lab\Clientes\Rafa work\forethink-health"
git init
git add .
git commit -m "Initial commit - Forethink Health Ecommerce"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/forethink-health.git
git push -u origin main
```

### 2. Conectar con Hostinger

1. En el panel de Hostinger, ve a **Git**
2. Haz clic en "Crear nuevo deployment"
3. Conecta tu cuenta de GitHub
4. Selecciona el repositorio `forethink-health`
5. Rama: `main`
6. Ruta de deployment: `/public_html`
7. Guarda y despliega

### 3. Actualizar el sitio

Cada vez que hagas cambios:

```bash
git add .
git commit -m "Descripción de los cambios"
git push
```

Hostinger detectará los cambios y actualizará automáticamente.

## 📧 Configuración del Email (Opcional)

Para que los formularios de contacto envíen emails, necesitas configurar SMTP:

Edita `config/config.php` y agrega:

```php
// Configuración de email
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu-email@tudominio.com');
define('SMTP_PASS', 'tu-contraseña');
```

## 🎨 Personalización

### Cambiar Colores

Edita `assets/css/style.css`:

```css
:root {
    --primary-color: #00d4ff;    /* Color principal */
    --secondary-color: #00bfe6;  /* Color secundario */
    --dark-bg: #1a1a1a;          /* Fondo oscuro */
}
```

### Agregar Productos

1. Inicia sesión como administrador
2. Ve a **Admin Panel → Productos**
3. Haz clic en "Agregar Nuevo Producto"
4. Completa la información y sube una imagen
5. Guarda

## 🐛 Solución de Problemas

### Error de conexión a la base de datos

- Verifica que los datos en `config/database.php` sean correctos
- Asegúrate de que la base de datos existe en Hostinger
- Verifica que el usuario tiene permisos

### Las imágenes no se muestran

- Verifica los permisos de la carpeta `uploads/`
- Asegúrate de que la ruta BASE_URL es correcta

### El sitio muestra una página en blanco

- Activa el modo debug en `config/config.php`:
  ```php
  define('DEBUG_MODE', true);
  ```
- Revisa los logs de error de PHP en Hostinger

## 📱 Funcionalidades del Sistema

### Para Clientes:
- Navegar catálogo de productos
- Buscar productos
- Filtrar por categorías
- Agregar al carrito
- Realizar pedidos
- Ver historial de pedidos
- Actualizar perfil

### Para Administradores:
- Dashboard con estadísticas
- Gestión de productos (crear, editar, eliminar)
- Gestión de categorías
- Ver y gestionar pedidos
- Gestión de usuarios
- Ver solicitudes de contacto
- Ver suscriptores del newsletter

## 📊 Base de Datos

El sistema incluye 8 tablas principales:

- `users` - Usuarios del sistema
- `categories` - Categorías de productos
- `products` - Productos
- `cart` - Carritos de compra
- `orders` - Pedidos
- `order_items` - Detalles de pedidos
- `contact_requests` - Solicitudes de contacto
- `newsletter_subscriptions` - Suscripciones al newsletter

## 🔒 Seguridad

- Contraseñas hasheadas con bcrypt
- Protección contra SQL injection (PDO prepared statements)
- Sanitización de inputs
- Validación de datos del lado del servidor
- Sesiones seguras
- Protección CSRF (pendiente implementar)

## 🚀 Próximas Mejoras

- [ ] Sistema de pagos (PayPal, Stripe)
- [ ] Seguimiento de envíos
- [ ] Sistema de reseñas de productos
- [ ] Wishlist
- [ ] Cupones de descuento
- [ ] Multi-idioma
- [ ] Búsqueda avanzada
- [ ] Filtros por precio
- [ ] Comparador de productos

## 📞 Soporte

Si tienes problemas con la instalación o configuración, revisa:

1. Documentación de Hostinger: https://support.hostinger.com
2. Logs de error en Hostinger
3. Activa el modo debug para ver errores detallados

## 📄 Licencia

Este proyecto es privado y desarrollado específicamente para Forethink Health.

---

**Desarrollado con ❤️ para Forethink Health**
