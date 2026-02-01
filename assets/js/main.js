/* ============================================
   ONLINE MEDICINE STORE - Main JavaScript
   Version 6.0 - Database Integration
============================================ */

// Update cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    initializeSearch();
    initializeNewsletterForm();
    initializeProductNavigation();
    initializeUserDropdown();
});

// Update cart count from server (PHP session)
function updateCartCount() {
    // El contador ya viene desde PHP en el header
    // Esta función está aquí por compatibilidad pero no es necesaria
    // ya que el badge del carrito se renderiza desde PHP
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        // El valor ya está puesto por PHP, no hacer nada
        console.log('Cart count:', cartBadge.textContent);
    }
}

// Initialize search functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            window.location.href = `${window.location.origin}${window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'))}/products.php?search=${encodeURIComponent(searchTerm)}`;
        }
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    if (searchButton) {
        searchButton.addEventListener('click', performSearch);
    }
}

// Initialize newsletter form in footer
function initializeNewsletterForm() {
    const newsletterForms = document.querySelectorAll('.newsletter-form');
    newsletterForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            // Here you would typically send to API
            console.log('Newsletter subscription:', email);
            
            // Show success message
            alert('Thank you for subscribing to our newsletter!');
            this.reset();
        });
    });
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add animation on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.querySelectorAll('.feature-item, .product-card, .testimonial-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'all 0.6s ease-out';
    observer.observe(el);
});

// Initialize product grid navigation
function initializeProductNavigation() {
    const navButtons = document.querySelectorAll('.nav-arrow');
    
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const grid = document.getElementById(`${category}-grid`);
            const isPrev = this.classList.contains('nav-prev');
            
            if (grid) {
                const scrollAmount = grid.offsetWidth / 2;
                grid.scrollBy({
                    left: isPrev ? -scrollAmount : scrollAmount,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Initialize user dropdown with click
function initializeUserDropdown() {
    const dropdown = document.querySelector('.dropdown');
    if (!dropdown) return;
    
    const userIcon = dropdown.querySelector('.user-icon');
    
    // Toggle dropdown on click
    userIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
    
    // Close dropdown when clicking on a link
    const dropdownLinks = dropdown.querySelectorAll('.dropdown-content a');
    dropdownLinks.forEach(link => {
        link.addEventListener('click', function() {
            dropdown.classList.remove('active');
        });
    });
}

console.log('Online Medicine Store v6.0 - JavaScript loaded - Database integration active');
