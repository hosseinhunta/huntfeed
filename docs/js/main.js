/**
 * HuntFeed - Modern JavaScript Enhancements
 * Responsive, Accessible, and Performant
 */

(() => {
    'use strict';
    
    // Configuration
    const CONFIG = {
        debounceDelay: 100,
        scrollThreshold: 100,
        animationDuration: 300
    };
    
    // State Management
    const state = {
        activeTab: 'telegram',
        activeFaq: null,
        scrolled: false,
        prefersReducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches
    };
    
    // DOM Elements Cache
    const elements = {
        navbar: document.querySelector('.navbar'),
        tabButtons: document.querySelectorAll('.tab-button'),
        tabPanes: document.querySelectorAll('.tab-pane'),
        faqQuestions: document.querySelectorAll('.faq-question'),
        faqAnswers: document.querySelectorAll('.faq-answer'),
        codeTabs: document.querySelectorAll('.code-tab'),
        codeBlocks: document.querySelectorAll('.code-block'),
        chartFills: document.querySelectorAll('.chart-fill'),
        lazyImages: document.querySelectorAll('img[data-src]')
    };
    
    // Initialize Application
    function init() {
        console.log('🚀 HuntFeed initialized');
        
        setupEventListeners();
        setupIntersectionObservers();
        setupPerformanceCharts();
        handleScroll();
        initTabs();
        initFAQ();
        initLazyLoading();
        
        // Add loading class for initial animations
        document.documentElement.classList.add('loaded');
    }
    
    // Event Listeners Setup
    function setupEventListeners() {
        // Window Events
        window.addEventListener('scroll', debounce(handleScroll, CONFIG.debounceDelay));
        window.addEventListener('resize', debounce(handleResize, CONFIG.debounceDelay));
        window.addEventListener('load', handleLoad);
        
        // Keyboard Navigation
        document.addEventListener('keydown', handleKeyNavigation);
        
        // Tab Accessibility
        elements.tabButtons.forEach(button => {
            button.addEventListener('keydown', handleTabKeyNavigation);
        });
    }
    
    // Scroll Handler
    function handleScroll() {
        const scrollY = window.scrollY;
        state.scrolled = scrollY > CONFIG.scrollThreshold;
        
        // Update navbar
        elements.navbar?.classList.toggle('scrolled', state.scrolled);
        
        // Update active section in navigation
        updateActiveNavLink();
    }
    
    // Resize Handler
    function handleResize() {
        console.log('📱 Window resized:', window.innerWidth);
    }
    
    // Load Handler
    function handleLoad() {
        console.log('✅ Page fully loaded');
        
        // Animate performance charts
        animateCharts();
        
        // Remove loading states
        document.documentElement.classList.remove('loading');
    }
    
    // Tab Navigation
    function initTabs() {
        elements.tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const tabId = button.dataset.tab;
                switchTab(tabId);
                
                // Update URL hash without scrolling
                history.replaceState(null, null, `#${tabId}`);
            });
        });
        
        // Check URL hash for initial tab
        const hash = window.location.hash.substring(1);
        if (hash && document.querySelector(`#${hash}`)) {
            switchTab(hash);
        }
    }
    
    function switchTab(tabId) {
        // Update buttons
        elements.tabButtons.forEach(button => {
            const isActive = button.dataset.tab === tabId;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-selected', isActive);
            
            if (isActive) {
                button.focus();
            }
        });
        
        // Update panes
        elements.tabPanes.forEach(pane => {
            const isActive = pane.id === tabId;
            pane.classList.toggle('active', isActive);
            pane.setAttribute('aria-hidden', !isActive);
        });
        
        // Update code blocks if available
        const codeBlock = document.querySelector(`.code-block[data-tab="${tabId}"]`);
        if (codeBlock) {
            switchCodeTab(tabId);
        }
        
        state.activeTab = tabId;
    }
    
    // Code Tabs
    function initCodeTabs() {
        elements.codeTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const language = tab.dataset.lang;
                switchCodeTab(language);
            });
        });
    }
    
    function switchCodeTab(language) {
        elements.codeTabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.lang === language);
        });
        
        elements.codeBlocks.forEach(block => {
            block.classList.toggle('active', block.dataset.lang === language);
        });
    }
    
    // FAQ Accordion
    function initFAQ() {
        elements.faqQuestions.forEach((question, index) => {
            question.addEventListener('click', () => {
                toggleFAQ(index);
            });
            
            question.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleFAQ(index);
                }
            });
        });
    }
    
    function toggleFAQ(index) {
        const answer = elements.faqAnswers[index];
        const isOpening = state.activeFaq !== index;
        
        // Close current FAQ if opening a different one
        if (state.activeFaq !== null && state.activeFaq !== index) {
            closeFAQ(state.activeFaq);
        }
        
        if (isOpening) {
            openFAQ(index);
            state.activeFaq = index;
        } else {
            closeFAQ(index);
            state.activeFaq = null;
        }
    }
    
    function openFAQ(index) {
        const question = elements.faqQuestions[index];
        const answer = elements.faqAnswers[index];
        
        question.classList.add('active');
        answer.classList.add('active');
        question.setAttribute('aria-expanded', 'true');
        
        // Animate height if reduced motion is not preferred
        if (!state.prefersReducedMotion) {
            answer.style.maxHeight = answer.scrollHeight + 10 + 'px';
        }
    }
    
    function closeFAQ(index) {
        const question = elements.faqQuestions[index];
        const answer = elements.faqAnswers[index];
        
        question.classList.remove('active');
        answer.classList.remove('active');
        question.setAttribute('aria-expanded', 'false');
        
        if (!state.prefersReducedMotion) {
            answer.style.maxHeight = null;
        }
    }
    
    // Performance Charts Animation
    function setupPerformanceCharts() {
        // Set initial widths from data attributes
        elements.chartFills.forEach(chart => {
            const value = chart.dataset.value;
            chart.style.width = '0%';
            
            // Store final value for animation
            chart.dataset.finalValue = value;
        });
    }
    
    function animateCharts() {
        if (state.prefersReducedMotion) {
            // Skip animation for reduced motion preference
            elements.chartFills.forEach(chart => {
                chart.style.width = chart.dataset.finalValue;
            });
            return;
        }
        
        // Animate each chart with delay
        elements.chartFills.forEach((chart, index) => {
            setTimeout(() => {
                chart.style.width = chart.dataset.finalValue;
            }, index * 200);
        });
    }
    
    // Lazy Loading Images
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            elements.lazyImages.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for browsers without IntersectionObserver
            elements.lazyImages.forEach(img => {
                img.src = img.dataset.src;
            });
        }
    }
    
    // Intersection Observers Setup
    function setupIntersectionObservers() {
        // Animate elements on scroll
        const animateOnScroll = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        };
        
        const scrollObserver = new IntersectionObserver(animateOnScroll, {
            threshold: 0.1,
            rootMargin: '50px'
        });
        
        // Observe elements to animate
        document.querySelectorAll('.feature-card, .api-card, .benefit-item').forEach(el => {
            scrollObserver.observe(el);
        });
    }
    
    // Update Active Navigation Link
    function updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const scrollPosition = window.scrollY + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }
    
    // Keyboard Navigation
    function handleKeyNavigation(e) {
        // Escape key closes modals/dropdowns
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
        
        // Tab key handling for focus management
        if (e.key === 'Tab') {
            document.documentElement.classList.add('user-is-tabbing');
        }
    }
    
    function handleTabKeyNavigation(e) {
        const tabs = Array.from(elements.tabButtons);
        const currentIndex = tabs.indexOf(e.target);
        
        let nextIndex;
        
        switch (e.key) {
            case 'ArrowLeft':
            case 'ArrowUp':
                e.preventDefault();
                nextIndex = currentIndex > 0 ? currentIndex - 1 : tabs.length - 1;
                tabs[nextIndex].focus();
                tabs[nextIndex].click();
                break;
                
            case 'ArrowRight':
            case 'ArrowDown':
                e.preventDefault();
                nextIndex = currentIndex < tabs.length - 1 ? currentIndex + 1 : 0;
                tabs[nextIndex].focus();
                tabs[nextIndex].click();
                break;
                
            case 'Home':
                e.preventDefault();
                tabs[0].focus();
                tabs[0].click();
                break;
                
            case 'End':
                e.preventDefault();
                tabs[tabs.length - 1].focus();
                tabs[tabs.length - 1].click();
                break;
        }
    }
    
    // Utility Functions
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
    
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    function closeAllDropdowns() {
        // Close any open dropdowns, modals, or menus
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }
    
    // Error Handling
    function handleError(error, context = '') {
        console.error(`❌ Error in ${context}:`, error);
        
        // Show user-friendly error message
        const errorElement = document.getElementById('error-message');
        if (errorElement) {
            errorElement.textContent = 'An error occurred. Please try again.';
            errorElement.style.display = 'block';
            
            setTimeout(() => {
                errorElement.style.display = 'none';
            }, 5000);
        }
    }
    
    // Performance Monitoring
    function measurePerformance(markName) {
        if ('performance' in window && 'mark' in performance) {
            performance.mark(markName);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Expose public API for debugging
    window.HuntFeed = {
        state,
        elements,
        switchTab,
        toggleFAQ,
        animateCharts,
        measurePerformance
    };
    
    // Error boundary for the module
    try {
        // Additional initialization can go here
    } catch (error) {
        handleError(error, 'module initialization');
    }
})();