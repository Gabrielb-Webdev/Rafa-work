# GUÍA RÁPIDA DE INSTALACIÓN - FORETHINK HEALTH

## ⚡ Pasos Rápidos para Hostinger

### 1️⃣ CONFIGURAR BASE DE DATOS (5 minutos)

1. Abre **phpMyAdmin** en Hostinger
2. Selecciona tu base de datos
3. Ve a la pestaña **SQL**
4. Copia y pega el contenido del archivo `database.sql`
5. Haz clic en **Ejecutar**

### 2️⃣ CONFIGURAR CONEXIÓN (2 minutos)

Edita el archivo: `config/database.php`

Busca estas líneas y reemplaza con tus datos de Hostinger:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u851317150_forethink');  // ← Tu nombre de BD
define('DB_USER', 'u851317150_fh');          // ← Tu usuario
define('DB_PASS', 'TU_CONTRASEÑA');          // ← Tu contraseña
```

**¿Dónde encuentro estos datos?**
- Panel de Hostinger → Sitios Web → tu sitio
- Sección "Bases de datos MySQL"

### 3️⃣ CONFIGURAR URL (1 minuto)

Edita el archivo: `config/config.php`

```php
define('BASE_URL', 'https://mediumvioletred-lobster-199641.hostingersite.com');
```

### 4️⃣ SUBIR A GITHUB (3 minutos)

```bash
cd "e:\Users\gabri\Documentos\Brodev Lab\Clientes\Rafa work\forethink-health"
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/forethink-health.git
git push -u origin main
```

### 5️⃣ CONECTAR HOSTINGER CON GITHUB (5 minutos)

1. En Hostinger → **Git** → "Crear nuevo deployment"
2. Conectar GitHub
3. Seleccionar repositorio: `forethink-health`
4. Rama: `main`
5. Ruta: `/public_html`
6. Guardar y Desplegar

## ✅ ¡LISTO!

Ahora puedes acceder a:
- **Sitio web:** https://mediumvioletred-lobster-199641.hostingersite.com
- **Panel Admin:** https://mediumvioletred-lobster-199641.hostingersite.com/admin/

### 🔐 Login de Administrador

- **Email:** admin@forethinkhealth.com
- **Contraseña:** admin123

**⚠️ CAMBIA LA CONTRASEÑA DESPUÉS DEL PRIMER LOGIN**

## 🔄 Actualizar el sitio después

```bash
git add .
git commit -m "Descripción del cambio"
git push
```

Hostinger detectará los cambios y actualizará automáticamente.

## ❓ Problemas Comunes

**Error de conexión a BD:**
- Verifica los datos en `config/database.php`
- Asegúrate de que la BD existe en phpMyAdmin

**Página en blanco:**
- Activa debug en `config/config.php`: `define('DEBUG_MODE', true);`

**Imágenes no se ven:**
- Verifica permisos de carpeta `uploads/` (755)

## 📞 ¿Necesitas ayuda?

Revisa el archivo `README.md` completo para instrucciones detalladas.
