<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Contact Us - Online Medicine Store';

include __DIR__ . '/includes/header.php';
?>

<!-- Contact Page -->
<section class="contact-page-section">
    <div class="container">
        <div class="contact-page-header">
            <h1 class="page-title">CONTACT US</h1>
            <p class="page-subtitle">Get in touch with us for any queries or support</p>
        </div>

        <div class="contact-page-wrapper">
            <!-- Contact Form -->
            <div class="contact-form-container">
                <h2 class="form-title">REQUEST A CALL BACK</h2>
                <form id="contactForm" method="POST" action="<?php echo BASE_URL; ?>/api/contact.php" class="contact-form">
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" id="name" name="name" placeholder="Enter your name" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Teléfono</label>
                        <input type="tel" id="phone" name="phone" placeholder="Enter your phone" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="medicine">Seleccionar medicina</label>
                        <select id="medicine" name="medicine">
                            <option value="">Selecciona una opción</option>
                            <option value="general">Consulta general</option>
                            <option value="prescription">Receta médica</option>
                            <option value="vitamins">Vitaminas y suplementos</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Mensaje</label>
                        <textarea id="message" name="message" rows="4" placeholder="Write your message..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit-contact">
                        <i class="fas fa-paper-plane"></i> Enviar
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="contact-info-container">
                <h2 class="info-title">Contáctanos</h2>
                <p class="info-description">Estamos aquí para ayudarte. Envíanos tu consulta y nos pondremos en contacto contigo lo antes posible.</p>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Teléfono</h4>
                            <p><?php echo SITE_PHONE; ?></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <p><?php echo SITE_EMAIL; ?></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Horario</h4>
                            <p>Lun - Vie: 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('contactForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('¡Mensaje enviado! Te contactaremos pronto.');
            this.reset();
        } else {
            alert(result.message || 'Error al enviar el mensaje');
        }
    } catch (error) {
        alert('Error al procesar la solicitud');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
