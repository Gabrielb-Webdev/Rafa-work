<?php
require_once __DIR__ . '/../config/config.php';

// Verificar que sea administrador
if (!isLoggedIn() || !isAdmin()) {
    redirect('/login.php');
}

$success = 0;
$error = '';
$imported = 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Validar archivo
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filePath = $file['tmp_name'];
        
        // Abrir archivo CSV
        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            // Leer encabezado
            $header = fgetcsv($handle, 1000, ',');
            
            if (!$header || count($header) < 2) {
                $error = 'El archivo CSV no tiene el formato correcto';
            } else {
                // Procesar cada fila
                $row_number = 1;
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    $row_number++;
                    
                    // Validar que tenga al menos nombre
                    if (empty($data[0])) {
                        $errors[] = "Fila $row_number: El nombre es obligatorio";
                        continue;
                    }
                    
                    $name = trim($data[0]);
                    $description = isset($data[1]) ? trim($data[1]) : '';
                    $stock = isset($data[2]) && is_numeric($data[2]) ? intval($data[2]) : 999999;
                    
                    try {
                        // Generar slug único
                        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                        $slug = preg_replace('/-+/', '-', $slug);
                        $slug = trim($slug, '-');
                        
                        // Verificar si el slug ya existe
                        $stmt = executeQuery("SELECT COUNT(*) as count FROM products WHERE slug = ?", [$slug]);
                        $existing = $stmt->fetch();
                        if ($existing['count'] > 0) {
                            $slug = $slug . '-' . time() . '-' . $row_number;
                        }
                        
                        // Insertar producto
                        executeQuery(
                            "INSERT INTO products (name, slug, description, price, stock, category_id, is_active, created_at) 
                             VALUES (?, ?, ?, 0, ?, NULL, 1, NOW())",
                            [$name, $slug, $description, $stock]
                        );
                        
                        $imported++;
                        
                    } catch (Exception $e) {
                        $errors[] = "Fila $row_number ($name): " . $e->getMessage();
                    }
                }
                
                fclose($handle);
                
                if ($imported > 0) {
                    $success = $imported;
                }
            }
        } else {
            $error = 'No se pudo leer el archivo CSV';
        }
    } else {
        $error = 'Error al subir el archivo';
    }
}

// Redirigir con mensaje
if ($success > 0) {
    $_SESSION['csv_import_success'] = $imported;
    $_SESSION['csv_import_errors'] = $errors;
    header('Location: ' . BASE_URL . '/admin/productos.php?csv_imported=' . $imported);
} else {
    $_SESSION['csv_import_error'] = $error ?: 'No se pudo importar el archivo';
    header('Location: ' . BASE_URL . '/admin/productos.php?csv_error=1');
}
exit;
