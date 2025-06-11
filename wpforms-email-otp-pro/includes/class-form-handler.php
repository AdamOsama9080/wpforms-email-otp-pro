<?php
namespace WPForms_Email_OTP_Pro;

class Form_Handler {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
        add_filter('wpforms_frontend_form_data', [$this, 'modify_form_data'], 10, 2);
    }

    public function frontend_assets() {
        if (!defined('WPFORMS_EMAIL_OTP_PRO_VERSION') || !defined('WPFORMS_EMAIL_OTP_PRO_URL')) {
            return;
        }

        wp_enqueue_script(
            'wpforms-email-otp-pro',
            WPFORMS_EMAIL_OTP_PRO_URL . 'assets/js/otp.js',
            ['jquery'],
            WPFORMS_EMAIL_OTP_PRO_VERSION,
            true
        );

        wp_enqueue_style(
            'wpforms-email-otp-pro',
            WPFORMS_EMAIL_OTP_PRO_URL . 'assets/css/otp.css',
            [],
            WPFORMS_EMAIL_OTP_PRO_VERSION
        );

        wp_localize_script('wpforms-email-otp-pro', 'wpformsOtpPro', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpforms_email_otp_nonce'),
            'i18n' => [
                'invalid_email' => __('Please enter a valid email address.', 'wpforms-email-otp-pro'),
                'invalid_otp' => __('Please enter the OTP.', 'wpforms-email-otp-pro'),
                'otp_sent' => __('OTP sent successfully!', 'wpforms-email-otp-pro'),
                'network_error' => __('Network error. Please try again.', 'wpforms-email-otp-pro'),
                'sending' => __('Sending...', 'wpforms-email-otp-pro'),
                'send_otp' => __('Send OTP', 'wpforms-email-otp-pro'),
                'checking' => __('Checking...', 'wpforms-email-otp-pro'),
                'verify' => __('Verify OTP', 'wpforms-email-otp-pro'),
                'verified' => __('OTP verified successfully.', 'wpforms-email-otp-pro'),
                'failed' => __('Failed to send OTP. Please try again.', 'wpforms-email-otp-pro'),
                'invalid' => __('Invalid OTP. Please try again.', 'wpforms-email-otp-pro'),
            ]
        ]);
    }

    public function modify_form_data($form_data, $form = null) {
        $settings = get_option('wpforms_email_otp_pro_settings');
        $form_id = $settings['form_id'] ?? 0;
        
        if ($form_data['id'] != $form_id) {
            return $form_data;
        }

        // Add OTP field to the form
        $form_data['fields'][] = [
            'id' => 'otp_field',
            'type' => 'html',
            'code' => $this->get_otp_field_html(),
        ];

        return $form_data;
    }

    private function get_otp_field_html() {
        ob_start();
        ?>
        <div class="wpforms-field-otp">
            <button type="button" class="wpforms-otp-btn send-otp-btn">
                <?php _e('Send OTP', 'wpforms-email-otp-pro'); ?>
            </button>
            <div class="wpforms-otp-field" style="display: none;">
                <input type="text" class="wpforms-otp-input" placeholder="<?php _e('Enter OTP', 'wpforms-email-otp-pro'); ?>" />
                <button type="button" class="wpforms-otp-btn check-otp-btn">
                    <?php _e('Verify OTP', 'wpforms-email-otp-pro'); ?>
                </button>
                <div class="wpforms-otp-status"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}