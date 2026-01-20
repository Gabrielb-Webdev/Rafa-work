<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Forethink Health - Online Medicine Store'; ?></title>
    <meta name="description" content="Forethink Health - Tu tienda online de medicinas, vitaminas y suplementos de confianza.">
    <link rel="icon" type="image/jpeg" href="<?php echo BASE_URL; ?>/assets/images/logo.jpeg">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/assets/images/logo.jpeg">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=6.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <a href="tel:<?php echo SITE_PHONE; ?>">
                    <i class="fas fa-phone"></i> CALL: <?php echo SITE_PHONE; ?>
                </a>
            </div>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <header>
        <nav>
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.jpeg" alt="<?php echo SITE_NAME; ?>" />
            </a>

            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>/index.php">HOME</a></li>
                <li><a href="<?php echo BASE_URL; ?>/about.php">ABOUT</a></li>
                <li><a href="<?php echo BASE_URL; ?>/products.php">ONLINE BUY</a></li>
                <li><a href="<?php echo BASE_URL; ?>/contact.php">CONTACT US</a></li>
            </ul>

            <div class="nav-right">
                <div class="search-box">
                    <input type="text" placeholder="SEARCH" id="searchInput">
                    <button type="button"><i class="fas fa-search"></i></button>
                </div>

                <a href="<?php echo BASE_URL; ?>/cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>

                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <div class="user-icon">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo strtoupper(explode(' ', $_SESSION['user_name'] ?? 'Usuario')[0]); ?></span>
                        </div>
                        <div class="dropdown-content">
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/index.php">
                                    <i class="fas fa-user-shield"></i>
                                    <span>Panel Admin</span>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/profile.php">
                                <i class="fas fa-user"></i>
                                <span>Mi Perfil</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/orders.php">
                                <i class="fas fa-shopping-bag"></i>
                                <span>Mis Pedidos</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="user-icon">
                        <i class="fas fa-user"></i> LOGIN
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
