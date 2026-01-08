<?php
/**
 * Procesar CSV y obtener vista previa con auto-completado
 * Retorna productos en JSON para revisión uno por uno
 */

require_once '../../config/database_production.php';

header('Content-Type: application/json');

// Si solo se solicitan los dropdowns (para recargar)
if (isset($_GET['reload_dropdowns'])) {
    try {
        $categorias = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $marcas = $pdo->query("SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $consolas = $pdo->query("SELECT id, name FROM consoles WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $generos = $pdo->query("SELECT id, name FROM genres WHERE is_active = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'dropdowns' => [
                'categories' => $categorias,
                'brands' => $marcas,
                'consoles' => $consolas,
                'genres' => $generos
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if (!isset($_FILES['csv_file'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo.']);
    exit;
}

$file = $_FILES['csv_file'];
$fileName = $file['name'];
$fileTmp = $file['tmp_name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Función para procesar Excel (.xlsx)
function parseXLSX($filePath) {
    $data = [];
    
    // Abrir el archivo ZIP (xlsx es un archivo zip)
    $zip = new ZipArchive;
    if ($zip->open($filePath) !== TRUE) {
        return false;
    }
    
    // Leer el archivo de hoja de cálculo
    $sheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
    $sharedStrings = $zip->getFromName('xl/sharedStrings.xml');
    $zip->close();
    
    if (!$sheetData) return false;
    
    // Parsear strings compartidos
    $strings = [];
    if ($sharedStrings) {
        $xml = simplexml_load_string($sharedStrings);
        foreach ($xml->si as $si) {
            $strings[] = (string)$si->t;
        }
    }
    
    // Parsear datos de la hoja
    $xml = simplexml_load_string($sheetData);
    $rows = [];
    
    foreach ($xml->sheetData->row as $row) {
        $rowData = [];
        foreach ($row->c as $cell) {
            $value = (string)$cell->v;
            
            // Si es un string compartido, buscar el valor real
            if (isset($cell['t']) && (string)$cell['t'] === 's') {
                $value = $strings[(int)$value] ?? '';
            }
            
            $rowData[] = $value;
        }
        $rows[] = $rowData;
    }
    
    return $rows;
}

// Función para procesar CSV
function parseCSV($filePath) {
    $data = [];
    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
    }
    return $data;
}

// Función para procesar archivos XLS (HTML)
function parseHTMLTable($filePath) {
    $content = file_get_contents($filePath);
    
    // Si es HTML, extraer las filas de la tabla
    if (strpos($content, '<table') !== false) {
        $data = [];
        
        // Usar DOMDocument para parsear HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        
        $rows = $dom->getElementsByTagName('tr');
        
        foreach ($rows as $row) {
            $cols = [];
            $cells = $row->getElementsByTagName('td');
            if ($cells->length === 0) {
                $cells = $row->getElementsByTagName('th');
            }
            
            foreach ($cells as $cell) {
                // Limpiar texto: remover saltos de línea y espacios extras
                $text = $cell->textContent;
                $text = preg_replace('/\s+/', ' ', $text);
                $cols[] = trim($text);
            }
            
            if (!empty($cols)) {
                $data[] = $cols;
            }
        }
        
        return $data;
    }
    
    // Si no es HTML, intentar como CSV
    return parseCSV($filePath);
}

// Procesar archivo según extensión
$rows = [];
if ($fileExt === 'xlsx') {
    $rows = parseXLSX($fileTmp);
    if ($rows === false) {
        echo json_encode(['success' => false, 'message' => 'Error al leer archivo XLSX. Intenta con formato XLS o CSV.']);
        exit;
    }
} elseif ($fileExt === 'xls') {
    // Los archivos .xls generados por el sistema son en realidad HTML
    $rows = parseHTMLTable($fileTmp);
} elseif ($fileExt === 'csv') {
    $rows = parseCSV($fileTmp);
} else {
    echo json_encode(['success' => false, 'message' => 'Formato de archivo no soportado. Use .xlsx, .xls o .csv']);
    exit;
}

if (empty($rows) || count($rows) < 3) {
    echo json_encode([
        'success' => false, 
        'message' => 'El archivo está vacío o no tiene datos válidos. Necesita al menos 3 filas (headers, instrucciones, datos).',
        'debug' => ['total_rows' => count($rows)]
    ]);
    exit;
}

// Obtener datos de la base de datos para dropdowns
try {
    $categorias = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $marcas = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $consolas = $pdo->query("SELECT id, name FROM consoles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $generos = $pdo->query("SELECT id, name FROM genres ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener datos de la base de datos: ' . $e->getMessage()]);
    exit;
}

// Mapeo de campos (español -> inglés)
$fieldMap = [
    'nombre_producto' => 'title',
    'tipo_producto' => 'product_type',
    'consola' => 'console_name',
    'condicion' => 'condition',
    'estado' => 'status',
    'stock' => 'stock',
    'precio_pesos' => 'price_cop',
    'precio_dolares' => 'price_usd'
];

// Procesar productos
$products = [];
$headers = array_map(function($h) {
    // Limpiar headers: remover espacios, caracteres especiales, convertir a minúsculas
    $h = trim($h);
    $h = str_replace([' ', '_', '-'], '_', $h);
    $h = strtolower($h);
    // Normalizar caracteres con acentos
    $h = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $h);
    return $h;
}, $rows[0]);

// Debug: registrar headers encontrados
error_log("Headers encontrados: " . print_r($headers, true));

// Saltar la fila de instrucciones (fila 2)
for ($i = 2; $i < count($rows); $i++) {
    $row = $rows[$i];
    
    // Saltar filas vacías
    if (empty(array_filter($row))) {
        continue;
    }
    
    // Crear array asociativo con headers
    $product = [];
    foreach ($headers as $index => $header) {
        $value = isset($row[$index]) ? trim($row[$index]) : '';
        $product[$header] = $value;
    }
    
    // Debug: registrar producto procesado
    error_log("Producto procesado fila $i: " . print_r($product, true));
    
    // Mapear campos (buscar tanto con guion bajo como sin él)
    $mappedProduct = [];
    foreach ($fieldMap as $spanishField => $englishField) {
        // Normalizar el campo español para comparación
        $normalizedSpanish = str_replace('_', '', $spanishField);
        
        // Buscar el valor en product
        $value = '';
        if (isset($product[$spanishField])) {
            $value = $product[$spanishField];
        } else {
            // Buscar sin guiones bajos
            foreach ($product as $key => $val) {
                if (str_replace('_', '', $key) === $normalizedSpanish) {
                    $value = $val;
                    break;
                }
            }
        }
        
        $mappedProduct[$englishField] = $value;
    }
    
    // Debug
    error_log("Producto mapeado: " . print_r($mappedProduct, true));
    
    // Validar datos mínimos (nombre y precio)
    if (empty(trim($mappedProduct['title'])) || empty(trim($mappedProduct['price_cop']))) {
        error_log("Producto saltado - falta título o precio: " . $mappedProduct['title']);
        continue;
    }
    
    // Validar y mapear tipo de producto
    $productTypeMap = [
        'juego' => 'game',
        'consola' => 'console',
        'accesorio' => 'accessory'
    ];
    $mappedProduct['product_type'] = $productTypeMap[strtolower($mappedProduct['product_type'] ?? 'juego')] ?? 'game';
    
    // Mapear estado
    $statusMap = [
        'activo' => 1,
        'inactivo' => 0,
        'agotado' => 0
    ];
    $mappedProduct['status'] = $statusMap[strtolower($mappedProduct['status'])] ?? 1;
    
    // Mapear condición del producto
    $conditionMap = [
        'nuevo' => 'nuevo',
        'usado' => 'usado',
        'refurbished' => 'refurbished',
        'reacondicionado' => 'refurbished'
    ];
    $mappedProduct['condition'] = $conditionMap[strtolower($mappedProduct['condition'] ?? 'nuevo')] ?? 'nuevo';
    
    // Intentar mapear consola a ID
    $consoleName = trim($mappedProduct['console_name'] ?? '');
    $mappedProduct['console_id'] = '';
    if (!empty($consoleName)) {
        foreach ($consolas as $console) {
            if (stripos($console['name'], $consoleName) !== false || stripos($consoleName, $console['name']) !== false) {
                $mappedProduct['console_id'] = $console['id'];
                break;
            }
        }
    }
    
    // Agregar campos vacíos que se completarán en el modal
    $mappedProduct['sku'] = '';
    $mappedProduct['description'] = '';
    $mappedProduct['short_description'] = '';
    // MANTENER el stock del CSV si existe, sino usar 0
    if (!isset($mappedProduct['stock']) || $mappedProduct['stock'] === '') {
        $mappedProduct['stock'] = 0;
    }
    $mappedProduct['category_id'] = '';
    $mappedProduct['brand_id'] = '';
    if (!isset($mappedProduct['console_id']) || $mappedProduct['console_id'] === '') {
        $mappedProduct['console_id'] = '';
    }
    $mappedProduct['genres'] = [];
    $mappedProduct['is_featured'] = 0;
    $mappedProduct['is_new'] = 0;
    $mappedProduct['on_sale'] = 0;
    $mappedProduct['is_active'] = 1;
    // Condición ya mapeada arriba, no sobreescribir
    if (!isset($mappedProduct['condition']) || $mappedProduct['condition'] === '') {
        $mappedProduct['condition'] = 'nuevo';
    }
    $mappedProduct['tags'] = '';
    $mappedProduct['meta_title'] = '';
    $mappedProduct['meta_description'] = '';
    $mappedProduct['images'] = [];
    
    $products[] = $mappedProduct;
}

if (empty($products)) {
    $errorMsg = 'No se encontraron productos válidos en el archivo. ';
    $errorMsg .= 'Verifica que el archivo tenga las columnas: nombre_producto, tipo_producto, consola, estado, precio_pesos, precio_dolares. ';
    $errorMsg .= 'Headers encontrados: ' . implode(', ', $headers);
    
    error_log("ERROR: " . $errorMsg);
    
    echo json_encode([
        'success' => false, 
        'message' => $errorMsg,
        'debug' => [
            'headers' => $headers,
            'total_rows' => count($rows)
        ]
    ]);
    exit;
}

// Retornar productos para revisión
echo json_encode([
    'success' => true,
    'products' => $products,
    'dropdowns' => [
        'categories' => $categorias,
        'brands' => $marcas,
        'consoles' => $consolas,
        'genres' => $generos
    ],
    'message' => 'Productos cargados. Revisa cada uno antes de guardar.'
]);
