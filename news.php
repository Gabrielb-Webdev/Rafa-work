<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Noticias - Forethink Health';

include __DIR__ . '/includes/header.php';
?>

<style>
.news-section {
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 20px;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.news-card {
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.news-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.news-content {
    padding: 25px;
}

.news-date {
    color: var(--primary-color);
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
}

.news-title {
    font-size: 20px;
    margin-bottom: 15px;
    color: var(--text-dark);
}

.news-excerpt {
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 15px;
}

.btn-read-more {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    transition: gap 0.3s;
}

.btn-read-more:hover {
    gap: 8px;
}

.btn-read-more i {
    margin-left: 5px;
}
</style>

<div class="news-section">
    <h1>Noticias y Actualizaciones</h1>
    <p style="color: var(--text-light); margin-top: 10px;">
        Mantente informado sobre las últimas novedades en salud y nuestros productos
    </p>

    <div class="news-grid">
        <div class="news-card">
            <img src="<?php echo BASE_URL; ?>/assets/images/news1.jpg" alt="News" class="news-image"
                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/product-placeholder.png'">
            <div class="news-content">
                <div class="news-date">
                    <i class="fas fa-calendar"></i> <?php echo date('d M Y'); ?>
                </div>
                <h3 class="news-title">Nuevos Productos de Vitamina C</h3>
                <p class="news-excerpt">
                    Descubre nuestra nueva línea de suplementos de Vitamina C de alta absorción, 
                    perfectos para fortalecer tu sistema inmunológico.
                </p>
                <a href="#" class="btn-read-more">
                    Leer más <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="news-card">
            <img src="<?php echo BASE_URL; ?>/assets/images/news2.jpg" alt="News" class="news-image"
                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/product-placeholder.png'">
            <div class="news-content">
                <div class="news-date">
                    <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime('-3 days')); ?>
                </div>
                <h3 class="news-title">Consejos para Mantener una Vida Saludable</h3>
                <p class="news-excerpt">
                    Aprende los mejores hábitos y prácticas para mantener tu salud en óptimas condiciones 
                    durante todo el año.
                </p>
                <a href="#" class="btn-read-more">
                    Leer más <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="news-card">
            <img src="<?php echo BASE_URL; ?>/assets/images/news3.jpg" alt="News" class="news-image"
                 onerror="this.src='<?php echo BASE_URL; ?>/assets/images/product-placeholder.png'">
            <div class="news-content">
                <div class="news-date">
                    <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime('-7 days')); ?>
                </div>
                <h3 class="news-title">Ofertas Especiales del Mes</h3>
                <p class="news-excerpt">
                    No te pierdas nuestras increíbles ofertas en productos seleccionados. 
                    ¡Hasta 30% de descuento!
                </p>
                <a href="<?php echo BASE_URL; ?>/products.php" class="btn-read-more">
                    Ver productos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
