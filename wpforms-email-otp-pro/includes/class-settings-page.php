<?php
function wpforms_email_otp_pro_admin_menu() {
    add_submenu_page(
        'wpforms-overview',
        'Email OTP Settings',
        'Email OTP Settings',
        'manage_options',
        'wpforms-email-otp-pro',
        'wpforms_email_otp_pro_settings_page'
    );
}
add_action('admin_menu', 'wpforms_email_otp_pro_admin_menu');

function wpforms_email_otp_pro_settings_page() {
    ?>
    <div class="wrap">
        <h1>Email OTP Pro Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wpforms_email_otp_pro_settings');
            do_settings_sections('wpforms-email-otp-pro');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function wpforms_email_otp_pro_register_settings() {
    register_setting('wpforms_email_otp_pro_settings', 'wpforms_email_otp_pro_settings');

    add_settings_section('wpforms_email_otp_pro_main', '', null, 'wpforms-email-otp-pro');

    add_settings_field('sender_email', 'Sender Gmail Address', function () {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="email" name="wpforms_email_otp_pro_settings[sender_email]" value="' . esc_attr($options['sender_email'] ?? '') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('app_password', 'Gmail App Password', function () {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="password" name="wpforms_email_otp_pro_settings[app_password]" value="' . esc_attr($options['app_password'] ?? '') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('form_id', 'WPForms Form ID', function () {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="text" name="wpforms_email_otp_pro_settings[form_id]" value="' . esc_attr($options['form_id'] ?? '') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('email_label', 'Email Field Label', function () {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="text" name="wpforms_email_otp_pro_settings[email_label]" value="' . esc_attr($options['email_label'] ?? '') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');

    add_settings_field('elementor_template_id', 'Elementor Template ID (optional)', function () {
        $options = get_option('wpforms_email_otp_pro_settings');
        echo '<input type="text" name="wpforms_email_otp_pro_settings[elementor_template_id]" value="' . esc_attr($options['elementor_template_id'] ?? '') . '" class="regular-text">';
    }, 'wpforms-email-otp-pro', 'wpforms_email_otp_pro_main');
}
add_action('admin_init', 'wpforms_email_otp_pro_register_settings');
