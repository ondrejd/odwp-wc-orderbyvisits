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
    register_activation_hook(ODWP_WC_SIMPLESTATS_FILE, 'odwpwcss_activate');
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

    add_action(
      'woocommerce_after_single_product_summary',
      array($this, 'ws_after_single_product_summary')
    );
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
   * Hook for WooCommerce's `woocommerce_after_single_product_summary` action.
   * Save record about product was viewed into the database.
   *
   * @uses get_post_ID()
   * @global wpdb $wpdb
   * @return void
   * @since 0.2.0
   */
  public function ws_after_single_product_summary() {
    global $post, $wpdb;
    $table = $wpdb->prefix . 'simplestats';

    $pid = (int)get_the_ID();

    if ($pid === 0) {
      return;
    }

    $row = $wpdb->get_row(
      'SELECT * FROM `'.$table.'` WHERE `post_ID`='.$pid.' '
    );

    if (is_null($row)) {
      $wpdb->query(
        'INSERT INTO `'.$table.'` VALUES (NULL,'.$pid.',1,0) '
      );
    } else {
      $viewed = (int)$row->viewed + 1;
      $wpdb->query(
        'UPDATE `'.$table.'` SET `viewed`='.$viewed.' WHERE `post_ID`='.$pid.' '
      );
    }
  } // end ws_after_single_product_summary()
} // End of ODWP_WC_SimpleStats

endif;
