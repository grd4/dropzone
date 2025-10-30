<?php
/**
 * Admin Settings Page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('ccm_settings', array());
?>

<div class="wrap">
    <h1><?php _e('Cookie Compliance Manager Settings', 'cookie-compliance-manager'); ?></h1>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('ccm_settings_group');
        ?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="banner_position"><?php _e('Banner Position', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <select name="ccm_settings[banner_position]" id="banner_position">
                            <option value="top" <?php selected(isset($settings['banner_position']) ? $settings['banner_position'] : '', 'top'); ?>>
                                <?php _e('Top', 'cookie-compliance-manager'); ?>
                            </option>
                            <option value="bottom" <?php selected(isset($settings['banner_position']) ? $settings['banner_position'] : '', 'bottom'); ?>>
                                <?php _e('Bottom', 'cookie-compliance-manager'); ?>
                            </option>
                        </select>
                        <p class="description"><?php _e('Where to display the cookie banner on the page.', 'cookie-compliance-manager'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="banner_title"><?php _e('Banner Title', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[banner_title]" id="banner_title"
                               value="<?php echo esc_attr(isset($settings['banner_title']) ? $settings['banner_title'] : 'We use cookies'); ?>"
                               class="regular-text">
                        <p class="description"><?php _e('The title displayed in the cookie banner.', 'cookie-compliance-manager'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="banner_message"><?php _e('Banner Message', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <textarea name="ccm_settings[banner_message]" id="banner_message" rows="4" class="large-text"><?php echo esc_textarea(isset($settings['banner_message']) ? $settings['banner_message'] : 'This website uses cookies to ensure you get the best experience on our website.'); ?></textarea>
                        <p class="description"><?php _e('The message displayed in the cookie banner.', 'cookie-compliance-manager'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="accept_button_text"><?php _e('Accept Button Text', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[accept_button_text]" id="accept_button_text"
                               value="<?php echo esc_attr(isset($settings['accept_button_text']) ? $settings['accept_button_text'] : 'Accept All'); ?>"
                               class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="reject_button_text"><?php _e('Reject Button Text', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[reject_button_text]" id="reject_button_text"
                               value="<?php echo esc_attr(isset($settings['reject_button_text']) ? $settings['reject_button_text'] : 'Reject All'); ?>"
                               class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="settings_button_text"><?php _e('Settings Button Text', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[settings_button_text]" id="settings_button_text"
                               value="<?php echo esc_attr(isset($settings['settings_button_text']) ? $settings['settings_button_text'] : 'Cookie Settings'); ?>"
                               class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="privacy_policy_url"><?php _e('Privacy Policy URL', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="ccm_settings[privacy_policy_url]" id="privacy_policy_url"
                               value="<?php echo esc_url(isset($settings['privacy_policy_url']) ? $settings['privacy_policy_url'] : ''); ?>"
                               class="regular-text">
                        <p class="description"><?php _e('URL to your privacy policy page.', 'cookie-compliance-manager'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('Cookie Categories', 'cookie-compliance-manager'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="ccm_settings[enable_necessary]" value="1" checked disabled>
                                <?php _e('Necessary Cookies (Always Enabled)', 'cookie-compliance-manager'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="ccm_settings[enable_analytics]" value="1"
                                       <?php checked(isset($settings['enable_analytics']) ? $settings['enable_analytics'] : true); ?>>
                                <?php _e('Analytics Cookies', 'cookie-compliance-manager'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="ccm_settings[enable_performance]" value="1"
                                       <?php checked(isset($settings['enable_performance']) ? $settings['enable_performance'] : true); ?>>
                                <?php _e('Performance Cookies', 'cookie-compliance-manager'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="ccm_settings[enable_advertisement]" value="1"
                                       <?php checked(isset($settings['enable_advertisement']) ? $settings['enable_advertisement'] : true); ?>>
                                <?php _e('Advertisement Cookies', 'cookie-compliance-manager'); ?>
                            </label>
                        </fieldset>
                        <p class="description"><?php _e('Select which cookie categories to offer to users.', 'cookie-compliance-manager'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="banner_bg_color"><?php _e('Banner Background Color', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[banner_bg_color]" id="banner_bg_color"
                               value="<?php echo esc_attr(isset($settings['banner_bg_color']) ? $settings['banner_bg_color'] : '#ffffff'); ?>"
                               class="ccm-color-picker">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="banner_text_color"><?php _e('Banner Text Color', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[banner_text_color]" id="banner_text_color"
                               value="<?php echo esc_attr(isset($settings['banner_text_color']) ? $settings['banner_text_color'] : '#333333'); ?>"
                               class="ccm-color-picker">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="button_bg_color"><?php _e('Button Background Color', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[button_bg_color]" id="button_bg_color"
                               value="<?php echo esc_attr(isset($settings['button_bg_color']) ? $settings['button_bg_color'] : '#4CAF50'); ?>"
                               class="ccm-color-picker">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="button_text_color"><?php _e('Button Text Color', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[button_text_color]" id="button_text_color"
                               value="<?php echo esc_attr(isset($settings['button_text_color']) ? $settings['button_text_color'] : '#ffffff'); ?>"
                               class="ccm-color-picker">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="button_border_color"><?php _e('Button Border Color', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="ccm_settings[button_border_color]" id="button_border_color"
                               value="<?php echo esc_attr(isset($settings['button_border_color']) ? $settings['button_border_color'] : '#084DAC'); ?>"
                               class="ccm-color-picker">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="consent_expiry_days"><?php _e('Consent Expiry (Days)', 'cookie-compliance-manager'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="ccm_settings[consent_expiry_days]" id="consent_expiry_days"
                               value="<?php echo esc_attr(isset($settings['consent_expiry_days']) ? $settings['consent_expiry_days'] : 365); ?>"
                               min="1" max="730">
                        <p class="description"><?php _e('How many days until the consent expires and the banner is shown again.', 'cookie-compliance-manager'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e('Regional Settings', 'cookie-compliance-manager'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="ccm_settings[show_for_gdpr_only]" value="1"
                                       <?php checked(isset($settings['show_for_gdpr_only']) ? $settings['show_for_gdpr_only'] : false); ?>>
                                <?php _e('Show banner only for GDPR regions (EU)', 'cookie-compliance-manager'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="ccm_settings[show_for_ccpa_only]" value="1"
                                       <?php checked(isset($settings['show_for_ccpa_only']) ? $settings['show_for_ccpa_only'] : false); ?>>
                                <?php _e('Show banner only for CCPA regions (California)', 'cookie-compliance-manager'); ?>
                            </label>
                        </fieldset>
                        <p class="description"><?php _e('Leave both unchecked to show the banner to all visitors.', 'cookie-compliance-manager'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>

    <hr>

    <h2><?php _e('Implementation Guide', 'cookie-compliance-manager'); ?></h2>
    <div class="ccm-implementation-guide">
        <h3><?php _e('How to Use Cookie Categories', 'cookie-compliance-manager'); ?></h3>
        <p><?php _e('To ensure cookies are only loaded when users consent, you need to modify how you load third-party scripts:', 'cookie-compliance-manager'); ?></p>

        <h4><?php _e('For Analytics Cookies (e.g., Google Analytics):', 'cookie-compliance-manager'); ?></h4>
        <pre><code>&lt;script type="text/plain" data-cookie-category="analytics"&gt;
// Your Google Analytics code here
&lt;/script&gt;</code></pre>

        <h4><?php _e('For Marketing Cookies (e.g., Facebook Pixel):', 'cookie-compliance-manager'); ?></h4>
        <pre><code>&lt;script type="text/plain" data-cookie-category="marketing"&gt;
// Your Facebook Pixel code here
&lt;/script&gt;</code></pre>

        <p><?php _e('The plugin will automatically enable these scripts when users consent to the respective cookie categories.', 'cookie-compliance-manager'); ?></p>

        <h3><?php _e('JavaScript API', 'cookie-compliance-manager'); ?></h3>
        <p><?php _e('You can check consent status programmatically:', 'cookie-compliance-manager'); ?></p>
        <pre><code>// Check if analytics is enabled
if (CCM.hasConsent('analytics')) {
    // Load analytics code
}

// Check if marketing is enabled
if (CCM.hasConsent('marketing')) {
    // Load marketing code
}

// Listen for consent changes
document.addEventListener('ccm-consent-updated', function(event) {
    console.log('Consent updated:', event.detail);
});</code></pre>
    </div>
</div>

<style>
.ccm-implementation-guide {
    background: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-top: 20px;
}
.ccm-implementation-guide pre {
    background: #272822;
    color: #f8f8f2;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
}
.ccm-implementation-guide code {
    font-family: 'Courier New', monospace;
    font-size: 13px;
}
.ccm-implementation-guide h4 {
    margin-top: 20px;
    margin-bottom: 10px;
}
</style>
