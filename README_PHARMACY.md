# 🏥 MediCareOnline - Tu Farmacia Digital

## 📋 Descripción del Proyecto

**MediCareOnline** es una plataforma de e-commerce diseñada específicamente para farmacias online, con un diseño moderno en tonos cyan/turquoise que refleja profesionalismo y confianza en el sector salud.

### ✨ Características Principales

- **Diseño Moderno**: Inspirado en las mejores prácticas de farmacias digitales
- **Colores Corporativos**: Cyan (#00D4FF) y turquoise (#00A8CC)
- **Categorías Especializadas**: Medicina, Vitaminas, Cuidado Personal, etc.
- **Sistema de Prescripciones**: Gestión de medicamentos que requieren receta
- **Carrito de Compras Avanzado**: Con validación de stock en tiempo real
- **Panel de Administración**: Gestión completa de productos farmacéuticos
- **Responsive Design**: Optimizado para móviles y tablets

---

## 🚀 Migración desde MultiGamer360

Este proyecto fue transformado de una tienda de videojuegos (MultiGamer360) a una farmacia online (MediCareOnline).

### 📝 Cambios Realizados

#### 1. **Diseño Visual**
- ✅ Actualización de colores a cyan/turquoise
- ✅ Nuevo header con branding de MediCareOnline
- ✅ Footer actualizado con información de contacto
- ✅ Estilos CSS específicos para farmacia (`pharmacy-style.css`)
- ✅ Fuente Poppins para mejor legibilidad

#### 2. **Página Principal (index.php)**
- ✅ Hero section con mensaje de bienvenida farmacéutico
- ✅ Sección de servicios (Fast Delivery, Support 24/7, etc.)
- ✅ Banner de descuento 10%
- ✅ Secciones de productos: "Medicine & Health" y "Vitamins & Supplements"
- ✅ About Us section
- ✅ Testimonios de clientes
- ✅ Newsletter subscription
- ✅ Formulario de contacto

#### 3. **Base de Datos**
- ✅ Nuevas categorías: Medicina, Vitaminas, Cuidado Personal, etc.
- ✅ Nuevas marcas: Bayer, Pfizer, Johnson & Johnson, etc.
- ✅ Campos adicionales en productos:
  - `requires_prescription` (BOOLEAN)
  - `expiration_date` (DATE)
  - `active_ingredient` (VARCHAR)
  - `dosage` (VARCHAR)
  - `presentation` (VARCHAR)
  - `warnings` (TEXT)
- ✅ Tabla de prescripciones médicas
- ✅ 12 productos de ejemplo con datos reales

---

## 💾 Instalación y Configuración

### Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Composer (opcional)

### Paso 1: Actualizar Base de Datos

Ejecuta el script SQL para actualizar la estructura:

```bash
mysql -u tu_usuario -p tu_base_de_datos < config/update_to_pharmacy.sql
```

O desde phpMyAdmin:
1. Abre phpMyAdmin
2. Selecciona tu base de datos
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `config/update_to_pharmacy.sql`
5. Ejecuta

### Paso 2: Actualizar Configuración

Edita `config/database.php` con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'medicareonline');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

### Paso 3: Configurar Permisos

```bash
chmod -R 755 uploads/
chmod -R 755 assets/
```

### Paso 4: Probar el Sitio

Visita tu dominio local:
- http://localhost/tu-proyecto/ o
- http://tu-dominio.com/

---

## 🎨 Estructura de Archivos Nuevos

```
├── assets/
│   └── css/
│       └── pharmacy-style.css          # Estilos específicos de farmacia
├── config/
│   └── update_to_pharmacy.sql          # Script de migración SQL
└── README_PHARMACY.md                  # Este archivo
```

---

## 🔧 Configuraciones Importantes

### Colores Corporativos

```css
--primary-color: #00D4FF;          /* Cyan principal */
--primary-dark: #00A8CC;           /* Cyan oscuro */
--secondary-color: #0088AA;         /* Secundario */
--accent-color: #FF6B9D;           /* Acento (badges) */
```

