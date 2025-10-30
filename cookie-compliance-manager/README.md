# Cookie Compliance Manager

A comprehensive WordPress plugin for managing website cookies in compliance with GDPR (General Data Protection Regulation) and CCPA (California Consumer Privacy Act) regulations.

## Features

### 🔒 Privacy Compliance
- **GDPR Compliant**: Full compliance with EU General Data Protection Regulation
- **CCPA Compliant**: Meets California Consumer Privacy Act requirements
- **Consent Logging**: Records all user consent decisions with timestamps and IP addresses
- **Right to Withdraw**: Users can revoke consent at any time

### 🎨 Customizable Cookie Banner
- Fully customizable banner position (top/bottom)
- Custom colors for banner, text, and buttons
- Editable banner title and message
- Privacy policy link integration
- Responsive design for all devices

### 🍪 Cookie Categories
- **Essential Cookies**: Always enabled (required for website functionality)
- **Analytics Cookies**: For tracking and understanding user behavior
- **Marketing Cookies**: For advertising and remarketing purposes

### ⚙️ Admin Features
- Intuitive settings page in WordPress admin
- Color picker for easy customization
- Consent log viewer with pagination
- CSV export for compliance auditing
- Regional settings (show banner only in specific regions)

### 🛠️ Developer Friendly
- JavaScript API for checking consent status
- Event system for consent changes
- Automatic script blocking/enabling based on consent
- Support for third-party scripts and iframes

## Installation

### Manual Installation

1. Download the plugin files
2. Upload the `cookie-compliance-manager` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to 'Cookie Compliance' in the admin menu to configure settings

### From WordPress Admin

1. Go to Plugins > Add New
2. Search for "Cookie Compliance Manager"
3. Click "Install Now" and then "Activate"
4. Configure the plugin settings

## Configuration

### Basic Settings

1. Navigate to **Cookie Compliance** in your WordPress admin menu
2. Configure the following settings:
   - **Banner Position**: Choose top or bottom
   - **Banner Title**: Customize the banner headline
   - **Banner Message**: Your cookie notice message
   - **Button Text**: Customize button labels
   - **Privacy Policy URL**: Link to your privacy policy

### Appearance Settings

Customize the look and feel:
- **Banner Background Color**: Choose your brand color
- **Banner Text Color**: Ensure readability
- **Button Colors**: Match your website design

### Cookie Categories

Enable/disable cookie categories:
- ✅ Essential Cookies (always enabled)
- ☑️ Analytics Cookies (optional)
- ☑️ Marketing Cookies (optional)

### Advanced Settings

- **Consent Expiry**: Set how long consent is valid (default: 365 days)
- **Regional Settings**: Show banner only for GDPR/CCPA regions

## Usage

### For Website Visitors

1. When visiting the site for the first time, users see the cookie banner
2. Users can:
   - Accept all cookies
   - Reject all non-essential cookies
   - Customize their preferences via "Cookie Settings"
3. Consent is saved for the configured expiry period

### Implementing Cookie Blocking

To ensure third-party scripts only load when users consent, modify your script tags:

#### For Analytics Cookies (e.g., Google Analytics)

```html
<script type="text/plain" data-cookie-category="analytics">
// Your Google Analytics code
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-XXXXX-Y', 'auto');
ga('send', 'pageview');
</script>
```

#### For Marketing Cookies (e.g., Facebook Pixel)

```html
<script type="text/plain" data-cookie-category="marketing">
// Your Facebook Pixel code
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', 'YOUR_PIXEL_ID');
fbq('track', 'PageView');
</script>
```

#### For Iframes (e.g., YouTube embeds)

```html
<iframe data-cookie-category="marketing"
        data-src="https://www.youtube.com/embed/VIDEO_ID"
        width="560"
        height="315"
        frameborder="0"
        allowfullscreen>
</iframe>
```

### JavaScript API

The plugin provides a JavaScript API for developers:

#### Check Consent Status

```javascript
// Check if analytics cookies are enabled
if (CCM.hasConsent('analytics')) {
    // Load analytics code
    console.log('Analytics enabled');
}

// Check if marketing cookies are enabled
if (CCM.hasConsent('marketing')) {
    // Load marketing code
    console.log('Marketing enabled');
}
```

