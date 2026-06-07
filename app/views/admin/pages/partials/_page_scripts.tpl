<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Define site URL from Smarty variable
        var siteUrl = '{$site_url}';
        var adminUrl = siteUrl + '/admin';
        
        // Form validation
        (function() {
            'use strict';
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation');
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Auto-generate slug from title
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        
        if (titleInput && slugInput) {
            titleInput.addEventListener('blur', function() {
                if (slugInput.value === '') {
                    const titleValue = titleInput.value.trim();
                    if (titleValue) {
                        // Convert to lowercase, replace spaces with hyphens, remove special chars
                        const slug = titleValue
                            .toLowerCase()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/\s+/g, '-')
                            .replace(/-+/g, '-');
                        
                        slugInput.value = slug;
                    }
                }
            });
        }
        
        // Preview functionality
        const previewBtn = document.getElementById('previewBtn');
        if (previewBtn) {
            previewBtn.addEventListener('click', function() {
                const title = document.getElementById('title').value || 'Untitled';
                let content = '';
                
                // Get content from TinyMCE if it's initialized
                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                    content = tinymce.get('content').getContent();
                } else {
                    content = document.getElementById('content').value || '';
                }
                
                // Update preview modal
                document.getElementById('previewTitle').textContent = title;
                document.getElementById('previewContent').innerHTML = content;
                
                // Show the modal
                const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
                previewModal.show();
            });
        }
        
        // Parent page selection validation
        const parentSelect = document.getElementById('parent_id');
        const pageIdInput = document.querySelector('input[name="id"]');
        
        if (parentSelect && pageIdInput) {
            const pageId = pageIdInput.value;
            
            parentSelect.addEventListener('change', function() {
                const selectedParentId = this.value;
                
                // Prevent selecting the page itself as parent
                if (selectedParentId === pageId) {
                    alert('A page cannot be its own parent.');
                    this.value = '0'; // Reset to "None"
                }
            });
        }
    });
</script>
