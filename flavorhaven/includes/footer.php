</main>

<footer role="contentinfo">
    <div class="footer-container">
        <div class="footer-section">
            <h3>Flavor Haven</h3>
            <p>Delivering authentic flavors to your doorstep. Made with love and fresh ingredients.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="<?= base_url('index.php') ?>">Home</a></li>
                <li><a href="<?= base_url('menu.php') ?>">Menu</a></li>
                <li><a href="<?= base_url('about.php') ?>">About</a></li>
                <li><a href="<?= base_url('privacy.php') ?>">Privacy Notice</a></li>
                <li><a href="<?= base_url('contact.php') ?>">Contact</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Info</h3>
            <address>
                <p><i class="fas fa-map-marker-alt"></i> 123 Food Street, Gourmet City</p>
                <p><i class="fas fa-phone"></i> <a href="tel:+1234567890">+1 (234) 567-890</a></p>
                <p><i class="fas fa-envelope"></i> <a href="mailto:info@flavorhaven.com">info@flavorhaven.com</a></p>
            </address>
        </div>
        <div class="footer-section">
            <h3>Hours</h3>
            <ul class="hours">
                <li>Mon - Fri: 11:00 AM - 10:00 PM</li>
                <li>Sat - Sun: 12:00 PM - 11:00 PM</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Flavor Haven. All rights reserved. | Restaurant Ordering System</p>
    </div>
</footer>

<script src="<?= base_url('assets/js/main.js') ?>"></script>

<?php if (is_logged_in() && $_SESSION['role'] === 'admin'): ?>
<script>
(function () {
    const apiUrl = '<?= base_url('api/get_stock_alerts.php') ?>';
    const badge = document.getElementById('stockAlertBadge');
    if (!badge) return;

    async function checkAlerts() {
        try {
            const res = await fetch(apiUrl, { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            if (data.total_alerts > 0) {
                badge.textContent = data.total_alerts;
                badge.style.display = 'inline-block';
                badge.setAttribute('title', data.low_count + ' low stock, ' + data.out_count + ' out of stock');
            } else {
                badge.style.display = 'none';
            }
        } catch (err) { /* silent */ }
    }

    checkAlerts();
    setInterval(checkAlerts, 30000);
})();
</script>
<?php endif; ?>

</body>
</html>