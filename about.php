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
            <div class="vitamins-placeholder">
                <div class="vitamin-bottle vitamin-c">
                    <div class="bottle-cap"></div>
                    <div class="bottle-body">
                        <div class="bottle-label">
                            <span class="vitamin-name">VITAMIN</span>
                            <span class="vitamin-letter">C</span>
                        </div>
                    </div>
                    <div class="pills-scattered">
                        <span class="pill pill-orange"></span>
                        <span class="pill pill-orange"></span>
                        <span class="pill pill-orange"></span>
                    </div>
                </div>
                <div class="vitamin-bottle vitamin-b12">
                    <div class="bottle-cap"></div>
                    <div class="bottle-body">
                        <div class="bottle-label">
                            <span class="vitamin-name">VITAMIN</span>
                            <span class="vitamin-letter">B<sub>12</sub></span>
                        </div>
                    </div>
                    <div class="pills-scattered">
                        <span class="pill pill-yellow"></span>
                        <span class="pill pill-yellow"></span>
                        <span class="pill pill-yellow"></span>
                    </div>
                </div>
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