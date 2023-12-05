=== Plugin Name ===
Contributors: robertutzu
Donate link: https://paypal.me/gheorghiucezarrobert
Tags: factura, facturare, persoana juridica, persoana fizica, woocommerce
Requires at least: 4.3.0
Requires PHP: 5.6
Tested up to: 6.4
WC requires at least: 4.0.0
WC tested up to: 7.2
Stable tag: 1.7.0
License: GPL v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Automatically adds Romanian billing fields to woocommerce checkout
== Description ==

This plugin adds Romanian billing fields to woocommerce checkout.
Also it verifies if it the company CUI/CIF exists in the ANAF database

Adds 4 custom fields for Romanian Legislation:
CIF/CUI
Registration number
Bank account number
Bank name

Also the fields are displayed in the adress billing beneath Company name - on chekcout page, thank you page, admin orders and emails.

You can also modify this fields as admin through orders or as a user from my-account page -> my-adresses.

See demo here: <a href="https://tastewp.com/new?pre-installed-plugin-slug=woocommerce%2Cromanian-billing-fields&redirect=plugins.php&ni=true">Romanian Billing Fields Demo</a>
Vezi demo aici: <a href="https://tastewp.com/new?pre-installed-plugin-slug=woocommerce%2Cromanian-billing-fields&redirect=plugins.php&ni=true">Romanian Billing Fields Demo</a>

== Installation ==

Please see [Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins) in the WordPress Codex.

Dupa instalare nu mai trebuie sa mai faci absolut nimic, campurile o sa apara pe pagina de checkout.


== Changelog ==
** 1.7.0 **
- Added support for CUI/CIF verification through ANAF API call
- Added support for HPOS
- Changed from radio buttons to select input PF/PJ
- Removed Bank Account Number and Bank Name

** 1.6.3 **
- Support check

** 1.6.2 **
- Support check

** 1.6.1 **
- Support check

** 1.6 **
- Support check

** 1.5 **
- Fixed admin field show.

** 1.4 **
- Fixed input save.

** 1.3 **
- Fixed tags.

** 1.2 **
- Fixed compatibility with WooCommerce 4.*

** 1.1 **
- Fixed error of get_order.

** 1.0 **
- Initial release
