<?php
// EJEMPLO DE CONFIGURACIÓN DE BASE DE DATOS
// Copia este archivo como database.php y reemplaza con tus datos reales

// ============================================
// CONFIGURACIÓN PARA HOSTINGER
// ============================================

// Host de la base de datos (normalmente 'localhost' en Hostinger)
define('DB_HOST', 'localhost');

// Nombre de tu base de datos
// Lo encuentras en: Panel Hostinger → Sitios Web → Bases de datos MySQL
// Ejemplo: u851317150_forethink
define('DB_NAME', 'TU_NOMBRE_DE_BASE_DE_DATOS');

// Usuario de MySQL
// Lo encuentras en el mismo lugar que el nombre de la BD
// Ejemplo: u851317150_fh
define('DB_USER', 'TU_USUARIO_MYSQL');

// Contraseña de MySQL
// La configuraste al crear la base de datos
define('DB_PASS', 'TU_CONTRASEÑA_MYSQL');

// Charset (no cambiar)
define('DB_CHARSET', 'utf8mb4');

// ============================================
// NO MODIFICAR DESDE AQUÍ
// ============================================

// Crear conexión
function getConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
    }
}

// Función auxiliar para ejecutar consultas preparadas
function executeQuery($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
