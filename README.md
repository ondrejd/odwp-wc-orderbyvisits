# Simple Stats for WooCommerce

Simple plugin for [WordPress](https://wordpress.org/) with [WooCommerce](https://wordpress.org/plugins/woocommerce/) that enables simple statistics on e-shop products.

## Description

Main features:

- statistics for visits per WooCommerce's product detail
- statistics for count of sellings per WooCommerce's product 

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

## Changelog/ToDo

### ~1.0

* [ ] tracking statistics about e-shop categories
* [ ] more statistics about products - (added/removed from cart etc)
* [ ] publish on [WordPress Plugins](https://wordpress.org/plugins) site

### 0.2.0

* [x] count how many times were project's detail viewed
* [ ] count how many times were project added to the cart
* [ ] create documentation for adding custom products sorting in _FE_ catalog
* [ ] create Czech version of documentation

### 0.1.1

* fixed `uninstall` hook
* fix integration (settings are not saved)

### 0.1.0

* initial public version
* source codes added to __GitHub__
