<?php
namespace WPForms_Email_OTP_Pro;

class Email_Sender {

    public function __construct() {
        add_action('wp_ajax_send_wpforms_otp', [$this, 'send_otp_ajax']);
        add_action('wp_ajax_nopriv_send_wpforms_otp', [$this, 'send_otp_ajax']);
        add_action('phpmailer_init', [$this, 'configure_smtp']);
    }

    public function configure_smtp($phpmailer) {
        $settings = get_option('wpforms_email_otp_pro_settings');
        if (!isset($settings['smtp_enabled']) || !$settings['smtp_enabled']) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = 'smtp.gmail.com';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = 587;
        $phpmailer->Username = $settings['sender_email'] ?? '';
        $phpmailer->Password = $settings['app_password'] ?? '';
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->From = $settings['sender_email'] ?? '';
        $phpmailer->FromName = get_bloginfo('name');
    }

    public function send_otp($email, $otp) {
        $settings = get_option('wpforms_email_otp_pro_settings');
        
        $subject = $settings['email_subject'] ?? __('Your Verification Code', 'wpforms-email-otp-pro');
        $message = $settings['email_message'] ?? __('Your verification code is: {otp}', 'wpforms-email-otp-pro');
        
        // Replace placeholders
        $message = str_replace('{otp}', $otp, $message);
        $message = str_replace('{site}', get_bloginfo('name'), $message);
        
        // Check for Elementor template
        $template_id = $settings['elementor_template_id'] ?? 0;
        
        if ($template_id && class_exists('\Elementor\Plugin')) {
            $message = $this->get_elementor_template($template_id, $otp, $email);
        }
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        return wp_mail($email, $subject, $message, $headers);
    }

    private function get_elementor_template($template_id, $otp, $email) {
        if (!class_exists('\Elementor\Plugin')) {
            return $this->default_email_template($otp);
        }

        $elementor = \Elementor\Plugin::instance();
        $content = $elementor->frontend->get_builder_content_for_display($template_id, true);
        
        if (empty($content)) {
            return $this->default_email_template($otp);
        }
        
        // Replace placeholders
        $content = str_replace('{otp}', $otp, $content);
        $content = str_replace('{site}', get_bloginfo('name'), $content);
        $content = str_replace('{email}', $email, $content);
        
        return $content;
    }

    private function default_email_template($otp) {
        if (!defined('WPFORMS_EMAIL_OTP_PRO_PATH')) {
            return '';
        }
        ob_start();
        include WPFORMS_EMAIL_OTP_PRO_PATH . 'templates/email-template.php';
        return ob_get_clean();
    }

    public function send_otp_ajax() {
        check_ajax_referer('wpforms_email_otp_nonce', 'nonce');

        $email = sanitize_email($_POST['email'] ?? '');
        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Invalid email address.', 'wpforms-email-otp-pro')]);
        }

        $otp_handler = new OTP_Handler();
        $otp = $otp_handler->generate_otp();
        $otp_handler->store_otp($email, $otp);

        $sent = $this->send_otp($email, $otp);

        if ($sent) {
            wp_send_json_success(['message' => __('OTP sent successfully.', 'wpforms-email-otp-pro')]);
        } else {
            wp_send_json_error(['message' => __('Failed to send OTP. Please try again.', 'wpforms-email-otp-pro')]);
        }
    }
}