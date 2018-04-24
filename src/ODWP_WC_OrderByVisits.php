<?php
/**
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-wc-orderbyvisits for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-orderbyvisits
 * @since 0.0.1
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'ODWP_WC_OrderByVisits' ) ) :
    /**
     * Main class of the plugin.
     * @since 0.1.0
     */
    class ODWP_WC_OrderByVisits {

        /**
         * @const int Parametr "min" for {@see rand} function.
         * @since 0.5.0
         */
        const MIN_RAND = 9;

        /**
         * @const int Parametr "max" for {@see rand} function.
         * @since 0.5.0
         */
        const MAX_RAND = 999;

        /**
         * Constructor.
         * @return void
         * @since 0.1.0
         * @uses add_action()
         * @uses register_activation_hook()
         * @uses register_deactivation_hook()
         * @uses register_uninstall_hook()
         */
        public function __construct() {
            register_activation_hook( ODWP_WC_ORDERBYVISITS_FILE, [__CLASS__, 'activate'] );
            register_deactivation_hook( ODWP_WC_ORDERBYVISITS_FILE, [__CLASS__, 'deactivate'] );
            register_uninstall_hook( ODWP_WC_ORDERBYVISITS_FILE, [__CLASS__, 'uninstall'] );

            add_action( 'init', [$this, 'load_plugin_textdomain'] );
            add_action( 'plugins_loaded', [$this, 'init'] );
        }

        /**
         * @internal Activates the plugin.
         * @global wpdb $wpdb
         * @return void
         * @since 0.5.0
         */
        public static function activate() {
            self::update_all_products();
        }

        /**
         * @internal Deactivates the plugin.
         * @global wpdb $wpdb
         * @return void
         * @since 0.5.0
         */
        public static function deactivate() {
            //...
        }

        /**
         * @internal Return our WooCommece integration.
         * @return ODWP_WC_OrderByVisits_Integration|null
         * @since 0.2.5
         * @uses WC()
         */
        public static function get_integration() {
            $integrations = WC()->integrations->get_integrations();

            if( array_key_exists( ODWP_WC_ORDERBYVISITS, $integrations ) ) {
                return $integrations[ODWP_WC_ORDERBYVISITS];
            }

            return null;
        }

        /**
         * @internal Uninstall the plugin.
         * @return void
         * @since 0.5.0
         */
        public static function uninstall() {
            if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
                return;
            }

            //...
        }

        /**
         * @internal Initialize localization.
         * @return void
         * @since 0.2.0
         * @uses load_plugin_textdomain()
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain(
                ODWP_WC_ORDERBYVISITS,
                false,
                ODWP_WC_ORDERBYVISITS . '/languages'
            );
        }

        /**
         * @internal Initialize plugin.
         * @return void
         * @since 0.1.0
         * @uses add_action()
         * @uses add_filter()
         * @uses is_admin()
         */
        public function init() {
            // add our WooCommerce integration form
            if( class_exists( 'WC_Integration' ) ) {
                include_once ODWP_WC_ORDERBYVISITS_DIR . '/src/ODWP_WC_OrderByVisits_Integration.php';
                add_filter( 'woocommerce_integrations', [$this, 'add_integration'] );

                if( is_admin() ) {
                    // add JavaScript for "Generate order" button in integration page
                    add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'] );
                    // add callback for our Ajax action triggered by the button
                    add_action( 'wp_ajax_odwpwcobv_generate_random', [$this, 'admin_ajax_generate_random'] );
                }
            }

            // count product detail pages visits
            add_action( 'woocommerce_after_single_product_summary', [$this, 'count_detail_visit'] );
            // modify products orderby
            add_filter( 'woocommerce_default_catalog_orderby_options', [$this, 'modify_orderby'] );
            add_filter( 'woocommerce_catalog_orderby', [$this, 'modify_orderby'] );
            add_filter( 'woocommerce_get_catalog_ordering_args', [$this, 'add_new_shop_ordering_args'] );

            if( is_admin() ) {
                // update product's meta after save
                add_action( 'save_post', [$this, 'update_post_meta'], 101, 2 );
            }
        }

        /**
         * @internal Add a new integration to WooCommerce.
         * @param array $integrations
         * @return aray
         * @since 0.2.5
         */
        public function add_integration( $integrations ) {
            $integrations[] = 'ODWP_WC_OrderByVisits_Integration';

            return $integrations;
        }

        /**
         * Hook for "admin_enqueue_scripts" action.
         * @param string $hook
         * @return void
         * @since 0.5.0
         */
        public function admin_enqueue_scripts( $hook ) {
            if( $hook != 'woocommerce_page_wc-settings' ) {
                // We need this only on our integration page, e.g. WC Settings
                return;
            }

            $js_file = 'assets/js/admin.js';
            $js_path = ODWP_WC_ORDERBYVISITS_DIR . '/' . $js_file;

            if( file_exists( $js_path ) && is_readable( $js_path ) ) {
                $js_url = plugins_url( $js_file, ODWP_WC_ORDERBYVISITS_FILE );
                wp_enqueue_script( ODWP_WC_ORDERBYVISITS, $js_url, ['jquery'] );
                wp_localize_script( ODWP_WC_ORDERBYVISITS, 'odwpwcobv', [
                    'msg_ok'  => __( 'Random values were generated successfully.', 'odwp-wc-orderbyvisits' ),
                    'msg_err' => __( 'There was an error while generating random values. Please try again or contact your administrator.', 'odwp-wc-orderbyvisits' ),
                ] );
            }

            $css_file = 'assets/css/admin.css';
            $css_path = ODWP_WC_ORDERBYVISITS_DIR . '/' . $css_file;

            if( file_exists( $css_path ) && is_readable( $css_path ) ) {
                wp_enqueue_style( ODWP_WC_ORDERBYVISITS, plugins_url( $css_file, ODWP_WC_ORDERBYVISITS_FILE ) );
            }
        }

        /**
         * @internal Hook for `woocommerce_after_single_product_summary` action.
         * @global wpdb $wpdb
         * @return void
         * @since 0.2.0
         * @uses get_post_ID()
         * @uses update_post_meta()
         */
        public function count_detail_visit() {
            global $wpdb;

            $project_id = intval( get_the_ID() );
            $viewed = intval( get_post_meta( $project_id, '_odwpwcobv_viewed', true ) );

            update_post_meta( $project_id, '_odwpwcobv_viewed', $viewed );
        }

        /**
         * @internal Modifies sorting settings (used for both administration and FE).
         * @param array $orderby
         * @return array
         * @since 0.0.1
         */
        public function modify_orderby( $orderby ) {
            if( self::get_integration()->is_enabled() ) {
                $orderby['odwpwcobv_by_views'] = __( 'Order by popularity (views)', 'odwp-wc-orderbyvisits' );
                $orderby['odwpwcobv_by_sales'] = __( 'Order by popularity (sales)', 'odwp-wc-orderbyvisits' );
            }

            return $orderby;
        }

        /**
         * @internal  Adds new catalog ordering.
         * @param array $sort_args
         * @return array
         * @since 0.2.0
         * @uses get_option()
         * @uses woocommerce_clean()
         * @uses getapply_filters_option()
         */
        public function add_new_shop_ordering_args( $sort_args ) {
            if( ! self::get_integration()->is_enabled() ) {
                return $sort_args;
            }

            $orderby = filter_input( INPUT_GET, 'orderby' );
            $orderby_default = get_option( 'woocommerce_default_catalog_orderby' );
            $orderby_value = ! is_null( $orderby )
                    ? woocommerce_clean( $orderby )
                    : apply_filters( 'woocommerce_default_catalog_orderby', $orderby_default );

            $sort_args['orderby'] = ['meta_value_num' => 'DESC'];

            if( self::get_integration()->is_enabled_random() ) {
                array_push( $sort_args['orderby'], 'rand' );
            }

            if( 'odwpwcobv_by_views' == $orderby_value ) {
                $sort_args['meta_key'] = '_odwpwcobv_viewed';
            }
            elseif( 'odwpwcobv_by_sales' == $orderby_value ) {
                $sort_args['meta_key'] = 'total_sales';
            }

            return $sort_args;
        }

        /**
         * @internal Adds our order meta key to all WooCommerce products.
         * @global wpdb $wpdb
         * @return void
         * @since 0.2.0
         * @uses update_post_meta()
         */
        public static function update_all_products() {
            global $wpdb;

            $integration = self::get_integration();
            $product_ids = new WP_Query( [
                'post_type' => 'product',
                'post_status' => 'publish',
                'fields' => 'ids',
            ] );
            $use_rand = false;

            if( ! is_array( $product_ids ) ) {
                return;
            }

            if( ( $integration instanceof ODWP_WC_OrderByVisits_Integration ) ) {
                $use_rand = self::get_integration()->is_enabled_random();
            }

            foreach( $product_ids as $product_id ) {
                $viewed = $use_rand ? rand( self::MIN_RAND, self::MAX_RAND ) : 0;
                update_post_meta( $product_id, '_odwpwcobv_viewed', $viewed );
            }
        }

        /**
         * @internal Updates order meta key on the WC product.
         * @param integer $post_id
         * @param WP_Post $post
         * @return void
         * @since 0.2.0
         * @uses wp_is_post_revision()
         * @uses wp_is_post_autosave()
         * @uses current_user_can()
         * @uses update_post_meta()
         */
        public function update_post_meta( $post_id, $post ) {
            if( is_int( wp_is_post_revision( $post_id ) ) ) {
                return;
            }

            if( is_int( wp_is_post_autosave( $post_id ) ) ) {
                return;
            }

            if( defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
                return $post_id;
            }

            if( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }

            if( $post->post_type != 'product' ) {
                return $post_id;
            }

            $viewed = 0;
            if( self::get_integration()->is_enabled_random() ) {
                $viewed = rand( self::MIN_RAND, self::MAX_RAND );
            }

            update_post_meta( $post_id, '_odwpwcobv_viewed', $viewed );
        }

        /**
         * @internal Callback for Ajax action on "Generate order" button.
         * @return void
         * @see ODWP_WC_OrderByVisits::add_admin_footer_js()
         * @since 0.2.5
         * @uses current_user_can
         * @uses wp_die
         */
        function admin_ajax_generate_random() {
            if( current_user_can( 'edit_posts' ) ) {
                self::update_all_products();
                echo 'OK';
            } else {
                echo 'ERR';
            }

            wp_die();
        }
    }
endif;

/**
 * @var ODWP_WC_OrderByVisits $ODWP_WC_OrderByVisits
 */
$ODWP_WC_OrderByVisits = new ODWP_WC_OrderByVisits();
