<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Acerca de Nosotros - Forethink Health';

include __DIR__ . '/includes/header.php';
?>

<div class="about-section">
    <div class="about-content">
        <h2>ABOUT US</h2>
        <div class="about-image">
            <img src="<?php echo BASE_URL; ?>/assets/images/vitamins.png" alt="About Us" onerror="this.style.display='none'">
        </div>
        <p>
            En Forethink Health nos dedicamos a proporcionar productos farmacéuticos de la más alta calidad 
            para cuidar la salud y el bienestar de nuestros clientes. Con años de experiencia en el sector, 
            nos hemos convertido en un referente de confianza.
        </p>
        <p>
            Nuestra misión es hacer que el acceso a medicamentos y suplementos de calidad sea fácil, 
            rápido y seguro. Contamos con un amplio catálogo de productos, desde medicinas esenciales 
            hasta vitaminas y suplementos para mejorar tu calidad de vida.
        </p>
        <p>
            Trabajamos con los mejores laboratorios y proveedores certificados, garantizando que cada 
            producto cumpla con los más altos estándares de calidad y seguridad. Tu salud es nuestra prioridad.
        </p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
