<?php
require_once 'includes/auth.php';

$page_title = "About Us";
$meta_desc  = "Learn about Flavor Haven's story, our passion for food, and our commitment to quality.";
include 'includes/header.php';
?>

<section class="page-header">
    <h1>About Flavor Haven</h1>
    <p>A story of passion, family, and unforgettable flavors</p>
</section>

<section class="about-section" aria-labelledby="our-story">
    <div class="about-grid">
        <div class="about-text">
            <h2 id="our-story">Our Story</h2>
            <p>Founded in 2015 by executive chef Keshavraj Gautam, Flavor Haven began with a simple dream: to bring the authentic tastes of her grandmother's kitchen to the modern dining table. With a deep passion for fresh ingredients and traditional cooking techniques, Keshav created a restaurant where every dish tells a story.</p>
            <p>What started as a small family-owned restaurant has grown into a beloved dining destination known for its warm hospitality, innovative menu, and commitment to using locally-sourced, sustainable ingredients. Every day, our team works tirelessly to ensure that each guest leaves with a smile and a satisfied palate.</p>
            <p>At Flavor Haven, we believe that food is more than just nourishment—it's a way to connect, celebrate, and create lasting memories. Whether you're joining us for a family dinner, a special occasion, or a quick bite, we're honored to share our passion with you.</p>
        </div>
        <div class="about-image">
            <img src="<?= base_url('assets/images/about.jpg') ?>"
                 alt="The Flavor Haven team in our kitchen preparing fresh dishes"
                 loading="lazy" />
            <span class="image-caption">Our dedicated team of chefs and staff</span>
        </div>
    </div>
</section>

<section class="values-section" aria-labelledby="values-heading">
    <h2 id="values-heading" class="section-title">Our Core Values</h2>
    <div class="values-grid">
        <article class="value-card" aria-labelledby="value1-title">
            <div class="value-icon"><i class="fas fa-leaf" aria-hidden="true"></i></div>
            <h3 id="value1-title">Quality Ingredients</h3>
            <p>We source only the finest, freshest ingredients from local farms and trusted suppliers.</p>
        </article>
        <article class="value-card" aria-labelledby="value2-title">
            <div class="value-icon"><i class="fas fa-heart" aria-hidden="true"></i></div>
            <h3 id="value2-title">Passion for Food</h3>
            <p>Every dish is crafted with love, care, and a deep respect for culinary traditions.</p>
        </article>
        <article class="value-card" aria-labelledby="value3-title">
            <div class="value-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
            <h3 id="value3-title">Community</h3>
            <p>We're proud to be part of our community and give back through local initiatives.</p>
        </article>
        <article class="value-card" aria-labelledby="value4-title">
            <div class="value-icon"><i class="fas fa-clock" aria-hidden="true"></i></div>
            <h3 id="value4-title">Sustainability</h3>
            <p>Committed to sustainable practices that protect our planet for future generations.</p>
        </article>
    </div>
</section>

<section class="team-section" aria-labelledby="team-heading">
    <h2 id="team-heading" class="section-title">Meet Our Team</h2>
    <div class="team-grid">
        <article class="team-card" aria-labelledby="chef1-title">
            <img src="<?= base_url('assets/images/keshav.jpg') ?>" alt="Executive Chef Keshavraj Gautam" loading="lazy" />
            <div class="team-info">
                <h3 id="chef1-title">Keshavraj Gautam</h3>
                <p class="role">Executive Chef & Founder</p>
                <p>With over 20 years of culinary experience, Keshav brings passion and creativity to every dish.</p>
            </div>
        </article>
        <article class="team-card" aria-labelledby="chef2-title">
            <img src="<?= base_url('assets/images/abhisekh.jpg') ?>" alt="Chef De Cuisine Abhishek Karki" loading="lazy" />
            <div class="team-info">
                <h3 id="chef2-title">Abhishek Karki</h3>
                <p class="role">Chef De Cuisine</p>
                <p>Abhishek specializes in modern American cuisine with a focus on seasonal ingredients.</p>
            </div>
        </article>
        <article class="team-card" aria-labelledby="chef3-title">
            <img src="<?= base_url('assets/images/sulakshya.jpg') ?>" alt="Pastry Chef Sulakshya" loading="lazy" />
            <div class="team-info">
                <h3 id="chef3-title">Sulakshya</h3>
                <p class="role">Pastry Chef</p>
                <p>Sulakshya creates stunning desserts that are as beautiful as they are delicious.</p>
            </div>
        </article>
    </div>
</section>

<section class="awards-section" aria-labelledby="awards-heading">
    <h2 id="awards-heading" class="section-title">Awards & Recognition</h2>
    <div class="awards-grid">
        <div class="award">
            <i class="fas fa-trophy" aria-hidden="true"></i>
            <h3>Best Restaurant 2023</h3>
            <p>Gourmet City Food Awards</p>
        </div>
        <div class="award">
            <i class="fas fa-medal" aria-hidden="true"></i>
            <h3>Excellence in Service</h3>
            <p>Hospitality Guild 2022</p>
        </div>
        <div class="award">
            <i class="fas fa-star" aria-hidden="true"></i>
            <h3>Top 10 Restaurants</h3>
            <p>Food & Dining Magazine 2021</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>