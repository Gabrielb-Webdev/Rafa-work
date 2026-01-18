<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Contacto - Forethink Health';

include __DIR__ . '/includes/header.php';
?>

<section class="contact-section" style="margin-top: 40px;">
    <div class="contact-wrapper">
        <div class="contact-form">
            <h2>REQUEST A CALL BACK</h2>
            <form id="contactForm" method="POST" action="<?php echo BASE_URL; ?>/api/contact.php">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="phone" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Seleccionar medicina</label>
                    <select name="medicine">
                        <option value="">Selecciona una opción</option>
                        <option value="general">Consulta general</option>
                        <option value="prescription">Receta médica</option>
                        <option value="vitamins">Vitaminas y suplementos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mensaje</label>
                    <textarea name="message" rows="4"></textarea>
                </div>

                <button type="submit" class="btn-submit">Enviar</button>
            </form>
        </div>

        <div class="contact-info">
            <h2>Contáctanos</h2>
            <p>Estamos aquí para ayudarte. Envíanos tu consulta y nos pondremos en contacto contigo lo antes posible.</p>
            
            <div style="margin-top: 30px;">
                <p style="margin-bottom: 15px;">
                    <i class="fas fa-phone"></i> 
                    <strong>Teléfono:</strong> <?php echo SITE_PHONE; ?>
                </p>
                <p style="margin-bottom: 15px;">
                    <i class="fas fa-envelope"></i> 
                    <strong>Email:</strong> <?php echo SITE_EMAIL; ?>
                </p>
                <p>
                    <i class="fas fa-clock"></i> 
                    <strong>Horario:</strong> Lun - Vie: 9:00 AM - 6:00 PM
                </p>
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
            showNotification('¡Mensaje enviado! Te contactaremos pronto.', 'success');
            this.reset();
        } else {
            showNotification(result.message || 'Error al enviar el mensaje', 'error');
        }
    } catch (error) {
        showNotification('Error al procesar la solicitud', 'error');
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
