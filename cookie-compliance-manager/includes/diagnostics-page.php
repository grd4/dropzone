<?php
/**
 * Diagnostic Page for Cookie Compliance Manager
 * Add to your theme's functions.php temporarily to debug
 */

add_action('admin_menu', function() {
    add_submenu_page(
        'cookie-compliance-manager',
        'Diagnostics',
        'Diagnostics',
        'manage_options',
        'cookie-compliance-diagnostics',
        'ccm_render_diagnostics_page'
    );
});

function ccm_render_diagnostics_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    ?>
    <div class="wrap">
        <h1>Cookie Compliance Manager - Diagnostics</h1>

        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
            <h2>Scanner Settings Check</h2>
            <?php
            $scanner_settings = get_option('ccm_scanner_settings', array());
            echo '<p><strong>Scanner Settings:</strong></p>';
            echo '<pre>';
            print_r($scanner_settings);
            echo '</pre>';

            if (isset($scanner_settings['auto_block']) && $scanner_settings['auto_block']) {
                echo '<p style="color: green;">✓ Auto-blocking is ENABLED</p>';
            } else {
                echo '<p style="color: red;">✗ Auto-blocking is DISABLED</p>';
                echo '<p>Go to <a href="' . admin_url('admin.php?page=cookie-compliance-scanner') . '">Script Scanner</a> and enable it!</p>';
            }
            ?>
        </div>

        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
            <h2>Output Buffering Test</h2>
            <?php
            if (isset($scanner_settings['auto_block']) && $scanner_settings['auto_block']) {
                echo '<p>Output buffering should be active on frontend pages.</p>';
                echo '<p><strong>Check:</strong> Does <code>template_redirect</code> hook exist?</p>';

                // Check if hook is registered
                global $wp_filter;
                if (isset($wp_filter['template_redirect'])) {
                    echo '<p style="color: green;">✓ template_redirect hook exists</p>';

                    // Check our specific callback
                    $found = false;
                    foreach ($wp_filter['template_redirect']->callbacks as $priority => $callbacks) {
                        foreach ($callbacks as $callback) {
                            if (is_array($callback['function']) &&
                                is_object($callback['function'][0]) &&
                                get_class($callback['function'][0]) === 'CookieComplianceManager') {
                                $found = true;
                                echo '<p style="color: green;">✓ Our output buffering hook is registered at priority ' . $priority . '</p>';
                            }
                        }
                    }

                    if (!$found) {
                        echo '<p style="color: red;">✗ Our output buffering hook is NOT registered!</p>';
                        echo '<p><strong>Fix:</strong> Try deactivating and reactivating the plugin.</p>';
                    }
                } else {
                    echo '<p style="color: orange;">⚠ template_redirect hook not found (this is okay in admin)</p>';
                }
            } else {
                echo '<p style="color: red;">Auto-blocking is disabled - no buffering will occur.</p>';
            }
            ?>
        </div>

        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
            <h2>Detected Patterns</h2>
            <?php
            $patterns = CCM_Script_Scanner::get_known_patterns();
            echo '<p><strong>Total patterns loaded:</strong></p>';
            echo '<ul>';
            foreach ($patterns as $category => $services) {
                echo '<li><strong>' . ucfirst($category) . ':</strong> ' . count($services) . ' patterns</li>';
            }
            echo '</ul>';

            // Check for Koko Analytics specifically
            $koko_found = false;
            foreach ($patterns as $category => $services) {
                foreach ($services as $pattern => $name) {
                    if (stripos($pattern, 'koko') !== false || stripos($name, 'Koko') !== false) {
                        $koko_found = true;
                        echo '<p style="color: green;">✓ Koko Analytics pattern found in <strong>' . $category . '</strong> category</p>';
                        echo '<p>Pattern: <code>' . $pattern . '</code> → ' . $name . '</p>';
                    }
                }
            }

            if (!$koko_found) {
                echo '<p style="color: red;">✗ Koko Analytics pattern NOT found!</p>';
            }
            ?>
        </div>

        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
            <h2>Caching Check</h2>
            <?php
            $caching_plugins = array(
                'wp-super-cache/wp-cache.php' => 'WP Super Cache',
                'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
                'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
                'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
                'wp-rocket/wp-rocket.php' => 'WP Rocket',
                'cache-enabler/cache-enabler.php' => 'Cache Enabler',
            );

            $active_cache = array();
            foreach ($caching_plugins as $plugin => $name) {
                if (is_plugin_active($plugin)) {
                    $active_cache[] = $name;
                }
            }

            if (empty($active_cache)) {
                echo '<p style="color: green;">✓ No known caching plugins detected</p>';
            } else {
                echo '<p style="color: orange;">⚠ Caching plugin(s) detected:</p>';
                echo '<ul>';
                foreach ($active_cache as $cache) {
                    echo '<li>' . $cache . '</li>';
                }
                echo '</ul>';
                echo '<p><strong>Action Required:</strong> Clear your cache and test again!</p>';
            }
            ?>
        </div>

        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
            <h2>Quick Fix Actions</h2>
            <ol>
                <li><strong>Enable Auto-Blocking:</strong>
                    <a href="<?php echo admin_url('admin.php?page=cookie-compliance-scanner'); ?>" class="button">
                        Go to Script Scanner
                    </a>
                </li>
                <li><strong>Clear Cache:</strong> If using a caching plugin, clear it now</li>
                <li><strong>Test Frontend:</strong> Visit your homepage in incognito mode</li>
                <li><strong>Check Console:</strong> Run: <code>document.querySelectorAll('script[data-cookie-category]').length</code></li>
            </ol>
        </div>

        <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px; background: #fff3cd;">
            <h2>Debug Frontend Output Buffering</h2>
            <p>Add this temporarily to see if buffering is running:</p>
            <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;">
// Add to cookie-compliance-manager.php in processOutput() method
error_log('CCM: Output buffering is processing! Content length: ' . strlen($content));
error_log('CCM: Scripts found: ' . substr_count($content, '&lt;script'));
            </pre>
            <p>Then check your error log: <code><?php echo ini_get('error_log'); ?></code></p>
        </div>
    </div>
    <?php
}
