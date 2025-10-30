<?php
/**
 * Plugin Name: Cookie Compliance Manager
 * Plugin URI: https://github.com/yourusername/cookie-compliance-manager
 * Description: A comprehensive WordPress plugin for managing website cookies in compliance with GDPR and CCPA regulations. Provides cookie consent banners, user preference management, and cookie blocking capabilities.
 * Version: 1.1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: cookie-compliance-manager
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CCM_VERSION', '1.1.0');
define('CCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CCM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CCM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load dependencies
require_once CCM_PLUGIN_DIR . 'includes/class-script-scanner.php';

class CookieComplianceManager {

    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->initHooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function initHooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminAssets'));

        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueueFrontendAssets'));
        add_action('wp_footer', array($this, 'renderCookieBanner'));

        // AJAX hooks
        add_action('wp_ajax_ccm_save_consent', array($this, 'saveConsent'));
        add_action('wp_ajax_nopriv_ccm_save_consent', array($this, 'saveConsent'));
        add_action('wp_ajax_ccm_export_logs', array($this, 'exportLogs'));
        add_action('wp_ajax_ccm_scan_page', array($this, 'scanPage'));

        // Script scanner hooks
        $scanner_settings = get_option('ccm_scanner_settings', array());
        if (isset($scanner_settings['auto_block']) && $scanner_settings['auto_block']) {
            add_action('template_redirect', array($this, 'startOutputBuffering'), 0);
        }

        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = array(
            'banner_position' => 'bottom',
            'banner_title' => 'We use cookies',
            'banner_message' => 'This website uses cookies to ensure you get the best experience on our website.',
            'accept_button_text' => 'Accept All',
            'reject_button_text' => 'Reject All',
            'settings_button_text' => 'Cookie Settings',
            'privacy_policy_url' => get_privacy_policy_url(),
            'enable_essential' => true,
            'enable_analytics' => true,
            'enable_marketing' => true,
            'banner_bg_color' => '#ffffff',
            'banner_text_color' => '#333333',
            'button_bg_color' => '#4CAF50',
            'button_text_color' => '#ffffff',
            'consent_expiry_days' => 365,
            'show_for_gdpr_only' => false,
            'show_for_ccpa_only' => false,
        );

        add_option('ccm_settings', $default_options);

        // Create consent log table
        global $wpdb;
        $table_name = $wpdb->prefix . 'ccm_consent_log';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_ip varchar(45) NOT NULL,
            user_agent text NOT NULL,
            consent_data text NOT NULL,
            consent_timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_ip (user_ip),
            KEY consent_timestamp (consent_timestamp)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }

    /**
     * Add admin menu
     */
    public function addAdminMenu() {
        add_menu_page(
            __('Cookie Compliance', 'cookie-compliance-manager'),
            __('Cookie Compliance', 'cookie-compliance-manager'),
            'manage_options',
            'cookie-compliance-manager',
            array($this, 'renderAdminPage'),
            'dashicons-shield-alt',
            80
        );

        add_submenu_page(
            'cookie-compliance-manager',
            __('Consent Logs', 'cookie-compliance-manager'),
            __('Consent Logs', 'cookie-compliance-manager'),
            'manage_options',
            'cookie-compliance-logs',
            array($this, 'renderLogsPage')
        );

        add_submenu_page(
            'cookie-compliance-manager',
            __('Script Scanner', 'cookie-compliance-manager'),
            __('Script Scanner', 'cookie-compliance-manager'),
            'manage_options',
            'cookie-compliance-scanner',
            array($this, 'renderScannerPage')
        );
    }

    /**
     * Register plugin settings
     */
    public function registerSettings() {
        register_setting('ccm_settings_group', 'ccm_settings', array($this, 'sanitizeSettings'));
        register_setting('ccm_scanner_settings_group', 'ccm_scanner_settings', array($this, 'sanitizeScannerSettings'));
    }

    /**
     * Sanitize settings
     */
    public function sanitizeSettings($input) {
        $sanitized = array();

        $sanitized['banner_position'] = sanitize_text_field($input['banner_position']);
        $sanitized['banner_title'] = sanitize_text_field($input['banner_title']);
        $sanitized['banner_message'] = wp_kses_post($input['banner_message']);
        $sanitized['accept_button_text'] = sanitize_text_field($input['accept_button_text']);
        $sanitized['reject_button_text'] = sanitize_text_field($input['reject_button_text']);
        $sanitized['settings_button_text'] = sanitize_text_field($input['settings_button_text']);
        $sanitized['privacy_policy_url'] = esc_url_raw($input['privacy_policy_url']);
        $sanitized['enable_essential'] = isset($input['enable_essential']) ? true : false;
        $sanitized['enable_analytics'] = isset($input['enable_analytics']) ? true : false;
        $sanitized['enable_marketing'] = isset($input['enable_marketing']) ? true : false;
        $sanitized['banner_bg_color'] = sanitize_hex_color($input['banner_bg_color']);
        $sanitized['banner_text_color'] = sanitize_hex_color($input['banner_text_color']);
        $sanitized['button_bg_color'] = sanitize_hex_color($input['button_bg_color']);
        $sanitized['button_text_color'] = sanitize_hex_color($input['button_text_color']);
        $sanitized['consent_expiry_days'] = absint($input['consent_expiry_days']);
        $sanitized['show_for_gdpr_only'] = isset($input['show_for_gdpr_only']) ? true : false;
        $sanitized['show_for_ccpa_only'] = isset($input['show_for_ccpa_only']) ? true : false;

        return $sanitized;
    }

    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets($hook) {
        if (strpos($hook, 'cookie-compliance') === false) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_style(
            'ccm-admin-css',
            CCM_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            CCM_VERSION
        );

        wp_enqueue_script(
            'ccm-admin-js',
            CCM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            CCM_VERSION,
            true
        );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueueFrontendAssets() {
        wp_enqueue_style(
            'ccm-frontend-css',
            CCM_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            CCM_VERSION
        );

        wp_enqueue_script(
            'ccm-frontend-js',
            CCM_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            CCM_VERSION,
            true
        );

        // Localize script
        $settings = get_option('ccm_settings', array());
        wp_localize_script('ccm-frontend-js', 'ccmData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ccm_consent_nonce'),
            'expiryDays' => isset($settings['consent_expiry_days']) ? $settings['consent_expiry_days'] : 365,
        ));
    }

    /**
     * Render cookie banner
     */
    public function renderCookieBanner() {
        $settings = get_option('ccm_settings', array());

        // Check if we should show banner based on location
        if (!empty($settings['show_for_gdpr_only']) || !empty($settings['show_for_ccpa_only'])) {
            // In a real implementation, you would check user's location via IP geolocation
            // For now, we'll show the banner to everyone
        }

        $banner_position = isset($settings['banner_position']) ? $settings['banner_position'] : 'bottom';
        $banner_title = isset($settings['banner_title']) ? $settings['banner_title'] : 'We use cookies';
        $banner_message = isset($settings['banner_message']) ? $settings['banner_message'] : 'This website uses cookies to ensure you get the best experience on our website.';
        $accept_text = isset($settings['accept_button_text']) ? $settings['accept_button_text'] : 'Accept All';
        $reject_text = isset($settings['reject_button_text']) ? $settings['reject_button_text'] : 'Reject All';
        $settings_text = isset($settings['settings_button_text']) ? $settings['settings_button_text'] : 'Cookie Settings';
        $privacy_url = isset($settings['privacy_policy_url']) ? $settings['privacy_policy_url'] : '';

        $enable_analytics = isset($settings['enable_analytics']) ? $settings['enable_analytics'] : true;
        $enable_marketing = isset($settings['enable_marketing']) ? $settings['enable_marketing'] : true;

        // Inline styles from settings
        $bg_color = isset($settings['banner_bg_color']) ? $settings['banner_bg_color'] : '#ffffff';
        $text_color = isset($settings['banner_text_color']) ? $settings['banner_text_color'] : '#333333';
        $button_bg = isset($settings['button_bg_color']) ? $settings['button_bg_color'] : '#4CAF50';
        $button_text = isset($settings['button_text_color']) ? $settings['button_text_color'] : '#ffffff';

        ?>
        <div id="ccm-cookie-banner" class="ccm-banner ccm-banner-<?php echo esc_attr($banner_position); ?>"
             style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;"
             data-shown="false">
            <div class="ccm-banner-content">
                <div class="ccm-banner-text">
                    <h3 class="ccm-banner-title"><?php echo esc_html($banner_title); ?></h3>
                    <p class="ccm-banner-message"><?php echo wp_kses_post($banner_message); ?></p>
                    <?php if ($privacy_url): ?>
                        <p class="ccm-privacy-link">
                            <a href="<?php echo esc_url($privacy_url); ?>" target="_blank">
                                <?php _e('Privacy Policy', 'cookie-compliance-manager'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="ccm-banner-buttons">
                    <button class="ccm-btn ccm-btn-accept"
                            style="background-color: <?php echo esc_attr($button_bg); ?>; color: <?php echo esc_attr($button_text); ?>;">
                        <?php echo esc_html($accept_text); ?>
                    </button>
                    <button class="ccm-btn ccm-btn-reject"
                            style="background-color: transparent; color: <?php echo esc_attr($text_color); ?>; border: 1px solid <?php echo esc_attr($text_color); ?>;">
                        <?php echo esc_html($reject_text); ?>
                    </button>
                    <button class="ccm-btn ccm-btn-settings"
                            style="background-color: transparent; color: <?php echo esc_attr($text_color); ?>;">
                        <?php echo esc_html($settings_text); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Cookie Settings Modal -->
        <div id="ccm-settings-modal" class="ccm-modal" style="display: none;">
            <div class="ccm-modal-content" style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
                <span class="ccm-modal-close">&times;</span>
                <h2><?php _e('Cookie Settings', 'cookie-compliance-manager'); ?></h2>
                <p><?php _e('We use cookies to enhance your browsing experience and analyze our traffic. You can choose which cookies to accept.', 'cookie-compliance-manager'); ?></p>

                <div class="ccm-cookie-category">
                    <div class="ccm-category-header">
                        <label class="ccm-switch">
                            <input type="checkbox" id="ccm-essential" checked disabled>
                            <span class="ccm-slider"></span>
                        </label>
                        <div class="ccm-category-info">
                            <h4><?php _e('Essential Cookies', 'cookie-compliance-manager'); ?></h4>
                            <p><?php _e('These cookies are necessary for the website to function and cannot be disabled.', 'cookie-compliance-manager'); ?></p>
                        </div>
                    </div>
                </div>

                <?php if ($enable_analytics): ?>
                <div class="ccm-cookie-category">
                    <div class="ccm-category-header">
                        <label class="ccm-switch">
                            <input type="checkbox" id="ccm-analytics">
                            <span class="ccm-slider"></span>
                        </label>
                        <div class="ccm-category-info">
                            <h4><?php _e('Analytics Cookies', 'cookie-compliance-manager'); ?></h4>
                            <p><?php _e('These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously.', 'cookie-compliance-manager'); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($enable_marketing): ?>
                <div class="ccm-cookie-category">
                    <div class="ccm-category-header">
                        <label class="ccm-switch">
                            <input type="checkbox" id="ccm-marketing">
                            <span class="ccm-slider"></span>
                        </label>
                        <div class="ccm-category-info">
                            <h4><?php _e('Marketing Cookies', 'cookie-compliance-manager'); ?></h4>
                            <p><?php _e('These cookies are used to track visitors across websites to display relevant advertisements.', 'cookie-compliance-manager'); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="ccm-modal-buttons">
                    <button class="ccm-btn ccm-btn-save-preferences"
                            style="background-color: <?php echo esc_attr($button_bg); ?>; color: <?php echo esc_attr($button_text); ?>;">
                        <?php _e('Save Preferences', 'cookie-compliance-manager'); ?>
                    </button>
                    <button class="ccm-btn ccm-btn-accept-all"
                            style="background-color: <?php echo esc_attr($button_bg); ?>; color: <?php echo esc_attr($button_text); ?>;">
                        <?php _e('Accept All', 'cookie-compliance-manager'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save user consent via AJAX
     */
    public function saveConsent() {
        check_ajax_referer('ccm_consent_nonce', 'nonce');

        $consent_data = isset($_POST['consent']) ? $_POST['consent'] : array();

        // Log consent to database for GDPR compliance
        global $wpdb;
        $table_name = $wpdb->prefix . 'ccm_consent_log';

        $wpdb->insert(
            $table_name,
            array(
                'user_ip' => $this->getUserIP(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
                'consent_data' => json_encode($consent_data),
                'consent_timestamp' => current_time('mysql')
            )
        );

        wp_send_json_success(array('message' => 'Consent saved successfully'));
    }

    /**
     * Get user IP address
     */
    private function getUserIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field($ip);
    }

    /**
     * Render admin page
     */
    public function renderAdminPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include CCM_PLUGIN_DIR . 'includes/admin-page.php';
    }

    /**
     * Render logs page
     */
    public function renderLogsPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include CCM_PLUGIN_DIR . 'includes/logs-page.php';
    }

    /**
     * Export consent logs to CSV
     */
    public function exportLogs() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ccm_consent_log';

        // Get all logs
        $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY consent_timestamp DESC");

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=cookie-consent-logs-' . date('Y-m-d') . '.csv');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, array('ID', 'IP Address', 'User Agent', 'Essential', 'Analytics', 'Marketing', 'Timestamp'));

        // Add data rows
        foreach ($logs as $log) {
            $consent = json_decode($log->consent_data, true);
            fputcsv($output, array(
                $log->id,
                $log->user_ip,
                $log->user_agent,
                isset($consent['essential']) && $consent['essential'] ? 'Yes' : 'No',
                isset($consent['analytics']) && $consent['analytics'] ? 'Yes' : 'No',
                isset($consent['marketing']) && $consent['marketing'] ? 'Yes' : 'No',
                $log->consent_timestamp
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * Sanitize scanner settings
     */
    public function sanitizeScannerSettings($input) {
        $sanitized = array();
        $sanitized['auto_block'] = isset($input['auto_block']) ? true : false;
        return $sanitized;
    }

    /**
     * Start output buffering to scan and block scripts
     */
    public function startOutputBuffering() {
        ob_start(array($this, 'processOutput'));
    }

    /**
     * Process output buffer to block scripts
     */
    public function processOutput($content) {
        // Don't process admin pages
        if (is_admin()) {
            return $content;
        }

        // Don't process AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return $content;
        }

        // Use the scanner to block scripts
        return CCM_Script_Scanner::block_scripts($content);
    }

    /**
     * AJAX handler to scan current page
     */
    public function scanPage() {
        check_ajax_referer('ccm_scanner_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

        if (empty($url)) {
            wp_send_json_error(array('message' => 'No URL provided'));
        }

        // Fetch the page content
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $content = wp_remote_retrieve_body($response);

        // Scan the content
        $detected_scripts = CCM_Script_Scanner::scan_content($content);

        wp_send_json_success(array(
            'scripts' => $detected_scripts,
            'count' => count($detected_scripts)
        ));
    }

    /**
     * Render scanner page
     */
    public function renderScannerPage() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include CCM_PLUGIN_DIR . 'includes/scanner-page.php';
    }
}

// Initialize plugin
function ccm_init() {
    return CookieComplianceManager::getInstance();
}

// Start the plugin
add_action('plugins_loaded', 'ccm_init');
