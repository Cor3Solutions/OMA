            </div>
            
            <footer class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> Oriental Muayboran Academy. All rights reserved.</p>
            </footer>
        </div>
    </div>
    
    <script>
        // Sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.admin-sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
            
            // Confirmation dialogs for delete actions
            document.querySelectorAll('[data-confirm]').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    const message = this.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
            
            // Image preview for file uploads
            document.querySelectorAll('input[type="file"][accept*="image"]').forEach(function(input) {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            let preview = document.getElementById('image-preview');
                            if (!preview) {
                                preview = document.createElement('img');
                                preview.id = 'image-preview';
                                preview.style.maxWidth = '200px';
                                preview.style.marginTop = '10px';
                                preview.style.borderRadius = '4px';
                                input.parentNode.appendChild(preview);
                            }
                            preview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });
            
            // Table sorting
            document.querySelectorAll('.sortable th').forEach(function(header) {
                header.addEventListener('click', function() {
                    const table = this.closest('table');
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    const index = Array.from(this.parentNode.children).indexOf(this);
                    const isAscending = this.classList.contains('asc');
                    
                    rows.sort(function(a, b) {
                        const aValue = a.children[index].textContent.trim();
                        const bValue = b.children[index].textContent.trim();
                        
                        if (isAscending) {
                            return aValue.localeCompare(bValue, undefined, {numeric: true});
                        } else {
                            return bValue.localeCompare(aValue, undefined, {numeric: true});
                        }
                    });
                    
                    rows.forEach(function(row) {
                        tbody.appendChild(row);
                    });
                    
                    // Toggle sort direction
                    table.querySelectorAll('th').forEach(function(th) {
                        th.classList.remove('asc', 'desc');
                    });
                    this.classList.add(isAscending ? 'desc' : 'asc');
                });
            });
            
            // Search/filter functionality
            const searchInput = document.querySelector('.search-box input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.data-table tbody tr');
                    
                    rows.forEach(function(row) {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>
