/**
 * TinyMCE initialization script
 * This file initializes TinyMCE on textareas with the class 'tinymce-editor'
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if TinyMCE is loaded
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: 'textarea.tinymce-editor',
            height: 400,
            promotion: false,
            menubar: true,
            readonly: false,
            license_key: 'gpl',
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            setup: function(editor) {
                editor.on('init', function() {
                    // Force standards mode
                    const doc = editor.getDoc();
                    if (doc && doc.compatMode !== 'CSS1Compat') {
                        console.warn('TinyMCE: Document not in standards mode. Adding DOCTYPE.');
                        const doctype = document.implementation.createDocumentType('html', '', '');
                        doc.insertBefore(doctype, doc.childNodes[0]);
                    }
                });
            }
        });
    } else {
        console.warn('TinyMCE is not loaded. Please check the path to the TinyMCE library.');
        
        // Add a message to textareas that would use TinyMCE
        document.querySelectorAll('textarea.tinymce-editor').forEach(function(textarea) {
            const message = document.createElement('div');
            message.className = 'alert alert-warning';
            message.innerHTML = 'TinyMCE editor is not available. Please check the TinyMCE library path.';
            textarea.parentNode.insertBefore(message, textarea);
        });
    }
});
