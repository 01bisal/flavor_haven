<?php
require_once 'includes/auth.php';

$page_title = "Gallery";
$meta_desc  = "Explore our photo gallery showcasing our delicious dishes, restaurant ambiance, and special events.";
include 'includes/header.php';
?>

<section class="page-header">
    <h1>Photo Gallery</h1>
    <p>Take a visual journey through our culinary creations and restaurant atmosphere</p>
</section>

<!-- Lightbox Gallery -->
<section class="gallery-section" aria-labelledby="gallery-heading">
    <h2 id="gallery-heading" class="sr-only">Gallery Images</h2>

    <div class="gallery-grid" role="list">

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/grilledchicken.jpg') ?>"
                 alt="Grilled Chicken with herb butter, served with roasted vegetables and mashed potatoes"
                 loading="lazy" data-full="<?= base_url('assets/images/grilledchicken.jpg') ?>" />
            <figcaption>Grilled Chicken</figcaption>
            <button class="expand-btn" aria-label="View larger image of Grilled Chicken">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/margarita.jpg') ?>"
                 alt="Wood-fired Margherita Pizza with fresh basil and melted mozzarella cheese"
                 loading="lazy" data-full="<?= base_url('assets/images/margarita.jpg') ?>" />
            <figcaption>Margherita Pizza</figcaption>
            <button class="expand-btn" aria-label="View larger image of Margherita Pizza">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/Spaghetti.jpg') ?>"
                 alt="Spaghetti Carbonara with crispy pancetta and fresh parmesan"
                 loading="lazy" data-full="<?= base_url('assets/images/Spaghetti.jpg') ?>" />
            <figcaption>Spaghetti Carbonara</figcaption>
            <button class="expand-btn" aria-label="View larger image of Spaghetti Carbonara">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/ribeye.jpg') ?>"
                 alt="Ribeye Steak cooked medium rare with roasted vegetables and sauce"
                 loading="lazy" data-full="<?= base_url('assets/images/ribeye.jpg') ?>" />
            <figcaption>Ribeye Steak</figcaption>
            <button class="expand-btn" aria-label="View larger image of Ribeye Steak">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/lavacake.jpg') ?>"
                 alt="Chocolate Lava Cake with vanilla ice cream and berry garnish"
                 loading="lazy" data-full="<?= base_url('assets/images/lavacake.jpg') ?>" />
            <figcaption>Chocolate Lava Cake</figcaption>
            <button class="expand-btn" aria-label="View larger image of Chocolate Lava Cake">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/berriessmothie.jpg') ?>"
                 alt="Fresh Fruit Smoothie with berries, served in a tall glass"
                 loading="lazy" data-full="<?= base_url('assets/images/berriessmothie.jpg') ?>" />
            <figcaption>Berry Smoothie</figcaption>
            <button class="expand-btn" aria-label="View larger image of Berry Smoothie">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/salmon.jpg') ?>"
                 alt="Grilled Salmon with lemon butter sauce and vegetables"
                 loading="lazy" data-full="<?= base_url('assets/images/salmon.jpg') ?>" />
            <figcaption>Grilled Salmon</figcaption>
            <button class="expand-btn" aria-label="View larger image of Grilled Salmon">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/tiramisu.jpg') ?>"
                 alt="Classic Tiramisu dessert with coffee and mascarpone"
                 loading="lazy" data-full="<?= base_url('assets/images/tiramisu.jpg') ?>" />
            <figcaption>Tiramisu</figcaption>
            <button class="expand-btn" aria-label="View larger image of Tiramisu">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/bruschetta.jpg') ?>"
                 alt="Bruschetta with fresh tomatoes and basil on toasted bread"
                 loading="lazy" data-full="<?= base_url('assets/images/bruschetta.jpg') ?>" />
            <figcaption>Bruschetta</figcaption>
            <button class="expand-btn" aria-label="View larger image of Bruschetta">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/calamari.jpg') ?>"
                 alt="Crispy Calamari with marinara sauce and lemon wedges"
                 loading="lazy" data-full="<?= base_url('assets/images/calamari.jpg') ?>" />
            <figcaption>Crispy Calamari</figcaption>
            <button class="expand-btn" aria-label="View larger image of Crispy Calamari">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/alfredo.jpg') ?>"
                 alt="Fettuccine Alfredo with grilled chicken and broccoli"
                 loading="lazy" data-full="<?= base_url('assets/images/alfredo.jpg') ?>" />
            <figcaption>Fettuccine Alfredo</figcaption>
            <button class="expand-btn" aria-label="View larger image of Fettuccine Alfredo">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

        <figure class="gallery-item" role="listitem">
            <img src="<?= base_url('assets/images/coffee.jpg') ?>"
                 alt="Freshly Brewed Coffee served with cream and sugar"
                 loading="lazy" data-full="<?= base_url('assets/images/coffee.jpg') ?>" />
            <figcaption>Fresh Brewed Coffee</figcaption>
            <button class="expand-btn" aria-label="View larger image of Fresh Brewed Coffee">
                <i class="fas fa-expand" aria-hidden="true"></i>
            </button>
        </figure>

    </div>
</section>

<!-- Lightbox overlay -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Image viewer">
    <button class="lightbox-close" aria-label="Close image viewer">&times;</button>
    <button class="lightbox-prev" aria-label="Previous image">&#10094;</button>
    <button class="lightbox-next" aria-label="Next image">&#10095;</button>
    <img src="" alt="" id="lightbox-img" />
    <p id="lightbox-caption"></p>
</div>

<!-- Video Section -->
<section class="video-section" aria-labelledby="video-heading">
    <h2 id="video-heading" class="section-title">Behind the Scenes</h2>

    <div class="video-container">
        <video controls preload="metadata" aria-label="Behind the scenes video of our kitchen and restaurant">
            <source src="<?= base_url('assets/video/video1.mp4') ?>" type="video/mp4" />
            <track kind="subtitles" srclang="en" label="English" />
            Your browser does not support the video tag. Please update your browser to view this content.
        </video>
        <p class="video-caption">Watch our chefs in action and see what makes Flavor Haven special</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>