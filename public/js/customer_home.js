/**
 * Customer Home Page JavaScript
 * Handles dynamic functionality, animations, and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize AOS (Animate On Scroll) if available
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

    // Initialize marquee functionality
    initializeMarquee();
    
    // Initialize card interactions
    initializeCardInteractions();
    
    // Initialize advertisement carousel
    initializeAdvertisementCarousel();
    
    // Initialize responsive features
    initializeResponsiveFeatures();
    
    // Initialize accessibility features
    initializeAccessibility();
});

/**
 * Initialize marquee functionality
 */
function initializeMarquee() {
    const marqueeContainer = document.querySelector('.marquee-container');
    const marqueeScroll = document.querySelector('.marquee-scroll');
    
    if (!marqueeContainer || !marqueeScroll) return;
    
    // Pause/resume marquee on hover
    marqueeContainer.addEventListener('mouseenter', function() {
        marqueeScroll.style.animationPlayState = 'paused';
    });
    
    marqueeContainer.addEventListener('mouseleave', function() {
        marqueeScroll.style.animationPlayState = 'running';
    });
    
    // Pause marquee when tab is not visible (performance optimization)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            marqueeScroll.style.animationPlayState = 'paused';
        } else {
            marqueeScroll.style.animationPlayState = 'running';
        }
    });
    
    // Adjust marquee speed based on content length
    adjustMarqueeSpeed();
}

/**
 * Adjust marquee animation speed based on content length
 */
function adjustMarqueeSpeed() {
    const marqueeScroll = document.querySelector('.marquee-scroll');
    if (!marqueeScroll) return;
    
    const contentLength = marqueeScroll.scrollWidth;
    const containerWidth = marqueeScroll.parentElement.offsetWidth;
    
    // Calculate optimal duration (longer content = longer duration)
    const baseDuration = 30; // seconds
    const duration = Math.max(20, (contentLength / containerWidth) * baseDuration);
    
    marqueeScroll.style.animationDuration = duration + 's';
}

/**
 * Initialize card interactions and effects
 */
function initializeCardInteractions() {
    // Add ripple effect to cards
    addRippleEffect();
    
    // Initialize card tilt effect for featured cards
    initializeCardTilt();
    
    // Add loading states for external links
    initializeExternalLinks();
    
    // Initialize card expand functionality
    initializeCardExpansion();
}

/**
 * Add ripple effect to clickable cards
 */
function addRippleEffect() {
    const clickableCards = document.querySelectorAll('.quick-action-card, .featured-card, .advertisement-card');
    
    clickableCards.forEach(card => {
        card.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: ripple 0.6s linear;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                pointer-events: none;
            `;
            
            // Add ripple animation keyframes if not already present
            if (!document.querySelector('#ripple-styles')) {
                const style = document.createElement('style');
                style.id = 'ripple-styles';
                style.textContent = `
                    @keyframes ripple {
                        to {
                            transform: scale(4);
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(style);
            }
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            // Remove ripple after animation
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.parentNode.removeChild(ripple);
                }
            }, 600);
        });
    });
}

/**
 * Initialize subtle tilt effect for featured cards
 */
