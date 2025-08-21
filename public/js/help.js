/**
 * Help Page JavaScript - Enhanced functionality for SAMPARK FOIS Help System
 */

class HelpPageManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupSmoothScrolling();
        this.setupIntersectionObserver();
        this.setupLoadingAnimations();
        this.setupMobileNavigation();
        this.setupKeyboardNavigation();
        this.setupAccessibilityFeatures();
    }

    setupSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    // Update active state in navigation
                    this.updateActiveNavigation(anchor);
                    
                    // Smooth scroll to target
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    updateActiveNavigation(activeAnchor) {
        document.querySelectorAll('.list-group-item').forEach(item => {
            item.classList.remove('active');
        });
        activeAnchor.classList.add('active');
    }

    setupIntersectionObserver() {
        const observerOptions = {
            root: null,
            rootMargin: '-20% 0px -70% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    this.highlightActiveSection(id);
                }
            });
        }, observerOptions);

        // Observe all sections
        document.querySelectorAll('.help-section[id]').forEach(section => {
            observer.observe(section);
        });
    }

    highlightActiveSection(sectionId) {
        document.querySelectorAll('.list-group-item').forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('href') === `#${sectionId}`) {
                item.classList.add('active');
            }
        });
    }

    setupLoadingAnimations() {
        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('.help-section');
            sections.forEach((section, index) => {
                setTimeout(() => {
                    section.style.opacity = '0';
                    section.style.transform = 'translateY(20px)';
                    section.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        section.style.opacity = '1';
                        section.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    }

    setupMobileNavigation() {
        const handleResize = () => {
            const toc = document.querySelector('.sticky-top');
            if (toc) {
                if (window.innerWidth <= 768) {
                    toc.classList.remove('sticky-top');
                    toc.style.position = 'relative';
                } else {
                    toc.classList.add('sticky-top');
                    toc.style.position = '';
                }
            }
        };

        // Initial setup
        handleResize();

        // Listen for window resize
        window.addEventListener('resize', handleResize);
    }

    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'Escape':
                    this.closeAllAccordions();
                    break;
                case 'Home':
                    e.preventDefault();
                    this.scrollToTop();
                    break;
                case 'End':
                    e.preventDefault();
                    this.scrollToBottom();
                    break;
            }
        });
    }

    closeAllAccordions() {
        const openAccordions = document.querySelectorAll('.accordion-collapse.show');
        openAccordions.forEach(accordion => {
            const button = document.querySelector(`[data-bs-target="#${accordion.id}"]`);
            if (button) {
                button.click();
            }
        });
    }

    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    scrollToBottom() {
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: 'smooth'
        });
    }

    setupAccessibilityFeatures() {
        // Add ARIA labels and roles
        this.addAriaLabels();
        
        // Add focus management
        this.setupFocusManagement();
        
        // Add skip links
        this.addSkipLinks();
    }

    addAriaLabels() {
        // Add ARIA labels to navigation items
        document.querySelectorAll('.list-group-item').forEach((item, index) => {
            item.setAttribute('aria-label', `Navigate to ${item.textContent.trim()}`);
            item.setAttribute('role', 'button');
            item.setAttribute('tabindex', '0');
        });

        // Add ARIA labels to accordion buttons
        document.querySelectorAll('.accordion-button').forEach(button => {
            const isExpanded = button.classList.contains('collapsed') ? 'false' : 'true';
            button.setAttribute('aria-expanded', isExpanded);
        });
    }

    setupFocusManagement() {
        // Ensure focus is visible on interactive elements
        document.querySelectorAll('a, button, [tabindex]').forEach(element => {
            element.addEventListener('focus', () => {
                element.style.outline = '2px solid #0088cc';
                element.style.outlineOffset = '2px';
            });

            element.addEventListener('blur', () => {
                element.style.outline = '';
                element.style.outlineOffset = '';
            });
        });
    }

    addSkipLinks() {
        // Add skip to main content link
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.textContent = 'Skip to main content';
        skipLink.className = 'skip-link';
        skipLink.style.cssText = `
            position: absolute;
            top: -40px;
            left: 6px;
            background: #0088cc;
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 1000;
        `;
        
        skipLink.addEventListener('focus', () => {
            skipLink.style.top = '6px';
        });
        
        skipLink.addEventListener('blur', () => {
            skipLink.style.top = '-40px';
        });

        document.body.insertBefore(skipLink, document.body.firstChild);

        // Add main content ID
        const mainContent = document.querySelector('.col-lg-9');
        if (mainContent) {
            mainContent.id = 'main-content';
        }
    }
}

// Enhanced accordion functionality
class EnhancedAccordion {
    constructor() {
        this.setupAccordionEnhancements();
    }

    setupAccordionEnhancements() {
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', (e) => {
                this.handleAccordionClick(e);
            });
        });
    }

    handleAccordionClick(e) {
        const button = e.currentTarget;
        const targetId = button.getAttribute('data-bs-target');
        const target = document.querySelector(targetId);
        
        if (target) {
            // Update ARIA attributes
            const isExpanded = button.classList.contains('collapsed');
            button.setAttribute('aria-expanded', isExpanded);
            
            // Add smooth animation
            if (isExpanded) {
                target.style.transition = 'all 0.3s ease';
                target.style.maxHeight = target.scrollHeight + 'px';
            }
        }
    }
}

// Enhanced search functionality
class HelpSearch {
    constructor() {
        this.setupSearch();
    }

    setupSearch() {
        // Only add search input on help pages (pages with help-specific content)
        const toc = document.querySelector('.card-body');
        const helpContent = document.querySelector('.help-content, .help-section, .help-page');
        
        // Only proceed if this is actually a help page
        if (toc && helpContent) {
            const searchContainer = document.createElement('div');
            searchContainer.className = 'mb-3';
            searchContainer.innerHTML = `
                <div class="input-group">
                    <input type="text" class="form-control" id="helpSearch" 
                           placeholder="Search help content..." 
                           aria-label="Search help content">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            toc.insertBefore(searchContainer, toc.firstChild);
            
            this.bindSearchEvents();
        }
    }

    bindSearchEvents() {
        const searchInput = document.getElementById('helpSearch');
        const clearButton = document.getElementById('clearSearch');
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.performSearch(e.target.value);
            });
        }
        
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                this.performSearch('');
            });
        }
    }

    performSearch(query) {
        const sections = document.querySelectorAll('.help-section');
        const navItems = document.querySelectorAll('.list-group-item');
        
        if (!query.trim()) {
            // Show all sections and nav items
            sections.forEach(section => section.style.display = 'block');
            navItems.forEach(item => item.style.display = 'block');
            return;
        }
        
        const searchTerm = query.toLowerCase();
        
        sections.forEach(section => {
            const text = section.textContent.toLowerCase();
            const isMatch = text.includes(searchTerm);
            section.style.display = isMatch ? 'block' : 'none';
            
            if (isMatch) {
                section.style.animation = 'highlight 0.5s ease';
            }
        });
        
        navItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            const isMatch = text.includes(searchTerm);
            item.style.display = isMatch ? 'block' : 'none';
        });
    }
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new HelpPageManager();
    new EnhancedAccordion();
    new HelpSearch();
    
    // Add highlight animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes highlight {
            0% { background-color: rgba(0, 136, 204, 0.2); }
            100% { background-color: transparent; }
        }
    `;
    document.head.appendChild(style);
});

// Export for potential external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { HelpPageManager, EnhancedAccordion, HelpSearch };
}
