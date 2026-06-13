{* Plugin Generator Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Generate New Plugin{/block}

{block name="head"}
<style>
    .plugin-generator-form {
        max-width: 800px;
    }
    .form-section {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-section h5 {
        color: #495057;
        border-bottom: 2px solid #007bff;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .checkbox-item {
        flex: 1;
        min-width: 200px;
    }
    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    .preview-section {
        background: #e9ecef;
        border-radius: 0.375rem;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
    }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/plugins">Plugins</a></li>
        <li class="breadcrumb-item active">Generate Plugin</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-magic me-2"></i>Generate New Plugin</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/admin/plugins" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Plugins
        </a>
    </div>
</div>

{* Flash Messages *}
{if isset($flash) && $flash}
    <div class="alert alert-{if $flash.type == 'error'}danger{else}{$flash.type}{/if} alert-dismissible fade show" role="alert">
        {$flash.message nofilter}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="/admin/plugins/generate" class="plugin-generator-form" id="pluginGeneratorForm">
            <input type="hidden" name="csrf_token" value="{$csrf_token}">

            {* Basic Information *}
            <div class="form-section">
                <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Plugin Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   placeholder="my-awesome-plugin" pattern="[a-zA-Z0-9_-]+" 
                                   title="Only letters, numbers, hyphens, and underscores allowed">
                            <div class="help-text">Internal name (lowercase, hyphens/underscores only)</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="display_name" class="form-label">Display Name</label>
                            <input type="text" class="form-control" id="display_name" name="display_name" 
                                   placeholder="My Awesome Plugin">
                            <div class="help-text">Human-readable name (auto-generated if empty)</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="description" name="description" rows="3" required 
                              placeholder="A brief description of what your plugin does..."></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="version" class="form-label">Version <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="version" name="version" 
                                   value="1.0.0" pattern="\d+\.\d+\.\d+" required 
                                   title="Version format: X.Y.Z (e.g., 1.0.0)">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="author" name="author" required 
                                   placeholder="Your Name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <input type="number" class="form-control" id="priority" name="priority" 
                                   value="10" min="1" max="100">
                            <div class="help-text">Hook execution priority (1-100)</div>
                        </div>
                    </div>
                </div>
            </div>

            {* URLs and Links *}
            <div class="form-section">
                <h5><i class="fas fa-link me-2"></i>URLs and Links</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="author_uri" class="form-label">Author URI</label>
                            <input type="url" class="form-control" id="author_uri" name="author_uri" 
                                   placeholder="https://yourwebsite.com">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="plugin_uri" class="form-label">Plugin URI</label>
                            <input type="url" class="form-control" id="plugin_uri" name="plugin_uri" 
                                   placeholder="https://yourwebsite.com/plugins/my-plugin">
                        </div>
                    </div>
                </div>
            </div>

            {* Requirements *}
            <div class="form-section">
                <h5><i class="fas fa-cogs me-2"></i>Requirements</h5>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="requires" class="form-label">CMS Version</label>
                            <input type="text" class="form-control" id="requires" name="requires" 
                                   value="1.0.0" pattern="\d+\.\d+\.\d+">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tested_up_to" class="form-label">Tested Up To</label>
                            <input type="text" class="form-control" id="tested_up_to" name="tested_up_to" 
                                   value="1.5.0" pattern="\d+\.\d+\.\d+">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="requires_php" class="form-label">PHP Version</label>
                            <input type="text" class="form-control" id="requires_php" name="requires_php" 
                                   value="7.4.0" pattern="\d+\.\d+\.\d+">
                        </div>
                    </div>
                </div>
            </div>

            {* Dependencies *}
            <div class="form-section">
                <h5><i class="fas fa-project-diagram me-2"></i>Dependencies & Conflicts</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="depends" class="form-label">Dependencies</label>
                            <input type="text" class="form-control" id="depends" name="depends" 
                                   placeholder="plugin-1, plugin-2 >= 1.0.0">
                            <div class="help-text">Comma-separated list of required plugins</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="conflicts" class="form-label">Conflicts</label>
                            <input type="text" class="form-control" id="conflicts" name="conflicts" 
                                   placeholder="incompatible-plugin, old-plugin">
                            <div class="help-text">Comma-separated list of conflicting plugins</div>
                        </div>
                    </div>
                </div>
            </div>

            {* Features to Include *}
            <div class="form-section">
                <h5><i class="fas fa-puzzle-piece me-2"></i>Features to Include</h5>
                
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_hooks" 
                                   name="include_hooks" checked>
                            <label class="form-check-label" for="include_hooks">
                                <strong>Hooks File</strong>
                                <div class="help-text">Generate hooks.php with examples</div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="checkbox-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_settings" 
                                   name="include_settings" checked>
                            <label class="form-check-label" for="include_settings">
                                <strong>Settings Interface</strong>
                                <div class="help-text">Generate settings.php with admin form</div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="checkbox-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_assets" 
                                   name="include_assets" checked>
                            <label class="form-check-label" for="include_assets">
                                <strong>Assets Structure</strong>
                                <div class="help-text">Create CSS/JS/images folders</div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="checkbox-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_readme" 
                                   name="include_readme" checked>
                            <label class="form-check-label" for="include_readme">
                                <strong>README File</strong>
                                <div class="help-text">Generate README.md documentation</div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="checkbox-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_tests" 
                                   name="include_tests">
                            <label class="form-check-label" for="include_tests">
                                <strong>Unit Tests</strong>
                                <div class="help-text">Generate PHPUnit test files</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {* Generate Button *}
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="button" class="btn btn-outline-secondary me-md-2" onclick="previewPlugin()">
                    <i class="fas fa-eye"></i> Preview Structure
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-magic"></i> Generate Plugin
                </button>
            </div>
        </form>
    </div>

    {* Help Sidebar *}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Plugin Generator Help</h6>
            </div>
            <div class="card-body">
                <h6>Getting Started</h6>
                <ul class="small">
                    <li>Choose a unique plugin name</li>
                    <li>Provide a clear description</li>
                    <li>Select features you need</li>
                    <li>Click "Generate Plugin"</li>
                </ul>
                
                <h6>Naming Guidelines</h6>
                <ul class="small">
                    <li>Use lowercase letters only</li>
                    <li>Replace spaces with hyphens</li>
                    <li>Avoid special characters</li>
                    <li>Example: "my-contact-form"</li>
                </ul>
                
                <h6>Generated Files</h6>
                <ul class="small">
                    <li><code>plugin-name.php</code> - Main file</li>
                    <li><code>hooks.php</code> - Hook definitions</li>
                    <li><code>settings.php</code> - Admin settings</li>
                    <li><code>README.md</code> - Documentation</li>
                    <li><code>assets/</code> - CSS, JS, images</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-code me-2"></i>Code Examples</h6>
            </div>
            <div class="card-body">
                <h6>Hook Example</h6>
                <div class="preview-section">
$hookSystem->addAction('init', 
  'my_plugin_init');

function my_plugin_init() {
  // Your code here
}
                </div>
                
                <h6 class="mt-3">Filter Example</h6>
                <div class="preview-section">
$hookSystem->addFilter('the_content', 
  'my_plugin_filter');

function my_plugin_filter($content) {
  return $content . ' Modified!';
}
                </div>
            </div>
        </div>
    </div>
</div>

{* Preview Modal *}
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Plugin Structure Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Preview content will be inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="$('#pluginGeneratorForm').submit()">
                    <i class="fas fa-magic"></i> Generate This Plugin
                </button>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="scripts"}
{literal}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate display name from plugin name
    const nameInput = document.getElementById('name');
    const displayNameInput = document.getElementById('display_name');
    
    nameInput.addEventListener('input', function() {
        if (!displayNameInput.value) {
            let displayName = this.value
                .replace(/[-_]/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
            displayNameInput.value = displayName;
        }
    });
    
    // Preview plugin structure
    window.previewPlugin = function() {
        const formData = new FormData(document.getElementById('pluginGeneratorForm'));
        const pluginName = formData.get('name') || 'my-plugin';
        
        let structure = `<div class="plugin-preview">
            <h6>Plugin Directory: <code>${pluginName}/</code></h6>
            <ul class="list-unstyled ms-3">
                <li><i class="fas fa-file text-primary me-2"></i><code>${pluginName}.php</code> <span class="badge bg-primary">Main</span></li>`;
        
        if (formData.get('include_hooks')) {
            structure += `<li><i class="fas fa-file text-warning me-2"></i><code>hooks.php</code> <span class="badge bg-warning">Hooks</span></li>`;
        }
        
        if (formData.get('include_settings')) {
            structure += `<li><i class="fas fa-file text-info me-2"></i><code>settings.php</code> <span class="badge bg-info">Settings</span></li>`;
        }
        
        if (formData.get('include_readme')) {
            structure += `<li><i class="fas fa-file text-secondary me-2"></i><code>README.md</code> <span class="badge bg-secondary">Docs</span></li>`;
        }
        
        if (formData.get('include_assets')) {
            structure += `<li><i class="fas fa-folder text-success me-2"></i><code>assets/</code>
                <ul class="list-unstyled ms-3">
                    <li><i class="fas fa-folder me-2"></i><code>css/</code></li>
                    <li><i class="fas fa-folder me-2"></i><code>js/</code></li>
                    <li><i class="fas fa-folder me-2"></i><code>img/</code></li>
                </ul>
            </li>`;
        }
        
        if (formData.get('include_tests')) {
            structure += `<li><i class="fas fa-folder text-danger me-2"></i><code>tests/</code>
                <ul class="list-unstyled ms-3">
                    <li><i class="fas fa-file me-2"></i><code>${pluginName.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join('')}PluginTest.php</code></li>
                </ul>
            </li>`;
        }
        
        structure += `</ul></div>`;
        
        document.getElementById('previewContent').innerHTML = structure;
        
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    };
});
</script>
{/literal}
{/block}