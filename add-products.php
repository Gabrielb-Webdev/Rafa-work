<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Script para agregar productos de ejemplo con imágenes

$products = [
    [
        'name' => 'Paracetamol 500mg',
        'description' => 'Analgésico y antipirético efectivo para el alivio del dolor y fiebre. Ideal para dolores de cabeza, musculares y resfriados.',
        'price' => 5.99,
        'category_id' => 1,
        'image_url' => 'assets/images/med1.svg',
        'stock' => 150
    ],
    [
        'name' => 'Ibuprofen 400mg',
        'description' => 'Antiinflamatorio no esteroideo. Alivia el dolor, reduce la inflamación y baja la fiebre. Efectivo para dolores articulares.',
        'price' => 8.50,
        'category_id' => 1,
        'image_url' => 'assets/images/med2.svg',
        'stock' => 120
    ],
    [
        'name' => 'Vitamina C 1000mg',
        'description' => 'Suplemento de vitamina C de alta potencia. Fortalece el sistema inmunológico y promueve la salud general.',
        'price' => 12.99,
        'category_id' => 2,
        'image_url' => 'assets/images/med3.svg',
        'stock' => 200
    ],
    [
        'name' => 'Multivitamínico Completo',
        'description' => 'Fórmula completa con vitaminas y minerales esenciales. Apoya la salud diaria y el bienestar general.',
        'price' => 18.99,
        'category_id' => 2,
        'image_url' => 'assets/images/med4.svg',
        'stock' => 90
    ],
    [
        'name' => 'Amoxicilina 500mg',
        'description' => 'Antibiótico de amplio espectro. Efectivo contra infecciones bacterianas. Requiere prescripción médica.',
        'price' => 15.50,
        'category_id' => 3,
        'image_url' => 'assets/images/med5.svg',
        'stock' => 60
    ],
    [
        'name' => 'Aspirina 100mg',
        'description' => 'Ácido acetilsalicílico. Antiagregante plaquetario y analgésico. Uso cardiovascular y dolor leve.',
        'price' => 6.75,
        'category_id' => 1,
        'image_url' => 'assets/images/med6.svg',
        'stock' => 180
    ],
    [
        'name' => 'Omeprazol 20mg',
        'description' => 'Inhibidor de la bomba de protones. Tratamiento de acidez estomacal, reflujo y úlceras gástricas.',
        'price' => 10.99,
        'category_id' => 3,
        'image_url' => 'assets/images/product-placeholder.svg',
        'stock' => 110
    ],
    [
        'name' => 'Loratadina 10mg',
        'description' => 'Antihistamínico de segunda generación. Alivia síntomas de alergias sin causar somnolencia.',
        'price' => 9.25,
        'category_id' => 1,
        'image_url' => 'assets/images/product-placeholder.svg',
        'stock' => 140
    ],
    [
        'name' => 'Complejo B-12',
        'description' => 'Suplemento de vitaminas del grupo B. Apoya la función nerviosa, energía y metabolismo celular.',
        'price' => 14.50,
        'category_id' => 2,
        'image_url' => 'assets/images/product-placeholder.svg',
        'stock' => 95
    ],
    [
        'name' => 'Zinc 50mg',
        'description' => 'Suplemento de zinc de alta absorción. Fortalece inmunidad, salud de la piel y cicatrización.',
        'price' => 11.99,
        'category_id' => 2,
        'image_url' => 'assets/images/product-placeholder.svg',
        'stock' => 130
    ],
    [
        'name' => 'Cetirizina 10mg',
        'description' => 'Antihistamínico efectivo para alergias estacionales, urticaria y rinitis alérgica.',
        'price' => 8.99,
        'category_id' => 1,
        'image_url' => 'assets/images/product-placeholder.svg',
        'stock' => 125
    ],
    [
        'name' => 'Omega 3 1000mg',
        'description' => 'Ácidos grasos esenciales EPA y DHA. Promueve salud cardiovascular y función cerebral.',
        'price' => 22.50,
        'category_id' => 2,
        'image_url' => 'assets/images/product-placeholder.svg',
        'stock' => 85
    ]
];

try {
    // Verificar si ya hay productos
    $check = executeQuery("SELECT COUNT(*) as count FROM products");
    if ($check[0]['count'] > 0) {
        echo "Ya hay productos en la base de datos. Limpiando...<br>";
        executeQuery("DELETE FROM products");
    }

    // Insertar productos
    foreach ($products as $product) {
        $query = "INSERT INTO products (name, description, price, category_id, image_url, stock, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        executeQuery($query, [
            $product['name'],
            $product['description'],
            $product['price'],
            $product['category_id'],
            $product['image_url'],
            $product['stock']
        ]);
        echo "✓ Producto agregado: {$product['name']}<br>";
    }

    echo "<br><strong style='color: green;'>✓ Todos los productos se agregaron exitosamente!</strong><br>";
    echo "<a href='index.php'>Ir al inicio</a> | <a href='products.php'>Ver productos</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>Error: " . $e->getMessage() . "</strong>";
}
?>