### Categorías de Productos

1. **Medicina y Salud** - Medicamentos generales
2. **Vitaminas y Suplementos** - Suplementos alimenticios
3. **Cuidado Personal** - Higiene y cuidado
4. **Primeros Auxilios** - Botiquín
5. **Bebé y Mamá** - Productos infantiles
6. **Dermatología** - Cuidado de la piel
7. **Nutrición Deportiva** - Suplementos deportivos
8. **Salud Sexual** - Bienestar sexual

---

## 📦 Subida a Hostinger

### Preparación

1. **Exportar Base de Datos**
```bash
mysqldump -u usuario -p base_datos > backup.sql
```

2. **Comprimir Archivos**
```bash
zip -r medicareonline.zip . -x "*.git*" "node_modules/*"
```

### Subida via FTP/SFTP

1. Conecta a tu hosting Hostinger
2. Sube todos los archivos a `public_html/`
3. Importa la base de datos desde cPanel → phpMyAdmin
4. Actualiza `config/database.php` con credenciales de Hostinger

### Configuración en Hostinger

1. **Base de Datos**
   - Crea una nueva base de datos MySQL
   - Crea un usuario con todos los privilegios
   - Importa `backup.sql`

2. **Archivos**
   - Verifica permisos de carpetas (755)
   - Verifica permisos de archivos (644)

3. **DNS y Dominio**
   - Configura tu dominio en Hostinger
   - Espera propagación DNS (24-48 horas)

---

## 🐛 Solución de Problemas

### Error: "Database connection failed"
- Verifica credenciales en `config/database.php`
- Asegúrate que el servidor MySQL esté corriendo
- Verifica que el usuario tenga permisos

### Error: "Images not loading"
- Verifica que la carpeta `uploads/` tenga permisos 755
- Verifica rutas de imágenes en productos
- Usa imágenes de placeholder si es necesario

### Error: "CSS not applying"
- Limpia caché del navegador (Ctrl + Shift + R)
- Verifica que `pharmacy-style.css` esté cargando
- Verifica versión de archivos CSS en header.php

---

## 📱 Responsive Design

El diseño está optimizado para:
- 📱 **Mobile**: 320px - 767px
- 📱 **Tablet**: 768px - 1023px
- 💻 **Desktop**: 1024px+

---

## 🔐 Seguridad

- ✅ Validación de entrada en todos los formularios
- ✅ Prepared statements para prevenir SQL injection
- ✅ Sanitización de datos
- ✅ Sistema de sesiones seguro
- ✅ Validación de prescripciones médicas

---

## 📊 Métricas y Analytics

Para implementar Google Analytics:

1. Obtén tu código de seguimiento
2. Agrégalo en `includes/header.php` antes de `</head>`

```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

---

## 🤝 Contribución y Soporte

Para reportar bugs o sugerir mejoras:
- 📧 Email: info@medicareonline.com
- 🐛 Issues: [GitHub Issues]

---

## 📄 Licencia

Este proyecto es privado y confidencial.

---

## 🎯 Próximos Pasos Recomendados

1. **Imágenes**: Agregar imágenes reales de medicamentos
2. **Productos**: Llenar catálogo con productos reales
3. **Pagos**: Integrar pasarela de pagos (MercadoPago, PayPal)
4. **Envíos**: Configurar opciones de envío
5. **Email**: Configurar SMTP para notificaciones
6. **SEO**: Optimizar metadatos y descripciones
7. **SSL**: Instalar certificado SSL (HTTPS)

---

## 📞 Información de Contacto

**MediCareOnline**
- 🌐 Website: www.medicareonline.com
- 📧 Email: info@medicareonline.com
- 📱 Teléfono: [Por definir]
- 📍 Dirección: [Por definir]

---

*Última actualización: 7 de enero de 2026*
