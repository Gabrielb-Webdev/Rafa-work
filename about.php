<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'About Us - Online Medicine Store';

include __DIR__ . '/includes/header.php';
?>

<!-- About Page -->
<section class="about-page-section">
    <div class="container">
        <h2 class="section-title centered">ABOUT US</h2>
        
        <div class="about-page-image">
            <div class="about-placeholder">
                <div class="about-placeholder-icon">🏥</div>
                <div class="about-placeholder-text">Vitamins Image</div>
                <div class="img-placeholder-subtext">Vitamin C & B12 bottles</div>
            </div>
        </div>
        
        <div class="about-page-text">
            <p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it</p>
        </div>
        
        <div class="about-page-button">
            <a href="<?php echo BASE_URL; ?>/products.php" class="btn-read-more">Read More</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>