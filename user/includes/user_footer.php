</main>
    
    <footer class="user-footer">
        <div class="footer-container">
            <div class="footer-content">
                <p class="footer-copyright">
                    <i class="fas fa-copyright"></i> <?php echo date('Y'); ?> Oriental Muayboran Academy. All rights reserved.
                </p>
                <div class="footer-links">
                    <a href="<?php echo SITE_URL; ?>/index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <span class="separator">•</span>
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                    <span class="separator">•</span>
                    <a href="<?php echo SITE_URL; ?>/pages/privacy.php">
                        <i class="fas fa-shield-alt"></i> Privacy
                    </a>
                </div>
            </div>
            <div class="footer-badge">
                <i class="fas fa-lock"></i> Secure Portal
            </div>
        </div>
    </footer>

    <script>
        // Auto-hide alerts with smooth animation
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(function() {
                        alert.remove();
                    }, 400);
                }, 5000);
                
                // Allow manual close
                const closeBtn = alert.querySelector('.alert-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-20px)';
                        setTimeout(function() {
                            alert.remove();
                        }, 400);
                    });
                }
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href !== '#' && href.length > 1) {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });

            // Add loading state to forms
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.disabled = true;
                        const originalHTML = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        
                        // Re-enable after 5 seconds as fallback
                        setTimeout(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalHTML;
                        }, 5000);
                    }
                });
            });

            // Form validation feedback
            document.querySelectorAll('.form-input, .form-textarea, .form-select').forEach(input => {
                input.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    this.classList.add('error');
                    
                    // Show error message
                    const errorMsg = this.getAttribute('data-error') || 'This field is required';
                    const errorElement = document.createElement('span');
                    errorElement.className = 'field-error';
                    errorElement.textContent = errorMsg;
                    
                    if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('field-error')) {
                        this.parentNode.insertBefore(errorElement, this.nextSibling);
                    }
                });
                
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    const errorElement = this.nextElementSibling;
                    if (errorElement && errorElement.classList.contains('field-error')) {
                        errorElement.remove();
                    }
                });
            });

            // Lazy load images
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                                observer.unobserve(img);
                            }
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }

            // Tooltips (simple implementation)
            document.querySelectorAll('[data-tooltip]').forEach(element => {
                element.addEventListener('mouseenter', function() {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = this.getAttribute('data-tooltip');
                    document.body.appendChild(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                    
                    setTimeout(() => tooltip.classList.add('show'), 10);
                });
                
                element.addEventListener('mouseleave', function() {
                    const tooltip = document.querySelector('.tooltip');
                    if (tooltip) {
                        tooltip.classList.remove('show');
                        setTimeout(() => tooltip.remove(), 200);
                    }
                });
            });
        });
    </script>

    <style>
        /* Security Alert Styles */
        .security-alert {
            position: fixed;
            top: 80px;
            right: -400px;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 10000;
            transition: right 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            max-width: 350px;
        }

        .security-alert.show {
            right: 20px;
        }

        .security-alert i {
            font-size: 1.5rem;
        }

        .security-alert span {
            font-weight: 500;
            font-size: 0.9375rem;
        }

        /* Footer Styles */
        .user-footer {
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            border-top: 1px solid var(--border);
            margin-top: 4rem;
            padding: 2.5rem 1rem;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .footer-copyright {
            color: var(--text-light);
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-links {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--text);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--primary);
            transform: translateY(-2px);
        }

        .footer-links .separator {
            color: var(--border);
            font-weight: 300;
        }

        .footer-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 50px;
            font-size: 0.8125rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.2);
        }

        /* Form Error Styles */
        .form-input.error,
        .form-textarea.error,
        .form-select.error {
            border-color: var(--danger);
            animation: shake 0.4s cubic-bezier(.36,.07,.19,.97);
        }

        .field-error {
            display: block;
            color: var(--danger);
            font-size: 0.8125rem;
            margin-top: 0.375rem;
            font-weight: 500;
        }

        @keyframes shake {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-6px); }
            40%, 60% { transform: translateX(6px); }
        }

        /* Tooltip Styles */
        .tooltip {
            position: fixed;
            background: rgba(30, 30, 45, 0.95);
            color: white;
            padding: 0.5rem 0.875rem;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
            pointer-events: none;
            z-index: 10001;
            opacity: 0;
            transform: translateY(5px);
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .tooltip.show {
            opacity: 1;
            transform: translateY(0);
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: rgba(30, 30, 45, 0.95);
        }

        /* Responsive Footer */
        @media (max-width: 768px) {
            .user-footer {
                padding: 2rem 1rem;
            }

            .footer-container {
                flex-direction: column;
                text-align: center;
            }

            .footer-content {
                align-items: center;
            }

            .footer-links {
                justify-content: center;
            }

            .security-alert {
                right: -350px;
                max-width: 90%;
            }

            .security-alert.show {
                right: 5%;
            }
        }

        @media (max-width: 480px) {
            .footer-links {
                flex-direction: column;
                gap: 0.5rem;
            }

            .footer-links .separator {
                display: none;
            }
        }
    </style>
</body>
</html>