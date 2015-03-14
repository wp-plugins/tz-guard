=== Plugin Name ===
Contributors: templaza,sonnyle
Donate link: http://www.templaza.com/tz-guard-site-security-wordpress-plugin/542.html
Tags: security, protect admin, protect
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: security, checker, tool, wordpress, wordpress.org
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a simple plugin which will help you to security your WordPress site.

== Description ==

The administrator will be protected by a security code.

Furthermore you can define a blacklist IP to refuse connection from spam ip and block the BOT system to access your WordPress site.

== Installation ==

1. Upload `tz-guard.zip` at Add Plugins Menu
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Tools >> TZ Guard >> Done!

== Frequently Asked Questions ==

= How can I access login page with security code? =

Just add security code in your login page url. Note: The first key has to an alphabet character. Ex: temp123
Ex: http://yourdomain.com/wp-login.php?securitycodehere or http://yourdomain.com/wp-admin/?securitycodehere

= May I define range of Black IP? =

Yes, you can use "*" for define range of Black IP. Ex 14.177.126.*

= May I define list of Black IP? =

Yes, you can define your list, one ip per line.
Ex
14.177.126.34
14.177.126.56
14.177.126.93

== Screenshots ==

1. /trunk/screenshot-1.png
2. /trunk/screenshot-2.jpg
3. /trunk/screenshot-3.jpg
4. /trunk/screenshot-4.jpg

== Changelog ==

= 0.1.1 =
* Fix error with wp_redirect function

= 0.1.0 =
* Start Version

== Upgrade Notice ==

= 0.1.1 =
This version fixes a major related bug.  Upgrade immediately.

= 0.1.0 =
First version

== Arbitrary section ==

Note: Please remember your security code. You may not able login to your WordPress if you forget it. The first key of Security Code has to an alphabet character. Ex: temp123