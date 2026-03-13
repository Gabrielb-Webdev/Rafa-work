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
    const searchBox = searchInput ? searchInput.closest('.search-box') : null;

    const BASE = document.querySelector('meta[name="base-url"]')
        ? document.querySelector('meta[name="base-url"]').content
        : window.location.origin;

    function performSearch(term) {
        const searchTerm = (term || searchInput.value).trim();
        if (searchTerm) {
            window.location.href = BASE + '/products?search=' + encodeURIComponent(searchTerm);
        }
    }

    if (!searchInput || !searchBox) return;

    // Create suggestion dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'search-suggestions';
    searchBox.appendChild(dropdown);

    let debounceTimer = null;
    let highlighted = -1;

    function hideSuggestions() {
        dropdown.classList.remove('active');
        dropdown.innerHTML = '';
        highlighted = -1;
    }

    function renderSuggestions(items) {
        dropdown.innerHTML = '';
        if (!items.length) { hideSuggestions(); return; }
        items.forEach((name, idx) => {
            const item = document.createElement('div');
            item.className = 'search-suggestion-item';
            item.innerHTML = `<i class="fas fa-search"></i> ${name}`;
            item.addEventListener('mousedown', function(e) {
                e.preventDefault();
                searchInput.value = name;
                hideSuggestions();
                performSearch(name);
            });
            dropdown.appendChild(item);
        });
        dropdown.classList.add('active');
        highlighted = -1;
    }

    searchInput.addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(debounceTimer);
        if (q.length < 2) { hideSuggestions(); return; }
        debounceTimer = setTimeout(() => {
            fetch(BASE + '/api/search-suggestions.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(renderSuggestions)
                .catch(() => hideSuggestions());
        }, 200);
    });

    searchInput.addEventListener('keydown', function(e) {
        const items = dropdown.querySelectorAll('.search-suggestion-item');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlighted = Math.min(highlighted + 1, items.length - 1);
            items.forEach((el, i) => el.classList.toggle('highlighted', i === highlighted));
            if (items[highlighted]) searchInput.value = items[highlighted].textContent.trim();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlighted = Math.max(highlighted - 1, -1);
            items.forEach((el, i) => el.classList.toggle('highlighted', i === highlighted));
            if (highlighted >= 0 && items[highlighted]) searchInput.value = items[highlighted].textContent.trim();
        } else if (e.key === 'Enter') {
            hideSuggestions();
            performSearch();
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    searchInput.addEventListener('blur', function() {
        setTimeout(hideSuggestions, 150);
    });

    if (searchButton) {
        searchButton.addEventListener('click', () => { hideSuggestions(); performSearch(); });
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
