    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>CONTACT</h3>
                <p><i class="fas fa-phone"></i> <?php echo SITE_PHONE; ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></p>
            </div>

            <div class="footer-section">
                <h3>MENU</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/about">About</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/products">Online Buy</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>NEWSLETTER</h3>
                <p>Get Now Medicines</p>
                <p style="font-size: 14px; margin-top: 10px; opacity: 0.9;">Suscríbete para recibir las últimas ofertas y noticias de salud</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter Your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> All Rights Reserved. Design by Forethink Health</p>
        </div>
    </footer>

    <script src="<?php echo BASE_URL; ?>/assets/js/main.js?v=6.2"></script>
</body>
</html>
