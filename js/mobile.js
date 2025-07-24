/**
 * TimeEffect Mobile JavaScript Framework
 * Phase 1: Mobile Foundation
 * Created: 2025-01-24
 */

(function() {
    'use strict';
    
    // Mobile detection
    const MobileDetector = {
        isMobile: function() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },
        
        isTablet: function() {
            return /iPad|Android(?!.*Mobile)/i.test(navigator.userAgent);
        },
        
        isTouch: function() {
            return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        },
        
        getViewportWidth: function() {
            return Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        },
        
        getViewportHeight: function() {
            return Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
        }
    };
    
    // Mobile Navigation Handler
    const MobileNav = {
        init: function() {
            this.createMobileNav();
            this.bindEvents();
        },
        
        createMobileNav: function() {
            // Create mobile navigation toggle button
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'mobile-nav-toggle mobile-only';
            toggleBtn.innerHTML = '☰';
            toggleBtn.setAttribute('aria-label', 'Toggle navigation');
            document.body.appendChild(toggleBtn);
            
            // Create mobile navigation overlay
            const overlay = document.createElement('div');
            overlay.className = 'mobile-nav-overlay';
            document.body.appendChild(overlay);
            
            // Convert existing navigation to mobile format
            this.convertNavigation();
        },
        
        convertNavigation: function() {
            // Auto-enable mobile navigation on mobile devices
            if (MobileDetector.isMobile() || MobileDetector.getViewportWidth() <= 768) {
                this.createMobileNavigation();
            } else {
                console.log('TimeEffect Mobile: Desktop detected, mobile navigation disabled');
                return;
            }
        },
        
        createMobileNavigation: function() {
            // Create mobile navigation container
            const mobileNav = document.createElement('nav');
            mobileNav.className = 'mobile-nav mobile-only';
            
            const navList = document.createElement('ul');
            
            // Add main options to mobile navigation
            this.addMainOptionsToMobileNav(navList);
            
            // Add regular navigation links if available
            this.addRegularNavToMobileNav(navList);
            
            mobileNav.appendChild(navList);
            document.body.appendChild(mobileNav);
        },
        
        addMainOptionsToMobileNav: function(navList) {
            const mainOptions = document.querySelector('#mainOptions');
            if (!mainOptions) return;
            
            // Extract main option links
            const mainLinks = mainOptions.querySelectorAll('a');
            
            // Create main options section in mobile nav
            const mainOptionsDiv = document.createElement('div');
            mainOptionsDiv.className = 'mobile-main-options';
            
            mainLinks.forEach(link => {
                const mobileLink = link.cloneNode(true);
                mobileLink.addEventListener('click', () => {
                    this.closeNav();
                });
                mainOptionsDiv.appendChild(mobileLink);
            });
            
            // Add to navigation list as first item
            const mainOptionsListItem = document.createElement('li');
            mainOptionsListItem.appendChild(mainOptionsDiv);
            navList.appendChild(mainOptionsListItem);
        },
        
        addRegularNavToMobileNav: function(navList) {
            // Add left navigation links if available
            const leftNav = document.querySelector('#leftNavigation');
            if (leftNav) {
                const links = leftNav.querySelectorAll('a');
                links.forEach(link => {
                    const listItem = document.createElement('li');
                    const mobileLink = link.cloneNode(true);
                    mobileLink.addEventListener('click', () => {
                        this.closeNav();
                    });
                    listItem.appendChild(mobileLink);
                    navList.appendChild(listItem);
                });
            }
        },
        
        bindEvents: function() {
            const toggleBtn = document.querySelector('.mobile-nav-toggle');
            const overlay = document.querySelector('.mobile-nav-overlay');
            const mobileNav = document.querySelector('.mobile-nav');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    this.toggleNav();
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', () => {
                    this.closeNav();
                });
            }
            
            // Close nav on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeNav();
                }
            });
        },
        
        toggleNav: function() {
            const mobileNav = document.querySelector('.mobile-nav');
            const overlay = document.querySelector('.mobile-nav-overlay');
            
            if (mobileNav && overlay) {
                mobileNav.classList.toggle('open');
                overlay.classList.toggle('open');
                document.body.style.overflow = mobileNav.classList.contains('open') ? 'hidden' : '';
            }
        },
        
        closeNav: function() {
            const mobileNav = document.querySelector('.mobile-nav');
            const overlay = document.querySelector('.mobile-nav-overlay');
            
            if (mobileNav && overlay) {
                mobileNav.classList.remove('open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
        }
    };
    
    // Touch-friendly enhancements
    const TouchEnhancements = {
        init: function() {
            this.enhanceButtons();
            this.enhanceLinks();
            this.enhanceForms();
            this.addTouchFeedback();
        },
        
        enhanceButtons: function() {
            const buttons = document.querySelectorAll('button, input[type="submit"], input[type="button"], .button');
            buttons.forEach(button => {
                if (!button.classList.contains('touch-target')) {
                    button.classList.add('touch-target');
                }
            });
        },
        
        enhanceLinks: function() {
            // Only enhance links that are explicitly marked for mobile optimization
            const links = document.querySelectorAll('a.mobile-link, a.responsive-link');
            links.forEach(link => {
                // Add touch-friendly padding for small links
                const rect = link.getBoundingClientRect();
                if (rect.height < 44 || rect.width < 44) {
                    link.style.padding = '10px';
                    link.style.display = 'inline-block';
                    link.style.minHeight = '44px';
                    link.style.minWidth = '44px';
                    link.style.textAlign = 'center';
                    link.style.lineHeight = '24px';
                }
            });
        },
        
        enhanceForms: function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.classList.add('form-responsive');
                
                // Enhance form inputs
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    // Prevent zoom on iOS
                    if (input.type === 'text' || input.type === 'email' || input.type === 'password') {
                        if (parseInt(getComputedStyle(input).fontSize) < 16) {
                            input.style.fontSize = '16px';
                        }
                    }
                    
                    // Add form group wrapper if not exists
                    if (!input.parentElement.classList.contains('form-group')) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'form-group';
                        input.parentNode.insertBefore(wrapper, input);
                        wrapper.appendChild(input);
                    }
                });
            });
        },
        
        addTouchFeedback: function() {
            // Add visual feedback for touch interactions
            document.addEventListener('touchstart', (e) => {
                const target = e.target.closest('button, a, input[type="submit"], input[type="button"], .button');
                if (target) {
                    target.style.opacity = '0.7';
                }
            });
            
            document.addEventListener('touchend', (e) => {
                const target = e.target.closest('button, a, input[type="submit"], input[type="button"], .button');
                if (target) {
                    setTimeout(() => {
                        target.style.opacity = '';
                    }, 150);
                }
            });
        }
    };
    
    // Responsive Tables Handler
    const ResponsiveTables = {
        init: function() {
            this.enhanceTables();
        },
        
        enhanceTables: function() {
            // Only enhance tables that are explicitly marked for mobile optimization
            // TimeEffect uses tables for layout - don't break them!
            const tables = document.querySelectorAll('table.data-table, table.responsive-table');
            tables.forEach(table => {
                this.makeTableResponsive(table);
            });
        },
        
        makeTableResponsive: function(table) {
            // Wrap table in responsive container
            const wrapper = document.createElement('div');
            wrapper.className = 'table-container';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
            
            // Add responsive class
            table.classList.add('table-responsive');
            
            // For mobile: add data labels for stacked view
            if (MobileDetector.getViewportWidth() <= 480) {
                this.addDataLabels(table);
                table.classList.add('table-stack');
            }
        },
        
        addDataLabels: function(table) {
            const headers = table.querySelectorAll('th');
            const headerTexts = Array.from(headers).map(th => th.textContent.trim());
            
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (headerTexts[index]) {
                        cell.setAttribute('data-label', headerTexts[index]);
                    }
                });
            });
        }
    };
    
    // Viewport management
    const ViewportManager = {
        init: function() {
            this.setViewportMeta();
            this.handleOrientationChange();
        },
        
        setViewportMeta: function() {
            let viewport = document.querySelector('meta[name="viewport"]');
            if (!viewport) {
                viewport = document.createElement('meta');
                viewport.name = 'viewport';
                document.head.appendChild(viewport);
            }
            viewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes';
        },
        
        handleOrientationChange: function() {
            window.addEventListener('orientationchange', () => {
                // Fix viewport issues on orientation change
                setTimeout(() => {
                    const viewport = document.querySelector('meta[name="viewport"]');
                    if (viewport) {
                        viewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes';
                    }
                }, 500);
            });
        }
    };
    
    // Performance optimizations
    const PerformanceOptimizer = {
        init: function() {
            this.lazyLoadImages();
            this.optimizeScrolling();
        },
        
        lazyLoadImages: function() {
            const images = document.querySelectorAll('img[src]');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                                imageObserver.unobserve(img);
                            }
                        }
                    });
                });
                
                images.forEach(img => {
                    if (img.offsetTop > window.innerHeight) {
                        img.dataset.src = img.src;
                        img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9IiNjY2MiLz48L3N2Zz4=';
                        imageObserver.observe(img);
                    }
                });
            }
        },
        
        optimizeScrolling: function() {
            // Passive event listeners for better scroll performance
            let ticking = false;
            
            function updateScrollPosition() {
                // Add scroll-based optimizations here
                ticking = false;
            }
            
            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateScrollPosition);
                    ticking = true;
                }
            }
            
            window.addEventListener('scroll', requestTick, { passive: true });
        }
    };
    
    // Main initialization
    const TimeEffectMobile = {
        init: function() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    this.initializeComponents();
                });
            } else {
                this.initializeComponents();
            }
        },
        
        initializeComponents: function() {
            // Only initialize mobile features if on mobile device or small screen
            if (MobileDetector.isMobile() || MobileDetector.getViewportWidth() <= 768) {
                console.log('TimeEffect Mobile: Initializing mobile features');
                
                ViewportManager.init();
                MobileNav.init();
                TouchEnhancements.init();
                ResponsiveTables.init();
                PerformanceOptimizer.init();
                
                // Add mobile class to body
                document.body.classList.add('mobile-device');
                
                console.log('TimeEffect Mobile: Mobile features initialized');
            } else {
                console.log('TimeEffect Mobile: Desktop detected, mobile features disabled');
                document.body.classList.add('desktop-device');
            }
        }
    };
    
    // Auto-initialize
    TimeEffectMobile.init();
    
    // Expose to global scope for debugging
    window.TimeEffectMobile = {
        MobileDetector,
        MobileNav,
        TouchEnhancements,
        ResponsiveTables,
        ViewportManager,
        PerformanceOptimizer
    };
    
})();
