<?php
namespace WPForms_Email_OTP_Pro;

// Add top-level admin menu
add_action('admin_menu', function() {
    add_menu_page(
        __('Email OTP Settings', 'wpforms-email-otp-pro'),
        __('Email OTP Settings', 'wpforms-email-otp-pro'),
        'manage_options',
        'wpforms-email-otp-pro',
        'WPForms_Email_OTP_Pro\wpforms_email_otp_pro_settings_page',
        'dashicons-email-alt',
        25
    );
});

// Register settings
add_action('admin_init', function() {
    register_setting('wpforms_email_otp_pro_settings', 'wpforms_email_otp_pro_settings', function($input) {
        $sanitized = [];
        $sanitized['sender_email'] = sanitize_email($input['sender_email'] ?? '');
        $sanitized['app_password'] = sanitize_text_field($input['app_password'] ?? '');
        $sanitized['forms'] = [];
        if (!empty($input['forms']) && is_array($input['forms'])) {
            foreach ($input['forms'] as $form) {
                if (!empty($form['form_id']) && !empty($form['email_label'])) {
                    $sanitized['forms'][] = [
                        'form_id' => absint($form['form_id']),
                        'email_label' => sanitize_text_field($form['email_label']),
                    ];
                }
            }
        }
        $sanitized['elementor_template_id'] = absint($input['elementor_template_id'] ?? 0);
        $sanitized['otp_length'] = in_array($input['otp_length'], ['4', '6', '8']) ? $input['otp_length'] : '6';
        $sanitized['otp_expiry'] = absint($input['otp_expiry'] ?? 5);
        $sanitized['email_subject'] = sanitize_text_field($input['email_subject'] ?? '');
        $sanitized['email_message'] = wp_kses_post($input['email_message'] ?? '');
        $sanitized['smtp_enabled'] = isset($input['smtp_enabled']) ? 1 : 0;
        return $sanitized;
    });

    add_settings_section('wpforms_email_otp_pro_main', '', null, 'wpforms-email-otp-pro');

    add_settings_field('sender_email', __('Sender Gmail Address', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="email" name="wpforms_email_otp_pro_settings[sender_email]" value="' . esc_attr($options['sender_email'] ?? '') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('app_password', __('Gmail App Password', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="password" name="wpforms_email_otp_pro_settings[app_password]" value="' . esc_attr($options['app_password'] ?? '') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('smtp_enabled', __('Enable SMTP', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        $checked = isset($options['smtp_enabled']) && $options['smtp_enabled'] ? 'checked' : '';
        echo '<input type="checkbox" name="wpforms_email_otp_pro_settings[smtp_enabled]" id="wpforms-setting-smtp-enabled" value="1" ' . $checked . '>';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('forms', __('WPForms Forms and Email Fields', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        $selected_forms = !empty($options['forms']) ? $options['forms'] : [['form_id' => '', 'email_label' => '']];
        
        // Get all WPForms forms
        $forms = wpforms()->form->get();
        ?>
        <div id="wpforms-otp-forms-repeater">
            <?php foreach ($selected_forms as $index => $form_config) : ?>
                <div class="wpforms-otp-form-row" style="margin-bottom: 15px;">
                    <select name="wpforms_email_otp_pro_settings[forms][<?php echo $index; ?>][form_id]" class="regular-text">
                        <option value=""><?php _e('Select a Form', 'wpforms-email-otp-pro'); ?></option>
                        <?php
                        if (!empty($forms)) {
                            foreach ($forms as $form) {
                                $form_id = absint($form->ID);
                                $form_title = esc_html($form->post_title);
                                echo '<option value="' . $form_id . '"' . selected($form_config['form_id'], $form_id, false) . '>' . $form_title . ' (ID: ' . $form_id . ')</option>';
                            }
                        }
                        ?>
                    </select>
                    <input type="text" name="wpforms_email_otp_pro_settings[forms][<?php echo $index; ?>][email_label]" value="<?php echo esc_attr($form_config['email_label'] ?? ''); ?>" placeholder="<?php _e('Email Field Label', 'wpforms-email-otp-pro'); ?>" class="regular-text" style="margin-left: 10px;">
                    <button type="button" class="button wpforms-otp-remove-form" style="margin-left: 10px;"><?php _e('Remove', 'wpforms-email-otp-pro'); ?></button>
                </div>
            <?php endforeach; ?>
            <button type="button" class="button wpforms-otp-add-form"><?php _e('Add Form', 'wpforms-email-otp-pro'); ?></button>
        </div>
        <p class="description"><?php _e('Add multiple WPForms forms and specify their email field labels for OTP verification.', 'wpforms-email-otp-pro'); ?></p>
        <script>
            jQuery(function($) {
                // Add new form row
                $('.wpforms-otp-add-form').on('click', function() {
                    var index = $('.wpforms-otp-form-row').length;
                    var html = '<div class="wpforms-otp-form-row" style="margin-bottom: 15px;">' +
                        '<select name="wpforms_email_otp_pro_settings[forms][' + index + '][form_id]" class="regular-text">' +
                        '<option value=""><?php _e('Select a Form', 'wpforms-email-otp-pro'); ?></option>' +
                        '<?php
                        if (!empty($forms)) {
                            foreach ($forms as $form) {
                                $form_id = absint($form->ID);
                                $form_title = esc_js($form->post_title);
                                echo '<option value="' . $form_id . '">' . $form_title . ' (ID: ' . $form_id . ')</option>';
                            }
                        }
                        ?>' +
                        '</select>' +
                        '<input type="text" name="wpforms_email_otp_pro_settings[forms][' + index + '][email_label]" placeholder="<?php _e('Email Field Label', 'wpforms-email-otp-pro'); ?>" class="regular-text" style="margin-left: 10px;">' +
                        '<button type="button" class="button wpforms-otp-remove-form" style="margin-left: 10px;"><?php _e('Remove', 'wpforms-email-otp-pro'); ?></button>' +
                        '</div>';
                    $('#wpforms-otp-forms-repeater').append(html);
                });

                // Remove form row
                $(document).on('click', '.wpforms-otp-remove-form', function() {
                    if ($('.wpforms-otp-form-row').length > 1) {
                        $(this).closest('.wpforms-otp-form-row').remove();
                    }
                });
            });
        </script>
        <?php
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('elementor_template_id', __('Elementor Template ID (optional)', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="number" name="wpforms_email_otp_pro_settings[elementor_template_id]" value="' . esc_attr($options['elementor_template_id'] ?? '') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('otp_length', __('OTP Length', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        $length = $options['otp_length'] ?? '6';
        echo '<select name="wpforms_email_otp_pro_settings[otp_length]" class="regular-text">';
        foreach (['4' => '4 Digits', '6' => '6 Digits', '8' => '8 Digits'] as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($length, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('otp_expiry', __('OTP Expiry (minutes)', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="number" name="wpforms_email_otp_pro_settings[otp_expiry]" value="' . esc_attr($options['otp_expiry'] ?? '5') . '" min="1" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('email_subject', __('Email Subject', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="text" name="wpforms_email_otp_pro_settings[email_subject]" value="' . esc_attr($options['email_subject'] ?? 'Your Verification Code') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('email_message', __('Email Message', 'wpforms-email-otp-pro'), function() {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<textarea name="wpforms_email_otp_pro_settings[email_message]" class="large-text" rows="5">' . esc_textarea($options['email_message'] ?? 'Your verification code is: {otp}') . '</textarea>';
        echo '<p class="description">' . __('Use {otp} for the OTP code, {site} for the site name.', 'wpforms-email-otp-pro') . '</p>';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');
});

// Render settings page
function wpforms_email_otp_pro_settings_page() {
    ?>
    <div class="wrap wpforms-email-otp-pro-settings">
        <h1><?php _e('Email OTP Pro Settings', 'wpforms-email-otp-pro'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wpforms_email_otp_pro_settings');
            do_settings_sections('wpforms-email-otp-pro');
            submit_button(__('Save Settings', 'wpforms-email-otp-pro'));
            ?>
        </form>
    </div>
    <?php
}