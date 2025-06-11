jQuery(function($) {
    console.log('WPForms Email OTP Pro admin script loaded.');

    // Toggle SMTP fields visibility
    function toggleSmtpFields() {
        if (!$('#wpforms-setting-smtp-enabled').length) {
            return;
        }
        const enabled = $('#wpforms-setting-smtp-enabled').is(':checked');
        $('.wpforms-setting-row-sender-email, .wpforms-setting-row-app-password').toggle(enabled);
    }

    // Initial toggle
    toggleSmtpFields();

    // Bind change event
    $(document).on('change', '#wpforms-setting-smtp-enabled', toggleSmtpFields);
});