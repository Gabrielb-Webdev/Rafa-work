-- Tabla de productos
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de mensajes de contacto
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de suscriptores al newsletter
CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL UNIQUE,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar categorías de ejemplo
INSERT IGNORE INTO `categories` (`name`, `description`, `is_active`) VALUES
('Suplementos', 'Suplementos alimenticios y nutricionales', 1),
('Vitaminas', 'Vitaminas y minerales esenciales', 1),
('Proteínas', 'Proteínas en polvo y batidos', 1),
('Salud General', 'Productos para el bienestar general', 1),
('Deportivos', 'Suplementos para deportistas', 1);

-- Insertar productos de ejemplo (opcional)
INSERT IGNORE INTO `products` (`name`, `description`, `price`, `stock`, `category`, `is_active`) VALUES
('Multivitamínico Premium', 'Complejo multivitamínico completo con minerales esenciales', 1299.00, 50, 'Vitaminas', 1),
('Proteína Whey', 'Proteína de suero de leche de alta calidad, sabor vainilla', 2499.00, 30, 'Proteínas', 1),
('Omega 3', 'Aceite de pescado rico en EPA y DHA para salud cardiovascular', 899.00, 75, 'Salud General', 1),
('Vitamina D3', 'Vitamina D3 de alta potencia para huesos y sistema inmune', 699.00, 100, 'Vitaminas', 1),
('BCAA 2:1:1', 'Aminoácidos ramificados para recuperación muscular', 1599.00, 40, 'Deportivos', 1);
