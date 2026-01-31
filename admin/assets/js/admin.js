/**
 * Modern Admin Panel JavaScript
 * Enhanced with better performance and UX
 */

(function() {
    'use strict';

    // ==================== CONFIGURATION ====================
    const config = {
        alertTimeout: 5000,
        debounceDelay: 300,
        animationDuration: 300
    };

    // ==================== MOBILE MENU ====================
    function initMobileMenu() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = createOverlay();
        
        if (!menuToggle || !sidebar) return;

        menuToggle.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', closeMenu);
        
        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeMenu();
            }
        });

        function toggleMenu() {
            const isActive = sidebar.classList.toggle('active');
            animateHamburger(isActive);
            
            if (isActive) {
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden';
            } else {
                closeMenu();
            }
        }

        function closeMenu() {
            sidebar.classList.remove('active');
            animateHamburger(false);
            if (overlay.parentNode) {
                overlay.remove();
            }
            document.body.style.overflow = '';
        }

        function animateHamburger(isActive) {
            const spans = menuToggle.querySelectorAll('span');
            if (isActive) {
                spans[0].style.transform = 'rotate(45deg) translateY(8px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translateY(-8px)';
            } else {
                spans.forEach(span => {
                    span.style.transform = '';
                    span.style.opacity = '';
                });
            }
        }

        function createOverlay() {
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                backdrop-filter: blur(2px);
                animation: fadeIn 0.2s ease;
            `;
            return overlay;
        }
    }

    // ==================== MODAL MANAGEMENT ====================
    const ModalManager = {
        activeModals: new Set(),

        open(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.style.display = 'block';
            this.activeModals.add(modalId);
            document.body.style.overflow = 'hidden';
            
            // Focus trap
            this.trapFocus(modal);
        },

        close(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            modal.style.display = 'none';
            this.activeModals.delete(modalId);
            
            if (this.activeModals.size === 0) {
                document.body.style.overflow = '';
            }

            // Reset form if exists
            const form = modal.querySelector('form');
            if (form) form.reset();
        },

        closeAll() {
            this.activeModals.forEach(id => this.close(id));
        },

        trapFocus(modal) {
            const focusableElements = modal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            modal.addEventListener('keydown', function(e) {
                if (e.key !== 'Tab') return;

                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        lastElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        firstElement.focus();
                        e.preventDefault();
                    }
                }
            });

            firstElement.focus();
        }
    };

    function initModals() {
        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                const modalId = e.target.id;
                ModalManager.close(modalId);
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                ModalManager.closeAll();
            }
        });

        // Setup close buttons
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) ModalManager.close(modal.id);
            });
        });
    }

    // ==================== FORM VALIDATION ====================
    const FormValidator = {
        validators: {
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            phone: (value) => /^[\d\s\-\+\(\)]+$/.test(value),
            url: (value) => /^https?:\/\/.+/.test(value),
            required: (value) => value.trim().length > 0,
            minLength: (value, min) => value.length >= min,
            maxLength: (value, max) => value.length <= max
        },

        validate(form) {
            let isValid = true;
            const fields = form.querySelectorAll('[required], [data-validate]');
            
            fields.forEach(field => {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        },

        validateField(field) {
            const value = field.value.trim();
            let isValid = true;
            let errorMessage = '';

            // Required validation
            if (field.hasAttribute('required') && !value) {
                isValid = false;
                errorMessage = 'This field is required';
            }

            // Type-specific validation
            if (isValid && value) {
                const type = field.type || field.getAttribute('data-validate');
                
                if (type === 'email' && !this.validators.email(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
                
                if (type === 'url' && !this.validators.url(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid URL';
                }
                
                if (type === 'tel' && value && !this.validators.phone(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid phone number';
                }
            }

            // Min length
            const minLength = field.getAttribute('minlength');
            if (isValid && minLength && value.length < parseInt(minLength)) {
                isValid = false;
                errorMessage = `Minimum ${minLength} characters required`;
            }

            this.showFieldError(field, isValid, errorMessage);
            return isValid;
        },

        showFieldError(field, isValid, message) {
            const errorEl = field.parentNode.querySelector('.error-message');
            
            if (!isValid) {
                field.classList.add('error');
                
                if (!errorEl) {
                    const error = document.createElement('div');
                    error.className = 'error-message';
                    error.style.cssText = 'color: var(--admin-danger); font-size: 0.8125rem; margin-top: 0.25rem;';
                    error.textContent = message;
                    field.parentNode.appendChild(error);
                }
            } else {
                field.classList.remove('error');
                if (errorEl) errorEl.remove();
            }
        }
    };

    function initFormValidation() {
        document.querySelectorAll('form').forEach(form => {
            // Real-time validation
            form.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('blur', () => {
                    if (field.value) FormValidator.validateField(field);
                });

                field.addEventListener('input', () => {
                    if (field.classList.contains('error')) {
                        FormValidator.validateField(field);
                    }
                });
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                if (!FormValidator.validate(this)) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    const firstError = this.querySelector('.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        });
    }

    // ==================== IMAGE PREVIEW ====================
    function initImagePreviews() {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        showImagePreview(input, e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }

    function showImagePreview(input, src) {
        let preview = input.parentNode.querySelector('.image-preview');
        
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'image-preview';
            preview.style.marginTop = '1rem';
            input.parentNode.appendChild(preview);
        }
        
        preview.innerHTML = `
            <div style="position: relative; display: inline-block;">
                <img src="${src}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
                <button type="button" class="btn btn-sm btn-danger" style="margin-top: 0.5rem;" onclick="this.parentElement.parentElement.remove(); this.parentElement.parentElement.previousElementSibling.value = '';">
                    Remove
                </button>
            </div>
        `;
    }

    // ==================== TABLE FEATURES ====================
    const TableManager = {
        init() {
            this.initSorting();
            this.initSearch();
            this.makeResponsive();
        },

        initSorting() {
            document.querySelectorAll('.data-table th').forEach((header, index) => {
                if (header.classList.contains('no-sort')) return;
                
                header.style.cursor = 'pointer';
                header.style.userSelect = 'none';
                header.title = 'Click to sort';
                
                header.addEventListener('click', () => {
                    this.sortTable(header, index);
                });
            });
        },

        sortTable(header, columnIndex) {
            const table = header.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const isAscending = header.dataset.sortOrder !== 'asc';
            
            // Clear other headers
            table.querySelectorAll('th').forEach(th => {
                th.dataset.sortOrder = '';
                th.innerHTML = th.textContent;
            });
            
            rows.sort((a, b) => {
                const aValue = a.cells[columnIndex]?.textContent.trim() || '';
                const bValue = b.cells[columnIndex]?.textContent.trim() || '';
                
                // Try numeric comparison
                const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
                const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return isAscending ? aNum - bNum : bNum - aNum;
                }
                
                // String comparison
                return isAscending ? 
                    aValue.localeCompare(bValue) : 
                    bValue.localeCompare(aValue);
            });
            
            rows.forEach(row => tbody.appendChild(row));
            
            // Update header
            header.dataset.sortOrder = isAscending ? 'asc' : 'desc';
            header.innerHTML = `${header.textContent} ${isAscending ? '↑' : '↓'}`;
        },

        initSearch() {
            const searchInputs = document.querySelectorAll('.search-box input, [data-table-search]');
            
            searchInputs.forEach(input => {
                let timeout;
                input.addEventListener('input', (e) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.filterTable(e.target);
                    }, config.debounceDelay);
                });
            });
        },

        filterTable(input) {
            const searchTerm = input.value.toLowerCase();
            const table = input.closest('.admin-section')?.querySelector('.data-table');
            
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const isVisible = text.includes(searchTerm);
                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCount++;
            });
            
            // Show no results message
            this.showNoResults(table, visibleCount === 0);
        },

        showNoResults(table, show) {
            let noResults = table.parentNode.querySelector('.no-results');
            
            if (show && !noResults) {
                noResults = document.createElement('div');
                noResults.className = 'no-results text-center';
                noResults.style.padding = '2rem';
                noResults.style.color = 'var(--admin-text-muted)';
                noResults.innerHTML = `
                    <svg width="48" height="48" fill="currentColor" viewBox="0 0 24 24" style="margin-bottom: 1rem; opacity: 0.5;">
                        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                    <p style="margin: 0;">No results found</p>
                `;
                table.parentNode.appendChild(noResults);
            } else if (!show && noResults) {
                noResults.remove();
            }
        },

        makeResponsive() {
            const tables = document.querySelectorAll('.data-table');
            
            const checkScroll = () => {
                tables.forEach(table => {
                    const wrapper = table.closest('.table-responsive');
                    if (!wrapper) return;
                    
                    if (table.scrollWidth > wrapper.clientWidth) {
                        wrapper.classList.add('has-scroll');
                        if (!wrapper.querySelector('.scroll-hint')) {
                            const hint = document.createElement('div');
                            hint.className = 'scroll-hint';
                            hint.style.cssText = `
                                position: absolute;
                                top: 50%;
                                right: 0;
                                transform: translateY(-50%);
                                background: linear-gradient(to left, rgba(255,255,255,0.9), transparent);
                                padding: 1rem 2rem 1rem 4rem;
                                pointer-events: none;
                                font-size: 0.875rem;
                                color: var(--admin-text-muted);
                            `;
                            hint.textContent = '→ Scroll';
                            wrapper.style.position = 'relative';
                            wrapper.appendChild(hint);
                            
                            wrapper.addEventListener('scroll', () => {
                                if (wrapper.scrollLeft > 20) {
                                    hint.style.opacity = '0';
                                } else {
                                    hint.style.opacity = '1';
                                }
                            });
                        }
                    } else {
                        wrapper.classList.remove('has-scroll');
                        wrapper.querySelector('.scroll-hint')?.remove();
                    }
                });
            };
            
            checkScroll();
            window.addEventListener('resize', checkScroll);
        }
    };

    // ==================== ALERTS ====================
    const AlertManager = {
        create(message, type = 'info') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.style.animation = 'slideInDown 0.3s ease';
            
            const icon = this.getIcon(type);
            alert.innerHTML = `
                ${icon}
                <span>${message}</span>
                <button class="alert-close" style="margin-left: auto; background: none; border: none; cursor: pointer; font-size: 1.25rem; opacity: 0.7; transition: opacity 0.2s;">&times;</button>
            `;
            
            const closeBtn = alert.querySelector('.alert-close');
            closeBtn.addEventListener('click', () => this.dismiss(alert));
            
            const container = document.querySelector('.admin-content');
            if (container) {
                container.insertBefore(alert, container.firstChild);
            }
            
            // Auto-dismiss
            setTimeout(() => this.dismiss(alert), config.alertTimeout);
            
            return alert;
        },

        dismiss(alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), config.animationDuration);
        },

        getIcon(type) {
            const icons = {
                success: '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
                error: '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>',
                warning: '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
                info: '<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>'
            };
            return icons[type] || icons.info;
        }
    };

    function initAlerts() {
        document.querySelectorAll('.alert').forEach(alert => {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = 'margin-left: auto; background: none; border: none; cursor: pointer; font-size: 1.25rem; opacity: 0.7;';
            closeBtn.addEventListener('click', () => AlertManager.dismiss(alert));
            alert.appendChild(closeBtn);
            
            setTimeout(() => AlertManager.dismiss(alert), config.alertTimeout);
        });
    }

    // ==================== CONFIRMATIONS ====================
    function initConfirmations() {
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', function(e) {
                const message = this.dataset.confirm || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }

    // ==================== UTILITIES ====================
    const Utils = {
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatDateTime(datetime) {
            return new Date(datetime).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // ==================== ACTIVE NAV INDICATOR ====================
    function setActiveNav() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.php';
        document.querySelectorAll('.nav-item').forEach(item => {
            const href = item.getAttribute('href');
            if (href && href.includes(currentPage)) {
                item.classList.add('active');
            }
        });
    }

    // ==================== INITIALIZATION ====================
    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeAll);
        } else {
            initializeAll();
        }
    }

    function initializeAll() {
        initMobileMenu();
        initModals();
        initFormValidation();
        initImagePreviews();
        TableManager.init();
        initAlerts();
        initConfirmations();
        setActiveNav();
        
        console.log('✓ Admin Panel Initialized');
    }

    // ==================== GLOBAL API ====================
    window.AdminPanel = {
        Modal: ModalManager,
        Alert: AlertManager,
        Table: TableManager,
        Utils: Utils,
        FormValidator: FormValidator
    };

    // Start
    init();

})();