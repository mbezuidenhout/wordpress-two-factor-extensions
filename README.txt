=== Plugin Name ===
Contributors: mbezuidenhout
Donate link: https://www.facebook.com/marius.bezuidenhout1
Tags: two-factor, sms, text, two factor, two step, authentication, login
Requires at least: 4.3
Tested up to: 5.4
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin provides extended features to the Two-Factor plugin.

== Description ==

Two-Factor-Extensions provides sms functionality to the Two-Factor plugin. In order to send sms messages this plugin
will require a plugin that provides sms messaging features. Currently it supports only supports WP SMS.

== Changelog ==

= 1.1.5 =
* Fix: Required plugin testing for WP SMS and Two Factor

= 1.1.4 =
* Fix: System failed to send SMS messages.

= 1.1.3 =
* Fix: System disabled all other alternative authentication methods.

= 1.1.2 =
* Fix: Removed the disablement of alternative two factor methods.

= 1.1.1 =
* Fix: Added fix supplied by @bfritton for sending using WP_SMS extension