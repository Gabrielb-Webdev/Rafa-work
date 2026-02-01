<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

// Configurar headers para descarga CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=productos_ejemplo.csv');

// Crear el output
$output = fopen('php://output', 'w');

// Agregar BOM para UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Escribir encabezados
fputcsv($output, ['nombre', 'descripcion', 'stock']);

// Agregar filas de ejemplo
fputcsv($output, ['Producto Ejemplo 1', 'Descripción del producto ejemplo 1', '999999']);
fputcsv($output, ['Producto Ejemplo 2', 'Descripción del producto ejemplo 2', '500']);
fputcsv($output, ['Producto Ejemplo 3', 'Descripción del producto ejemplo 3', '1000']);

fclose($output);
exit;
