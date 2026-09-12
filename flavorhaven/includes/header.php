<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($page_title) ? e($page_title) . ' - Flavor Haven Restaurant' : 'Flavor Haven - Restaurant Ordering System' ?></title>
    <meta name="description" content="<?= isset($meta_desc) ? e($meta_desc) : 'Flavor Haven - Order delicious meals online. Fresh ingredients, authentic flavors, delivered to your door.' ?>">
    <meta name="keywords" content="restaurant, online ordering, food delivery, Flavor Haven, gourmet food">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>

<header role="banner">
    <div class="header-container">
        <div class="logo">
            <img src="<?= base_url('assets/images/logo.png') ?>" alt="Flavor Haven Restaurant Logo" />
            <span class="logo-text">Flavor Haven</span>
        </div>
        <button class="nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
            <span class="hamburger"></span>
            <span class="hamburger"></span>
            <span class="hamburger"></span>
        </button>
        <nav role="navigation" aria-label="Main navigation">
            <ul>
                <li><a href="<?= base_url('index.php') ?>">Home</a></li>
                <li><a href="<?= base_url('menu.php') ?>">Menu</a></li>
                <li><a href="<?= base_url('about.php') ?>">About</a></li>
                <li><a href="<?= base_url('media.php') ?>">Gallery</a></li>
                <li><a href="<?= base_url('contact.php') ?>">Contact</a></li>

                <?php if (is_logged_in()): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li style="position:relative;">
                            <a href="<?= base_url('admin/manage_menu.php') ?>?stock=low"
                               id="stockAlertLink"
                               style="position:relative;display:inline-block;"
                               aria-label="Stock alerts">
                                <i class="fas fa-bell" aria-hidden="true"></i> Alerts
                                <span id="stockAlertBadge"
                                      style="display:none;position:absolute;top:-8px;right:-14px;background:#C0392B;color:#fff;font-size:0.7rem;font-weight:700;padding:1px 6px;border-radius:50px;min-width:18px;text-align:center;"></span>
                            </a>
                        </li>
                        <li><a href="<?= base_url('admin/dashboard.php') ?>">Admin</a></li>
                    <?php else: ?>
                        <li>
                            <a href="<?= base_url('cart.php') ?>" style="position:relative;display:inline-block;">
                                <i class="fas fa-shopping-cart" aria-hidden="true"></i> Cart
                                <?php $cart_n = cart_count(); ?>
                                <?php if ($cart_n > 0): ?>
                                    <span aria-label="<?= $cart_n ?> items in cart"
                                          style="
                                            position:absolute;
                                            top:-8px;
                                            right:-14px;
                                            background:#C0392B;
                                            color:#fff;
                                            font-size:0.7rem;
                                            font-weight:700;
                                            padding:1px 6px;
                                            border-radius:50px;
                                            min-width:18px;
                                            text-align:center;
                                          "><?= $cart_n ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a href="<?= base_url('member/my_messages.php') ?>">Messages</a></li>
                        <li><a href="<?= base_url('member/dashboard.php') ?>">My Account</a></li>
                    <?php endif; ?>
                    <li><a href="<?= base_url('logout.php') ?>">Logout (<?= e($_SESSION['username']) ?>)</a></li>
                <?php else: ?>
                    <li><a href="<?= base_url('login.php') ?>">Login</a></li>
                    <li><a href="<?= base_url('register.php') ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main id="main-content">