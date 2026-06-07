{* User Scripts Partial *}
<script>
$(document).ready(function() {
    // Password strength validation
    $('#password').on('input', function() {
        const password = $(this).val();
        if (password.length > 0) {
            // Validate password strength
            const hasMinLength = password.length >= 8;
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*]/.test(password);
            
            // Update UI feedback
            $('.password-strength').removeClass('d-none');
            $('.length-requirement').toggleClass('text-success', hasMinLength);
            $('.number-requirement').toggleClass('text-success', hasNumber);
            $('.special-requirement').toggleClass('text-success', hasSpecial);
            
            // Enable/disable submit button
            const isValid = hasMinLength && hasNumber && hasSpecial;
            $('button[type="submit"]').prop('disabled', !isValid);
        } else {
            $('.password-strength').addClass('d-none');
            $('button[type="submit"]').prop('disabled', false);
        }
    });
    
    // Confirm password match
    $('#password_confirm').on('input', function() {
        const password = $('#password').val();
        const confirm = $(this).val();
        
        if (password.length > 0 && confirm.length > 0) {
            if (password !== confirm) {
                $(this).addClass('is-invalid');
                $('.password-match-feedback').removeClass('d-none');
                $('button[type="submit"]').prop('disabled', true);
            } else {
                $(this).removeClass('is-invalid');
                $('.password-match-feedback').addClass('d-none');
                $('button[type="submit"]').prop('disabled', false);
            }
        }
    });
});
</script>
