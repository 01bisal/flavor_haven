<?php
require_once 'includes/auth.php';
$page_title = "Privacy Notice";
include 'includes/header.php';
?>

<section class="page-header"><h1>Privacy Notice</h1></section>
<section class="about-section" style="max-width:800px;margin:0 auto;">
    <h2>What we collect</h2>
    <p>When you register or place an order, we collect your username, email, and order details.</p>

    <h2>How we use it</h2>
    <p>Your data is used only to process orders and respond to inquiries. We never sell your information.</p>

    <h2>How we protect it</h2>
    <p>Passwords are hashed with modern algorithms. Sessions are secured with HTTP-only cookies. All database access uses prepared statements to prevent SQL injection.</p>

    <h2>Your rights</h2>
    <p>You may request deletion of your account and associated data anytime by contacting info@flavorhaven.com.</p>

    <h2>Contact</h2>
    <p>Questions? Email <a href="mailto:info@flavorhaven.com">info@flavorhaven.com</a>.</p>
</section>

<?php include 'includes/footer.php'; ?>