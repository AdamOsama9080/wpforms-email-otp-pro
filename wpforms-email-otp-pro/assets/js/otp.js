jQuery(function($) {
    // Disable submit buttons on forms with OTP
    $('form.wpforms-form').each(function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // Send OTP
    $(document).on('click', '.send-otp-btn', function() {
        const $btn = $(this);
        const $form = $btn.closest('form');
        const email = $form.find('input[type="email"]').val();

        if (!email) {
            alert(wpformsOtpPro.i18n.invalid_email);
            return;
        }

        $btn.prop('disabled', true).text(wpformsOtpPro.i18n.sending);

        $.post(wpformsOtpPro.ajaxurl, {
            action: 'send_wpforms_otp',
            email: email,
            nonce: wpformsOtpPro.nonce
        })
        .done(function(response) {
            if (response.success) {
                alert(wpformsOtpPro.i18n.otp_sent);
                $form.find('.wpforms-otp-field').show();
            } else {
                alert(response.data.message || wpformsOtpPro.i18n.failed);
            }
        })
        .fail(function() {
            alert(wpformsOtpPro.i18n.network_error);
        })
        .always(function() {
            $btn.prop('disabled', false).text(wpformsOtpPro.i18n.send_otp);
        });
    });

    // Check OTP
    $(document).on('click', '.check-otp-btn', function() {
        const $btn = $(this);
        const $form = $btn.closest('form');
        const email = $form.find('input[type="email"]').val();
        const otp = $form.find('.wpforms-otp-input').val();

        if (!otp) {
            alert(wpformsOtpPro.i18n.invalid_otp);
            return;
        }

        $btn.prop('disabled', true).text(wpformsOtpPro.i18n.checking);

        $.post(wpformsOtpPro.ajaxurl, {
            action: 'check_wpforms_otp',
            email: email,
            otp: otp,
            nonce: wpformsOtpPro.nonce
        })
        .done(function(response) {
            const $status = $form.find('.wpforms-otp-status');
            $status.removeClass('error success').empty();

            if (response.success) {
                $status.addClass('success').text(wpformsOtpPro.i18n.verified);
                $form.find('.wpforms-otp-field').hide();
                $form.find('button[type="submit"]').prop('disabled', false);
            } else {
                $status.addClass('error').text(response.data.message || wpformsOtpPro.i18n.invalid);
                $btn.prop('disabled', false).text(wpformsOtpPro.i18n.verify);
            }
        })
        .fail(function() {
            alert(wpformsOtpPro.i18n.network_error);
            $btn.prop('disabled', false).text(wpformsOtpPro.i18n.verify);
        });
    });
});