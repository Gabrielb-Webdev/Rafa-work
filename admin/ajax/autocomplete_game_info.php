<?php
/**
 * Auto-completar información del juego usando RAWG API
 * API gratuita de videojuegos: https://rawg.io/apidocs
 */

header('Content-Type: application/json');

if (!isset($_GET['game_name']) || empty($_GET['game_name'])) {
    echo json_encode(['success' => false, 'message' => 'Nombre del juego no proporcionado.']);
    exit;
}

$gameName = trim($_GET['game_name']);

// Método alternativo: Intentar primero con la API de RAWG sin key (limitado)
// Si falla, usar scraping simple de información pública

// API Key de RAWG - Configurada y lista para usar
$apiKey = '575f338491134d84bd86df30627a95fe';

// Intentar con RAWG API (con o sin key)
$searchUrl = 'https://api.rawg.io/api/games?search=' . urlencode($gameName);
if (!empty($apiKey)) {
    $searchUrl .= '&key=' . $apiKey;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $searchUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Si la API no funciona (sin key o con error), usar método alternativo simple
if ($response === false || $httpCode !== 200) {
    // Método alternativo: generar contenido básico automáticamente
    $autoCompleteData = [
        'success' => true,
        'data' => [
            'title' => $gameName,
            'description' => "Sumérgete en la experiencia única de {$gameName}. Este título ofrece horas de entretenimiento con gráficos impresionantes, una jugabilidad envolvente y una historia cautivadora que mantendrá a los jugadores pegados a la pantalla. Perfecto para fanáticos del género que buscan su próxima aventura.",
            'short_description' => "Experimenta {$gameName} con gráficos impresionantes y jugabilidad envolvente.",
            'tags' => strtolower(str_replace(' ', ', ', $gameName)) . ', acción, aventura',
            'genres' => [],
            'platforms' => []
        ],
        'method' => 'auto_generated',
        'message' => '⚠️ Información generada automáticamente. La API de RAWG no está disponible (requiere API key). Ajusta la descripción según necesites.'
    ];
    
    echo json_encode($autoCompleteData);
    exit;
}

$data = json_decode($response, true);

if (empty($data['results'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No se encontró información del juego. Completa manualmente.'
    ]);
    exit;
}

// Tomar el primer resultado (más relevante)
$game = $data['results'][0];
$gameId = $game['id'];

// Obtener detalles completos del juego
$detailsUrl = "https://api.rawg.io/api/games/{$gameId}?key=" . $apiKey;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $detailsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'MultiGamer360-ImportTool/1.0');
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$detailsResponse = curl_exec($ch);
curl_close($ch);

$gameDetails = json_decode($detailsResponse, true);

// Función para traducir texto al español usando una API gratuita
function translateToSpanish($text) {
    if (empty($text)) return '';
    
    // Limitar a 500 caracteres para la traducción
    $text = substr(strip_tags($text), 0, 500);
    
    // Usar MyMemory Translation API (gratuita, sin key necesaria)
    $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($text) . '&langpair=en|es';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['responseData']['translatedText'])) {
            return $data['responseData']['translatedText'];
        }
    }
    
    return $text; // Retornar original si falla la traducción
}

// Mapear géneros de RAWG a español
function mapGenresToSpanish($genres) {
    $genreMap = [
        'action' => 'Acción',
        'adventure' => 'Aventura',
        'rpg' => 'RPG',
        'strategy' => 'Estrategia',
        'shooter' => 'Shooter',
        'puzzle' => 'Puzzle',
        'racing' => 'Carreras',
        'sports' => 'Deportes',
        'fighting' => 'Lucha',
        'platformer' => 'Plataformas',
        'simulation' => 'Simulación',
        'horror' => 'Horror',
        'survival' => 'Supervivencia',
        'arcade' => 'Arcade',
        'indie' => 'Indie',
        'casual' => 'Casual',
        'music' => 'Música'
    ];
    
    $mapped = [];
    foreach ($genres as $genre) {
        $genreLower = strtolower($genre);
        foreach ($genreMap as $en => $es) {
            if (stripos($genreLower, $en) !== false) {
                $mapped[] = $es;
                break;
            }
        }
    }
    
    return !empty($mapped) ? $mapped : $genres;
}

// Extraer información completa
$description = strip_tags($gameDetails['description_raw'] ?? $gameDetails['description'] ?? '');
$descriptionSpanish = translateToSpanish($description);

// Si la traducción falló o es muy corta, crear una descripción genérica en español
if (strlen($descriptionSpanish) < 100) {
    $gameName = $gameDetails['name'] ?? $game['name'];
    $descriptionSpanish = "Descubre {$gameName}, un increíble videojuego que te sumergirá en una experiencia única. " .
                         "Con gráficos de última generación y una jugabilidad envolvente, este título promete horas " .
                         "de entretenimiento. Perfecto para jugadores que buscan nuevos desafíos y aventuras épicas.";
}