function initializeCardTilt() {
    const featuredCards = document.querySelectorAll('.featured-card');
    
    featuredCards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            if (window.innerWidth < 768) return; // Disable on mobile
            
            const rect = this.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            
            const deltaX = (e.clientX - centerX) / (rect.width / 2);
            const deltaY = (e.clientY - centerY) / (rect.height / 2);
            
            const tiltX = deltaY * 5; // Max 5 degrees
            const tiltY = deltaX * -5; // Max 5 degrees
            
            this.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateZ(10px)`;
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
        });
    });
}

/**
 * Initialize external link handling
 */
function initializeExternalLinks() {
    const externalLinks = document.querySelectorAll('a[target="_blank"]');
    
    externalLinks.forEach(link => {
        // Add loading state
        link.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening...';
            this.style.pointerEvents = 'none';
            
            // Reset after 2 seconds
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }, 2000);
        });
        
        // Add security attributes
        link.setAttribute('rel', 'noopener noreferrer');
    });
}

/**
 * Initialize card expansion functionality for mobile
 */
function initializeCardExpansion() {
    const newsCards = document.querySelectorAll('.news-card, .announcement-card');
    
    newsCards.forEach(card => {
        card.addEventListener('click', function() {
            if (window.innerWidth >= 768) return; // Only on mobile
            
            this.classList.toggle('expanded');
            
            // Toggle full content visibility
            const excerpt = this.querySelector('.news-content, .announcement-text');
            if (excerpt && this.classList.contains('expanded')) {
                excerpt.style.webkitLineClamp = 'unset';
                excerpt.style.maxHeight = 'none';
            } else if (excerpt) {
                excerpt.style.webkitLineClamp = '3';
                excerpt.style.maxHeight = '4.5em';
            }
        });
    });
}

/**
 * Initialize advertisement carousel functionality
 */
function initializeAdvertisementCarousel() {
    const carousel = document.querySelector('.advertisement-carousel');
    if (!carousel) return;
    
    // Add auto-scroll for advertisements on desktop
    if (window.innerWidth >= 992) {
        initializeAutoScroll(carousel);
    }
    
    // Add touch/swipe support for mobile
    if (window.innerWidth < 768) {
        initializeTouchSwipe(carousel);
    }
}

/**
 * Initialize auto-scroll for advertisement carousel
 */
function initializeAutoScroll(carousel) {
    const slides = carousel.querySelectorAll('.advertisement-slide');
    if (slides.length <= 1) return;
    
    let currentIndex = 0;
    let autoScrollInterval;
    
    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.style.opacity = i === index ? '1' : '0.7';
            slide.style.transform = i === index ? 'scale(1)' : 'scale(0.95)';
        });
    }
    
    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
    }
    
    function startAutoScroll() {
        autoScrollInterval = setInterval(nextSlide, 4000);
    }
    
    function stopAutoScroll() {
        clearInterval(autoScrollInterval);
    }
    
    // Initialize
    showSlide(0);
    startAutoScroll();
    
    // Pause on hover
    carousel.addEventListener('mouseenter', stopAutoScroll);
    carousel.addEventListener('mouseleave', startAutoScroll);
    
    // Pause when tab is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoScroll();
        } else {
            startAutoScroll();
        }
    });
}

/**
 * Initialize touch/swipe support for mobile
 */
function initializeTouchSwipe(carousel) {
    let startX, startY, distX, distY;
    const threshold = 50; // Minimum distance for swipe
    
    carousel.addEventListener('touchstart', function(e) {
        const touch = e.touches[0];
        startX = touch.clientX;
        startY = touch.clientY;
    });
    
    carousel.addEventListener('touchmove', function(e) {
        if (!startX || !startY) return;
        
        const touch = e.touches[0];
        distX = touch.clientX - startX;
        distY = touch.clientY - startY;
        
        // Prevent default scroll if horizontal swipe
        if (Math.abs(distX) > Math.abs(distY)) {
            e.preventDefault();
        }
    });
    
    carousel.addEventListener('touchend', function() {
        if (!startX || !startY) return;
        
        if (Math.abs(distX) > threshold && Math.abs(distX) > Math.abs(distY)) {
            // Horizontal swipe detected
            if (distX > 0) {
                // Swipe right - scroll left
                carousel.scrollBy({ left: -200, behavior: 'smooth' });
            } else {
                // Swipe left - scroll right
                carousel.scrollBy({ left: 200, behavior: 'smooth' });
            }
        }
        
        // Reset
        startX = startY = distX = distY = null;
    });
}

/**
 * Initialize responsive features
 */
function initializeResponsiveFeatures() {
    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            adjustMarqueeSpeed();
            handleResponsiveChanges();
        }, 250);
    });
    
    // Handle orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(function() {
            adjustMarqueeSpeed();
            handleResponsiveChanges();
        }, 100);
    });
}

/**
 * Handle responsive layout changes
 */
function handleResponsiveChanges() {
    const isMobile = window.innerWidth < 768;
    const isTablet = window.innerWidth >= 768 && window.innerWidth < 992;
    
    // Update grid layouts dynamically
    updateGridLayouts(isMobile, isTablet);
    
    // Update card interactions
    updateCardInteractions(isMobile);
    
    // Update animation states
    updateAnimations(isMobile);
}

/**
 * Update grid layouts based on screen size
 */
function updateGridLayouts(isMobile, isTablet) {
    const featuredCascade = document.querySelector('.featured-cascade');
    const quickActionsGrid = document.querySelector('.quick-actions-grid');
    const advertisementCarousel = document.querySelector('.advertisement-carousel');
    
    if (featuredCascade) {
        if (isMobile) {
            featuredCascade.style.gridTemplateColumns = '1fr';
        } else if (isTablet) {
            featuredCascade.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else {
            featuredCascade.style.gridTemplateColumns = 'repeat(3, 1fr)';
        }
    }
    
    if (quickActionsGrid) {
        if (isMobile) {
            quickActionsGrid.style.gridTemplateColumns = '1fr';
        } else if (isTablet) {
            quickActionsGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else {
            quickActionsGrid.style.gridTemplateColumns = 'repeat(4, 1fr)';
        }
    }
}

/**
 * Update card interactions for different screen sizes
 */
function updateCardInteractions(isMobile) {
    const featuredCards = document.querySelectorAll('.featured-card');
    
    featuredCards.forEach(card => {
        if (isMobile) {
            // Disable hover effects on mobile
            card.style.transform = 'none';
        } else {
            // Re-enable hover effects on desktop
            card.style.transform = '';
        }
    });
}

/**
 * Update animations based on device capabilities
 */
function updateAnimations(isMobile) {
    // Reduce animations on mobile for better performance
    if (isMobile) {
        document.documentElement.style.setProperty('--animation-duration', '0.2s');
    } else {
        document.documentElement.style.setProperty('--animation-duration', '0.3s');
    }
}

/**
 * Initialize accessibility features
 */
function initializeAccessibility() {
    // Add keyboard navigation
    initializeKeyboardNavigation();
    
    // Add focus management
    initializeFocusManagement();
    
    // Add ARIA labels and roles
    addAriaLabels();
    
    // Handle reduced motion preference
    handleReducedMotion();
}

/**
 * Initialize keyboard navigation
 */
function initializeKeyboardNavigation() {
    const focusableElements = document.querySelectorAll('.quick-action-card, .featured-card a, .news-card a, .announcement-card a');
    
    focusableElements.forEach(element => {
        element.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
}

/**
 * Initialize focus management
 */
function initializeFocusManagement() {
    // Add visible focus indicators
    const focusableElements = document.querySelectorAll('a, button, .quick-action-card');
    
    focusableElements.forEach(element => {
        element.addEventListener('focus', function() {
            this.style.outline = '3px solid #3498db';
            this.style.outlineOffset = '2px';
        });
        
        element.addEventListener('blur', function() {
            this.style.outline = '';
            this.style.outlineOffset = '';
        });
    });
}

/**
 * Add ARIA labels and roles for screen readers
 */
function addAriaLabels() {
    // Add ARIA labels to marquee
    const marquee = document.querySelector('.marquee-container');
    if (marquee) {
        marquee.setAttribute('role', 'banner');
        marquee.setAttribute('aria-label', 'Important announcements');
    }
    
    // Add ARIA labels to card sections
    const sections = document.querySelectorAll('.news-section, .announcements-section, .advertisements-banner, .quick-actions-section');
    sections.forEach(section => {
        section.setAttribute('role', 'region');
        const title = section.querySelector('.section-title');
        if (title) {
            const id = 'section-' + Math.random().toString(36).substr(2, 9);
            title.id = id;
            section.setAttribute('aria-labelledby', id);
        }
    });
    
    // Add ARIA labels to quick action cards
    const quickActionCards = document.querySelectorAll('.quick-action-card');
    quickActionCards.forEach(card => {
        card.setAttribute('role', 'button');
        card.setAttribute('tabindex', '0');
        const title = card.querySelector('h5');
        if (title) {
            card.setAttribute('aria-label', title.textContent);
        }
    });
}

/**
 * Handle reduced motion preference
 */
function handleReducedMotion() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    if (prefersReducedMotion) {
        // Disable marquee animation
        const marqueeScroll = document.querySelector('.marquee-scroll');
        if (marqueeScroll) {
            marqueeScroll.style.animation = 'none';
        }
        
        // Disable other animations
        const animatedElements = document.querySelectorAll('.featured-card, .news-card, .announcement-card, .quick-action-card');
        animatedElements.forEach(element => {
            element.style.transition = 'none';
        });
        
        // Disable AOS animations
        if (typeof AOS !== 'undefined') {
            AOS.init({ disable: true });
        }
    }
}

/**
 * Utility function to debounce function calls
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Performance monitoring and optimization
 */
function initializePerformanceOptimization() {
    // Lazy load images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    // Optimize scroll events
    let ticking = false;
    function updateOnScroll() {
        // Add scroll-based optimizations here
        ticking = false;
    }
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateOnScroll);
            ticking = true;
        }
    });
}

// Initialize performance optimization
initializePerformanceOptimization();
