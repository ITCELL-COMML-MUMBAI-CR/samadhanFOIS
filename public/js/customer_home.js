/**
 * Customer Home Page JavaScript
 * Optimized for performance with marquee functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize marquee functionality
    initializeMarquee();
    
    // Initialize basic functionality
    initializeBasicInteractions();
    
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
 * Initialize basic interactions without heavy animations
 */
function initializeBasicInteractions() {
    // Add basic hover effects for cards
    initializeCardHoverEffects();
    
    // Add loading states for external links
    initializeExternalLinks();
}

/**
 * Initialize simple card hover effects
 */
function initializeCardHoverEffects() {
    const cards = document.querySelectorAll('.quick-action-card, .news-card, .announcement-card, .advertisement-card');
    
    cards.forEach(card => {
        // Add basic hover class for styling
        card.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });
}

/**
 * Initialize external link handling
 */
function initializeExternalLinks() {
    const externalLinks = document.querySelectorAll('a[target="_blank"]');
    
    externalLinks.forEach(link => {
        // Add security attributes
        link.setAttribute('rel', 'noopener noreferrer');
        
        // Add basic loading state
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
}

/**
 * Update grid layouts based on screen size
 */
function updateGridLayouts(isMobile, isTablet) {
    const quickActionsGrid = document.querySelector('.quick-actions-grid');
    const advertisementGrid = document.querySelector('.advertisement-grid');
    
    if (quickActionsGrid) {
        if (isMobile) {
            quickActionsGrid.style.gridTemplateColumns = '1fr';
        } else if (isTablet) {
            quickActionsGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else {
            quickActionsGrid.style.gridTemplateColumns = 'repeat(4, 1fr)';
        }
    }
    
    if (advertisementGrid) {
        if (isMobile) {
            advertisementGrid.style.gridTemplateColumns = '1fr';
        } else if (isTablet) {
            advertisementGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else {
            advertisementGrid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(280px, 1fr))';
        }
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
    const focusableElements = document.querySelectorAll('.quick-action-card, .news-card a, .announcement-card a, .advertisement-card a');
    
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
        
        // Disable all transitions and animations
        const animatedElements = document.querySelectorAll('.news-card, .announcement-card, .quick-action-card, .advertisement-card, .support-ticket-card');
        animatedElements.forEach(element => {
            element.style.transition = 'none';
        });
        
        // Remove hover effects
        const cards = document.querySelectorAll('.quick-action-card, .news-card, .announcement-card, .advertisement-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function(e) {
                e.preventDefault();
            });
        });
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
 * Performance monitoring
 */
function initializePerformanceOptimization() {
    // Optimize scroll events
    let ticking = false;
    function updateOnScroll() {
        // Minimal scroll handling
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
