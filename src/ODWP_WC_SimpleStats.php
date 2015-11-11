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
   * @uses add_action()
   * @uses register_activation_hook()
   * @uses register_uninstall_hook()
   */
  public function __construct() {
    register_activation_hook(ODWP_WC_SIMPLESTATS_FILE, 'odwpwcss_activate');
    register_uninstall_hook(ODWP_WC_SIMPLESTATS_FILE, 'odwpwcss_uninstall');

    add_action('init', array($this, 'load_plugin_textdomain'));
    add_action('plugins_loaded', array($this, 'init'));
  } // end __construct()

  /**
   * Initialize localization.
   *
   * @return void
   * @since 0.2.0
   * @uses load_plugin_textdomain()
   */
  public function load_plugin_textdomain() {
    $path = ODWP_WC_SIMPLESTATS.'/languages';
    load_plugin_textdomain(ODWP_WC_SIMPLESTATS, false, $path);
  } // end load_plugin_textdomain()

  /**
   * Initialize plug-in.
   *
   * @return void
   * @since 0.1.0
   * @uses add_action()
   * @uses add_filter()
   * @uses is_admin()
   */
  public function init() {
    // add our WooCommerce integration form
    if (class_exists('WC_Integration')) {
      include_once dirname(__FILE__).'/ODWP_WC_SimpleStats_Integration.php';
      add_filter('woocommerce_integrations', array($this, 'add_integration'));

      if (is_admin()) {
        // add JavaScript for "Generate order" button in integration page
        add_action('admin_footer', array($this, 'add_admin_footer_js'));
        // add callback for our Ajax action
        add_action('wp_ajax_odwpwcss_generate_random', array($this, 'admin_ajax_generate_random'));
      }
    }

    // count product detail pages visits
    add_action('woocommerce_after_single_product_summary', array($this, 'count_detail_visit'));
    // count added to cart action per product
    add_action('woocommerce_add_to_cart', array($this, 'count_add_to_cart'));
    // modify product sorting settings
    add_filter('woocommerce_default_catalog_orderby_options', array($this, 'modify_sorting_settings'));
    // add new sorting options to orderby dropdown (FE)
    add_filter('woocommerce_catalog_orderby', array($this, 'modify_sorting_settings'));
    // add new product sorting arguments
    add_filter('woocommerce_get_catalog_ordering_args', array($this, 'add_new_shop_ordering_args'));

    if (is_admin()) {
      // update product's meta keyes with our order value
      add_action('save_post', array($this, 'update_post_meta'), 101, 2);
    }

    add_action(ODWP_WC_SIMPLESTATS . '-cron_event_hook', array($this, 'cron_event'));
  } // end init()

  /**
   * @return void
   * @since 0.2.9
   */
  public static function cron_event() {
    if (self::get_integration()->is_enabled_cron() !== true) {
      return;
    }

    self::auto_update_all_posts_meta();
  } // end cron_event()

  /**
   * Returns our integration.
   *
   * @return ODWP_WC_SimpleStats_Integration|null
   * @since 0.2.5
   * @static
   * @uses WC()
   */
  public static function get_integration() {
    $integrations = WC()->integrations->get_integrations();
    if (array_key_exists(ODWP_WC_SIMPLESTATS, $integrations)) {
      return $integrations[ODWP_WC_SIMPLESTATS];
    }

    return null;
  } // end get_integration()

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
   * @global wpdb $wpdb
   * @return void
   * @since 0.2.0
   * @uses get_post_ID()
   * @uses update_post_meta()
   */
  public function count_detail_visit() {
    global $wpdb;
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

      if (self::get_integration()->is_enabled_cron() !== true) {
        update_post_meta($pid, '_odwpwcss_viewed', 1);
      }
    } else {
      $viewed = (int)$row->viewed + 1;
      $wpdb->query(
        'UPDATE `'.$table.'` SET `viewed`='.$viewed.' WHERE `post_ID`='.$pid.' '
      );

      if (self::get_integration()->is_enabled_cron() !== true) {
        update_post_meta($pid, '_odwpwcss_viewed', $viewed);
      }
    }
  } // end count_detail_visit()

  /**
   * Hook for WooCommerce's `woocommerce_add_to_cart` action.
   * Save into the database that product was added to the cart.
   *
   * @global wpdb $wpdb
   * @param string $cart_item_key
   * @return void
   * @since 0.2.0
   * @uses WC()
   */
  public function count_add_to_cart($cart_item_key) {
    // Pozn. Nezohlednujeme pocet pridanych kusu...
    global $wpdb;
    $table = $wpdb->prefix . 'simplestats';

    $cart_item = WC()->cart->get_cart_item($cart_item_key);
    if (!array_key_exists('product_id', $cart_item)) {
      return;
    }

    $pid = $cart_item['product_id'];
    $row = $wpdb->get_row(
      'SELECT * FROM `'.$table.'` WHERE `post_ID`='.$pid.' '
    );

    if (is_null($row)) {
      $wpdb->query(
        'INSERT INTO `'.$table.'` VALUES (NULL,'.$pid.',0,1) '
      );
    } else {
      $selled = (int)$row->selled + 1;
      $wpdb->query(
        'UPDATE `'.$table.'` SET `selled`='.$selled.' WHERE `post_ID`='.$pid.' '
      );
    }
  } // end count_add_to_cart()

  /**
   * Modify sorting settings (used for both administration and FE).
   *
   * @param array $sortby
   * @return array
   */
  public function modify_sorting_settings($sortby) {
    if (self::get_integration()->is_enabled()) {
      $sortby['by_views'] = __('Popularity (visits)', ODWP_WC_SIMPLESTATS);
    }
    
    return $sortby;
  } // end modify_sorting_settings($sortby)

  /**
   * Add new catalog ordering (Popularity (detail's visits)).
   *
   * @param array $sort_args
   * @return array
   * @since 0.2.0
   * @uses get_option()
   * @uses woocommerce_clean()
   * @uses getapply_filters_option()
   */
  public function add_new_shop_ordering_args($sort_args) {
    $orderby = filter_input(INPUT_GET, 'orderby');
    $orderby_default = get_option('woocommerce_default_catalog_orderby');
    $orderby_value = !is_null($orderby) 
      ? woocommerce_clean($orderby) 
      : apply_filters('woocommerce_default_catalog_orderby', $orderby_default);

    if ('by_views' == $orderby_value && self::get_integration()->is_enabled()) {
      $our_orderby = array('meta_value_num' => 'DESC');

      if (self::get_integration()->is_enabled_random()) {
        array_push($our_orderby, 'rand');
      }

      $sort_args['orderby'] = $our_orderby;
      $sort_args['meta_key'] = '_odwpwcss_viewed';
    }

    return $sort_args;
  } // end wc_popularity_shop_ordering($sort_args)

  /**
   * Add our order meta key to all WooCommerce products.
   *
   * @global wpdb $wpdb
   * @return void
   * @since 0.2.0
   * @static
   * @uses update_post_meta()
   */
  public static function auto_update_all_posts_meta() {
    global $wpdb;
    $table = $wpdb->prefix . 'simplestats';
    
    // Get all products with count of viewed
    $all_products = $wpdb->get_results(
      'SELECT '.
      '  `t1`.`ID` AS `post_id`,'.
      '  `t2`.`viewed` AS `viewed`,'.
      '  `t2`.`selled` AS `selled` '.
      'FROM `'.$wpdb->posts.'` AS `t1` '.
      'LEFT JOIN `'.$table.'` AS `t2` ON `t2`.`post_ID` = `t1`.`ID` '.
      'WHERE `t1`.`post_type`="product" '
    );

    if (!is_array($all_products)) {
      return;
    }

    $integration = self::get_integration();
    $use_rand = false;
    if (($integration instanceof ODWP_WC_SimpleStats_Integration)) {
      $use_rand = self::get_integration()->is_enabled_random();
    }

    foreach ($all_products as $p) {
      $viewed = (int)$p->viewed;
      if ($use_rand === true) {
        $viewed = rand(0, 99);
      }

      update_post_meta($p->post_id, '_odwpwcss_viewed', $viewed);
      // TODO update_post_meta($p->post_id, '_odwpwcss_selled', (int)$p->selled);
    }
  } // end auto_update_all_posts_meta()

  /**
   * Update order meta key to all WooCommerce products.
   *
   * @global wpdb $wpdb
   * @param integer $post_id
   * @param WP_Post $post
   * @return void
   * @since 0.2.0
   * @uses wp_is_post_revision()
   * @uses wp_is_post_autosave()
   * @uses current_user_can()
   * @uses update_post_meta()
   */
  public function update_post_meta($post_id, $post) {
    if (is_int(wp_is_post_revision($post_id))) return;
    if (is_int(wp_is_post_autosave($post_id))) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    if (!current_user_can('edit_post', $post_id)) return $post_id;
    if ($post->post_type != 'product') return $post_id;

    $viewed = (self::get_integration()->is_enabled_random()) ? rand(0, 99) : 0;
    update_post_meta($post_id, '_odwpwcss_viewed', $viewed);
    // TODO update_post_meta($post_id, '_odwpwcss_selled', 0);
  } // end update_post_meta($post_id, $post)

  /**
   * Add JavaScript for "Generate order" button in integration page.
   *
   * @return void
   * @since 0.2.5
   */
  public function add_admin_footer_js() {?>
    <script type="text/javascript">
jQuery(document).ready(function($) {
  $('#<?= ODWP_WC_SIMPLESTATS?>odwp-wc-simplestats_generate_btn').prop('disabled', false).click(function() {
    // show progress image and disable the button
    jQuery(this).prop('disabled', true);
    jQuery('#<?= ODWP_WC_SIMPLESTATS?>_progress_img').show();
    jQuery('#<?= ODWP_WC_SIMPLESTATS?>_progress_msg').show();

    // since WP 2.8 is `ajaxurl` always defined in the admin header 
    // and points to `admin-ajax.php`
    jQuery.post(ajaxurl, { 'action': 'odwpwcss_generate_random' }, function(response) {
      jQuery('#<?= ODWP_WC_SIMPLESTATS?>odwp-wc-simplestats_generate_btn').prop('disabled', false);
      jQuery('#<?= ODWP_WC_SIMPLESTATS?>_progress_img').hide();

      if (response === 'OK') {
        jQuery('#<?= ODWP_WC_SIMPLESTATS?>_progress_msg').html(
          "<?= __('Random values were generated successfully.', ODWP_WC_SIMPLESTATS)?>"
        );
      } else {
        jQuery('#<?= ODWP_WC_SIMPLESTATS?>_progress_msg').html(
          "<?= __('There was an error while generating random values. Please try again or contact your administrator.', ODWP_WC_SIMPLESTATS)?>"
        );
      }
    });
  });
});
    </script><?php
  } // end add_admin_footer_js()

  /**
   * Callback for Ajax action on "Generate order" button.
   *
   * @return void
   * @see ODWP_WC_SimpleStats::add_admin_footer_js()
   * @since 0.2.5
   * @uses wp_die()
   */
  function admin_ajax_generate_random() {
    if (current_user_can('edit_posts')) {
      self::auto_update_all_posts_meta();
      echo 'OK';
    } else {
      echo 'ERR';
    }

    wp_die();
  } // end admin_ajax_generate_random()
} // End of ODWP_WC_SimpleStats

endif;
