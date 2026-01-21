</main>
    
    <footer class="user-footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Oriental Muayboran Academy. All rights reserved.</p>
            <div class="footer-links">
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
                <span>|</span>
                <a href="<?php echo SITE_URL; ?>/pages/contact.php">Contact</a>
            </div>
        </div>
    </footer>
    
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileNav = document.getElementById('mobileNav');
        const mobileOverlay = document.getElementById('mobileOverlay');
        
        if (mobileMenuBtn && mobileNav && mobileOverlay) {
            mobileMenuBtn.addEventListener('click', function() {
                this.classList.toggle('active');
                mobileNav.classList.toggle('active');
                mobileOverlay.classList.toggle('active');
            });
            
            mobileOverlay.addEventListener('click', function() {
                mobileMenuBtn.classList.remove('active');
                mobileNav.classList.remove('active');
                this.classList.remove('active');
            });
        }
        
        // User dropdown toggle
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                this.classList.toggle('active');
                userDropdown.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    userMenuBtn.classList.remove('active');
                    userDropdown.classList.remove('active');
                }
            });
        }
        
        // Auto-hide alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });
        
        // Smooth scroll for anchor links
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
        
        // Add loading state to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                }
            });
        });
        
        // Form validation feedback
        document.querySelectorAll('.form-input, .form-textarea').forEach(input => {
            input.addEventListener('invalid', function(e) {
                e.preventDefault();
                this.classList.add('error');
            });
            
            input.addEventListener('input', function() {
                this.classList.remove('error');
            });
        });
    </script>
    
    <style>
        .user-footer {
            background: white;
            border-top: 1px solid var(--border);
            margin-top: 4rem;
            padding: 2rem 1rem;
        }
        
        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .footer-container p {
            color: var(--text-light);
            font-size: 0.875rem;
        }
        
        .footer-links {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .footer-links a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 0.875rem;
            transition: var(--transition);
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .footer-links span {
            color: var(--border);
        }
        
        .form-input.error,
        .form-textarea.error {
            border-color: var(--danger);
            animation: shake 0.3s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        @media (max-width: 768px) {
            .footer-container {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>