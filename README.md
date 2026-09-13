# 🍽️ Flavor Haven — Dynamic Restaurant Ordering System

A full-stack dynamic web application for a fictional restaurant built with **PHP, MySQL, HTML5, CSS3, and JavaScript**. This project was developed as part of **ICT726 Assignment 4** and demonstrates real-world e-commerce functionality including user authentication, online ordering, live order tracking, and an admin management panel.

<p align="center">
  <img src="assets/images/logo.png" alt="Flavor Haven Logo" width="120">
</p>

---

## 🌐 Live Demo

**Live URL:** [https://flavorhaven.freedev.app/flavorhaven/](https://flavorhaven.freedev.app/flavorhaven/)

> **Note:** The site is hosted on InfinityFree, which includes JavaScript-based bot protection. When you visit, you may see a brief "Checking your browser..." screen before the site loads. This is normal and expected.

### 🔑 Test Credentials

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `Admin@123` |
| Member | (register a new account) | — |

---

## 📖 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Folder Structure](#-folder-structure)
- [Database Schema](#-database-schema)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Security](#-security)
- [API Endpoints](#-api-endpoints)
- [Team](#-team)
- [License](#-license)

---

## ✨ Features

### 👤 User Authentication & Roles
- Register, log in, log out
- Three-tier role system: `admin`, `member`, `normal`
- Secure password storage using bcrypt (`password_hash()`)
- Session hardening: `HttpOnly`, `SameSite=Strict`, session ID regeneration
- Role-based access control (RBAC) enforced server-side

### 🍔 Menu & Ordering
- Database-driven menu with six categories
- Category filter and search
- Live stock management (Available / Low Stock / Out of Stock)
- Out-of-stock items hidden automatically
- Session-based shopping cart (add, update, remove, clear)
- Stock validation on add, update, and checkout

### 💳 Payment & Checkout
- Delivery **or** pickup selection
- Dynamic form (address fields only shown for delivery)
- Payment methods: **Google Pay**, **Apple Pay**, **Card**
- Card validation with auto-formatting (spaces every 4 digits, MM/YY expiry slash)
- Demo simulation — **no real charges**
- Atomic order creation via database transactions

### 📦 Order Tracking
- Customer order confirmation page with progress bar
- **Live status updates** every 5 seconds via AJAX
- Status flow: `Pending → Preparing → Delivered`
- Admin live order queue showing only active orders

### 🛠️ Admin Panel
- Clickable stat cards (Menu Items, Users, Orders, Reviews, Messages)
- Stock status cards (Available / Low / Out)
- Full CRUD for menu items
- Inline stock editor
- One-click visibility toggle
- Live order management with one-click status changes
- Contact message replies
- Review moderation

### ⭐ Review System
- Only **verified buyers** (users with a completed order) can review
- One review per user per dish (enforced by DB UNIQUE constraint)
- Star rating (1–5) + optional comment
- Average rating displayed on menu and dish pages

### 💬 Contact & Reply System
- Contact form with validation
- Admin reply with filters (All / Unreplied / Replied)
- Members view their messages + replies at `/member/my_messages.php`

### 🔒 Security
- PDO prepared statements (100% coverage — no string concatenation)
- XSS prevention via `htmlspecialchars()` on all output
- CSRF token verification on every POST form
- Password hashing (bcrypt)
- Server-side validation on all inputs
- Role-based access control

### ♿ Accessibility
- Semantic HTML5 landmarks (`<header>`, `<nav>`, `<main>`, `<footer>`)
- ARIA labels on icon-only buttons
- Skip-to-content link on every page
- Keyboard navigable (Tab, Enter, Escape, arrow keys)
- Focus-visible outlines
- Alt text on all images
- WCAG AA colour contrast

### 📈 SEO
- Unique `<title>` and `<meta description>` per page
- Keyword-targeted meta tags
- Semantic heading hierarchy (one `<h1>` per page)
- `robots.txt` blocking private directories
- `sitemap.xml` listing all public pages
- Lazy-loaded, compressed images

---

## 🧰 Tech Stack

| Layer | Technology |
|---|---|
| **Front-end** | HTML5, CSS3 (Grid, Flexbox, Custom Properties), Vanilla JavaScript (ES6+, Fetch API) |
| **Back-end** | PHP 8.x (PDO, Sessions) |
| **Database** | MySQL / MariaDB |
| **Icons** | Font Awesome 6 |
| **Local Dev** | XAMPP (Apache + MySQL + PHP) |
| **Live Host** | InfinityFree |
| **Version Control** | Git + GitHub |

---

## 📁 Folder Structure

```
flavorhaven/
├── api/                           # JSON endpoints (AJAX)
│   ├── get_dashboard_stats.php
│   ├── get_live_orders.php
│   ├── get_order_status.php
│   ├── get_reviews.php
│   ├── get_stock_alerts.php
│   └── toggle_availability.php
│
├── admin/                         # Admin-only pages (RBAC protected)
│   ├── dashboard.php
│   ├── live_orders.php
│   ├── manage_menu.php
│   ├── add_item.php
│   ├── edit_item.php
│   ├── delete_item.php
│   ├── view_orders.php
│   ├── update_order_status.php
│   ├── messages.php
│   ├── reviews.php
│   └── delete_review.php
│
├── member/                        # Member-only pages
│   ├── dashboard.php
│   ├── edit_profile.php
│   ├── my_orders.php
│   ├── my_messages.php
│   └── track_order.php
│
├── includes/                      # Shared components
│   ├── auth.php
│   ├── header.php
│   ├── footer.php
│   └── functions.php
│
├── config/
│   └── db.php                     # PDO database connection
│
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   ├── images/                    # Menu & team photos
│   └── video/                     # Behind-the-scenes video
│
├── index.php                      # Homepage
├── menu.php                       # Menu with filters
├── dish.php                       # Dish detail + reviews
├── about.php                      # About page
├── media.php                      # Photo gallery
├── contact.php                    # Contact form
│
├── cart.php, cart_add.php, cart_update.php, cart_remove.php, cart_clear.php
├── payment.php                    # Checkout + payment
├── add_review.php                 # Submit review
│
├── register.php, login.php, logout.php
├── privacy.php                    # Privacy notice
├── robots.txt, sitemap.xml        # SEO
│
└── database.sql                   # Full schema
```

---

## 🗄️ Database Schema

Seven normalized tables with foreign keys and referential integrity:

| Table | Purpose |
|---|---|
| `users` | Accounts, address, phone, role |
| `categories` | Menu categories (6 rows) |
| `menu_items` | Dishes with price, image, stock, threshold |
| `orders` | Order header with delivery + payment info |
| `order_items` | Line items per order |
| `contact_messages` | Customer inquiries + admin reply |
| `reviews` | Verified-buyer ratings (1 per user per dish) |

### Entity Relationships

```
users  ──┬──< orders       (1 user → many orders)
         ├──< reviews      (1 user → many reviews)
         ├──< contact_messages (admin replies)
         └──< menu_items   (creator)

categories ──< menu_items  (1 category → many dishes)

orders ──< order_items ──> menu_items

menu_items ──< reviews
```

Full schema available in [`database.sql`](database.sql).

---

## ⚙️ Installation

### Prerequisites
- **XAMPP** (or any Apache + PHP 8+ + MySQL stack)
- **Git** (for cloning)

### Step 1 — Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/flavorhaven.git
```

### Step 2 — Move to XAMPP `htdocs`

Move the `flavorhaven` folder into your XAMPP web root:

| OS | Path |
|---|---|
| Windows | `C:\xampp\htdocs\flavorhaven` |
| macOS | `/Applications/XAMPP/htdocs/flavorhaven` |
| Linux | `/opt/lampp/htdocs/flavorhaven` |

### Step 3 — Start Services

Open **XAMPP Control Panel** and start:
- ✅ Apache
- ✅ MySQL

### Step 4 — Create the Database

1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **New** in the left sidebar
3. Create a database named `flavorhaven_db` (collation: `utf8mb4_unicode_ci`)
4. Click `flavorhaven_db` → **Import** tab
5. Select `database.sql` from the project folder → click **Go**

### Step 5 — Configure Database Connection

Open `config/db.php` and verify credentials for **local XAMPP**:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'flavorhaven_db');
define('DB_USER', 'root');
define('DB_PASS', '');           // Empty by default in XAMPP
```

### Step 6 — Create the Admin Account

Create a file `make_admin.php` in the project root:

```php
<?php
require_once 'config/db.php';
$hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
$stmt->execute(['admin', 'admin@flavorhaven.com', $hash]);
echo "✅ Admin created. Delete this file now.";
```

Visit: [http://localhost/flavorhaven/make_admin.php](http://localhost/flavorhaven/make_admin.php)

⚠️ **Delete `make_admin.php` immediately after running.**

### Step 7 — Access the Site

```
http://localhost/flavorhaven/
```

Log in as `admin` / `Admin@123`.

---

## 🔧 Configuration

### Local Development (`config/db.php`)

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'flavorhaven_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Live Deployment (InfinityFree)

```php
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_NAME', 'ifX_XXXXXXXX_flavorhaven');
define('DB_USER', 'ifX_XXXXXXXX');
define('DB_PASS', 'your_password');
```

Also update `base_url()` in `includes/functions.php` if your folder name differs from `flavorhaven`.

---

## 🔒 Security

| Threat | Mitigation |
|---|---|
| **SQL Injection** | PDO prepared statements on 100% of queries |
| **XSS** | `htmlspecialchars()` via `e()` helper on all dynamic output |
| **CSRF** | Session-bound token verified on every POST |
| **Session Hijacking** | `HttpOnly` + `SameSite=Strict` cookies; `session_regenerate_id()` on login |
| **Password Theft** | bcrypt via `password_hash()` with `PASSWORD_DEFAULT` |
| **Privilege Escalation** | `require_role()` server-side checks on all admin endpoints |
| **Overselling** | Stock re-validated + conditional UPDATE inside a transaction |

---

## 🔌 API Endpoints

All endpoints return JSON. Authentication is enforced server-side.

| Method | Endpoint | Purpose | Auth |
|---|---|---|---|
| GET | `/api/get_order_status.php?id=N` | Order status for tracking | Member (own) or Admin |
| GET | `/api/get_live_orders.php` | Active orders (pending + preparing) | Admin |
| GET | `/api/get_dashboard_stats.php` | Live counters for admin dashboard | Admin |
| GET | `/api/get_reviews.php?item_id=N&offset=0&limit=10` | Paginated reviews | Public |
| GET | `/api/get_stock_alerts.php` | Low + out-of-stock alerts | Admin |
| POST | `/api/toggle_availability.php` | Toggle item visibility | Admin + CSRF |

### Example Response

```json
{
  "success": true,
  "stats": {
    "menu_items": 12,
    "users": 3,
    "orders": 8,
    "reviews": 5,
    "unread_msgs": 2,
    "available": 9,
    "low_stock": 2,
    "out_of_stock": 1,
    "active_orders": 2
  }
}
```

---

## 👥 Team

| Member | Role | Responsibilities |
|---|---|---|
| **[Name 1]** | Backend & Database Lead | Database design, authentication, RBAC, helper functions |
| **[Name 2]** | Frontend & UX Lead | CSS, JavaScript, responsive design, accessibility, SEO |
| **[Name 3]** | Cart & Admin Lead | Cart, payment, admin panel, reviews, AJAX APIs |

**Course:** ICT726 Web Development
**Assignment:** Assignment 4 — Dynamic Website
**Institution:** [Your Institution]

---

## 📄 License

This project was created for educational purposes as part of ICT726 coursework.

Free to view, learn from, and reference. Please do not submit as your own work — that would violate academic integrity policies.

---

## 🙏 Acknowledgements

- **W3Schools** and **MDN Web Docs** for PHP, MySQL, and web standard references
- **Font Awesome** for icons
- **InfinityFree** for free PHP/MySQL hosting
- **Google** for the SEO Starter Guide

---

## 📞 Contact

For questions about this project:
- 📧 Email: bisalgtm111@gmail.com
- 🌐 Live Site: [https://flavorhaven.freedev.app/flavorhaven/](https://flavorhaven.freedev.app/flavorhaven/)

---

<p align="center">
  <strong>Built with ❤️ by the Flavor Haven team</strong><br>
  <em>ICT726 — Assignment 4 · 2025</em>
</p>
