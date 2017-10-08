<?php
/**
 * Plugin Name: Order By Visits
 * Plugin URI: https://github.com/ondrejd/odwp-wc-orderbyvisits
 * Description: Plugin for <a href="https://wordpress.org/" target="_blank">WordPress</a> and <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> that adds new products <em>orderby</em> rules - <strong>Order by popularity (views)</strong> and <strong>Order by popularity (sales)</strong>.
 * Version: 0.5.0
 * Author: Ondrej Donek
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.3
 * Tested up to: 4.8.2
 * Tags: woocommerce,custom product orderby
 * Donate link: https://www.paypal.me/ondrejd
 *
 * Text Domain: odwp-wc-orderbyvisits
 * Domain Path: /languages/
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-wc-orderbyvisits for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-orderbyvisits
 * @since 0.0.1
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

defined( 'ODWP_WC_ORDERBYVISITS' ) || define( 'ODWP_WC_ORDERBYVISITS', 'odwp-wc-orderbyvisits' );
defined( 'ODWP_WC_ORDERBYVISITS_DIR' ) || define( 'ODWP_WC_ORDERBYVISITS_DIR', dirname( __FILE__ ) );
defined( 'ODWP_WC_ORDERBYVISITS_FILE' ) || define( 'ODWP_WC_ORDERBYVISITS_FILE', __FILE__ );
defined( 'ODWP_WC_ORDERBYVISITS_VERSION' ) || define( 'ODWP_WC_ORDERBYVISITS_VERSION', '0.5.0' );


if( ! function_exists( 'odwpwcobv_deactivate_raw' ) ) :
    /**
     * @internal Deactivates plugin directly by updating WP option `active_plugins`.
     * @link https://developer.wordpress.org/reference/functions/deactivate_plugins/
     * @return void
     * @since 0.1.0
     * @todo Check if using `deactivate_plugins` whould be better.
     */
    function odwpwcobv_deactivate_raw() {
        $plugins = get_option( 'active_plugins' );
        $out = [];

        foreach( $plugins as $key => $val ) {
            if( $val != 'odwp-wc-orderbyvisits/odwp-wc-orderbyvisits.php' ) {
                $out[$key] = $val;
            }
        }

        update_option( 'active_plugins', $out );
    }
endif;


// Our plug-in is dependant on WooCommerce
if( ! in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', [] ) ) ) {
    // WooCommerce is not found - deactivate plugin
    odwpwcobv_deactivate_raw();

    if( is_admin() ) {
        add_action( 'admin_head', function() {
?>
    <div class="error notice is-dismissible">
        <p><?php _e( 'Plugin <strong>Order by Visits</strong> requires <strong>WooCommerce</strong> plugin installed and activated. Plugin was <strong>deactivated</strong>.', 'odwp-wc-orderbyvisits' ) ?></p>
    </div>
<?php
        } );
    }
} else {
    // Everything is OK - initialize the plugin
    include_once ODWP_WC_ORDERBYVISITS_DIR . '/src/ODWP_WC_OrderByVisits.php';
}
