<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo $pageTitle ?? 'Forethink Health - Online Medicine Store'; ?></title>
    <meta name="description" content="Forethink Health - Tu tienda online de medicinas, vitaminas y suplementos de confianza.">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/assets/images/logo.png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=6.7">
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
            <a href="<?php echo BASE_URL; ?>/" class="logo">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" />
            </a>

            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>/">HOME</a></li>
                <li><a href="<?php echo BASE_URL; ?>/about">ABOUT</a></li>
                <li><a href="<?php echo BASE_URL; ?>/products">ONLINE BUY</a></li>
                <li><a href="<?php echo BASE_URL; ?>/contact">CONTACT US</a></li>
            </ul>

            <div class="nav-right">
                <div class="search-box">
                    <input type="text" placeholder="SEARCH" id="searchInput" autocomplete="off">
                    <button type="button" id="searchButton"><i class="fas fa-search"></i></button>
                </div>

                <a href="<?php echo BASE_URL; ?>/cart" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php 
                    $cartCount = 0;
                    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            $cartCount += isset($item['quantity']) ? intval($item['quantity']) : 0;
                        }
                    }
                    ?>
                    <span class="cart-count cart-badge"><?php echo $cartCount; ?></span>
                </a>

                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <div class="user-icon">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo strtoupper(explode(' ', $_SESSION['user_name'] ?? 'User')[0]); ?></span>
                        </div>
                        <div class="dropdown-content">
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/index.php">
                                    <i class="fas fa-user-shield"></i>
                                    <span>Admin Panel</span>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/profile">
                                <i class="fas fa-user"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/orders">
                                <i class="fas fa-shopping-bag"></i>
                                <span>My Orders</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login" class="user-icon">
                        <i class="fas fa-user"></i> LOGIN
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
