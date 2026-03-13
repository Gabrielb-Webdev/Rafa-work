<?php
// Configuración general del sitio
session_start();

// URL base del sitio
define('BASE_URL', 'https://mediumvioletred-lobster-199641.hostingersite.com');

// Configuración del sitio
define('SITE_NAME', 'Forethink Health');
define('SITE_EMAIL', 'info@forethinkhealth.com');
define('SITE_PHONE', '+01 123456789');

// Rutas del proyecto
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Manejo de errores (cambiar a false en producción)
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Incluir funciones de base de datos
require_once __DIR__ . '/database.php';

// Funciones auxiliares
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Función para cargar el carrito del usuario desde BD
function loadCartFromDB() {
    if (isLoggedIn()) {
        $userId = intval($_SESSION['user_id']);
        try {
            $stmt = executeQuery(
                "SELECT c.*, p.name, p.price, p.stock, p.image, p.is_active 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ? AND p.is_active = 1",
                [$userId]
            );
            $items = $stmt->fetchAll();
            
            $_SESSION['cart'] = [];
            foreach ($items as $item) {
                $_SESSION['cart'][(int)$item['product_id']] = [
                    'id' => (int)$item['product_id'],
                    'name' => $item['name'],
                    'price' => (float)$item['price'],
                    'quantity' => min((int)$item['quantity'], (int)$item['stock']),
                    'image' => $item['image']
                ];
            }
        } catch (Exception $e) {
            // Mantener carrito de sesión si hay error
        }
    }
}

// Cargar carrito al iniciar (solo si está logueado)
if (isLoggedIn() && !isset($_SESSION['cart_loaded'])) {
    loadCartFromDB();
    $_SESSION['cart_loaded'] = true;
}
