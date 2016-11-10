<?php
/**
 * Plugin Name: Order By Visits
 * Plugin URI: https://github.com/ondrejd/odwp-wc-orderbyvisits
 * Description: Plugin for WordPress with WooCommerce installed that enables simple visits statistics on e-shop products and add custom products sorting based on them.
 * Version: 0.3.0
 * Author: Ondřej Doněk
 * Author URI: http://ondrejd.info/
 * Requires at least: 4.3
 * Tested up to: 4.3.1
 *
 * Text Domain: odwp-wc-orderbyvisits
 * Domain Path: /languages/
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-wc-orderbyvisits for the canonical source repository
 * @license https://www.mozilla.org/MPL/2.0/ Mozilla Public License 2.0
 * @package odwp-wc-orderbyvisits
 */


defined('ODWP_WC_ORDERBYVISITS') || define('ODWP_WC_ORDERBYVISITS', 'odwp-wc-orderbyvisits');
defined('ODWP_WC_ORDERBYVISITS_FILE') || define('ODWP_WC_ORDERBYVISITS_FILE', __FILE__);
defined('ODWP_WC_ORDERBYVISITS_VERSION') || define('ODWP_WC_ORDERBYVISITS_VERSION', '0.3.0');


if (!function_exists('odwpwcobv_check_requirements')):

/**
 * Check if requirements are met.
 *
 * @internal
 * @link https://developer.wordpress.org/reference/functions/is_plugin_active_for_network/#source-code
 * @return boolean Returns `true` if requirements are met.
 * @since 0.1.0
 * @todo Current solution doesn't work for WPMU... 
 */
function odwpwcobv_check_requirements() {
  if (in_array('woocommerce/woocommerce.php', (array) get_option('active_plugins', array()))) {
    return true;
  }

  return false;
} // end odwpwcobv_check_requirements()

endif;


if (!function_exists('odwpwcobv_deactivate_raw')):

/**
 * Deactivates plugin directly by updating WP option `active_plugins`.
 *
 * @internal
 * @link https://developer.wordpress.org/reference/functions/deactivate_plugins/
 * @return void
 * @since 0.1.0
 * @todo Check if using `deactivate_plugins` whouldn't be better.
 */
function odwpwcobv_deactivate_raw() {
  $plugins = get_option('active_plugins');
  $out = array();
  foreach($plugins as $key => $val) {
    if($val != ODWP_WC_ORDERBYVISITS.'/'.ODWP_WC_ORDERBYVISITS.'.php') {
      $out[$key] = $val;
    }
  }
  update_option('active_plugins', $out);
} // end odwpwcobv_deactivate_raw()

endif;


if (!function_exists('odwpwcobv_minreq_error')):

/**
 * Shows error in WP administration that minimum requirements were not met.
 *
 * @internal
 * @return void
 * @since 0.1.0
 */
function odwpwcobv_minreq_error() {
  echo ''.
    '<div id="'.ODWP_WC_ORDERBYVISITS.'_message1" class="error notice is-dismissible">'.
      '<p>'.
        __('The <b>Simple Stats Plugin for WooCommerce</b> plugin requires <b>WooCommerce</b> plugin installed and activated.', ODWP_WC_ORDERBYVISITS).
      '</p>'.
    '</div>'.
    '<div id="'.ODWP_WC_ORDERBYVISITS.'_message2" class="updated notice is-dismissible">'.
      '<p>'.
        __('Plugin <b>Simple Stats Plugin for WooCommerce</b> was <b>deactivated</b>.', ODWP_WC_ORDERBYVISITS).
      '</p>'.
    '</div>';
} // end odwpwcobv_minreq_error()

endif;


if (!function_exists('odwpwcobv_activate')):

/**
 * Activates the plugin.
 *
 * @internal
 * @return void
 * @since 0.2.0
 */
function odwpwcobv_activate() {
  global $wpdb;
  $table = $wpdb->prefix . 'simplestats';

  $sql = '' .
    'CREATE TABLE IF NOT EXISTS `'.$table.'` (' .
    '  `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , ' .
    '  `post_ID` BIGINT(20) UNSIGNED NULL , ' .
    '  `viewed` BIGINT(20) NOT NULL DEFAULT 0 , ' .
    '  `selled` BIGINT(20) NOT NULL DEFAULT 0 ' .
    ') ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;';

  $wpdb->query($sql);

  ODWP_WC_OrderByVisits::auto_update_all_posts_meta();
} // end odwpwcobv_activate()

endif;


if (!function_exists('odwpwcobv_uninstall')):

/**
 * Uninstall the plugin.
 *
 * @internal
 * @return void
 * @since 0.1.1
 */
function odwpwcobv_uninstall() {
  if (!defined('WP_UNINSTALL_PLUGIN')) {
    return;
  }

  global $wpdb;
  $table = $wpdb->prefix . 'simplestats';
  $wpdb->query('DROP TABLE `'.$table.'` ');
} // end odwpwcobv_uninstall()

endif;


// Our plug-in is dependant on WooCommerce
if (!odwpwcobv_check_requirements()) {
  odwpwcobv_deactivate_raw();

  if (is_admin()) {
    add_action('admin_head', 'odwpwcobv_minreq_error');
  }

  return;
}

// Everything is OK - initialize the plugin
include_once dirname(__FILE__).'/src/ODWP_WC_OrderByVisits.php';

/**
 * @var ODWP_WC_OrderByVisits
 */
$ODWP_WC_OrderByVisits = new ODWP_WC_OrderByVisits();
