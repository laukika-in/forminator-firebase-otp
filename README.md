=== Forminator Firebase OTP ===
Contributors: Laukika  
Tags: firebase, otp, forminator, sms, phone verification  
Requires at least: 5.5  
Tested up to: 6.5  
Stable tag: 1.4  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add Firebase OTP-based phone verification to selected Forminator forms with configurable Firebase settings, field mapping, and seamless frontend integration.

== Description ==

**Forminator Firebase OTP** lets you easily enable OTP-based phone verification on your Forminator forms using Firebase Authentication.

- Works with Forminator form fields (`text`, `number`, `phone`)
- Admin settings page to configure Firebase credentials
- Automatically detects all Forminator forms and phone fields
- Supports multiple phone fields per form
- Real-time frontend validation using Firebase JS SDK
- Invisible reCAPTCHA protection
- Fully customizable UI and styling via CSS

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/forminator-firebase-otp` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Firebase OTP** under the WordPress Admin Menu.
4. Enter your Firebase configuration and map the desired forms and phone fields.
5. Done!

== Frequently Asked Questions ==

= Does this plugin send SMS? =  
No. Firebase Authentication handles OTP generation and SMS delivery.

= Does this work with all field types? =  
It works with `text`, `phone`, and `number` fields. You can map them via the admin panel.

= Can I style the OTP box? =  
Yes. Frontend CSS is enqueued and can be overridden or extended in your theme.

== Screenshots ==

1. Firebase settings panel in admin
2. Phone field mapping UI
3. OTP verification UI in Forminator form

== Changelog ==

= 1.4 =

- Added dynamic plugin versioning
- Improved form field detection
- Removed hardcoded country select in favour of native Forminator fields
- Cleaner UI with CSS

= 1.0 =

- Initial release

== Upgrade Notice ==

= 1.4 =
Recommended upgrade for better field compatibility and frontend flexibility.
