<?php
/**
 * =====================================================
 * INSTALACIÓN COMPLETA - MediCareOnline
 * =====================================================
 * Script para crear toda la estructura de base de datos desde cero
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('INSTALL_PASSWORD', 'MediCare2026');

// Credenciales de base de datos
$host = 'localhost';
$dbname = 'u851317150_mg360_db';
$username = 'u851317150_mg360_user';
$password = 'MultiGamer2025';

$executed = false;
$results = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['install_password']) || $_POST['install_password'] !== INSTALL_PASSWORD) {
        $errors[] = "Password incorrecta";
    } else {
        try {
            // Conectar a la base de datos
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $results[] = "✅ Conexión establecida a: $dbname";
            
            // ==================== CREAR ARCHIVO config/database.php ====================
            $database_php_content = "<?php
/**
 * CONFIGURACIÓN DE BASE DE DATOS - MediCareOnline
 */
\$host = 'localhost';
\$dbname = 'u851317150_mg360_db';
\$username = 'u851317150_mg360_user';
\$password = 'MultiGamer2025';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    die('Error de conexión: ' . \$e->getMessage());
}
?>";
            
            if (!is_dir('config')) {
                mkdir('config', 0755, true);
            }
            file_put_contents('config/database.php', $database_php_content);
            $results[] = "✅ Archivo config/database.php creado";
            
            // ==================== ELIMINAR TABLAS ANTIGUAS ====================
            // Desactivar verificación de foreign keys
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            $pdo->exec("DROP TABLE IF EXISTS prescriptions");
            $pdo->exec("DROP TABLE IF EXISTS order_items");
            $pdo->exec("DROP TABLE IF EXISTS orders");
            $pdo->exec("DROP TABLE IF EXISTS reviews");
            $pdo->exec("DROP TABLE IF EXISTS product_images");
            $pdo->exec("DROP TABLE IF EXISTS products");
            $pdo->exec("DROP TABLE IF EXISTS categories");
            $pdo->exec("DROP TABLE IF EXISTS brands");
            $pdo->exec("DROP TABLE IF EXISTS users");
            
            // Reactivar verificación de foreign keys
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            $results[] = "✅ Tablas antiguas eliminadas";
            
            $pdo->exec("
                CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    first_name VARCHAR(100) NOT NULL,
                    last_name VARCHAR(100),
                    phone VARCHAR(20),
                    role ENUM('cliente', 'administrador') DEFAULT 'cliente',
                    is_active TINYINT(1) DEFAULT 1,
                    email_verified TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $results[] = "✅ Tabla users creada";
            
            // ==================== TABLA CATEGORIES ====================
            $pdo->exec("
                CREATE TABLE categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    slug VARCHAR(100) UNIQUE NOT NULL,
                    description TEXT,
                    icon VARCHAR(50),
                    is_active TINYINT(1) DEFAULT 1,
                    display_order INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $results[] = "✅ Tabla categories creada";
            
            // ==================== TABLA BRANDS ====================
            $pdo->exec("
                CREATE TABLE brands (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    slug VARCHAR(100) UNIQUE NOT NULL,
                    description TEXT,
                    logo VARCHAR(255),
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $results[] = "✅ Tabla brands creada";
            
            // ==================== TABLA PRODUCTS ====================
            $pdo->exec("
                CREATE TABLE products (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) UNIQUE NOT NULL,
                    description TEXT,
                    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    stock_quantity INT DEFAULT 0,
                    category_id INT,
                    brand_id INT,
                    is_active TINYINT(1) DEFAULT 1,
                    is_featured TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $results[] = "✅ Tabla products creada";
            
            // ==================== TABLA ORDERS ====================
            $pdo->exec("
                CREATE TABLE orders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_number VARCHAR(50) UNIQUE NOT NULL,
                    user_id INT,
                    customer_name VARCHAR(255) NOT NULL,
                    customer_email VARCHAR(255) NOT NULL,
                    customer_phone VARCHAR(20),
                    shipping_address TEXT NOT NULL,
                    total_amount DECIMAL(10,2) NOT NULL,
                    status ENUM('pendiente', 'confirmado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $results[] = "✅ Tabla orders creada";
            
            // ==================== TABLA ORDER_ITEMS ====================
            $pdo->exec("
                CREATE TABLE order_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    order_id INT NOT NULL,
                    product_id INT,
                    product_name VARCHAR(255) NOT NULL,
                    quantity INT NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    subtotal DECIMAL(10,2) NOT NULL,
                    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $results[] = "✅ Tabla order_items creada";
            
            // ==================== INSERTAR CATEGORÍAS ====================
            $categories = [
                ['Medicina General', 'medicina-salud', '💊', 1],
                ['Vitaminas y Suplementos', 'vitaminas-suplementos', '🌿', 2],
                ['Cuidado Personal', 'cuidado-personal', '🧴', 3],
                ['Dermatología', 'dermatologia', '✨', 4],
                ['Bebé y Mamá', 'bebe-mama', '👶', 5],
                ['Primeros Auxilios', 'primeros-auxilios', '🏥', 6],
                ['Salud Digestiva', 'salud-digestiva', '🔬', 7],
                ['Nutrición Deportiva', 'nutricion-deportiva', '💪', 8]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, display_order) VALUES (?, ?, ?, ?)");
            foreach ($categories as $cat) {
                $stmt->execute($cat);
            }
            $results[] = "✅ " . count($categories) . " categorías insertadas";
            
            // ==================== INSERTAR MARCAS ====================
            $brands = [
                ['Bayer', 'bayer'],
                ['Pfizer', 'pfizer'],
                ['Johnson & Johnson', 'johnson-johnson'],
                ['Abbott', 'abbott'],
                ['Roche', 'roche'],
                ['Sanofi', 'sanofi'],
                ['GSK', 'gsk'],
                ['Novartis', 'novartis'],
                ['Merck', 'merck'],
                ['Boehringer', 'boehringer']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO brands (name, slug) VALUES (?, ?)");
            foreach ($brands as $brand) {
                $stmt->execute($brand);
            }
            $results[] = "✅ " . count($brands) . " marcas insertadas";
            
            // ==================== INSERTAR PRODUCTOS ====================
            $cat_medicina = $pdo->query("SELECT id FROM categories WHERE slug = 'medicina-salud'")->fetchColumn();
            $cat_vitaminas = $pdo->query("SELECT id FROM categories WHERE slug = 'vitaminas-suplementos'")->fetchColumn();
            $cat_cuidado = $pdo->query("SELECT id FROM categories WHERE slug = 'cuidado-personal'")->fetchColumn();
            
            $brand_bayer = $pdo->query("SELECT id FROM brands WHERE slug = 'bayer'")->fetchColumn();
            $brand_pfizer = $pdo->query("SELECT id FROM brands WHERE slug = 'pfizer'")->fetchColumn();
            $brand_jj = $pdo->query("SELECT id FROM brands WHERE slug = 'johnson-johnson'")->fetchColumn();
            
            $products = [
                ['Paracetamol 500mg', 'paracetamol-500mg', 'Analgésico y antipirético para dolor y fiebre', 8.99, 100, $cat_medicina, $brand_bayer, 1],
                ['Ibuprofeno 400mg', 'ibuprofeno-400mg', 'Antiinflamatorio para dolor muscular', 12.50, 150, $cat_medicina, $brand_bayer, 1],
                ['Vitamina C 1000mg', 'vitamina-c-1000mg', 'Refuerza el sistema inmunológico', 15.99, 200, $cat_vitaminas, $brand_pfizer, 1],
                ['Complejo B', 'complejo-b', 'Vitaminas del complejo B para energía', 22.50, 150, $cat_vitaminas, $brand_pfizer, 1],
                ['Alcohol en Gel 500ml', 'alcohol-gel', 'Desinfectante antibacterial para manos', 6.99, 300, $cat_cuidado, $brand_jj, 1],
                ['Termómetro Digital', 'termometro-digital', 'Medición rápida y precisa de temperatura', 12.99, 80, $cat_cuidado, $brand_jj, 1]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, stock_quantity, category_id, brand_id, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($products as $product) {
                $stmt->execute($product);
            }
            $results[] = "✅ " . count($products) . " productos insertados";
            
            // ==================== CREAR USUARIO ADMIN ====================
            $admin_email = 'admin@medicareonline.com';
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, role, is_active, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$admin_email, $admin_password, 'Administrador', 'MediCare', 'administrador', 1, 1]);
            
            $results[] = "✅ Usuario administrador creado";
            $results[] = "<strong>📧 Email:</strong> $admin_email";
            $results[] = "<strong>🔑 Password:</strong> admin123";
            
            $executed = true;
            
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación MediCareOnline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #00D4FF 0%, #0088AA 100%); min-height: 100vh; padding: 40px 0; font-family: 'Poppins', sans-serif; }
        .install-container { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 800px; margin: 0 auto; }
        .success-box { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; border-radius: 10px; margin: 10px 0; }
        .error-box { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 10px; }
        .btn-install { background: linear-gradient(135deg, #00D4FF, #00A8CC); border: none; color: white; padding: 15px 40px; font-weight: 600; border-radius: 10px; width: 100%; }
        .icon-box { width: 80px; height: 80px; background: linear-gradient(135deg, #00D4FF, #00A8CC); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .icon-box i { font-size: 40px; color: white; }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="text-center mb-4">
            <div class="icon-box"><i class="fas fa-database"></i></div>
            <h1>Instalación MediCareOnline</h1>
            <p class="text-muted">Configuración completa de base de datos</p>
        </div>

        <?php if ($executed): ?>
            <div class="success-box">
                <h3>🎉 ¡Instalación Completada!</h3>
                <?php foreach ($results as $result): ?>
                    <p><?php echo $result; ?></p>
                <?php endforeach; ?>
                <hr>
                <div class="d-grid gap-2 mt-4">
                    <a href="admin/login_simple.php" class="btn btn-success btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Ir al Login Admin
                    </a>
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-home"></i> Ver Sitio Web
                    </a>
                </div>
                <div class="alert alert-danger mt-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>IMPORTANTE:</strong> Elimina este archivo (install_complete.php) inmediatamente por seguridad.
                </div>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Esta instalación:</h5>
                    <ul>
                        <li>Creará todas las tablas necesarias</li>
                        <li>Insertará categorías y marcas</li>
                        <li>Creará productos de ejemplo</li>
                        <li>Creará usuario administrador</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-lock"></i> Password de Instalación</label>
                    <input type="password" class="form-control form-control-lg" name="install_password" placeholder="MediCare2026" required>
                </div>

                <button type="submit" class="btn-install">
                    <i class="fas fa-rocket"></i> Iniciar Instalación
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
