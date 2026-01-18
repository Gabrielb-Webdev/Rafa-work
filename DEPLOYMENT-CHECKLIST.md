# ✅ CHECKLIST DE DEPLOYMENT - FORETHINK HEALTH

## 📋 ANTES DE SUBIR A PRODUCCIÓN

### 1. Configuración de Base de Datos
- [ ] Acceder a phpMyAdmin en Hostinger
- [ ] Crear/Seleccionar base de datos
- [ ] Ejecutar `database.sql` completo
- [ ] (Opcional) Ejecutar `database-sample-data.sql` para datos de prueba
- [ ] Verificar que todas las tablas se crearon correctamente (8 tablas)
- [ ] Anotar: Nombre de BD, Usuario, Contraseña

### 2. Configuración de Archivos
- [ ] Editar `config/database.php`:
  - [ ] DB_HOST (normalmente 'localhost')
  - [ ] DB_NAME (tu nombre de base de datos)
  - [ ] DB_USER (tu usuario de MySQL)
  - [ ] DB_PASS (tu contraseña)
- [ ] Editar `config/config.php`:
  - [ ] BASE_URL (https://mediumvioletred-lobster-199641.hostingersite.com)
  - [ ] SITE_NAME (Forethink Health)
  - [ ] SITE_EMAIL (tu email)
  - [ ] SITE_PHONE (tu teléfono)
  - [ ] DEBUG_MODE = false (para producción)

### 3. Preparar Repositorio GitHub
- [ ] Crear repositorio en GitHub
- [ ] Inicializar git local:
  ```bash
  cd "e:\Users\gabri\Documentos\Brodev Lab\Clientes\Rafa work\forethink-health"
  git init
  git add .
  git commit -m "Initial commit - Forethink Health"
  git branch -M main
  git remote add origin https://github.com/TU_USUARIO/forethink-health.git
  git push -u origin main
  ```

### 4. Conectar Hostinger con GitHub
- [ ] Ir a Panel de Hostinger → Git
- [ ] Clic en "Crear nuevo deployment"
- [ ] Conectar cuenta de GitHub
- [ ] Seleccionar repositorio: forethink-health
- [ ] Seleccionar rama: main
- [ ] Ruta de deployment: /public_html
- [ ] Guardar configuración
- [ ] Hacer primer deployment manual

### 5. Verificar Deployment
- [ ] Acceder a: https://mediumvioletred-lobster-199641.hostingersite.com
- [ ] Verificar que la página principal carga correctamente
- [ ] Verificar que los estilos se aplican
- [ ] Verificar que las imágenes cargan (o placeholder)
- [ ] Probar navegación entre páginas

### 6. Probar Funcionalidades Básicas
- [ ] Probar formulario de contacto
- [ ] Probar suscripción al newsletter
- [ ] Probar búsqueda de productos
- [ ] Probar filtros por categoría
- [ ] Probar agregar productos al carrito
- [ ] Verificar contador del carrito

### 7. Probar Sistema de Usuarios
- [ ] Ir a /login.php
- [ ] Login con admin: admin@forethinkhealth.com / admin123
- [ ] Verificar acceso al panel de administración
- [ ] Probar logout
- [ ] Probar registro de nuevo usuario
- [ ] Login con usuario nuevo

### 8. Probar Panel de Administración
- [ ] Dashboard muestra estadísticas
- [ ] Ver lista de productos
- [ ] Ver lista de pedidos
- [ ] Ver lista de usuarios
- [ ] Ver solicitudes de contacto
- [ ] Ver suscriptores de newsletter

### 9. Configurar Permisos
- [ ] Verificar permisos de carpeta uploads/ (755)
  ```bash
  chmod -R 755 uploads/
  ```
- [ ] Verificar que .htaccess está activo
- [ ] Probar upload de imágenes (si implementado)

### 10. Seguridad
- [ ] Cambiar contraseña del admin:
  - Login como admin
  - Ir a perfil
  - Cambiar "admin123" por contraseña segura
- [ ] Verificar que database.php NO es accesible vía web
- [ ] Verificar que archivos .git NO son accesibles
- [ ] Activar HTTPS (Hostinger lo hace automático)

### 11. Optimización
- [ ] Verificar que .htaccess está cargando
- [ ] Probar compresión GZIP
- [ ] Verificar cache de recursos estáticos
- [ ] Probar velocidad de carga

### 12. Testing Cross-Browser
- [ ] Probar en Chrome
- [ ] Probar en Firefox
- [ ] Probar en Safari
- [ ] Probar en Edge
- [ ] Probar en móvil (Chrome Mobile)
- [ ] Probar en móvil (Safari iOS)

### 13. Testing Responsive
- [ ] Desktop (1920x1080)
- [ ] Laptop (1366x768)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)
- [ ] Mobile (414x896)

### 14. SEO y Metadata
- [ ] Verificar títulos de páginas
- [ ] Verificar meta descriptions
- [ ] Verificar estructura de headings (H1, H2, etc.)
- [ ] Verificar alt text en imágenes

### 15. Analytics y Monitoreo (Opcional)
- [ ] Configurar Google Analytics
- [ ] Configurar Google Search Console
- [ ] Configurar monitoreo de uptime

---

## 🚨 PROBLEMAS COMUNES Y SOLUCIONES

### Error: "Connection failed"
**Causa:** Datos de BD incorrectos  
**Solución:** Verificar config/database.php

### Error: Página en blanco
**Causa:** Error de PHP  
**Solución:** Activar DEBUG_MODE = true en config.php

### Error: Estilos no cargan
**Causa:** BASE_URL incorrecta  
**Solución:** Verificar BASE_URL en config.php

### Error: 404 en recursos
**Causa:** Archivos no subidos  
**Solución:** Verificar deployment de GitHub

### Error: Cannot write to uploads/
**Causa:** Permisos incorrectos  
**Solución:** chmod 755 uploads/

---

## 📝 NOTAS POST-DEPLOYMENT

### Credenciales de Administrador
- Email: admin@forethinkhealth.com
- Password: [ANOTAR NUEVA CONTRASEÑA AQUÍ]

### URLs Importantes
- Sitio público: https://mediumvioletred-lobster-199641.hostingersite.com
- Panel admin: https://mediumvioletred-lobster-199641.hostingersite.com/admin/
- phpMyAdmin: [URL de Hostinger]

### Información de Base de Datos
- Host: localhost
- Database: [ANOTAR AQUÍ]
- Usuario: [ANOTAR AQUÍ]
- Password: [ANOTAR AQUÍ]

### Repositorio GitHub
- URL: [ANOTAR AQUÍ]
- Rama: main

---

## 🔄 WORKFLOW DE ACTUALIZACIÓN

```bash
# 1. Hacer cambios en local
# 2. Probar cambios localmente
# 3. Commit y push

git add .
git commit -m "Descripción del cambio"
git push

# 4. Hostinger detecta y despliega automáticamente
# 5. Verificar cambios en producción
```

---

## 📞 SOPORTE

Si algo no funciona:
1. Revisar logs de error en Hostinger
2. Activar DEBUG_MODE
3. Verificar configuración de BD
4. Revisar documentación en README.md
5. Contactar soporte de Hostinger

---

## ✅ DEPLOYMENT COMPLETADO

**Fecha:** _______________  
**Por:** _______________  
**Versión:** 1.0.0  
**Status:** ☐ En Progreso  ☐ Completado  ☐ Con Issues

**Notas adicionales:**
_________________________________
_________________________________
_________________________________

---

**¡Felicidades! Tu ecommerce está listo para funcionar** 🎉
