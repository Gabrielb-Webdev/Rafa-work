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
            
            $categories = [
                ['Medicina y Salud', 'medicina-salud', 'Medicamentos para tratamientos diversos'],
                ['Vitaminas y Suplementos', 'vitaminas-suplementos', 'Vitaminas y suplementos alimenticios'],
                ['Cuidado Personal', 'cuidado-personal', 'Productos de higiene y cuidado personal'],
                ['Primeros Auxilios', 'primeros-auxilios', 'Botiquín y productos de emergencia'],
                ['Bebé y Mamá', 'bebe-mama', 'Productos para el cuidado del bebé y la madre'],
                ['Dermatología', 'dermatologia', 'Productos para el cuidado de la piel'],
                ['Nutrición Deportiva', 'nutricion-deportiva', 'Suplementos y productos para deportistas'],
                ['Salud Sexual', 'salud-sexual', 'Productos para el bienestar sexual']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
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
            
            $brands = [
                ['Bayer', 'bayer', 'Líder mundial en salud y nutrición'],
                ['Pfizer', 'pfizer', 'Innovación farmacéutica de calidad'],
                ['Johnson & Johnson', 'johnson-johnson', 'Cuidado de la salud familiar'],
                ['Roche', 'roche', 'Pioneros en biotecnología'],
                ['Novartis', 'novartis', 'Soluciones innovadoras en salud'],
                ['GSK', 'gsk', 'GlaxoSmithKline - Ciencia para la salud'],
                ['Sanofi', 'sanofi', 'Salud para todos'],
                ['Abbott', 'abbott', 'Nutrición y diagnóstico de calidad'],
                ['Merck', 'merck', 'Ciencia para una vida mejor'],
                ['Boehringer Ingelheim', 'boehringer-ingelheim', 'Innovación en medicina']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO brands (name, slug, description, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
            foreach ($brands as $brand) {
                $stmt->execute($brand);
            }
            $results[] = "✅ " . count($brands) . " marcas farmacéuticas creadas";
            
            // =====================================================
            // 3. AGREGAR CAMPOS PARA MEDICAMENTOS
            // =====================================================
            $results[] = "<br><strong>🔧 ACTUALIZANDO ESTRUCTURA DE PRODUCTOS</strong>";
            
            // Verificar y agregar columnas si no existen
            $columns_to_add = [
                "ALTER TABLE products ADD COLUMN requires_prescription BOOLEAN DEFAULT FALSE AFTER stock",
                "ALTER TABLE products ADD COLUMN expiration_date DATE NULL AFTER requires_prescription",
                "ALTER TABLE products ADD COLUMN active_ingredient VARCHAR(255) NULL AFTER expiration_date",
                "ALTER TABLE products ADD COLUMN dosage VARCHAR(100) NULL AFTER active_ingredient",
                "ALTER TABLE products ADD COLUMN presentation VARCHAR(100) NULL AFTER dosage",
                "ALTER TABLE products ADD COLUMN warnings TEXT NULL AFTER presentation"
            ];
            
            foreach ($columns_to_add as $sql) {
                try {
                    $pdo->exec($sql);
                } catch (PDOException $e) {
                    // Si la columna ya existe, continuar
                    if ($e->getCode() != '42S21') {
                        throw $e;
                    }
                }
            }
            $results[] = "✅ Campos de medicamentos agregados a tabla products";
            
            // =====================================================
            // 4. CREAR TABLA DE PRESCRIPCIONES
            // =====================================================
            $results[] = "<br><strong>💊 CREANDO TABLA DE PRESCRIPCIONES</strong>";
            
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
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $results[] = "✅ Tabla de prescripciones médicas creada";
            
            // =====================================================
            // 5. CREAR ÍNDICES
            // =====================================================
            $results[] = "<br><strong>⚡ OPTIMIZANDO ÍNDICES</strong>";
            
            $indexes = [
                "CREATE INDEX IF NOT EXISTS idx_requires_prescription ON products(requires_prescription)",
                "CREATE INDEX IF NOT EXISTS idx_expiration_date ON products(expiration_date)",
                "CREATE INDEX IF NOT EXISTS idx_active_ingredient ON products(active_ingredient)"
            ];
            
            foreach ($indexes as $sql) {
                try {
                    $pdo->exec($sql);
                } catch (PDOException $e) {
                    // Si el índice ya existe, continuar
                }
            }
            $results[] = "✅ Índices de rendimiento creados";
            
            // =====================================================
            // 6. ACTUALIZAR CONFIGURACIÓN DEL SITIO
            // =====================================================
            $results[] = "<br><strong>⚙️ ACTUALIZANDO CONFIGURACIÓN</strong>";
            
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
            
            // =====================================================
            // 7. CREAR PRODUCTOS DE EJEMPLO
            // =====================================================
            $results[] = "<br><strong>📦 CREANDO PRODUCTOS DE EJEMPLO</strong>";
            
            // Obtener IDs de categorías y marcas
            $cat_medicina = $pdo->query("SELECT id FROM categories WHERE slug = 'medicina-salud'")->fetchColumn();
            $cat_vitaminas = $pdo->query("SELECT id FROM categories WHERE slug = 'vitaminas-suplementos'")->fetchColumn();
            $cat_cuidado = $pdo->query("SELECT id FROM categories WHERE slug = 'cuidado-personal'")->fetchColumn();
            $cat_dermato = $pdo->query("SELECT id FROM categories WHERE slug = 'dermatologia'")->fetchColumn();
            
            $brand_bayer = $pdo->query("SELECT id FROM brands WHERE slug = 'bayer'")->fetchColumn();
            $brand_pfizer = $pdo->query("SELECT id FROM brands WHERE slug = 'pfizer'")->fetchColumn();
            $brand_jj = $pdo->query("SELECT id FROM brands WHERE slug = 'johnson-johnson'")->fetchColumn();
            $brand_abbott = $pdo->query("SELECT id FROM brands WHERE slug = 'abbott'")->fetchColumn();
            
            $products = [
                ['Paracetamol 500mg', 'paracetamol-500mg', 'Analgésico y antipirético para alivio del dolor y fiebre', 8.99, null, 100, $cat_medicina, $brand_bayer, 1, 1, 0, 'Paracetamol', '500mg', 'Caja x 20 tabletas'],
                ['Ibuprofeno 400mg', 'ibuprofeno-400mg', 'Antiinflamatorio no esteroideo para dolor e inflamación', 12.50, 10.99, 150, $cat_medicina, $brand_bayer, 1, 1, 0, 'Ibuprofeno', '400mg', 'Caja x 30 cápsulas'],
                ['Amoxicilina 500mg', 'amoxicilina-500mg', 'Antibiótico de amplio espectro', 25.00, null, 80, $cat_medicina, $brand_pfizer, 1, 0, 1, 'Amoxicilina', '500mg', 'Caja x 21 cápsulas'],
                ['Omeprazol 20mg', 'omeprazol-20mg', 'Protector gástrico e inhibidor de bomba de protones', 18.75, 15.99, 120, $cat_medicina, $brand_jj, 1, 1, 0, 'Omeprazol', '20mg', 'Caja x 14 cápsulas'],
                ['Vitamina C 1000mg', 'vitamina-c-1000mg', 'Suplemento de vitamina C para fortalecer el sistema inmune', 15.99, null, 200, $cat_vitaminas, $brand_abbott, 1, 1, 0, 'Ácido Ascórbico', '1000mg', 'Frasco x 60 tabletas'],
                ['Complejo B', 'complejo-b', 'Vitaminas del complejo B para energía y metabolismo', 22.50, 19.99, 150, $cat_vitaminas, $brand_abbott, 1, 1, 0, 'Complejo B', 'Múltiple', 'Frasco x 90 cápsulas'],
                ['Omega 3', 'omega-3', 'Ácidos grasos esenciales para salud cardiovascular', 28.99, null, 100, $cat_vitaminas, $brand_abbott, 1, 1, 0, 'EPA y DHA', '1000mg', 'Frasco x 60 cápsulas'],
                ['Multivitamínico Complete', 'multivitaminico-complete', 'Fórmula completa de vitaminas y minerales', 32.50, 29.99, 120, $cat_vitaminas, $brand_abbott, 1, 1, 0, 'Múltiple', 'Diaria', 'Frasco x 90 tabletas'],
                ['Alcohol en Gel', 'alcohol-gel', 'Gel desinfectante antibacterial para manos', 6.99, null, 300, $cat_cuidado, $brand_jj, 1, 0, 0, 'Alcohol 70%', '70%', 'Frasco x 250ml'],
                ['Termómetro Digital', 'termometro-digital', 'Termómetro digital de lectura rápida', 12.99, null, 80, $cat_cuidado, $brand_jj, 1, 1, 0, null, null, 'Unidad'],
                ['Protector Solar FPS 50+', 'protector-solar-fps50', 'Protección solar de amplio espectro', 24.99, 21.99, 100, $cat_dermato, $brand_jj, 1, 1, 0, 'Óxido de Zinc', 'FPS 50+', 'Frasco x 120ml'],
                ['Crema Hidratante Facial', 'crema-hidratante-facial', 'Hidratación profunda para todo tipo de piel', 18.50, null, 90, $cat_dermato, $brand_jj, 1, 0, 0, null, null, 'Frasco x 50g']
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO products (name, slug, description, price, sale_price, stock, category_id, brand_id, is_active, is_featured, requires_prescription, active_ingredient, dosage, presentation, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            foreach ($products as $product) {
                $stmt->execute($product);
            }
            $results[] = "✅ " . count($products) . " productos farmacéuticos de ejemplo creados";
            
            // =====================================================
            // COMMIT TRANSACTION
            // =====================================================
            $pdo->commit();
            
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
