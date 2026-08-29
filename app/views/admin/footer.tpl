    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; {$current_year|default:"2025"} {$site_name|escape}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0 text-muted">Powered by <a href="#" class="text-decoration-none">swCMS</a> v{$system_info.version|default:"1.0.0"}</p>
                </div>
            </div>
        </div>
    </footer>

    {* Bootstrap, jQuery, DataTables and admin.js are loaded from public/vendor/
       by layout.tpl, which includes this file: no CDN copies, no duplicates. *}

    <!-- Initialize TinyMCE if needed -->
    {if isset($use_tinymce) && $use_tinymce}
    <script>
        tinymce.init({
            selector: 'textarea.tinymce',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            height: 400
        });
    </script>
    {/if}

