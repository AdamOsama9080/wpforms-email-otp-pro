<?php
namespace WPForms_Email_OTP_Pro;

class Admin_Dashboard {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Email OTP Pro', 'wpforms-email-otp-pro'),
            __('Email OTP Pro', 'wpforms-email-otp-pro'),
            'manage_options',
            'wpforms-email-otp-pro',
            [$this, 'render_dashboard'],
            'dashicons-email-alt',
            30
        );

        add_submenu_page(
            'wpforms-email-otp-pro',
            __('Settings', 'wpforms-email-otp-pro'),
            __('Settings', 'wpforms-email-otp-pro'),
            'manage_options',
            'wpforms-email-otp-pro-settings',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'wpforms-email-otp-pro',
            __('Email Templates', 'wpforms-email-otp-pro'),
            __('Email Templates', 'wpforms-email-otp-pro'),
            'manage_options',
            'wpforms-email-otp-pro-templates',
            [$this, 'render_templates_page']
        );

        add_submenu_page(
            'wpforms-email-otp-pro',
            __('Statistics', 'wpforms-email-otp-pro'),
            __('Statistics', 'wpforms-email-otp-pro'),
            'manage_options',
            'wpforms-email-otp-pro-stats',
            [$this, 'render_stats_page']
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wpforms-email-otp-pro') === false) {
            return;
        }

        wp_enqueue_style(
            'wpforms-email-otp-pro-admin',
            WPFORMS_EMAIL_OTP_PRO_URL . 'assets/css/admin.css',
            [],
            WPFORMS_EMAIL_OTP_PRO_VERSION
        );

        wp_enqueue_script(
            'wpforms-email-otp-pro-admin',
            WPFORMS_EMAIL_OTP_PRO_URL . 'assets/js/admin.js',
            ['jquery'],
            WPFORMS_EMAIL_OTP_PRO_VERSION,
            true
        );
    }

    public function render_dashboard() {
        ?>
        <div class="wrap wpforms-email-otp-pro-dashboard">
            <h1><?php _e('WPForms Email OTP Pro Dashboard', 'wpforms-email-otp-pro'); ?></h1>
            
            <div class="wpforms-email-otp-pro-cards">
                <div class="wpforms-email-otp-pro-card">
                    <h3><?php _e('Quick Setup', 'wpforms-email-otp-pro'); ?></h3>
                    <p><?php _e('Configure your OTP settings and start verifying emails.', 'wpforms-email-otp-pro'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wpforms-email-otp-pro-settings'); ?>" class="button button-primary">
                        <?php _e('Go to Settings', 'wpforms-email-otp-pro'); ?>
                    </a>
                </div>
                
                <div class="wpforms-email-otp-pro-card">
                    <h3><?php _e('Email Templates', 'wpforms-email-otp-pro'); ?></h3>
                    <p><?php _e('Customize your OTP email templates for better user experience.', 'wpforms-email-otp-pro'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wpforms-email-otp-pro-templates'); ?>" class="button button-primary">
                        <?php _e('Manage Templates', 'wpforms-email-otp-pro'); ?>
                    </a>
                </div>
                
                <div class="wpforms-email-otp-pro-card">
                    <h3><?php _e('Statistics', 'wpforms-email-otp-pro'); ?></h3>
                    <p><?php _e('View OTP usage statistics and success rates.', 'wpforms-email-otp-pro'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=wpforms-email-otp-pro-stats'); ?>" class="button button-primary">
                        <?php _e('View Stats', 'wpforms-email-otp-pro'); ?>
                    </a>
                </div>
            </div>
            
            <div class="wpforms-email-otp-pro-recent">
                <h2><?php _e('Recent Activity', 'wpforms-email-otp-pro'); ?></h2>
                <p><?php _e('No recent activity yet.', 'wpforms-email-otp-pro'); ?></p>
            </div>
        </div>
        <?php
    }

    public function render_settings_page() {
        ?>
        <div class="wrap wpforms-email-otp-pro-settings">
            <h1><?php _e('WPForms Email OTP Pro Settings', 'wpforms-email-otp-pro'); ?></h1>
            
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

    public function render_templates_page() {
        ?>
        <div class="wrap wpforms-email-otp-pro-templates">
            <h1><?php _e('Email Templates', 'wpforms-email-otp-pro'); ?></h1>
            
            <div class="wpforms-email-otp-pro-tabs">
                <h2 class="nav-tab-wrapper">
                    <a href="#" class="nav-tab nav-tab-active"><?php _e('Default Template', 'wpforms-email-otp-pro'); ?></a>
                    <a href="#" class="nav-tab"><?php _e('Custom HTML', 'wpforms-email-otp-pro'); ?></a>
                    <a href="#" class="nav-tab"><?php _e('Elementor', 'wpforms-email-otp-pro'); ?></a>
                </h2>
                
                <div class="wpforms-email-otp-pro-tab-content">
                    <div class="wpforms-email-otp-pro-tab-pane active">
                        <h3><?php _e('Default Email Template', 'wpforms-email-otp-pro'); ?></h3>
                        <p><?php _e('This is the default template that will be used if no custom template is selected.', 'wpforms-email-otp-pro'); ?></p>
                        <div class="wpforms-email-otp-pro-template-preview">
                            <iframe src="<?php echo WPFORMS_EMAIL_OTP_PRO_URL . 'templates/email-template.php?preview=1'; ?>"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_stats_page() {
        ?>
        <div class="wrap wpforms-email-otp-pro-stats">
            <h1><?php _e('OTP Statistics', 'wpforms-email-otp-pro'); ?></h1>
            
            <div class="wpforms-email-otp-pro-stats-grid">
                <div class="wpforms-email-otp-pro-stat-card">
                    <h3><?php _e('Total OTPs Sent', 'wpforms-email-otp-pro'); ?></h3>
                    <p class="stat-value">0</p>
                </div>
                
                <div class="wpforms-email-otp-pro-stat-card">
                    <h3><?php _e('Successful Verifications', 'wpforms-email-otp-pro'); ?></h3>
                    <p class="stat-value">0</p>
                </div>
                
                <div class="wpforms-email-otp-pro-stat-card">
                    <h3><?php _e('Failed Attempts', 'wpforms-email-otp-pro'); ?></h3>
                    <p class="stat-value">0</p>
                </div>
                
                <div class="wpforms-email-otp-pro-stat-card">
                    <h3><?php _e('Success Rate', 'wpforms-email-otp-pro'); ?></h3>
                    <p class="stat-value">0%</p>
                </div>
            </div>
            
            <div class="wpforms-email-otp-pro-chart">
                <h3><?php _e('OTP Usage Over Time', 'wpforms-email-otp-pro'); ?></h3>
                <div class="chart-container">
                    <canvas id="wpforms-email-otp-pro-chart"></canvas>
                </div>
            </div>
        </div>
        <?php
    }
}