<?php
/**
 * Script Scanner - Automatically detects and categorizes third-party scripts
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class CCM_Script_Scanner {

    /**
     * Known script patterns and their categories
     */
    private static $script_patterns = array(
        // Analytics (Social Media)
        'analytics' => array(
            'facebook.net' => 'Facebook Pixel',
            'fbevents.js' => 'Facebook Pixel',
            'connect.facebook.net' => 'Facebook Pixel',
            'twitter.com/widgets.js' => 'Twitter Widget',
            'platform.twitter.com' => 'Twitter Widget',
            'linkedin.com/embed' => 'LinkedIn Widget',
            'snap.licdn.com' => 'LinkedIn Insight Tag',
            'addthis.com' => 'AddThis',
            'sharethis.com' => 'ShareThis',
        ),
        // Performance
        'performance' => array(
            'google-analytics.com' => 'Google Analytics',
            'googletagmanager.com' => 'Google Tag Manager',
            'ga.js' => 'Google Analytics (Legacy)',
            'analytics.js' => 'Google Analytics',
            'gtag/js' => 'Google Analytics 4',
            'gtm.js' => 'Google Tag Manager',
            'matomo.js' => 'Matomo Analytics',
            'piwik.js' => 'Piwik Analytics',
            'hotjar.com' => 'Hotjar',
            'mouseflow.com' => 'Mouseflow',
            'crazyegg.com' => 'Crazy Egg',
            'luckyorange.com' => 'Lucky Orange',
            'mixpanel.com' => 'Mixpanel',
            'segment.com' => 'Segment',
            'amplitude.com' => 'Amplitude',
            'heap.io' => 'Heap Analytics',
            'fullstory.com' => 'FullStory',
            'logrocket.com' => 'LogRocket',
        ),
        // Advertisement (Targeting)
        'advertisement' => array(
            'doubleclick.net' => 'Google DoubleClick',
            'googlesyndication.com' => 'Google AdSense',
            'adsbygoogle.js' => 'Google AdSense',
            'googleadservices.com' => 'Google Ads',
            'google.com/ads' => 'Google Ads',
            'ads-twitter.com' => 'Twitter Ads',
            'analytics.twitter.com' => 'Twitter Analytics',
            'bing.com/bat.js' => 'Bing Ads',
            'bat.bing.com' => 'Bing Ads',
            'pinterest.com/ct' => 'Pinterest Tag',
            'reddit.com/pixel' => 'Reddit Pixel',
            'snapchat.com/pixel' => 'Snapchat Pixel',
            'tiktok.com/pixel' => 'TikTok Pixel',
            'outbrain.com' => 'Outbrain',
            'taboola.com' => 'Taboola',
            'criteo.com' => 'Criteo',
        ),
    );

    /**
     * JavaScript function patterns for detection
     */
    private static $function_patterns = array(
        'analytics' => array(
            'fbq(' => 'Facebook Pixel',
            'twq(' => 'Twitter Pixel',
        ),
        'performance' => array(
            'ga(' => 'Google Analytics',
            'gtag(' => 'Google Analytics 4',
            '_gaq.push' => 'Google Analytics (Legacy)',
            'dataLayer.push' => 'Google Tag Manager',
            '_paq.push' => 'Matomo/Piwik',
            'mixpanel.track' => 'Mixpanel',
            'amplitude.track' => 'Amplitude',
            'heap.track' => 'Heap Analytics',
        ),
        'advertisement' => array(
            'pintrk(' => 'Pinterest Tag',
            'rdt(' => 'Reddit Pixel',
            'ttq.track' => 'TikTok Pixel',
            'snaptr(' => 'Snapchat Pixel',
            'uetq.push' => 'Bing Ads',
        ),
    );

    /**
     * Scan page content for scripts
     */
    public static function scan_content($content) {
        $detected_scripts = array();

        // Find all script tags
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $content, $script_matches, PREG_SET_ORDER);

        foreach ($script_matches as $match) {
            $full_tag = $match[0];
            $script_content = $match[1];

            // Check if already categorized
            if (strpos($full_tag, 'data-cookie-category') !== false) {
                continue;
            }

            // Detect script type
            $detection = self::detect_script($full_tag, $script_content);

            if ($detection) {
                $detected_scripts[] = array(
                    'full_tag' => $full_tag,
                    'content' => $script_content,
                    'type' => $detection['type'],
                    'category' => $detection['category'],
                    'service' => $detection['service'],
                    'confidence' => $detection['confidence'],
                );
            }
        }

        // Also find external script tags
        preg_match_all('/<script[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $external_matches, PREG_SET_ORDER);

        foreach ($external_matches as $match) {
            $full_tag = $match[0];
            $src = $match[1];

            // Check if already categorized
            if (strpos($full_tag, 'data-cookie-category') !== false) {
                continue;
            }

            $detection = self::detect_script($full_tag, '', $src);

            if ($detection) {
                $detected_scripts[] = array(
                    'full_tag' => $full_tag,
                    'src' => $src,
                    'type' => 'external',
                    'category' => $detection['category'],
                    'service' => $detection['service'],
                    'confidence' => $detection['confidence'],
                );
            }
        }

        return $detected_scripts;
    }

    /**
     * Detect script type and category
     */
    private static function detect_script($full_tag, $content = '', $src = '') {
        $detections = array();

        // Check URL patterns
        foreach (self::$script_patterns as $category => $patterns) {
            foreach ($patterns as $pattern => $service) {
                if (strpos($full_tag, $pattern) !== false || strpos($src, $pattern) !== false) {
                    $detections[] = array(
                        'category' => $category,
                        'service' => $service,
                        'confidence' => 'high',
                        'type' => 'url',
                    );
                    break 2;
                }
            }
        }

        // Check function patterns in content
        foreach (self::$function_patterns as $category => $patterns) {
            foreach ($patterns as $pattern => $service) {
                if (strpos($content, $pattern) !== false) {
                    $detections[] = array(
                        'category' => $category,
                        'service' => $service,
                        'confidence' => 'medium',
                        'type' => 'function',
                    );
                    break 2;
                }
            }
        }

        return !empty($detections) ? $detections[0] : null;
    }

    /**
     * Automatically block scripts in content
     */
    public static function block_scripts($content) {
        $settings = get_option('ccm_scanner_settings', array());
        $auto_block = isset($settings['auto_block']) && $settings['auto_block'];

        if (!$auto_block) {
            return $content;
        }

        // Get blocked scripts from settings
        $blocked_patterns = self::get_blocked_patterns();

        // Process script tags
        $content = preg_replace_callback(
            '/<script([^>]*)>(.*?)<\/script>/is',
            function($matches) use ($blocked_patterns) {
                $attributes = $matches[1];
                $script_content = $matches[2];
                $full_match = $matches[0];

                // Skip if already categorized
                if (strpos($attributes, 'data-cookie-category') !== false) {
                    return $full_match;
                }

                // Check if script should be blocked
                foreach ($blocked_patterns as $category => $patterns) {
                    foreach ($patterns as $pattern) {
                        if (stripos($full_match, $pattern) !== false || stripos($script_content, $pattern) !== false) {
                            // Block the script by changing type and adding category
                            $new_attributes = $attributes;

                            // Remove existing type attribute if present
                            $new_attributes = preg_replace('/type=["\'][^"\']*["\']/', '', $new_attributes);

                            // Add new type and category
                            $new_attributes = trim($new_attributes);
                            $new_attributes .= ' type="text/plain" data-cookie-category="' . esc_attr($category) . '"';

                            return '<script' . $new_attributes . '>' . $script_content . '</script>';
                        }
                    }
                }

                return $full_match;
            },
            $content
        );

        // Process external script tags
        $content = preg_replace_callback(
            '/<script([^>]+)src=["\']([^"\']+)["\']([^>]*)><\/script>/i',
            function($matches) use ($blocked_patterns) {
                $attributes_before = $matches[1];
                $src = $matches[2];
                $attributes_after = $matches[3];
                $full_match = $matches[0];

                // Skip if already categorized
                if (strpos($full_match, 'data-cookie-category') !== false) {
                    return $full_match;
                }

                // Check if script should be blocked
                foreach ($blocked_patterns as $category => $patterns) {
                    foreach ($patterns as $pattern) {
                        if (stripos($src, $pattern) !== false) {
                            // Block the script
                            $new_attributes = $attributes_before . $attributes_after;

                            // Remove existing type attribute
                            $new_attributes = preg_replace('/type=["\'][^"\']*["\']/', '', $new_attributes);

                            // Add new type and category
                            $new_attributes = trim($new_attributes);
                            $new_attributes .= ' type="text/plain" data-cookie-category="' . esc_attr($category) . '"';

                            return '<script' . $new_attributes . ' src="' . $src . '"></script>';
                        }
                    }
                }

                return $full_match;
            },
            $content
        );

        // Process iframes (e.g., YouTube, Vimeo)
        $iframe_patterns = array(
            'advertisement' => array('youtube.com', 'youtu.be', 'vimeo.com', 'dailymotion.com'),
        );

        $content = preg_replace_callback(
            '/<iframe([^>]+)src=["\']([^"\']+)["\']([^>]*)><\/iframe>/i',
            function($matches) use ($iframe_patterns) {
                $attributes_before = $matches[1];
                $src = $matches[2];
                $attributes_after = $matches[3];
                $full_match = $matches[0];

                // Skip if already categorized
                if (strpos($full_match, 'data-cookie-category') !== false) {
                    return $full_match;
                }

                // Check if iframe should be blocked
                foreach ($iframe_patterns as $category => $patterns) {
                    foreach ($patterns as $pattern) {
                        if (stripos($src, $pattern) !== false) {
                            // Block the iframe by moving src to data-src
                            $new_attributes = $attributes_before . $attributes_after;
                            $new_attributes = trim($new_attributes);
                            $new_attributes .= ' data-cookie-category="' . esc_attr($category) . '"';

                            return '<iframe' . $new_attributes . ' data-src="' . $src . '"></iframe>';
                        }
                    }
                }

                return $full_match;
            },
            $content
        );

        return $content;
    }

    /**
     * Get blocked patterns from settings
     */
    private static function get_blocked_patterns() {
        $patterns = array(
            'analytics' => array(),
            'marketing' => array(),
        );

        foreach (self::$script_patterns as $category => $services) {
            $patterns[$category] = array_merge($patterns[$category], array_keys($services));
        }

        return $patterns;
    }

    /**
     * Get all known script patterns
     */
    public static function get_known_patterns() {
        return self::$script_patterns;
    }

    /**
     * Add custom pattern
     */
    public static function add_custom_pattern($category, $pattern, $service_name) {
        $custom_patterns = get_option('ccm_custom_patterns', array());

        if (!isset($custom_patterns[$category])) {
            $custom_patterns[$category] = array();
        }

        $custom_patterns[$category][$pattern] = $service_name;
        update_option('ccm_custom_patterns', $custom_patterns);

        return true;
    }

    /**
     * Get custom patterns
     */
    public static function get_custom_patterns() {
        return get_option('ccm_custom_patterns', array());
    }

    /**
     * Merge custom patterns with default patterns
     */
    public static function get_all_patterns() {
        $custom_patterns = self::get_custom_patterns();
        $all_patterns = self::$script_patterns;

        foreach ($custom_patterns as $category => $patterns) {
            if (!isset($all_patterns[$category])) {
                $all_patterns[$category] = array();
            }
            $all_patterns[$category] = array_merge($all_patterns[$category], $patterns);
        }

        return $all_patterns;
    }
}
