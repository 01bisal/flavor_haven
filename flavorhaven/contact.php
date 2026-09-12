<?php
require_once 'includes/auth.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid session.";
    } else {
        $name    = clean($_POST['fullName'] ?? '');
        $email   = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $phone   = clean($_POST['phone'] ?? '');
        $subject = clean($_POST['subject'] ?? '');
        $message = clean($_POST['message'] ?? '');
        $pref    = isset($_POST['preferredContact']) ? 1 : 0;

        if (strlen($name) < 2) $errors[] = "Please enter your full name.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
        if ($phone && !preg_match('/^[\+\d\s\-\(\)]{7,15}$/', $phone)) $errors[] = "Invalid phone number.";
        if ($subject === '') $errors[] = "Please select a subject.";
        if (strlen($message) < 10) $errors[] = "Message must be at least 10 characters.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO contact_messages 
                (name, email, phone, subject, message, preferred_contact) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message, $pref]);
            $success = "Thank you! Your message has been sent successfully.";
        }
    }
}

$page_title = "Contact Us";
$meta_desc = "Get in touch with Flavor Haven.";
include 'includes/header.php';
?>

<section class="page-header">
    <h1>Contact Us</h1>
    <p>We'd love to hear from you!</p>
</section>

<section class="contact-section">
    <div class="contact-container">
        <article class="contact-form-container">
            <h2>Send Us a Message</h2>

            <?php if ($success): ?>
                <div class="alert success" style="padding:1rem;background:#d4edda;color:#155724;border-radius:8px;margin-bottom:1rem;" role="alert"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert error" style="padding:1rem;background:#f8d7da;color:#721c24;border-radius:8px;margin-bottom:1rem;" role="alert">
                    <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate id="contactForm">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" name="fullName" required
                           value="<?= e($_POST['fullName'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <select id="subject" name="subject" required>
                        <option value="">Please select...</option>
                        <option value="reservation">Make a Reservation</option>
                        <option value="catering">Catering Inquiry</option>
                        <option value="feedback">Feedback</option>
                        <option value="complaint">Report an Issue</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="5" required minlength="10"><?= e($_POST['message'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="preferredContact"> I prefer to be contacted by phone
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </article>

        <aside class="contact-info">
            <h2>Get in Touch</h2>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div><h3>Address</h3><address>123 Food Street, Gourmet City</address></div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <div><h3>Phone</h3><p><a href="tel:+1234567890">+1 (234) 567-890</a></p></div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <div><h3>Email</h3><p><a href="mailto:info@flavorhaven.com">info@flavorhaven.com</a></p></div>
            </div>
        </aside>
    </div>
</section>

<?php include 'includes/footer.php'; ?>