# Simple Stats for WooCommerce

Simple plugin for [WordPress](https://wordpress.org/) with [WooCommerce](https://wordpress.org/plugins/woocommerce/) installed that enables simple visits statistics on e-shop products and add custom products sorting based on them.

## Dontations

If your like this plugin and you want to be maintained and improved more frequently consider donation:

[![Make donation](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif "PayPal - The safer, eaisier way to pay online!")](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ondrejd%40gmail%2ecom&lc=CZ&item_name=ondrejd%2fodwp%2dwc%2dsimplestats&currency_code=CZK&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted)

## Description

Main features:

- statistics for visits per WooCommerce's product detail page
- custom products sorting based on how many times were product's detail viewed

## Installation

This section describes how to install the plugin and get it working.

1. Upload plugin's folder `odwp-wc-simplestats` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. For other details see [plugin's home page](https://github.com/ondrejd/odwp-wc-simplestats)

## Frequently Asked Questions

> When I should use this plug-in?

Whenever you want simple statistics for your WooCommerce installation.

> Why I should want these simple statistic data?

Because you can use them in various ways - for example custom ordering of products.

## Screenshots

1. Screen of _WP Admin_ after successfull installation of our plugin:
   ![Plugin successfully installed.](screenshot-1.png?raw=true "Plugin successfully installed.")
2. Screen of _WP Admin_ after unsuccessfull installation because of <b>WooCommerce</b> is missing:
   ![Plugin not installed - WooCommerce is missing.](screenshot-2.png?raw=true "Plugin not installed - WooCommerce is missing.")
3. Screen with settings of our plugin (_Wp Admin - WooCommerce - Settings_):
   ![Plugin's integration within WooCommerce'](screenshot-3.png?raw=true "Plugin's integration within WooCommerce")
4. Here we are selecting our custom products ordering (_Wp Admin - WooCommerce - Products - Display_):
   ![Selecting our custom WooCommerce products ordering'](screenshot-4.png?raw=true "Selecting our custom WooCommerce products ordering")
5. Here we are selecting our custom products ordering (_front-end_):
   ![Selecting our custom WooCommerce products ordering on FE'](screenshot-5.png?raw=true "Selecting our custom WooCommerce products ordering on FE")

## Changelog/ToDo

### ~1.0

* [ ] tracking statistics about e-shop categories
* [ ] more products statistics and custom sorting - (added/removed from cart etc...)
* [ ] publish on [WordPress Plugins](https://wordpress.org/plugins) site

### 0.2.10

* fixed bug that occured after plugin's activation
* updated localization

### 0.2.9

* added _Enable CRON_ option and corresponding functionality

### 0.2.6

* fixed code in `ODWP_WC_SimpleStats_Integration`

### 0.2.5

* fixed that `enable` options was not used
* added option `enable_random` for enabling/disabling random order for products with same visits count.
* added button for generating random order values

### 0.2.0

* count how many times were project's detail viewed
* count how many times were project added to the cart
* adding our custom products ordering (_Popularity (visits)_)
* add Czech localization

### 0.1.1

* fixed `uninstall` hook
* fix integration (settings are not saved)

### 0.1.0

* initial public version
* source codes added to __GitHub__
