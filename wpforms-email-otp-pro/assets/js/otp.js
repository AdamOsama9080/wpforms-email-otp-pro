jQuery(function($) {
    var wpformsOtp = {
        init: function() {
            $(document).on('click', '.wpforms-otp-btn.send-otp-btn', this.sendOtp);
            $(document).on('click', '.wpforms-otp-btn.check-otp-btn', this.checkOtp);
        },

        sendOtp: function(e) {
            e.preventDefault();
            var $btn = $(this),
                $container = $btn.closest('.wpforms-field-otp'),
                $form = $container.closest('form.wpforms-form'),
                $emailField = $form.find('.wpforms-field-email input[type="email"]'),
                email = $emailField.val();

            if (!email || !/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/.test(email)) {
                $container.find('.wpforms-otp-status').text(wpformsOtpPro.i18n.invalid_email).css('color', 'red');
                return;
            }

            $btn.prop('disabled', true).text(wpformsOtpPro.i18n.sending);

            $.ajax({
                url: wpformsOtpPro.ajaxurl,
                type: 'POST',
                data: {
                    action: 'send_wpforms_otp',
                    email: email,
                    nonce: wpformsOtpPro.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $container.find('.wpforms-otp-status').text(response.data.message).css('color', 'green');
                        $container.find('.wpforms-otp-field').show();
                        $btn.hide();
                    } else {
                        $container.find('.wpforms-otp-status').text(response.data.message).css('color', 'red');
                    }
                },
                error: function() {
                    $container.find('.wpforms-otp-status').text(wpformsOtpPro.i18n.network_error).css('color', 'red');
                },
                complete: function() {
                    $btn.prop('disabled', false).text(wpformsOtpPro.i18n.send_otp);
                }
            });
        },

        checkOtp: function(e) {
            e.preventDefault();
            var $btn = $(this),
                $container = $btn.closest('.wpforms-field-otp'),
                $form = $container.closest('form.wpforms-form'),
                $otpInput = $container.find('.wpforms-otp-input'),
                $emailField = $form.find('.wpforms-field-email input[type="email"]'),
                email = $emailField.val(),
                otp = $otpInput.val();

            if (!otp) {
                $container.find('.wpforms-otp-status').text(wpformsOtpPro.i18n.invalid_otp).css('color', 'red');
                return;
            }

            $btn.prop('disabled', true).text(wpformsOtpPro.i18n.checking);

            $.ajax({
                url: wpformsOtpPro.ajaxurl,
                type: 'POST',
                data: {
                    action: 'check_wpforms_otp',
                    email: email,
                    otp: otp,
                    nonce: wpformsOtpPro.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $container.find('.wpforms-otp-status').text(response.data.message).css('color', 'green');
                        $container.find('.wpforms-otp-hidden').val(otp); // Store OTP
                        $form.find('.wpforms-submit').prop('disabled', false);
                        // Remove OTP field and Send OTP button
                        $container.remove();
                    } else {
                        $container.find('.wpforms-otp-status').text(response.data.message).css('color', 'red');
                    }
                },
                error: function() {
                    $container.find('.wpforms-otp-status').text(wpformsOtpPro.i18n.network_error).css('color', 'red');
                },
                complete: function() {
                    $btn.prop('disabled', false).text(wpformsOtpPro.i18n.verify);
                }
            });
        }
    };

    wpformsOtp.init();
});