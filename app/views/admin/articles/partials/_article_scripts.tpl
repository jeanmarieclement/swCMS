<script>
// Define admin URL from site_url
let adminUrl = '{$admin_url}';
let defaultImage = '<svg class="svg-inline--fa fa-file fa-3x text-muted" aria-hidden="true" focusable="false"'+
    'data-prefix="fas" data-icon="file" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"'+
    'data-fa-i2svg=""><path fill="currentColor"'+
        'd="M0 64C0 28.7 28.7 0 64 0L224 0l0 128c0 17.7 14.3 32 32 32l128 0 0 288c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64zm384 64l-128 0L256 0 384 128z">'+
    '</path></svg>';
</script>
{literal}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Add Category Modal Logic ---
        const categoryNameInput = document.getElementById('categoryName');
        const categorySlugInput = document.getElementById('categorySlug');
        const categoryDescriptionInput = document.getElementById('categoryDescription');
        const addCategoryForm = document.getElementById('addCategoryForm');
        const addCategoryError = document.getElementById('addCategoryError');
        const saveCategoryBtn = document.getElementById('saveCategoryBtn');
        const addCategoryModal = document.getElementById('addCategoryModal');

        // Auto-generate slug from name
        if (categoryNameInput && categorySlugInput) {
            categoryNameInput.addEventListener('input', function() {
                let slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');
                categorySlugInput.value = slug;
            });
        }

        // Handle form submission via AJAX
        if (addCategoryForm) {
            addCategoryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                addCategoryError.classList.add('d-none');
                saveCategoryBtn.disabled = true;
                const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
                fetch(adminUrl + '/categories/ajax_create', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken},
                    body: JSON.stringify({
                        name: categoryNameInput.value,
                        slug: categorySlugInput.value,
                        description: categoryDescriptionInput.value
                    })
                })
                .then(res => res.json())
                .then(data => {
                    saveCategoryBtn.disabled = false;
                    if (data.success) {
                        // Add new category to the list and select it
                        const catList = document.querySelector('.card-body .form-check');
                        if (catList) {
                            const div = document.createElement('div');
                            div.className = 'form-check';
                            div.innerHTML = `<input class="form-check-input" type="checkbox" name="categories[]" value="${data.category.id}" id="category${data.category.id}" checked form="articleForm">
                                <label class="form-check-label" for="category${data.category.id}">${data.category.name}</label>`;
                            catList.appendChild(div);
                        }
                        // Reset and close modal
                        addCategoryForm.reset();
                        const modal = bootstrap.Modal.getOrCreateInstance(addCategoryModal);
                        modal.hide();
                    } else {
                        addCategoryError.textContent = data.error || 'Errore durante la creazione.';
                        addCategoryError.classList.remove('d-none');
                    }
                })
                .catch(() => {
                    saveCategoryBtn.disabled = false;
                    addCategoryError.textContent = 'Errore durante la richiesta.';
                    addCategoryError.classList.remove('d-none');
                });
            });
        }
        // --- End Add Category Modal Logic ---
    
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
        
        // Media Library functionality
        const selectImageBtn = document.getElementById('selectImageBtn');
        const featuredImageInput = document.getElementById('featured_image');
        
        if (selectImageBtn) {
            selectImageBtn.addEventListener('click', function() {
                // Load media items here (AJAX call to get media)
                loadMediaItems();
                
                // Show the modal
                const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));
                mediaModal.show();
            });
        }
        
        // Function to load media items
        function loadMediaItems() {
            const mediaList = document.getElementById('mediaList');
            
            // Here you would typically make an AJAX call to get media items
            fetch(adminUrl + '/media/api/list')
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        let html = '';
                        data.items.forEach(item => {
                            console.log(item);
                            let url = '/uploads/media' + item.filepath + 'thumbs/' + item.filename;
                            if (item.filetype  == 'image/svg') {
                                url = '/uploads/media' + item.filepath + item.filename;
                            }
                            let img = '';
                              if (item.filetype.substring(0, 6)  == 'image/') {                           6
                              img = '<img src="' + url + '" class="card-img-top" alt="' + item.title + '">';
                              } else {
                              img = defaultImage;
                              }
                            html += '<div class="col-md-4 mb-3">' +
                                '<div class="card h-100">' +
                                img +
                                '<div class="card-body">' +
                                '<div class="form-check">' +
                                '<input class="form-check-input media-select" type="radio" name="media" value="' + url + '" id="media' + item.id + '">' +
                                '<label class="form-check-label" for="media' + item.id + '">' + item.title + '</label>' +
                                '</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                        });
                        mediaList.innerHTML = html;
                        
                        // Add event listeners to radio buttons
                        document.querySelectorAll('.media-select').forEach(radio => {
                            radio.addEventListener('change', function() {
                                document.getElementById('selectMediaBtn').disabled = false;
                            });
                        });
                    } else {
                        mediaList.innerHTML = '<div class="col-12"><p class="text-center">No media items found.</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading media:', error);
                    mediaList.innerHTML = '<div class="col-12"><p class="text-center text-danger">Error loading media items.</p></div>';
                });
        }
        
        // Media selection
        const selectMediaBtn = document.getElementById('selectMediaBtn');
        if (selectMediaBtn) {
            selectMediaBtn.addEventListener('click', function() {
                const selectedMedia = document.querySelector('.media-select:checked');
                if (selectedMedia) {
                    featuredImageInput.value = selectedMedia.value;
                    
                    // Add preview image
                    const previewContainer = featuredImageInput.closest('.mb-3').querySelector('.mt-2') || 
                                           document.createElement('div');
                    
                    if (!previewContainer.classList.contains('mt-2')) {
                        previewContainer.classList.add('mt-2');
                        featuredImageInput.closest('.mb-3').appendChild(previewContainer);
                    }
                    
                    previewContainer.innerHTML = '<img src="' + selectedMedia.value + '" alt="Featured Image" class="img-thumbnail" style="max-height: 150px;">';
                }
                
                // Close the modal
                const mediaModal = bootstrap.Modal.getInstance(document.getElementById('mediaModal'));
                mediaModal.hide();
            });
        }
        
        // Media upload form
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const uploadStatus = document.createElement('div');
                uploadStatus.className = 'alert alert-info mt-2';
                uploadStatus.textContent = 'Uploading...';
                this.appendChild(uploadStatus);
                
                fetch(adminUrl + '/media/api/upload', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        uploadStatus.className = 'alert alert-success mt-2';
                        uploadStatus.textContent = 'Upload successful!';
                        // Reload media items
                        loadMediaItems();
                        // Reset form
                        uploadForm.reset();
                    } else {
                        uploadStatus.className = 'alert alert-danger mt-2';
                        uploadStatus.textContent = data.message || 'Upload failed.';
                    }
                    
                    // Remove status after 3 seconds
                    setTimeout(() => {
                        uploadStatus.remove();
                    }, 3000);
                })
                .catch(error => {
                    console.error('Error uploading file:', error);
                    uploadStatus.className = 'alert alert-danger mt-2';
                    uploadStatus.textContent = 'Upload failed. Please try again.';
                    
                    // Remove status after 3 seconds
                    setTimeout(() => {
                        uploadStatus.remove();
                    }, 3000);
                });
            });
        }
        
        // Status change handler for publish button visibility
        const statusSelect = document.querySelector('select[name="status"]');
        const publishBtn = document.querySelector('button[name="publish"]');
        
        if (statusSelect && publishBtn) {
            statusSelect.addEventListener('change', function() {
                publishBtn.style.display = this.value === 'published' ? 'none' : 'block';
            });
        }
        
        // Initialize Select2 for tags widget
        if (document.getElementById('tags')) {
            $('#tags').select2({
                tags: true, // allow new tag creation
                placeholder: 'Add or select tags',
                minimumInputLength: 2,
                ajax: {
                    url: adminUrl + '/tags/ajax-list', // <-- implement this endpoint in backend
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        // expects [{text: 'tag'}]
                        return { results: data };
                    },
                    cache: true
                },
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term === '') return null;
                    return {
                        id: term,
                        text: term,
                        newTag: true // add additional parameters
                    };
                }
            });

            // Optional: handle creation of new tags via AJAX
            $('#tags').on('select2:select', function (e) {
                var data = e.params.data;
                if (data.newTag) {
                    $.ajax({
                        type: 'POST',
                        url: adminUrl + '/tags/ajax-create',
                        data: {
                            name: data.text,
                            csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
                        },
                        success: function(response) {
                            // Optionally show a notification or update UI
                        },
                        error: function() {
                            alert('Error creating new tag');
                        }
                    });
                }
            });
        }
    });
</script>
{/literal}