#### Listen for Consent Changes

```javascript
document.addEventListener('ccm-consent-updated', function(event) {
    console.log('Consent updated:', event.detail);

    // event.detail contains:
    // {
    //   essential: true,
    //   analytics: true/false,
    //   marketing: true/false,
    //   timestamp: "2025-10-30T12:00:00.000Z"
    // }
});
```

#### Revoke Consent Programmatically

```javascript
// This will delete the consent cookie and reload the page
CCMRevokeConsent();
```

### Adding a "Manage Cookie Preferences" Link

You can add a link anywhere on your site to let users manage their preferences:

```html
<a href="#" onclick="CCMRevokeConsent(); return false;">
    Manage Cookie Preferences
</a>
```

Or open the settings modal directly:

```javascript
<a href="#" onclick="CCM.openModal(); return false;">
    Cookie Settings
</a>
```

## Compliance Features

### GDPR Compliance

The plugin helps you comply with GDPR requirements:

- ✅ **Explicit Consent**: Users must actively consent to cookies
- ✅ **Granular Control**: Users can choose which cookie categories to accept
- ✅ **Right to Withdraw**: Users can revoke consent at any time
- ✅ **Consent Records**: All consent decisions are logged with timestamps
- ✅ **Privacy by Design**: No cookies set before consent (except essential)

### CCPA Compliance

The plugin supports CCPA requirements:

- ✅ **Opt-Out Option**: Users can reject non-essential cookies
- ✅ **Do Not Sell**: Rejecting marketing cookies prevents tracking
- ✅ **Disclosure**: Clear information about cookie usage
- ✅ **Regional Detection**: Can show banner only for California visitors

## Consent Logs

### Viewing Consent Logs

1. Go to **Cookie Compliance > Consent Logs**
2. View all recorded consent decisions
3. See user IP, user agent, consent data, and timestamp

### Exporting Logs

Click "Export to CSV" to download consent records for compliance auditing.

## Frequently Asked Questions

### Do I need this plugin?

If your website:
- Targets users in the EU (GDPR)
- Targets users in California (CCPA)
- Uses analytics tools (Google Analytics, etc.)
- Uses marketing/advertising tools
- Uses any third-party tracking cookies

Then YES, you should use a cookie consent solution.

### Will this plugin slow down my website?

No. The plugin is lightweight and optimized for performance:
- Minimal CSS and JavaScript
- No external dependencies
- Efficient database queries
- Cached settings

### Can I customize the banner design?

Yes! The plugin provides:
- Color customization for all elements
- Position options (top/bottom)
- Customizable text and button labels
- CSS classes for advanced styling

### Does it work with caching plugins?

Yes, the plugin is compatible with popular caching plugins like:
- WP Super Cache
- W3 Total Cache
- WP Rocket
- LiteSpeed Cache

### How do I block Google Analytics?

Wrap your Google Analytics code in a script tag with the appropriate attributes:

```html
<script type="text/plain" data-cookie-category="analytics">
// Your GA code here
</script>
```

### Can users change their preferences later?

Yes! Add a link anywhere on your site:

```html
<a href="#" onclick="CCMRevokeConsent(); return false;">
    Manage Cookie Preferences
</a>
```

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Changelog

### Version 1.0.0
- Initial release
- Cookie banner with customizable design
- Three cookie categories (Essential, Analytics, Marketing)
- Admin settings page
- Consent logging
- JavaScript API
- GDPR and CCPA compliance features

## Support

For support, please open an issue on the [GitHub repository](https://github.com/yourusername/cookie-compliance-manager).

## License

This plugin is licensed under the MIT License. See LICENSE file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

Developed with ❤️ for WordPress community

## Privacy

This plugin stores:
- User consent decisions (essential for compliance)
- IP addresses (for consent verification)
- User agents (for logging purposes)
- Timestamps (for audit trails)

All data is stored in your WordPress database and never transmitted to external servers.

## Disclaimer

While this plugin helps you comply with GDPR and CCPA regulations, it's recommended to consult with a legal professional to ensure full compliance with all applicable laws and regulations.
