<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u851317150_fh');
define('DB_USER', 'u851317150_fh');
define('DB_PASS', 'Lg030920.');
define('DB_CHARSET', 'utf8mb4');

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
        throw new RuntimeException("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

// Función auxiliar para ejecutar consultas preparadas
function executeQuery($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
