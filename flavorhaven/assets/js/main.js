/**
 * Flavor Haven - Main JavaScript
 * Handles navigation, gallery lightbox, menu filtering, and client-side validation
 */

document.addEventListener('DOMContentLoaded', function () {

    // =====================================
    // Mobile Navigation Toggle
    // =====================================
    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('nav');

    if (navToggle && nav) {
        navToggle.addEventListener('click', function () {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            nav.classList.toggle('open');
        });

        // Close nav on link click (mobile)
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                nav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // =====================================
    // Menu Category Filter
    // =====================================
    const categoryBtns = document.querySelectorAll('.menu-category-btn');
    const menuItems = document.querySelectorAll('.menu-item');

    if (categoryBtns.length && menuItems.length) {
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                categoryBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const category = this.getAttribute('data-category');

                menuItems.forEach(item => {
                    if (category === 'all' || item.getAttribute('data-category') === category) {
                        item.style.display = 'flex';
                        item.style.animation = 'fadeIn 0.3s ease-out';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    // =====================================
    // Gallery Lightbox
    // =====================================
    const galleryItems = document.querySelectorAll('.gallery-item img');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxCaption = document.getElementById('lightbox-caption');
    const closeBtn = document.querySelector('.lightbox-close');
    const prevBtn = document.querySelector('.lightbox-prev');
    const nextBtn = document.querySelector('.lightbox-next');

    let currentImageIndex = 0;
    const images = [];

    if (galleryItems.length && lightbox) {
        galleryItems.forEach((img, index) => {
            images.push({
                src: img.getAttribute('data-full') || img.src,
                alt: img.alt || 'Gallery image',
                caption: img.closest('.gallery-item')?.querySelector('figcaption')?.textContent || ''
            });

            img.addEventListener('click', function () {
                openLightbox(index);
            });

            const expandBtn = img.closest('.gallery-item')?.querySelector('.expand-btn');
            if (expandBtn) {
                expandBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const parentImg = this.closest('.gallery-item').querySelector('img');
                    const idx = Array.from(galleryItems).indexOf(parentImg);
                    openLightbox(idx);
                });
            }
        });

        // Keyboard support for gallery items
        galleryItems.forEach((img, index) => {
            img.setAttribute('tabindex', '0');
            img.setAttribute('role', 'button');
            img.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openLightbox(index);
                }
            });
        });

        function openLightbox(index) {
            currentImageIndex = index;
            updateLightbox();
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function updateLightbox() {
            const image = images[currentImageIndex];
            if (image) {
                lightboxImg.src = image.src;
                lightboxImg.alt = image.alt;
                lightboxCaption.textContent = image.caption;
            }
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            updateLightbox();
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            updateLightbox();
        }

        if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
        if (prevBtn) prevBtn.addEventListener('click', prevImage);
        if (nextBtn) nextBtn.addEventListener('click', nextImage);

        lightbox.addEventListener('click', function (e) {
            if (e.target === this) closeLightbox();
        });

        document.addEventListener('keydown', function (e) {
            if (lightbox.classList.contains('active')) {
                switch (e.key) {
                    case 'Escape': closeLightbox(); break;
                    case 'ArrowLeft': prevImage(); break;
                    case 'ArrowRight': nextImage(); break;
                }
            }
        });
    }

    // =====================================
    // Client-Side Form Validation (UX Enhancement)
    // Server-side validation is the source of truth.
    // =====================================
    const contactForm = document.getElementById('contactForm');
    const registerForm = document.getElementById('registerForm');

    /**
     * Attach real-time validation to a form field
     * @param {HTMLFormElement} form
     */
    function attachValidation(form) {
        if (!form) return;

        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', function () {
                validateField(this);
            });

            field.addEventListener('input', function () {
                // Clear error once user starts fixing it
                clearFieldError(this);
            });
        });
    }

    /**
     * Validate a single field on the client
     * @param {HTMLElement} field
     * @returns {boolean}
     */
    function validateField(field) {
        const id = field.id;
        const value = (field.value || '').trim();

        // Skip optional fields when empty
        if (id === 'phone' && value === '') {
            clearFieldError(field);
            return true;
        }

        let valid = true;
        let message = '';

        switch (id) {
            case 'fullName':
                if (value.length < 2) {
                    message = 'Please enter your full name (min 2 characters).';
                    valid = false;
                }
                break;

            case 'email':
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    message = 'Please enter a valid email address.';
                    valid = false;
                }
                break;

            case 'phone':
                if (!/^[\+\d\s\-\(\)]{7,15}$/.test(value)) {
                    message = 'Please enter a valid phone number.';
                    valid = false;
                }
                break;

            case 'subject':
                if (value === '') {
                    message = 'Please select a subject.';
                    valid = false;
                }
                break;

            case 'message':
                if (value.length < 10) {
                    message = 'Message must be at least 10 characters.';
                    valid = false;
                }
                break;

            case 'username':
                if (!/^[a-zA-Z0-9_]{3,20}$/.test(value)) {
                    message = 'Username must be 3-20 characters (letters, numbers, underscore).';
                    valid = false;
                }
                break;

            case 'password':
                if (value.length < 8 || !/[A-Z]/.test(value) || !/[0-9]/.test(value)) {
                    message = 'Password: 8+ chars with 1 uppercase and 1 number.';
                    valid = false;
                }
                break;

            case 'confirm':
                const pwd = document.getElementById('password');
                if (pwd && value !== pwd.value) {
                    message = 'Passwords do not match.';
                    valid = false;
                }
                break;
        }

        if (!valid) {
            showFieldError(field, message);
            return false;
        }

        clearFieldError(field);
        return true;
    }

    function showFieldError(field, message) {
        field.classList.add('error');
        field.setAttribute('aria-invalid', 'true');

        // Find the error span (id = fieldId + "Error")
        let errorEl = document.getElementById(field.id + 'Error');
        if (!errorEl) {
            // Fallback: create one
            errorEl = document.createElement('span');
            errorEl.className = 'error-message';
            errorEl.id = field.id + 'Error';
            field.parentNode.appendChild(errorEl);
        }
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    function clearFieldError(field) {
        field.classList.remove('error');
        field.removeAttribute('aria-invalid');
        const errorEl = document.getElementById(field.id + 'Error');
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }
    }

    function clearAllErrors(form) {
        if (!form) return;
        form.querySelectorAll('.error').forEach(f => {
            f.classList.remove('error');
            f.removeAttribute('aria-invalid');
        });
        form.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }

    // Attach validation to all forms that need it
    attachValidation(contactForm);
    attachValidation(registerForm);

    // Prevent submission if client validation fails
    [contactForm, registerForm].forEach(form => {
        if (!form) return;
        form.addEventListener('submit', function (e) {
            const fields = this.querySelectorAll('input, select, textarea');
            let allValid = true;

            fields.forEach(field => {
                if (!validateField(field)) allValid = false;
            });

            if (!allValid) {
                e.preventDefault();
                const firstError = this.querySelector('.error');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });

    // =====================================
    // Smooth Scroll for Anchor Links
    // =====================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#' && targetId.length > 1) {
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    // =====================================
    // Fade-in on Scroll
    // =====================================
    const fadeElements = document.querySelectorAll(
        '.feature-card, .dish-card, .testimonial, .menu-item, .team-card, .value-card, .stat'
    );

    if ('IntersectionObserver' in window && fadeElements.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        fadeElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(el);
        });
    }

    console.log('Flavor Haven loaded successfully!');
});