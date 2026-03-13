<?php
// HERRAMIENTA TEMPORAL - ELIMINAR DEL SERVIDOR DESPUÉS DE USAR
require_once __DIR__ . '/config/config.php';

if (!isAdmin()) {
    http_response_code(403);
    die('Acceso denegado. Debes iniciar sesión como administrador.');
}

$old_base = 'https://mediumvioletred-lobster-199641.hostingersite.com/uploads/products/';
$local_dir = __DIR__ . '/uploads/products/';

// Obtener todas las imágenes de la BD
try {
    $stmt = executeQuery("SELECT id, name, image FROM products WHERE image IS NOT NULL AND image != ''");
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    die('Error al leer la BD: ' . $e->getMessage());
}

$copied   = 0;
$skipped  = 0;
$failed   = 0;
$results  = [];

foreach ($products as $p) {
    $filename   = $p['image'];
    $local_path = $local_dir . $filename;

    if (file_exists($local_path)) {
        $skipped++;
        $results[] = ['status' => 'skip', 'msg' => "Ya existe: {$p['name']} ({$filename})"];
        continue;
    }

    $old_url = $old_base . $filename;

    // Intentar con cURL primero
    $content = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($old_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $content   = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code !== 200) $content = false;
    }

    // Fallback a file_get_contents
    if ($content === false) {
        $content = @file_get_contents($old_url);
    }

    if ($content !== false && strlen($content) > 0) {
        if (file_put_contents($local_path, $content)) {
            $copied++;
            $results[] = ['status' => 'ok', 'msg' => "Copiado: {$p['name']} ({$filename})"];
        } else {
            $failed++;
            $results[] = ['status' => 'error', 'msg' => "Sin permisos para escribir: {$filename}"];
        }
    } else {
        $failed++;
        $results[] = ['status' => 'error', 'msg' => "No encontrado en origen: {$p['name']} ({$filename})"];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restaurar Imágenes</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        h1 { color: #2c3e50; }
        .summary { display: flex; gap: 20px; margin: 20px 0; }
        .badge { padding: 10px 20px; border-radius: 8px; font-weight: bold; font-size: 18px; }
        .ok    { background: #d4edda; color: #155724; }
        .skip  { background: #fff3cd; color: #856404; }
        .error { background: #f8d7da; color: #721c24; }
        .log   { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-top: 20px; }
        .log p { margin: 4px 0; font-size: 14px; }
        .warn  { background: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 8px; margin-top: 30px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🖼️ Restaurar Imágenes de Productos</h1>
    <div class="summary">
        <div class="badge ok">✅ Copiadas: <?php echo $copied; ?></div>
        <div class="badge skip">⏭️ Ya existían: <?php echo $skipped; ?></div>
        <div class="badge error">❌ Fallidas: <?php echo $failed; ?></div>
    </div>
    <div class="log">
        <?php foreach ($results as $r): ?>
            <p style="color: <?php echo $r['status'] === 'ok' ? '#155724' : ($r['status'] === 'skip' ? '#856404' : '#721c24'); ?>">
                <?php echo htmlspecialchars($r['msg']); ?>
            </p>
        <?php endforeach; ?>
    </div>
    <div class="warn">
        ⚠️ IMPORTANTE: Elimina este archivo del servidor cuando hayas terminado.<br>
        Ruta: <code>/restore-images.php</code>
    </div>
</body>
</html>
