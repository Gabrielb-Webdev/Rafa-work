<?php
/**
 * CONFIGURACIÓN DE BASE DE DATOS - MediCareOnline
 * Hostinger Production
 */

// Configuración de base de datos Hostinger
define('DB_HOST', 'localhost');
define('DB_NAME', 'u851317150_mg360_db');
define('DB_USER', 'u851317150_mg360_user');
define('DB_PASS', 'MultiGamer2025');

// Zona horaria
date_default_timezone_set('America/Lima');

// Crear conexión PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Variables para compatibilidad con código antiguo
$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;
?>
