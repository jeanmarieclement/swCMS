{* Edit Article Template - Simple Version *}
{extends file="admin/layout.tpl"}

{block name="title"}Edit Simple Article{/block}

{block name="head"}
<!-- TinyMCE CSS -->
{if isset($tinymce_include)}
{$tinymce_include}
{/if}
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/articles">Articles</a></li>
        <li class="breadcrumb-item active">Edit Article</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">{if isset($article.id)}Edit Article{else}New Article{/if}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="previewBtn">
                <i class="fas fa-eye"></i> Preview
            </button>
        </div>
    </div>
</div>

{* Display success/error messages *}
{if isset($saved) && $saved}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Article saved successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

{if isset($error)}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {$error}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<div class="row">
    <div class="col-lg-9">
        <!-- Main Content Form -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{$admin_url}/articles/{if isset($article.id)}edit?id={$article.id}{else}create{/if}" method="post" id="articleForm">
                    <input type="hidden" name="id" value="{$article.id|default:''}">
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="{$article.title|default:''}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (URL)</label>
                        <div class="input-group">
                            <span class="input-group-text">{$site_url}/</span>
                            <input type="text" class="form-control" id="slug" name="slug" value="{$article.slug|default:''}" aria-describedby="slugHelp">
                        </div>
                        <div id="slugHelp" class="form-text">Leave empty to generate automatically from title.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" aria-describedby="excerptHelp">{$article.excerpt|default:''}</textarea>
                        <div id="excerptHelp" class="form-text">A short summary of the article. If left empty, it will be generated from the content.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="content" class="form-label">Content</label>
                        {if isset($editor_html)}
                            {$editor_html}
                        {else}
                            <textarea class="form-control tinymce-editor" id="content" name="content" rows="15">{$article.content|default:''}</textarea>
                        {/if}
                    </div>
                    
                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="featured_image" name="featured_image" value="{$article.featured_image|default:''}" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="selectImageBtn">Select Image</button>
                        </div>
                        {if isset($article.featured_image) && $article.featured_image}
                            <div class="mt-2">
                                <img src="{$article.featured_image}" alt="Featured Image" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        {/if}
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <!-- Publishing Options Widget -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Publishing</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" form="articleForm">
                        <option value="draft" {if isset($article.status) && $article.status == 'draft'}selected{/if}>Draft</option>
                        <option value="published" {if isset($article.status) && $article.status == 'published'}selected{/if}>Published</option>
                        <option value="trash" {if isset($article.status) && $article.status == 'trash'}selected{/if}>Trash</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="comment_status" class="form-label">Comments</label>
                    <select class="form-select" id="comment_status" name="comment_status" form="articleForm">
                        <option value="open" {if isset($article.comment_status) && $article.comment_status == 'open'}selected{/if}>Allow comments</option>
                        <option value="closed" {if isset($article.comment_status) && $article.comment_status == 'closed'}selected{/if}>Disable comments</option>
                    </select>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" form="articleForm">Save</button>
                    {if !isset($article.status) || $article.status != 'published'}
                    <button type="submit" class="btn btn-success" name="publish" value="1" form="articleForm">Publish</button>
                    {/if}
                </div>
            </div>
        </div>
        
        <!-- Categories Widget -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
            </div>
            <div class="card-body">
                {if isset($categories) && $categories}
                    <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                        {foreach $categories as $category}
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="{$category.id}" id="category{$category.id}" 
                                    name="categories[]" form="articleForm"
                                    {if isset($article_categories) && in_array($category.id, $article_categories)}checked{/if}>
                                <label class="form-check-label" for="category{$category.id}">
                                    {$category.name}
                                </label>
                            </div>
                        {/foreach}
                    </div>
                {else}
                    <p class="text-muted">No categories found.</p>
                {/if}
                
                <div class="d-grid">
                    <a href="{$admin_url}/categories" class="btn btn-outline-secondary btn-sm">Manage Categories</a>
                </div>
            </div>
        </div>
        
        <!-- Tags Widget -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tags</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="tags" name="tags" value="{$tags|default:''}" 
                        placeholder="Enter tags separated by commas" form="articleForm">
                    <div class="form-text">Separate tags with commas.</div>
                </div>
                
                <div class="d-grid">
                    <a href="{$admin_url}/tags" class="btn btn-outline-secondary btn-sm">Manage Tags</a>
                </div>
            </div>
        </div>
        
        <!-- Article Information Widget -->
        {if isset($article.id) && isset($article.created_at)}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Article Information</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Created
                        <span>{$article.created_at|date_format:"%b %e, %Y at %H:%M"}</span>
                    </li>
                    {if isset($article.updated_at)}
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Last Updated
                        <span>{$article.updated_at|date_format:"%b %e, %Y at %H:%M"}</span>
                    </li>
                    {/if}
                    {if isset($article.published_at) && $article.status == 'published'}
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Published
                        <span>{$article.published_at|date_format:"%b %e, %Y at %H:%M"}</span>
                    </li>
                    {/if}
                </ul>
            </div>
        </div>
        {/if}
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Article Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <h1 id="previewTitle"></h1>
                            <div id="previewContent"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Media Library Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaModalLabel">Media Library</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="mediaSearch" placeholder="Search media...">
                        </div>
                        <div id="mediaList" class="row g-3">
                            <!-- Media items will be loaded here -->
                            <div class="text-center p-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading media...</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Upload New Media</div>
                            <div class="card-body">
                                <form id="uploadForm" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="mediaFile" class="form-label">Select File</label>
                                        <input class="form-control" type="file" id="mediaFile" name="file">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Upload</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="selectMediaBtn" disabled>Select</button>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="scripts"}
{if !isset($tinymce_include)}
<!-- TinyMCE -->
<script src="{$site_url}/vendor/tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script src="{$site_url}/js/tinymce-init.js"></script>
{/if}
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
            fetch(`${admin_url}/media/api/list`)
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        let html = '';
                        data.items.forEach(item => {
                            html += `
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <img src="${item.url}" class="card-img-top" alt="${item.name}">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input media-select" type="radio" name="media" value="${item.url}" id="media${item.id}">
                                                <label class="form-check-label" for="media${item.id}">${item.name}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
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
                    
                    previewContainer.innerHTML = `<img src="${selectedMedia.value}" alt="Featured Image" class="img-thumbnail" style="max-height: 150px;">`;
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
                
                fetch(`${admin_url}/media/api/upload`, {
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
    });
    
    // Make admin_url available to JavaScript
    const admin_url = '{$admin_url}';
</script>
{/block}
