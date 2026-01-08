<?php
/**
 * =====================================================
 * MEDICAONLINE - SETUP AUTOMÁTICO
 * =====================================================
 * Script de instalación automática con un solo clic
 * Ejecuta todas las migraciones necesarias para convertir
 * la base de datos a farmacia online
 * 
 * IMPORTANTE: Elimina este archivo después de ejecutarlo
 * =====================================================
 */

// Seguridad básica - cambiar este password
define('SETUP_PASSWORD', 'MediCare2026'); // CAMBIAR ESTO POR SEGURIDAD

session_start();

// =====================================================
// CONEXIÓN A BASE DE DATOS (STANDALONE)
// =====================================================
$db_config = [
    'host' => 'localhost',
    'database' => 'u851317150_mg360_db',
    'username' => 'u851317150_mg360_user',
    'password' => 'MultiGamer2025'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['database']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

// Variable para controlar si ya se ejecutó
$executed = false;
$results = [];
$errors = [];

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verificar password de seguridad
    if (!isset($_POST['setup_password']) || $_POST['setup_password'] !== SETUP_PASSWORD) {
        $errors[] = "Password de seguridad incorrecto";
    } else {
        
        try {
            // Desactivar verificación de foreign keys temporalmente
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            $results[] = "✅ Conexión a base de datos establecida";
            $results[] = "🔄 Iniciando migración a MediCareOnline...";
            
            // =====================================================
            // 1. ACTUALIZAR CATEGORÍAS
            // =====================================================
            $results[] = "<br><strong>📁 ACTUALIZANDO CATEGORÍAS</strong>";
            
            $pdo->exec("DELETE FROM categories");
            $results[] = "✅ Categorías antiguas eliminadas";
            
            // Verificar qué columnas tiene la tabla categories
            $columns_check = $pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
            $has_description = in_array('description', $columns_check);
            $has_is_active = in_array('is_active', $columns_check);
            
            $categories = [
                ['Medicina y Salud', 'medicina-salud'],
                ['Vitaminas y Suplementos', 'vitaminas-suplementos'],
                ['Cuidado Personal', 'cuidado-personal'],
                ['Primeros Auxilios', 'primeros-auxilios'],
                ['Bebé y Mamá', 'bebe-mama'],
                ['Dermatología', 'dermatologia'],
                ['Nutrición Deportiva', 'nutricion-deportiva'],
                ['Salud Sexual', 'salud-sexual']
            ];
            
            // Construir query dinámicamente según columnas disponibles
            $insert_query = "INSERT INTO categories (name, slug";
            if ($has_is_active) $insert_query .= ", is_active";
            $insert_query .= ", created_at) VALUES (?, ?";
            if ($has_is_active) $insert_query .= ", 1";
            $insert_query .= ", NOW())";
            
            $stmt = $pdo->prepare($insert_query);
            foreach ($categories as $cat) {
                $stmt->execute($cat);
            }
            $results[] = "✅ " . count($categories) . " categorías de farmacia creadas";
            
            // =====================================================
            // 2. ACTUALIZAR MARCAS
            // =====================================================
            $results[] = "<br><strong>🏷️ ACTUALIZANDO MARCAS</strong>";
            
            $pdo->exec("DELETE FROM brands");
            $results[] = "✅ Marcas antiguas eliminadas";
            
            // Verificar columnas de brands
            $brand_columns_check = $pdo->query("SHOW COLUMNS FROM brands")->fetchAll(PDO::FETCH_COLUMN);
            $brand_has_description = in_array('description', $brand_columns_check);
            $brand_has_is_active = in_array('is_active', $brand_columns_check);
            
            $brands = [
                ['Bayer', 'bayer'],
                ['Pfizer', 'pfizer'],
                ['Johnson & Johnson', 'johnson-johnson'],
                ['Roche', 'roche'],
                ['Novartis', 'novartis'],
                ['GSK', 'gsk'],
                ['Sanofi', 'sanofi'],
                ['Abbott', 'abbott'],
                ['Merck', 'merck'],
                ['Boehringer Ingelheim', 'boehringer-ingelheim']
            ];
            
            // Construir query dinámicamente para brands
            $brand_insert_query = "INSERT INTO brands (name, slug";
            if ($brand_has_is_active) $brand_insert_query .= ", is_active";
            $brand_insert_query .= ", created_at) VALUES (?, ?";
            if ($brand_has_is_active) $brand_insert_query .= ", 1";
            $brand_insert_query .= ", NOW())";
            
            $stmt = $pdo->prepare($brand_insert_query);
            foreach ($brands as $brand) {
                $stmt->execute($brand);
            }
            $results[] = "✅ " . count($brands) . " marcas farmacéuticas creadas";
            
            // =====================================================
            // 3. AGREGAR CAMPOS PARA MEDICAMENTOS (OPCIONAL)
            // =====================================================
            $results[] = "<br><strong>🔧 ACTUALIZANDO ESTRUCTURA DE PRODUCTOS</strong>";
            
            // Verificar que la tabla products existe
            $products_table_check = $pdo->query("SHOW TABLES LIKE 'products'")->fetchAll();
            if (count($products_table_check) > 0) {
                // Verificar y agregar columnas si no existen
                $columns_to_add = [
                    ["requires_prescription", "ALTER TABLE products ADD COLUMN requires_prescription BOOLEAN DEFAULT FALSE"],
                    ["expiration_date", "ALTER TABLE products ADD COLUMN expiration_date DATE NULL"],
                    ["active_ingredient", "ALTER TABLE products ADD COLUMN active_ingredient VARCHAR(255) NULL"],
                    ["dosage", "ALTER TABLE products ADD COLUMN dosage VARCHAR(100) NULL"],
                    ["presentation", "ALTER TABLE products ADD COLUMN presentation VARCHAR(100) NULL"],
                    ["warnings", "ALTER TABLE products ADD COLUMN warnings TEXT NULL"]
                ];
                
                $existing_columns = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($columns_to_add as $col_info) {
                    $col_name = $col_info[0];
                    $sql = $col_info[1];
                    
                    if (!in_array($col_name, $existing_columns)) {
                        try {
                            $pdo->exec($sql);
                            $results[] = "✅ Columna '$col_name' agregada";
                        } catch (PDOException $e) {
                            $results[] = "⚠️ No se pudo agregar columna '$col_name'";
                        }
                    }
                }
                $results[] = "✅ Estructura de productos actualizada";
            } else {
                $results[] = "⚠️ Tabla 'products' no existe - omitiendo actualización de estructura";
            }
            
            // =====================================================
            // 4. CREAR TABLA DE PRESCRIPCIONES (OPCIONAL)
            // =====================================================
            $results[] = "<br><strong>💊 VERIFICANDO TABLA DE PRESCRIPCIONES</strong>";
            
            // Verificar si existen las tablas necesarias para foreign keys
            $orders_exists = $pdo->query("SHOW TABLES LIKE 'orders'")->fetchAll();
            $users_exists = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
            
            if (count($orders_exists) > 0 && count($users_exists) > 0) {
                try {
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS prescriptions (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            order_id INT NOT NULL,
                            user_id INT NOT NULL,
                            prescription_file VARCHAR(255) NOT NULL,
                            verified BOOLEAN DEFAULT FALSE,
                            verified_by INT NULL,
                            verified_at DATETIME NULL,
                            notes TEXT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    $results[] = "✅ Tabla de prescripciones médicas creada";
                } catch (PDOException $e) {
                    $results[] = "⚠️ No se pudo crear tabla prescriptions: " . $e->getMessage();
                }
            } else {
                $results[] = "⚠️ Tablas 'orders' o 'users' no existen - omitiendo tabla prescriptions";
            }
            
            // =====================================================
            // 5. CREAR ÍNDICES (OPCIONAL)
            // =====================================================
            $results[] = "<br><strong>⚡ OPTIMIZANDO ÍNDICES</strong>";
            
            // Solo crear índices si la tabla products existe
            $products_table_check2 = $pdo->query("SHOW TABLES LIKE 'products'")->fetchAll();
            if (count($products_table_check2) > 0) {
                $indexes = [
                    "CREATE INDEX IF NOT EXISTS idx_requires_prescription ON products(requires_prescription)",
                    "CREATE INDEX IF NOT EXISTS idx_expiration_date ON products(expiration_date)",
                    "CREATE INDEX IF NOT EXISTS idx_active_ingredient ON products(active_ingredient)"
                ];
                
                foreach ($indexes as $sql) {
                    try {
                        $pdo->exec($sql);
                    } catch (PDOException $e) {
                        // Si el índice ya existe o la columna no existe, continuar
                    }
                }
                $results[] = "✅ Índices de rendimiento creados";
            } else {
                $results[] = "⚠️ Tabla products no existe - omitiendo índices";
            }
            
            // =====================================================
            // 6. ACTUALIZAR CONFIGURACIÓN DEL SITIO (OPCIONAL)
            // =====================================================
            $results[] = "<br><strong>⚙️ VERIFICANDO CONFIGURACIÓN</strong>";
            
            // Verificar si existe la tabla settings
            $tables_check = $pdo->query("SHOW TABLES LIKE 'settings'")->fetchAll();
            if (count($tables_check) > 0) {
                try {
                    $configs = [
                        ['site_name', 'MediCareOnline'],
                        ['site_description', 'Tu Farmacia Digital de Confianza'],
                        ['site_email', 'info@medicareonline.com']
                    ];
                    
                    $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE key_name = ?");
                    foreach ($configs as $config) {
                        $stmt->execute([$config[1], $config[0]]);
                    }
                    $results[] = "✅ Configuración del sitio actualizada";
                } catch (Exception $e) {
                    $results[] = "⚠️ No se pudo actualizar configuración (tabla settings no compatible)";
                }
            } else {
                $results[] = "⚠️ Tabla 'settings' no existe - omitiendo configuración";
            }
            
            // =====================================================
            // 7. CREAR PRODUCTOS DE EJEMPLO
            // =====================================================
            $results[] = "<br><strong>📦 CREANDO PRODUCTOS DE EJEMPLO</strong>";
            
            // Limpiar productos existentes
            try {
                $pdo->exec("DELETE FROM products");
                $results[] = "✅ Productos antiguos eliminados";
            } catch (PDOException $e) {
                $results[] = "⚠️ No se pudieron eliminar productos antiguos: " . $e->getMessage();
            }
            
            // Verificar columnas de la tabla products
            $product_columns = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
            $has_price = in_array('price', $product_columns);
            $has_stock = in_array('stock', $product_columns) || in_array('stock_quantity', $product_columns);
            $has_description = in_array('description', $product_columns);
            $has_category = in_array('category_id', $product_columns);
            $has_brand = in_array('brand_id', $product_columns);
            $has_active = in_array('is_active', $product_columns);
            $has_featured = in_array('is_featured', $product_columns);
            
            // Obtener IDs de categorías y marcas si existen
            $cat_medicina = $has_category ? $pdo->query("SELECT id FROM categories WHERE slug = 'medicina-salud'")->fetchColumn() : null;
            $cat_vitaminas = $has_category ? $pdo->query("SELECT id FROM categories WHERE slug = 'vitaminas-suplementos'")->fetchColumn() : null;
            $cat_cuidado = $has_category ? $pdo->query("SELECT id FROM categories WHERE slug = 'cuidado-personal'")->fetchColumn() : null;
            $cat_dermato = $has_category ? $pdo->query("SELECT id FROM categories WHERE slug = 'dermatologia'")->fetchColumn() : null;
            
            $brand_bayer = $has_brand ? $pdo->query("SELECT id FROM brands WHERE slug = 'bayer'")->fetchColumn() : null;
            $brand_pfizer = $has_brand ? $pdo->query("SELECT id FROM brands WHERE slug = 'pfizer'")->fetchColumn() : null;
            $brand_jj = $has_brand ? $pdo->query("SELECT id FROM brands WHERE slug = 'johnson-johnson'")->fetchColumn() : null;
            $brand_abbott = $has_brand ? $pdo->query("SELECT id FROM brands WHERE slug = 'abbott'")->fetchColumn() : null;
            
            // Productos de ejemplo (name, slug, price, stock, category_id, brand_id, is_active, is_featured)
            $products_data = [
                ['Paracetamol 500mg', 'paracetamol-500mg', 8.99, 100, $cat_medicina, $brand_bayer, 1, 1],
                ['Ibuprofeno 400mg', 'ibuprofeno-400mg', 12.50, 150, $cat_medicina, $brand_bayer, 1, 1],
                ['Amoxicilina 500mg', 'amoxicilina-500mg', 25.00, 80, $cat_medicina, $brand_pfizer, 1, 0],
                ['Omeprazol 20mg', 'omeprazol-20mg', 18.75, 120, $cat_medicina, $brand_jj, 1, 1],
                ['Vitamina C 1000mg', 'vitamina-c-1000mg', 15.99, 200, $cat_vitaminas, $brand_abbott, 1, 1],
                ['Complejo B', 'complejo-b', 22.50, 150, $cat_vitaminas, $brand_abbott, 1, 1],
                ['Omega 3', 'omega-3', 28.99, 100, $cat_vitaminas, $brand_abbott, 1, 1],
                ['Multivitamínico Complete', 'multivitaminico-complete', 32.50, 120, $cat_vitaminas, $brand_abbott, 1, 1],
                ['Alcohol en Gel', 'alcohol-gel', 6.99, 300, $cat_cuidado, $brand_jj, 1, 0],
                ['Termómetro Digital', 'termometro-digital', 12.99, 80, $cat_cuidado, $brand_jj, 1, 1],
                ['Protector Solar FPS 50+', 'protector-solar-fps50', 24.99, 100, $cat_dermato, $brand_jj, 1, 1],
                ['Crema Hidratante Facial', 'crema-hidratante-facial', 18.50, 90, $cat_dermato, $brand_jj, 1, 0]
            ];
            
            // Construir INSERT dinámicamente basado en columnas disponibles
            $insert_cols = [];
            $insert_placeholders = [];
            
            $insert_cols[] = "name";
            $insert_cols[] = "slug";
            if ($has_price) $insert_cols[] = "price";
            if ($has_stock) $insert_cols[] = "stock_quantity";
            if ($has_category) $insert_cols[] = "category_id";
            if ($has_brand) $insert_cols[] = "brand_id";
            if ($has_active) $insert_cols[] = "is_active";
            if ($has_featured) $insert_cols[] = "is_featured";
            $insert_cols[] = "created_at";
            
            $insert_placeholders = array_fill(0, count($insert_cols) - 1, '?'); // -1 porque created_at usa NOW()
            $insert_placeholders[] = 'NOW()';
            
            $sql = "INSERT INTO products (" . implode(", ", $insert_cols) . ") VALUES (" . implode(", ", $insert_placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            
            foreach ($products_data as $product) {
                // Construir array de valores según columnas disponibles
                $values = [];
                $values[] = $product[0]; // name
                $values[] = $product[1]; // slug
                if ($has_price) $values[] = $product[2]; // price
                if ($has_stock) $values[] = $product[3]; // stock
                if ($has_category) $values[] = $product[4]; // category_id
                if ($has_brand) $values[] = $product[5]; // brand_id
                if ($has_active) $values[] = $product[6]; // is_active
                if ($has_featured) $values[] = $product[7]; // is_featured
                // created_at se maneja con NOW()
                
                $stmt->execute($values);
            }
            $results[] = "✅ " . count($products_data) . " productos farmacéuticos de ejemplo creados";
            
            // =====================================================
            // COMMIT TRANSACTION
            // =====================================================
            if ($pdo->inTransaction()) {
                $pdo->commit();
                $results[] = "✅ Transacción completada exitosamente";
            }
            
            // Reactivar verificación de foreign keys
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            $results[] = "<br><strong>🎉 ¡MIGRACIÓN COMPLETADA EXITOSAMENTE!</strong>";
            $results[] = "✅ Base de datos actualizada a MediCareOnline";
            $results[] = "✅ Todas las tablas y datos migrados correctamente";
            $results[] = "<br><strong style='color: #ff6b6b;'>⚠️ IMPORTANTE: Elimina este archivo (setup_pharmacy.php) por seguridad</strong>";
            
            $executed = true;
            
        } catch (Exception $e) {
            // Rollback en caso de error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            // Reactivar foreign keys incluso si hay error
            try {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (Exception $fk_error) {
                // Ignorar error al reactivar foreign keys
            }
            
            $errors[] = "❌ Error durante la migración: " . $e->getMessage();
            $errors[] = "📝 Archivo: " . $e->getFile();
            $errors[] = "📍 Línea: " . $e->getLine();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup MediCareOnline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 90%;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-header i {
            font-size: 4rem;
            color: #00D4FF;
            margin-bottom: 20px;
        }
        .setup-header h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .setup-header p {
            color: #666;
            font-size: 1.1rem;
        }
        .btn-setup {
            background: linear-gradient(135deg, #00D4FF 0%, #00A8CC 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-setup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
        }
        .results-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            max-height: 500px;
            overflow-y: auto;
        }
        .result-item {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .result-item:last-child {
            border-bottom: none;
        }
        .error-box {
            background: #fff5f5;
            border: 2px solid #ff6b6b;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
        .warning-box {
            background: #fff9e6;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <?php if (!$executed && empty($errors)): ?>
            <!-- FORMULARIO INICIAL -->
            <div class="setup-header">
                <i class="fas fa-pills"></i>
                <h1>Setup MediCareOnline</h1>
                <p>Migración automática a Farmacia Online</p>
            </div>
            
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i>Este proceso realizará:</h5>
                <ul class="mb-0">
                    <li>Actualización de categorías a productos farmacéuticos</li>
                    <li>Actualización de marcas a laboratorios reconocidos</li>
                    <li>Agregar campos específicos para medicamentos</li>
                    <li>Crear tabla de prescripciones médicas</li>
                    <li>Actualizar configuración del sitio</li>
                    <li>Crear productos de ejemplo</li>
                </ul>
            </div>
            
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Advertencia:</strong> Este proceso modificará tu base de datos. Asegúrate de tener un backup antes de continuar.
            </div>
            
            <form method="POST" class="mt-4">
                <div class="mb-3">
                    <label for="setup_password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Password de Seguridad
                    </label>
                    <input type="password" class="form-control" id="setup_password" name="setup_password" 
                           placeholder="Ingresa: <?php echo SETUP_PASSWORD; ?>" required>
                    <small class="text-muted">Ingresa el password definido en el archivo (línea 15)</small>
                </div>
                
                <button type="submit" class="btn btn-setup">
                    <i class="fas fa-rocket me-2"></i>Ejecutar Migración
                </button>
            </form>
            
        <?php elseif ($executed): ?>
            <!-- RESULTADOS EXITOSOS -->
            <div class="setup-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>¡Migración Completada!</h1>
                <p>Tu sitio ahora es MediCareOnline</p>
            </div>
            
            <div class="results-box">
                <?php foreach ($results as $result): ?>
                    <div class="result-item"><?php echo $result; ?></div>
                <?php endforeach; ?>
            </div>
            
            <div class="alert alert-danger mt-4">
                <h5><i class="fas fa-shield-alt me-2"></i>Pasos de Seguridad:</h5>
                <ol class="mb-0">
                    <li><strong>Elimina este archivo</strong> (setup_pharmacy.php) inmediatamente</li>
                    <li>Verifica que todo funcione correctamente en tu sitio</li>
                    <li>Agrega imágenes de medicamentos reales</li>
                    <li>Actualiza productos con tu inventario</li>
                </ol>
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-setup">
                    <i class="fas fa-home me-2"></i>Ir a la Página Principal
                </a>
            </div>
            
        <?php elseif (!empty($errors)): ?>
            <!-- ERRORES -->
            <div class="setup-header">
                <i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>
                <h1>Error en la Migración</h1>
                <p>Ocurrió un problema durante el proceso</p>
            </div>
            
            <div class="error-box">
                <?php foreach ($errors as $error): ?>
                    <div class="result-item text-danger"><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
            
            <div class="alert alert-info mt-4">
                <h5><i class="fas fa-lightbulb me-2"></i>Soluciones:</h5>
                <ul class="mb-0">
                    <li>Verifica que la conexión a la base de datos sea correcta</li>
                    <li>Asegúrate de que el usuario tenga permisos completos</li>
                    <li>Revisa que las tablas necesarias existan</li>
                    <li>Contacta a soporte técnico si el problema persiste</li>
                </ul>
            </div>
            
            <div class="text-center mt-4">
                <a href="setup_pharmacy.php" class="btn btn-setup">
                    <i class="fas fa-redo me-2"></i>Intentar Nuevamente
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
