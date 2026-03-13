<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'About Us - Online Medicine Store';

include __DIR__ . '/includes/header.php';
?>

<!-- About Page -->
<section class="about-page-section">
    <div class="container">
        <div class="about-page-content">
            <h2 class="section-title centered animate-fade-in">ABOUT US</h2>
            
            <div class="about-page-image animate-slide-up">
                <img src="https://plantillashtmlgratis.com/wp-content/themes/helium-child/vista_previa/page279/medion/images/about-medicine.png" 
                     alt="Vitamin C and B12 bottles" 
                     class="vitamins-image">
            </div>
            
            <div class="about-page-text animate-fade-in-delay">
                <p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it</p>
            </div>
            
            <div class="about-page-button animate-fade-in-delay-2">
                <a href="<?php echo BASE_URL; ?>/products" class="btn-read-more">Read More</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>