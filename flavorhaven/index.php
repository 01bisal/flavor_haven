<?php
require_once 'includes/auth.php';

// Only show featured dishes that are available AND in stock
$featured = $pdo->query("
    SELECT m.id, m.name, m.description, m.price, m.image_path, c.name AS category
    FROM menu_items m
    JOIN categories c ON c.id = m.category_id
    WHERE m.is_available = 1 AND m.stock > 0
    ORDER BY RAND()
    LIMIT 3
")->fetchAll();

$page_title = "Home";
$meta_desc = "Flavor Haven - Order delicious meals online. Fresh ingredients, authentic flavors.";
include 'includes/header.php';
?>

<section class="hero" aria-labelledby="hero-heading">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 id="hero-heading">Welcome to Flavor Haven</h1>
        <p>Experience the taste of authentic cuisine made with love and the freshest ingredients.</p>
        <div class="hero-buttons">
            <a href="menu.php" class="btn btn-primary">Explore Menu</a>
            <?php if (!is_logged_in()): ?>
                <a href="register.php" class="btn btn-secondary">Sign Up to Order</a>
            <?php else: ?>
                <a href="menu.php" class="btn btn-secondary">Order Now</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features" aria-labelledby="features-heading">
    <h2 id="features-heading" class="section-title">Why Choose Flavor Haven</h2>
    <div class="features-grid">
        <article class="feature-card">
            <div class="feature-icon"><i class="fas fa-utensils"></i></div>
            <h3>Fresh Ingredients</h3>
            <p>We source only the freshest, locally-sourced ingredients.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><i class="fas fa-truck"></i></div>
            <h3>Fast Delivery</h3>
            <p>Hot and fresh meals delivered to your doorstep within 30 minutes.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><i class="fas fa-star"></i></div>
            <h3>Authentic Recipes</h3>
            <p>Generations of culinary expertise in every dish.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon"><i class="fas fa-hand-holding-heart"></i></div>
            <h3>Affordable Pricing</h3>
            <p>High-quality meals at prices that won't break the bank.</p>
        </article>
    </div>
</section>

<section class="popular-dishes" aria-labelledby="popular-heading">
    <h2 id="popular-heading" class="section-title">Popular Dishes</h2>

    <?php if (empty($featured)): ?>
        <div class="alert info" role="alert">
            No featured dishes right now. <a href="menu.php">Check the menu</a> later.
        </div>
    <?php else: ?>
        <div class="dishes-grid">
            <?php foreach ($featured as $item): ?>
                <div class="dish-card">
                    <img src="<?= base_url(e($item['image_path'])) ?>"
                         alt="<?= e($item['name']) ?>" loading="lazy" />
                    <div class="dish-info">
                        <h3><?= e($item['name']) ?></h3>
                        <p><?= e($item['description']) ?></p>
                        <span class="price">$<?= number_format($item['price'], 2) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="view-all">
            <a href="menu.php" class="btn btn-primary">
                View Full Menu <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    <?php endif; ?>
</section>

<section class="testimonials" aria-labelledby="testimonials-heading">
    <h2 id="testimonials-heading" class="section-title">What Our Customers Say</h2>
    <div class="testimonial-grid">
        <blockquote class="testimonial">
            <p>"Absolutely amazing food! The grilled chicken was perfectly cooked."</p>
            <cite>- Sarah M.</cite>
        </blockquote>
        <blockquote class="testimonial">
            <p>"Best pizza in town! The crust is perfect and the ingredients are always fresh."</p>
            <cite>- James K.</cite>
        </blockquote>
        <blockquote class="testimonial">
            <p>"Flavor Haven has become our go-to for family dinners."</p>
            <cite>- The Thompson Family</cite>
        </blockquote>
    </div>
</section>

<?php include 'includes/footer.php'; ?>