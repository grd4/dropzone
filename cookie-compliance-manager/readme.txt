=== Cookie Compliance Manager ===
Contributors: yourusername
Tags: gdpr, ccpa, cookies, privacy, consent
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.2
License: MIT
License URI: https://opensource.org/licenses/MIT

A comprehensive WordPress plugin for managing website cookies in compliance with GDPR and CCPA regulations.

== Description ==

Cookie Compliance Manager is a powerful yet easy-to-use WordPress plugin that helps you comply with GDPR (General Data Protection Regulation) and CCPA (California Consumer Privacy Act) cookie consent requirements.

= Features =

* **Fully Customizable Cookie Banner** - Customize colors, text, position, and button labels
* **Three Cookie Categories** - Essential, Analytics, and Marketing cookies
* **GDPR & CCPA Compliant** - Meets all requirements for both regulations
* **Consent Logging** - Records all user consent decisions for audit purposes
* **Easy Integration** - Simple implementation for blocking third-party scripts
* **JavaScript API** - Check consent status and listen for consent changes
* **Responsive Design** - Works perfectly on all devices
* **Export Consent Logs** - Download consent records as CSV for compliance auditing

= Cookie Categories =

1. **Essential Cookies** - Always enabled, required for website functionality
2. **Analytics Cookies** - Optional, for tracking user behavior (e.g., Google Analytics)
3. **Marketing Cookies** - Optional, for advertising and remarketing

= GDPR Compliance =

* Explicit consent required before setting cookies
* Users can choose which cookie categories to accept
* Right to withdraw consent at any time
* All consent decisions are logged with timestamps
* No cookies set before user consent (except essential)

= CCPA Compliance =

* Users can opt-out of non-essential cookies
* Clear disclosure of cookie usage
* "Do Not Sell" functionality via cookie rejection
* Regional detection support

== Installation ==

1. Upload the `cookie-compliance-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Cookie Compliance' in the admin menu to configure settings
4. Customize the banner appearance and text
5. Save your settings

== Frequently Asked Questions ==

= Do I need this plugin? =

If your website uses any cookies (analytics, marketing, etc.) and you have visitors from the EU or California, you should use a cookie consent solution to comply with GDPR and CCPA.

= How do I block Google Analytics until consent is given? =

Wrap your Google Analytics code like this:

`<script type="text/plain" data-cookie-category="analytics">
// Your Google Analytics code here
</script>`

The plugin will automatically enable the script when users consent to analytics cookies.

= Can users change their preferences later? =

Yes! You can add a "Manage Cookie Preferences" link anywhere:

`<a href="#" onclick="CCMRevokeConsent(); return false;">Manage Cookie Preferences</a>`

= Does this work with caching plugins? =

Yes, the plugin is compatible with popular caching plugins like WP Super Cache, W3 Total Cache, and WP Rocket.

= Will this slow down my website? =

No, the plugin is lightweight and optimized for performance with minimal CSS and JavaScript.

= Can I customize the banner design? =

Yes! You can customize colors, position, text, and button labels through the admin settings page.

== Screenshots ==

1. Cookie banner displayed at the bottom of the page
2. Cookie settings modal with granular controls
3. Admin settings page
4. Consent logs viewer
5. Color customization options

== Changelog ==

= 1.0.0 =
* Initial release
* Cookie banner with customizable design
* Three cookie categories (Essential, Analytics, Marketing)
* Admin settings page with color picker
* Consent logging with IP and timestamp
* CSV export functionality
* JavaScript API for developers
* GDPR and CCPA compliance features
* Responsive design for all devices

== Upgrade Notice ==

= 1.0.0 =
Initial release of Cookie Compliance Manager.

== Implementation Guide ==

= For Analytics Cookies (e.g., Google Analytics) =

`<script type="text/plain" data-cookie-category="analytics">
// Your Google Analytics code
</script>`

= For Marketing Cookies (e.g., Facebook Pixel) =

`<script type="text/plain" data-cookie-category="marketing">
// Your Facebook Pixel code
</script>`

= JavaScript API =

Check consent status:
`if (CCM.hasConsent('analytics')) {
    // Load analytics
}`

Listen for consent changes:
`document.addEventListener('ccm-consent-updated', function(event) {
    console.log('Consent updated:', event.detail);
});`

== Privacy ==

This plugin stores the following data in your WordPress database:
* User consent decisions
* IP addresses (for consent verification)
* User agents (for logging)
* Timestamps (for audit trails)

No data is transmitted to external servers. All information remains in your database.

== Support ==

For support, please visit the plugin's GitHub repository or WordPress.org support forum.
