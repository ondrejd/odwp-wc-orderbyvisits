<?php
/**
 * Plugin Name: Simple Stats for WooCommerce
 * Plugin URI: https://github.com/ondrejd/odwp-wc-simplestats
 * Description: Simple plugin for WordPress with WooCommerce that enables simple stats on e-shop products.
 * Version: 0.1.0
 * Author: Ondřej Doněk
 * Author URI: http://ondrejdonek.blogspot.cz/
 * Requires at least: 4.3
 * Tested up to: 4.3.1
 *
 * Text Domain: odwp-wc-simplestats
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-wc-simplestats for the canonical source repository
 * @license https://www.mozilla.org/MPL/2.0/ Mozilla Public License 2.0
 * @package odwp-wc-simplestats
 */


defined('ODWP_WC_SIMPLESTATS') || define('ODWP_WC_SIMPLESTATS', 'odwp-wc-simplestats');


if (!function_exists('odwpwcss_check_requirements')):

/**
 * Check if requirements are met.
 *
 * @internal
 * @link https://developer.wordpress.org/reference/functions/is_plugin_active_for_network/#source-code
 * @return boolean Returns `true` if requirements are met.
 * @since 0.1.0
 * @todo Current solution doesn't work for WPMU... 
 */
function odwpwcss_check_requirements() {
  if (in_array('woocommerce/woocommerce.php', (array) get_option('active_plugins', array()))) {
    return true;
  }

  return false;
} // end odwpwcss_check_requirements()

endif;


if (!function_exists('odwpwcss_deactivate_raw')):

/**
 * Deactivates plugin directly by updating WP option `active_plugins`.
 *
 * @internal
 * @link https://developer.wordpress.org/reference/functions/deactivate_plugins/
 * @return void
 * @since 0.1.0
 * @todo Check if using `deactivate_plugins` whouldn't be better.
 */
function odwpwcss_deactivate_raw() {
  $plugins = get_option('active_plugins');
  $out = array();
  foreach($plugins as $key => $val) {
    if($val != ODWP_WC_SIMPLESTATS.'/'.ODWP_WC_SIMPLESTATS.'.php') {
      $out[$key] = $val;
    }
  }
  update_option('active_plugins', $out);
} // end odwpwcss_deactivate_raw()

endif;


if (!function_exists('odwpwcss_minreq_error')):

/**
 * Shows error in WP administration that minimum requirements were not met.
 *
 * @internal
 * @return void
 * @since 0.1.0
 */
function odwpwcss_minreq_error() {
  echo ''.
    '<div id="'.ODWP_WC_SIMPLESTATS.'_message1" class="error notice is-dismissible">'.
      '<p>'.
        __('The <b>Simple Stats Plugin for Woocommerce</b> plugin requires <b>WooCommerce</b> plugin installed and activated.</p>', ODWP_WC_SIMPLESTATS).
      '</p>'.
    '</div>'.
    '<div id="'.ODWP_WC_SIMPLESTATS.'_message2" class="updated notice is-dismissible">'.
      '<p>'.
        __('Plugin <b>Simple Stats Plugin for Woocommerce</b> was <b>deactivated</b>.', ODWP_WC_SIMPLESTATS).
      '</p>'.
    '</div>';
} // end odwpwcss_minreq_error()

endif;


if (!class_exists('ODWP_WC_SimpleStats')):

/**
 * Main class of the plug-in.
 * 
 * @since 0.1.0
 */
class ODWP_WC_SimpleStats {  
  const ID = 'odwp-wc-simplestats';
  const VERSION = '0.1.0';

  /**
   * Constructor.
   *
   * @return void
   * @since 0.1.0
   */
  public function __construct() {
    register_activation_hook(__FILE__, array($this, 'activate'));
    register_uninstall_hook(__FILE__, array($this, 'uninstall'));

    add_action('plugins_loaded', array($this, 'init'));
  } // end __construct()

  /**
   * Initialize plug-in.
   *
   * @return void
   * @since 0.1.0
   */
  public function init() {
    if (class_exists('WC_Integration')) {
      include_once 'src/ODWP_WC_SimpleStats_Integration.php';
      add_filter('woocommerce_integrations', array($this, 'add_integration'));
    } else {
      //add_action('admin_notices', )
    }
  } // end init()

  /**
   * Add a new integration to WooCommerce.
   *
   * @param array $integrations
   * @return aray
   */
  public function add_integration($integrations) {
    $integrations[] = 'ODWP_WC_SimpleStats_Integration';
    return $integrations;
  } // end add_integration($integrations)

  /**
   * Activates the plug-in.
   *
   * @global wpdb $wpdb
   * @return void
   * @since 0.1.0
   */
  public function activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'simplestats';
    $sql = '' .
      'CREATE TABLE IF NOT EXISTS `'.$table_name.'` (' .
      '  `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , ' .
      '  `post_ID` BIGINT(20) UNSIGNED NULL , ' .
      '  `viewed` BIGINT(20) NOT NULL DEFAULT 0 , ' .
      '  `selled` BIGINT(20) NOT NULL DEFAULT 0 ' .
      ') ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;';
    $wpdb->query($sql);
  } // end activate()

  /**
   * Uninstall.
   *
   * @global wpdb $wpdb
   * @return void
   * @since 0.1.0
   */
  public function uninstall() {
    if (!defined('WP_UNINSTALL_PLUGIN')) {
      return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'simplestats';
    $wpdb->query('DROP TABLE `'.$table_name.'` ');
  } // end uninstall()
} // End of ODWP_WC_SimpleStats

endif;


// ==========================================================================

// Our plug-in is dependant on Woocommerce
if (!odwpwcss_check_requirements()) {
  odwpwcss_deactivate_raw();

  if (is_admin()) {
    add_action('admin_head', 'odwpwcss_minreq_error');
  }

  return;
}

// Everything is OK - initialize the plugin

/**
 * @var ODWP_WC_SimpleStats
 */
$ODWP_WC_SimpleStats = new ODWP_WC_SimpleStats();
