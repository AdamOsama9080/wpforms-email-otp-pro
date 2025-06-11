<?php
/**
 * Plugin Name: WPForms Email OTP Pro
 * Description: Professional email OTP verification for WPForms with advanced settings and email templates.
 * Version: 2.0.5
 * Author: Adam Osama
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('WPFORMS_EMAIL_OTP_PRO_VERSION', '2.0');
define('WPFORMS_EMAIL_OTP_PRO_FILE', __FILE__);
define('WPFORMS_EMAIL_OTP_PRO_PATH', plugin_dir_path(__FILE__));
define('WPFORMS_EMAIL_OTP_PRO_URL', plugin_dir_url(__FILE__));
define('WPFORMS_EMAIL_OTP_PRO_BASENAME', plugin_basename(__FILE__));

// Check if WPForms is active during activation
register_activation_hook(__FILE__, function() {
    if (!is_plugin_active('wpforms-lite/wpforms.php') && !is_plugin_active('wpforms/wpforms.php')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>' . __('WPForms Email OTP Pro requires WPForms to be installed and active. Please install and activate WPForms.', 'wpforms-email-otp-pro') . '</p></div>';
        });
        deactivate_plugins(plugin_basename(__FILE__));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
});

// Load the plugin if WPForms is active
add_action('plugins_loaded', function() {
    if (!class_exists('WPForms')) {
        return;
    }

    // Load plugin classes
    require_once WPFORMS_EMAIL_OTP_PRO_PATH . 'includes/settings-page.php';
    require_once WPFORMS_EMAIL_OTP_PRO_PATH . 'includes/class-email-sender.php';
    require_once WPFORMS_EMAIL_OTP_PRO_PATH . 'includes/class-otp-handler.php';
    require_once WPFORMS_EMAIL_OTP_PRO_PATH . 'includes/class-form-handler.php';

    // Initialize classes
    new WPForms_Email_OTP_Pro\Email_Sender();
    new WPForms_Email_OTP_Pro\OTP_Handler();
    new WPForms_Email_OTP_Pro\Form_Handler();

    // Enqueue admin scripts
    add_action('admin_enqueue_scripts', function($hook) {
        if ($hook !== 'toplevel_page_wpforms-email-otp-pro') {
            return;
        }
        wp_enqueue_script(
            'wpforms-email-otp-pro-admin',
            WPFORMS_EMAIL_OTP_PRO_URL . 'assets/js/admin.js',
            ['jquery'],
            WPFORMS_EMAIL_OTP_PRO_VERSION,
            true
        );
        wp_enqueue_style(
            'wpforms-email-otp-pro-admin',
            WPFORMS_EMAIL_OTP_PRO_URL . 'assets/css/admin.css',
            [],
            WPFORMS_EMAIL_OTP_PRO_VERSION
        );
    });
});

// Add settings link to plugin actions
add_filter('plugin_action_links_' . WPFORMS_EMAIL_OTP_PRO_BASENAME, function($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=wpforms-email-otp-pro') . '">' . __('Settings', 'wpforms-email-otp-pro') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});