<?php
namespace WPForms_Email_OTP_Pro;

class OTP_Handler {

    public function __construct() {
        add_action('wp_ajax_check_wpforms_otp', [$this, 'check_otp_ajax']);
        add_action('wp_ajax_nopriv_check_wpforms_otp', [$this, 'check_otp_ajax']);
        add_filter('wpforms_process_validate', [$this, 'validate_otp'], 10, 3);
    }

    public function generate_otp() {
        $settings = get_option('wpforms_email_otp_pro_settings');
        $length = $settings['otp_length'] ?? 6;
        
        if ($length == 4) {
            return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } elseif ($length == 8) {
            return str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        }
        
        // Default to 6 digits
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function store_otp($email, $otp) {
        $settings = get_option('wpforms_email_otp_pro_settings');
        $expiry = $settings['otp_expiry'] ?? 5;
        
        set_transient('otp_' . md5($email), $otp, $expiry * MINUTE_IN_SECONDS);
    }

    public function verify_otp($email, $otp) {
        $stored_otp = get_transient('otp_' . md5($email));
        return $stored_otp === $otp;
    }

    public function check_otp_ajax() {
        check_ajax_referer('wpforms_email_otp_nonce', 'nonce');

        $email = sanitize_email($_POST['email'] ?? '');
        $otp = sanitize_text_field($_POST['otp'] ?? '');

        if (!$this->verify_otp($email, $otp)) {
            wp_send_json_error(['message' => __('Invalid OTP. Please try again.', 'wpforms-email-otp-pro')]);
        }

        wp_send_json_success(['message' => __('OTP verified successfully.', 'wpforms-email-otp-pro')]);
    }

    public function validate_otp($fields, $entry, $form_data) {
        $settings = get_option('wpforms_email_otp_pro_settings');
        $form_ids = !empty($settings['forms']) ? array_column($settings['forms'], 'form_id') : [];
        
        if (!in_array($form_data['id'], $form_ids)) {
            return $fields;
        }

        // Find the email field label for this form
        $email_field_label = 'Email';
        foreach ($settings['forms'] as $form_config) {
            if ($form_config['form_id'] == $form_data['id']) {
                $email_field_label = $form_config['email_label'];
                break;
            }
        }

        $email = '';
        
        // Find the email field by label
        foreach ($form_data['fields'] as $field) {
            if ($field['label'] === $email_field_label && $field['type'] === 'email') {
                $email = $fields[$field['id']]['value'];
                break;
            }
        }

        if (empty($email)) {
            wpforms()->process->errors[$form_data['id']][] = __('Email field not found.', 'wpforms-email-otp-pro');
            return $fields;
        }

        $otp = sanitize_text_field($_POST['wpforms']['otp'] ?? '');

        if (!$this->verify_otp($email, $otp)) {
            wpforms()->process->errors[$form_data['id']][] = __('Invalid or expired OTP. Please request a new code.', 'wpforms-email-otp-pro');
        }

        return $fields;
    }
}