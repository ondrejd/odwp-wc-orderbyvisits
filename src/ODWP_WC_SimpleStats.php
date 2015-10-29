<?php
/**
 * Simple Stats for WooCommerce
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-wc-simplestats for the canonical source repository
 * @license https://www.mozilla.org/MPL/2.0/ Mozilla Public License 2.0
 * @package odwp-wc-simplestats
 */

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
    register_uninstall_hook(ODWP_WC_SIMPLESTATS_FILE, 'odwpwcss_uninstall');

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
      include_once dirname(__FILE__).'/ODWP_WC_SimpleStats_Integration.php';
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
} // End of ODWP_WC_SimpleStats

endif;
