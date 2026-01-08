// MediCareOnline Enhanced JavaScript
(function() {
    'use strict';

    // Scroll Header Effect
    const header = document.querySelector('.main-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Smooth Scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
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

    // Add to Cart Function with Animation
    window.addToCart = function(productId) {
        const button = event.target.closest('button');
        const productCard = button.closest('.product-card');
        const productName = button.getAttribute('data-product-name');
        const productPrice = button.getAttribute('data-product-price');
        
        // Show loading state
        const originalHTML = button.innerHTML;
        button.innerHTML = '<div class="loading"></div> Adding...';
        button.disabled = true;

        // Simulate API call
        fetch('ajax/add-to-cart.php?v=' + Date.now(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success animation
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                button.style.background = '#28a745';
                
                // Update cart count
                updateCartCount();
                
                // Show success message
                showNotification('Product added to cart successfully!', 'success');
                
                // Animate product card
                productCard.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    productCard.style.transform = '';
                }, 200);
                
                // Reset button after delay
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.style.background = '';
                    button.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to add product');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = '<i class="fas fa-times"></i> Error';
            button.style.background = '#dc3545';
            showNotification('Failed to add product to cart', 'error');
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.style.background = '';
                button.disabled = false;
            }, 2000);
        });
    };

    // Update Cart Count
    function updateCartCount() {
        fetch('ajax/get-cart-count.php?v=' + Date.now())
            .then(response => response.json())
            .then(data => {
                const cartButton = document.querySelector('.btn-cart');
                if (cartButton && data.count !== undefined) {
                    const cartHTML = `<i class="fas fa-shopping-cart"></i> (${data.count}) $${data.total.toFixed(2)}`;
                    cartButton.innerHTML = cartHTML;
                    
                    // Animate cart button
                    cartButton.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        cartButton.style.transform = '';
                    }, 300);
                }
            })
            .catch(error => console.error('Error updating cart:', error));
    }

    // Show Notification
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            z-index: 10000;
            font-weight: 600;
            animation: slideIn 0.3s ease-out;
        `;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Newsletter Form Submission
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const submitButton = this.querySelector('button');
            const email = emailInput.value;
            
            submitButton.innerHTML = '<div class="loading"></div> Subscribing...';
            submitButton.disabled = true;
            
            // Simulate subscription
            setTimeout(() => {
                showNotification('Successfully subscribed to newsletter!', 'success');
                emailInput.value = '';
                submitButton.innerHTML = 'Subscribe';
                submitButton.disabled = false;
            }, 1000);
        });
    }

    // Contact Form Submission
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitButton = this.querySelector('button');
            const originalHTML = submitButton.innerHTML;
            
            submitButton.innerHTML = '<div class="loading"></div> Sending...';
            submitButton.disabled = true;
            
            // Simulate form submission
            setTimeout(() => {
                showNotification('Message sent successfully! We\'ll get back to you soon.', 'success');
                this.reset();
                submitButton.innerHTML = originalHTML;
                submitButton.disabled = false;
            }, 1500);
        });
    }

    // Lazy Load Images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('.product-image img').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Product Card Hover Effects
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '';
        });
    });

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        // Update cart count on page load
        updateCartCount();
        
        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Animate service cards
        document.querySelectorAll('.service-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `all 0.6s ease-out ${index * 0.1}s`;
            fadeInObserver.observe(card);
        });

        // Animate product cards
        document.querySelectorAll('.product-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `all 0.6s ease-out ${(index % 4) * 0.1}s`;
            fadeInObserver.observe(card);
        });
    });

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .product-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .btn-add-cart {
            transition: all 0.3s;
        }
    `;
    document.head.appendChild(style);

    console.log('MediCareOnline Enhanced JS Loaded ✓');
})();
