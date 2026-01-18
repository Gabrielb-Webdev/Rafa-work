<?php
// Configuración general del sitio
session_start();

// URL base del sitio
define('BASE_URL', 'https://mediumvioletred-lobster-199641.hostingersite.com');

// Configuración del sitio
define('SITE_NAME', 'Forethink Health');
define('SITE_EMAIL', 'demo@gmail.com');
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
