=== Order By Visits ===
Contributors: Ondřej Doněk
Donate link: https://github.com/ondrejd/odwp-wc-orderbyvisits
License: Mozilla Public License 2.0
Tags: woocommerce,statistics
Requires at least: 4.3
Tested up to: 4.8.2
Stable tag: 0.5.0

Plugin for WordPress and WooCommerce that adds new products orderby rules - Order by popularity (views) and Order by popularity (sales).

== Description ==

Plugin for WordPress and WooCommerce that adds new products orderby rules - Order by popularity (views)and Order by popularity (sales).

Main features:

* simple statistics about how many times was product displayed,
* new products _orderby_ rules - Order by popularity (views) and Order by popularity (sales),
* Czech and English localization.


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload plugin's folder `odwp-wc-orderbyvisits` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. For other details see [plugin's home page](https://github.com/ondrejd/odwp-wc-orderbyvisits)


== Frequently Asked Questions ==

= When I should use this plugin? =

Whenever you want some more products orderby rules on WooCommerce shop page.

= Why I should want these simple statistic data? =

Just count of display of product's detail page - value is saved as a product's meta value.


== Screenshots ==

1. `screenshot-1.png`
2. `screenshot-2.png`
3. `screenshot-3.png`


== License ==

This Source Code is subject to the terms of the GNU General Public License 3.0.


== Changelog ==

= 0.5.0 =
* code refactored and fixed
* tested on WordPress 4.8.2 a WooCommerce 3.1.2
* released on GitHub

= 0.3.0 =
* tested on WordPress 4.6.1
* renamed to "Order By Visits"

= 0.2.10 =
* fixed bug that occured after plugin's activation
* updated localization

= 0.2.9 =
* added _Enable CRON_ option and corresponding functionality

= 0.2.6 =
* fixed code in `ODWP_WC_SimpleStats_Integration`

= 0.2.5 =
* fixed that `enable` options was not used
* added option `enable_random` for enabling/disabling random order for products with same visits count.
* added button for generating random order values

= 0.2.0 =
* count how many times were project's detail viewed
* count how many times were project added to the cart
* adding our custom products ordering (*Popularity (visits)*)
* added Czech localization

= 0.1.1 =
* fixed `uninstall` hook
* fix integration (settings are not saved)

= 0.1.0 =
* initial public version
* source codes added to GitHub
