<?php
/**
 * Script Scanner Admin Page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('ccm_scanner_settings', array());
$auto_block = isset($settings['auto_block']) ? $settings['auto_block'] : false;

// Get all patterns
$all_patterns = CCM_Script_Scanner::get_all_patterns();
$custom_patterns = CCM_Script_Scanner::get_custom_patterns();

// Handle custom pattern submission
if (isset($_POST['ccm_add_pattern']) && check_admin_referer('ccm_add_pattern_action', 'ccm_add_pattern_nonce')) {
    $category = sanitize_text_field($_POST['pattern_category']);
    $pattern = sanitize_text_field($_POST['pattern_string']);
    $service_name = sanitize_text_field($_POST['service_name']);

    if (!empty($category) && !empty($pattern) && !empty($service_name)) {
        CCM_Script_Scanner::add_custom_pattern($category, $pattern, $service_name);
        echo '<div class="notice notice-success"><p>' . __('Custom pattern added successfully!', 'cookie-compliance-manager') . '</p></div>';
        // Refresh patterns
        $all_patterns = CCM_Script_Scanner::get_all_patterns();
        $custom_patterns = CCM_Script_Scanner::get_custom_patterns();
    }
}
?>

<div class="wrap">
    <h1><?php _e('Script Scanner', 'cookie-compliance-manager'); ?></h1>
    <p><?php _e('The Script Scanner automatically detects and blocks third-party scripts based on user consent. No manual coding required!', 'cookie-compliance-manager'); ?></p>

    <div class="ccm-scanner-content">
        <!-- Auto-Block Settings -->
        <div class="card">
            <h2><?php _e('Auto-Block Settings', 'cookie-compliance-manager'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('ccm_scanner_settings_group');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="auto_block"><?php _e('Enable Auto-Blocking', 'cookie-compliance-manager'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="ccm_scanner_settings[auto_block]" id="auto_block" value="1"
                                       <?php checked($auto_block); ?>>
                                <?php _e('Automatically detect and block third-party scripts', 'cookie-compliance-manager'); ?>
                            </label>
                            <p class="description">
                                <?php _e('When enabled, the plugin will automatically detect and block known third-party scripts (Google Analytics, Facebook Pixel, etc.) until users consent.', 'cookie-compliance-manager'); ?>
                            </p>
                            <?php if ($auto_block): ?>
                                <p class="description" style="color: #46b450; font-weight: bold;">
                                    ✓ <?php _e('Auto-blocking is currently ACTIVE', 'cookie-compliance-manager'); ?>
                                </p>
                            <?php else: ?>
                                <p class="description" style="color: #dc3232;">
                                    ✗ <?php _e('Auto-blocking is currently DISABLED', 'cookie-compliance-manager'); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save Settings', 'cookie-compliance-manager')); ?>
            </form>
        </div>

        <!-- Page Scanner -->
        <div class="card" style="margin-top: 20px;">
            <h2><?php _e('Scan Your Website', 'cookie-compliance-manager'); ?></h2>
            <p><?php _e('Test the scanner on your homepage to see what scripts will be detected and blocked.', 'cookie-compliance-manager'); ?></p>

            <div class="ccm-scanner-test">
                <input type="text" id="ccm-scan-url" value="<?php echo esc_url(home_url()); ?>" class="regular-text">
                <button type="button" id="ccm-scan-button" class="button button-primary">
                    <?php _e('Scan Page', 'cookie-compliance-manager'); ?>
                </button>
                <span id="ccm-scan-loader" style="display: none;">
                    <span class="spinner is-active" style="float: none; margin: 0 10px;"></span>
                    <?php _e('Scanning...', 'cookie-compliance-manager'); ?>
                </span>
            </div>

            <div id="ccm-scan-results" style="margin-top: 20px; display: none;">
                <h3><?php _e('Detected Scripts', 'cookie-compliance-manager'); ?></h3>
                <div id="ccm-scan-results-content"></div>
            </div>
        </div>

        <!-- Known Patterns -->
        <div class="card" style="margin-top: 20px;">
            <h2><?php _e('Known Script Patterns', 'cookie-compliance-manager'); ?></h2>
            <p><?php _e('The scanner automatically detects these common third-party services:', 'cookie-compliance-manager'); ?></p>

            <div class="ccm-patterns-list">
                <?php foreach ($all_patterns as $category => $patterns): ?>
                    <div class="ccm-pattern-category">
                        <h3>
                            <span class="ccm-category-badge ccm-category-<?php echo esc_attr($category); ?>">
                                <?php echo esc_html(ucfirst($category)); ?>
                            </span>
                            (<?php echo count($patterns); ?> <?php _e('patterns', 'cookie-compliance-manager'); ?>)
                        </h3>
                        <ul class="ccm-pattern-list">
                            <?php foreach ($patterns as $pattern => $service): ?>
                                <li>
                                    <strong><?php echo esc_html($service); ?></strong>
                                    <code><?php echo esc_html($pattern); ?></code>
                                    <?php if (isset($custom_patterns[$category][$pattern])): ?>
                                        <span class="ccm-custom-badge"><?php _e('Custom', 'cookie-compliance-manager'); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Add Custom Pattern -->
        <div class="card" style="margin-top: 20px;">
            <h2><?php _e('Add Custom Pattern', 'cookie-compliance-manager'); ?></h2>
            <p><?php _e('Add your own script patterns to detect custom third-party services.', 'cookie-compliance-manager'); ?></p>

            <form method="post" action="">
                <?php wp_nonce_field('ccm_add_pattern_action', 'ccm_add_pattern_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="pattern_category"><?php _e('Category', 'cookie-compliance-manager'); ?></label>
                        </th>
                        <td>
                            <select name="pattern_category" id="pattern_category" required>
                                <option value="analytics"><?php _e('Analytics', 'cookie-compliance-manager'); ?></option>
                                <option value="marketing"><?php _e('Marketing', 'cookie-compliance-manager'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="pattern_string"><?php _e('Pattern String', 'cookie-compliance-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="pattern_string" id="pattern_string" class="regular-text" required>
                            <p class="description">
                                <?php _e('Enter a URL pattern or keyword to match (e.g., "example.com/tracking.js" or "myTracker")', 'cookie-compliance-manager'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="service_name"><?php _e('Service Name', 'cookie-compliance-manager'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="service_name" id="service_name" class="regular-text" required>
                            <p class="description">
                                <?php _e('A friendly name for this service (e.g., "My Custom Analytics")', 'cookie-compliance-manager'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <button type="submit" name="ccm_add_pattern" class="button button-secondary">
                    <?php _e('Add Pattern', 'cookie-compliance-manager'); ?>
                </button>
            </form>
        </div>

        <!-- How It Works -->
        <div class="card" style="margin-top: 20px;">
            <h2><?php _e('How Auto-Blocking Works', 'cookie-compliance-manager'); ?></h2>
            <div class="ccm-how-it-works">
                <ol>
                    <li>
                        <strong><?php _e('Enable Auto-Blocking', 'cookie-compliance-manager'); ?></strong>
                        <p><?php _e('Turn on the "Enable Auto-Blocking" setting above.', 'cookie-compliance-manager'); ?></p>
                    </li>
                    <li>
                        <strong><?php _e('Scripts Are Detected', 'cookie-compliance-manager'); ?></strong>
                        <p><?php _e('The scanner automatically detects third-party scripts on your pages using pattern matching.', 'cookie-compliance-manager'); ?></p>
                    </li>
                    <li>
                        <strong><?php _e('Scripts Are Blocked', 'cookie-compliance-manager'); ?></strong>
                        <p><?php _e('Detected scripts are automatically converted to type="text/plain" and tagged with the appropriate cookie category, preventing them from running.', 'cookie-compliance-manager'); ?></p>
                    </li>
                    <li>
                        <strong><?php _e('User Consents', 'cookie-compliance-manager'); ?></strong>
                        <p><?php _e('When users accept cookies, the scripts are enabled based on their consent preferences.', 'cookie-compliance-manager'); ?></p>
                    </li>
                </ol>

                <div class="ccm-note">
                    <strong><?php _e('Note:', 'cookie-compliance-manager'); ?></strong>
                    <?php _e('Auto-blocking works on the frontend output. You don\'t need to modify your theme or plugin code. Just enable it and it works!', 'cookie-compliance-manager'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ccm-scanner-content .card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ccm-scanner-content .card h2 {
    margin-top: 0;
    font-size: 18px;
}

.ccm-scanner-test {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 15px;
}

.ccm-patterns-list {
    margin-top: 20px;
}

.ccm-pattern-category {
    margin-bottom: 30px;
}

.ccm-pattern-category h3 {
    font-size: 16px;
    margin-bottom: 10px;
}

.ccm-category-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
}

.ccm-category-analytics {
    background-color: #0073aa;
}

.ccm-category-marketing {
    background-color: #d63638;
}

.ccm-pattern-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 10px;
}

.ccm-pattern-list li {
    background: #f9f9f9;
    padding: 10px;
    border-radius: 3px;
    border-left: 3px solid #0073aa;
}

.ccm-pattern-list code {
    display: block;
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.ccm-custom-badge {
    display: inline-block;
    background: #46b450;
    color: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 5px;
}

.ccm-how-it-works ol {
    counter-reset: item;
    list-style: none;
    padding-left: 0;
}

.ccm-how-it-works ol li {
    counter-increment: item;
    margin-bottom: 20px;
    padding-left: 50px;
    position: relative;
}

.ccm-how-it-works ol li:before {
    content: counter(item);
    position: absolute;
    left: 0;
    top: 0;
    background: #0073aa;
    color: #fff;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

.ccm-how-it-works ol li p {
    margin: 5px 0 0 0;
    color: #666;
}

.ccm-note {
    background: #fff8e5;
    border-left: 4px solid #ffb900;
    padding: 15px;
    margin-top: 20px;
}

#ccm-scan-results-content {
    max-height: 400px;
    overflow-y: auto;
}

.ccm-detected-script {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 15px;
    margin-bottom: 10px;
}

.ccm-detected-script-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.ccm-detected-script-service {
    font-weight: bold;
    font-size: 15px;
}

.ccm-detected-script-code {
    background: #272822;
    color: #f8f8f2;
    padding: 10px;
    border-radius: 3px;
    overflow-x: auto;
    font-size: 12px;
    line-height: 1.5;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#ccm-scan-button').on('click', function() {
        var url = $('#ccm-scan-url').val();
        var button = $(this);
        var loader = $('#ccm-scan-loader');
        var results = $('#ccm-scan-results');
        var resultsContent = $('#ccm-scan-results-content');

        button.prop('disabled', true);
        loader.show();
        results.hide();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ccm_scan_page',
                nonce: '<?php echo wp_create_nonce('ccm_scanner_nonce'); ?>',
                url: url
            },
            success: function(response) {
                if (response.success) {
                    var scripts = response.data.scripts;
                    var html = '';

                    if (scripts.length === 0) {
                        html = '<p><?php _e('No third-party scripts detected on this page.', 'cookie-compliance-manager'); ?></p>';
                    } else {
                        html = '<p><strong>' + scripts.length + ' <?php _e('script(s) detected', 'cookie-compliance-manager'); ?></strong></p>';

                        scripts.forEach(function(script) {
                            var categoryColor = script.category === 'analytics' ? '#0073aa' : '#d63638';

                            html += '<div class="ccm-detected-script">';
                            html += '<div class="ccm-detected-script-header">';
                            html += '<div class="ccm-detected-script-service">' + script.service + '</div>';
                            html += '<span class="ccm-category-badge" style="background-color: ' + categoryColor + ';">' + script.category + '</span>';
                            html += '</div>';

                            if (script.src) {
                                html += '<p><strong>Source:</strong> <code>' + script.src + '</code></p>';
                            }

                            if (script.content && script.content.trim()) {
                                var truncatedContent = script.content.substring(0, 200);
                                if (script.content.length > 200) {
                                    truncatedContent += '...';
                                }
                                html += '<div class="ccm-detected-script-code">' + truncatedContent + '</div>';
                            }

                            html += '<p><small>Confidence: ' + script.confidence + '</small></p>';
                            html += '</div>';
                        });
                    }

                    resultsContent.html(html);
                    results.show();
                } else {
                    alert('<?php _e('Scan failed:', 'cookie-compliance-manager'); ?> ' + response.data.message);
                }
            },
            error: function() {
                alert('<?php _e('An error occurred during scanning.', 'cookie-compliance-manager'); ?>');
            },
            complete: function() {
                button.prop('disabled', false);
                loader.hide();
            }
        });
    });
});
</script>