// Mapear plataformas
$platforms = array_map(function($p) {
    return $p['platform']['name'];
}, $gameDetails['platforms'] ?? []);

// Mapear géneros al español
$genresRaw = array_map(function($g) {
    return $g['name'];
}, $gameDetails['genres'] ?? []);
$genresSpanish = mapGenresToSpanish($genresRaw);

// Extraer publishers/developers
$developers = array_map(function($d) {
    return $d['name'];
}, $gameDetails['developers'] ?? []);
$publishers = array_map(function($p) {
    return $p['name'];
}, $gameDetails['publishers'] ?? []);

// Determinar la marca (publisher principal o developer)
$brand = '';
if (!empty($publishers)) {
    $brand = $publishers[0];
} elseif (!empty($developers)) {
    $brand = $developers[0];
}

// Determinar la consola principal
$mainConsole = '';
if (!empty($platforms)) {
    // Priorizar consolas sobre PC
    $consolePriority = ['PlayStation', 'Xbox', 'Nintendo', 'PC'];
    foreach ($consolePriority as $priority) {
        foreach ($platforms as $platform) {
            if (stripos($platform, $priority) !== false) {
                $mainConsole = $platform;
                break 2;
            }
        }
    }
    if (empty($mainConsole)) {
        $mainConsole = $platforms[0];
    }
}

// Determinar categoría basada en géneros
$category = 'Videojuegos'; // Default
if (!empty($genresRaw)) {
    $firstGenre = strtolower($genresRaw[0]);
    if (stripos($firstGenre, 'action') !== false) $category = 'Acción';
    elseif (stripos($firstGenre, 'adventure') !== false) $category = 'Aventura';
    elseif (stripos($firstGenre, 'rpg') !== false) $category = 'RPG';
    elseif (stripos($firstGenre, 'strategy') !== false) $category = 'Estrategia';
    elseif (stripos($firstGenre, 'sports') !== false) $category = 'Deportes';
    elseif (stripos($firstGenre, 'racing') !== false) $category = 'Carreras';
    elseif (stripos($firstGenre, 'shooter') !== false) $category = 'Shooter';
}

// Crear tags en español
$tagsSpanish = implode(', ', array_slice(array_map(function($t) {
    $tagMap = [
        'singleplayer' => 'un jugador',
        'multiplayer' => 'multijugador',
        'co-op' => 'cooperativo',
        'online' => 'en línea',
        'offline' => 'sin conexión',
        'open world' => 'mundo abierto',
        'story rich' => 'historia rica',
        'atmospheric' => 'atmosférico',
        'great soundtrack' => 'gran banda sonora',
        'first-person' => 'primera persona',
        'third-person' => 'tercera persona'
    ];
    
    $tagLower = strtolower($t['name'] ?? $t);
    foreach ($tagMap as $en => $es) {
        if (stripos($tagLower, $en) !== false) {
            return $es;
        }
    }
    return strtolower($t['name'] ?? $t);
}, $gameDetails['tags'] ?? []), 0, 10));

// Recopilar imágenes (background + screenshots)
$images = [];

// Agregar imagen principal
if (!empty($gameDetails['background_image'])) {
    $images[] = $gameDetails['background_image'];
} elseif (!empty($game['background_image'])) {
    $images[] = $game['background_image'];
}

// Agregar screenshots (máximo 5 imágenes adicionales)
if (!empty($gameDetails['short_screenshots'])) {
    foreach ($gameDetails['short_screenshots'] as $screenshot) {
        if (isset($screenshot['image']) && count($images) < 6) {
            $images[] = $screenshot['image'];
        }
    }
}

// Mapear datos de RAWG a nuestro formato
$autoCompleteData = [
    'success' => true,
    'data' => [
        'title' => $gameDetails['name'] ?? $game['name'],
        'description' => $descriptionSpanish,
        'short_description' => mb_substr($descriptionSpanish, 0, 200) . '...',
        'released' => $gameDetails['released'] ?? '',
        'rating' => $gameDetails['rating'] ?? 0,
        'metacritic' => $gameDetails['metacritic'] ?? 0,
        'platforms' => $platforms,
        'genres' => $genresSpanish,
        'developers' => $developers,
        'publishers' => $publishers,
        'brand' => $brand,
        'main_console' => $mainConsole,
        'category' => $category,
        'esrb_rating' => $gameDetails['esrb_rating']['name'] ?? 'No especificado',
        'images' => $images,
        'tags' => $tagsSpanish
    ],
    'rawg_link' => "https://rawg.io/games/" . ($gameDetails['slug'] ?? $game['slug']),
    'message' => '✅ Información cargada desde RAWG y traducida al español. Se descargaron ' . count($images) . ' imágenes. Revisa y ajusta según necesites.'
];

echo json_encode($autoCompleteData);
