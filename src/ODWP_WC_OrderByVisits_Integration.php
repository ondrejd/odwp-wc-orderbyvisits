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

if( ! class_exists( 'ODWP_WC_OrderByVisits_Integration' ) ) :
    /**
     * Implementation of WooCommerce integration.
     * @since 0.1.0
     */
    class ODWP_WC_OrderByVisits_Integration extends WC_Integration {

        /**
         * @var string $enable Possible values ['yes', 'no'].
         * @since 0.1.0
         */
        protected $enable = 'no';

        /**
         * @var string $enable_random Possible values ['yes', 'no'].
         * @since 0.2.5
         */
        protected $enable_random = 'no';

        /**
         * Constructor.
         * @return void
         * @since 0.1.0
         * @uses add_action()
         * @uses add_filter()
         */
        public function __construct() {
            $this->id = ODWP_WC_ORDERBYVISITS;
            $this->method_title = __( 'Order By Visits', 'odwp-wc-orderbyvisits' );
            $this->method_description = __( 'Options for <strong>Order By Visits</strong> plugin.', 'odwp-wc-orderbyvisits' );

            $this->init_form_fields();
            $this->init_settings();

            $this->enable = $this->get_option( 'enable' );
            $this->enable_random = $this->get_option( 'enable_random' );

            add_action( 'woocommerce_update_options_integration_'. $this->id, [$this, 'process_admin_options'] );
            add_filter( 'woocommerce_settings_api_sanitized_fields_'.$this->id, [$this, 'sanitize_settings'] );
        }

        /**
         * @return boolean Returns TRUE if our custom ordering is enabled.
         * @since 0.2.5
         */
        public function is_enabled() {
            return ( $this->enable === 'yes' );
        }

        /**
         * @return boolean Returns `TRUE` if random ordering for products is enabled.
         * @since 0.2.5
         */
        public function is_enabled_random() {
            return ( $this->enable_random === 'yes' );
        }

        /**
         * Initialize integration settings form fields.
         * @return void
         * @since 0.1.0
         */
        public function init_form_fields() {
            $this->form_fields = [
                'enable' => [
                    'title'             => __( 'Enable Order By Visits', 'odwp-wc-orderbyvisits' ),
                    'type'              => 'checkbox',
                    'description'       => __( 'Check if you want to start using this plugin.', 'odwp-wc-orderbyvisits' ),
                    'desc_tip'          => true,
                    'default'           => 'yes'
                ],
                'enable_random' => [
                    'title'             => __( 'Enable random ordering', 'odwp-wc-orderbyvisits' ),
                    'type'              => 'checkbox',
                    'description'       => __( 'Check if you want random ordering for products with same count of visits.', 'odwp-wc-orderbyvisits' ),
                    'desc_tip'          => true,
                    'default'           => 'yes'
                ],
                'generate_btn' => [
                    'title'             => __( 'Generate order', 'odwp-wc-orderbyvisits' ),
                    'type'              => 'button',
                    'custom_attributes' => [],
                    'description'       => __( 'Generate random order values for all products. This can be time consuming according to total count of products.', 'odwp-wc-orderbyvisits' ),
                    'desc_tip'          => true,
                ]
            ];
        }

        /**
         * Generates HTML for the button.
         * @link https://docs.woothemes.com/document/implementing-wc-integration/
         * @param string $key
         * @param array $data
         * @return void
         * @since 0.2.5
         * @todo Move inline CSS into `assets/css/admin.css`!
         * @uses wp_parse_args()
         * @uses wp_kses_post()
         */
        public function generate_button_html( $key, $data ) {
            $field = 'generate_btn';
            $data = wp_parse_args(
                ODWP_WC_ORDERBYVISITS . $this->id . '_' . $key,
                [
                    'class'             => 'button-secondary',
                    'css'               => '',
                    'custom_attributes' => ['disabled' => 'disabled'],
                    'desc_tip'          => true,
                    'description'       => __( 'Update meta values of all products', 'odwp-wc-orderbyvisits' ),
                    'title'             => __( 'Update meta of products', 'odwp-wc-orderbyvisits' ),
                ]
            );

            ob_start();
?>
<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?php echo esc_attr( $field ) ?>"><?php echo wp_kses_post( $data['title'] ) ?></label>
        <?php echo $this->get_tooltip_html( $data ) ?>
    </th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] )?></span></legend>
            <button class="<?php echo esc_attr( $data['class'] ) ?>" type="button" name="<?php echo esc_attr( $field ) ?>" id="<?php echo esc_attr( $field ) ?>" style="<?php echo esc_attr( $data['css'] ) ?>" <?php echo $this->get_custom_attribute_html( $data ) ?>>
                <?php echo wp_kses_post( $data['title'] ) ?>
            </button>
            <?php echo $this->get_description_html( $data ) ?>
            <img id="<?php echo ODWP_WC_ORDERBYVISITS . '_progress_img' ?>" src="<?php echo get_site_url() . '/wp-admin/images/wpspin_light'; ?>" style="display: none; position: relative; top: 6px;"/>
            <p id="<?php echo ODWP_WC_ORDERBYVISITS . '_progress_msg' ?>" class="description" style="display:none;"><?php _e( 'Please wait until the button is ready again and the result message is displayed.', 'odwp-wc-orderbyvisits' ) ?></p>
        </fieldset>
    </td>
</tr>
<?php
            return ob_get_clean();
        }

        /**
         * Santize our settings
         * @param array $settings
         * @return array
         * @see process_admin_options()
         * @since 0.1.0
         */
        public function sanitize_settings( $settings ) {
            $opts = [
                'enable'        => 'no',
                'enable_random' => 'no',
            ];

            if( ! is_array( $settings ) ) {
                return $opts;
            }

            foreach( $opts as $key => $val ) {
                if( array_key_exists( $key, $settings ) ) {
                    $opts[$key] = ( strtolower( $settings[$key] ) === 'yes' ) ? 'yes' : 'no';
                }
            }

            return $opts;
        }
    }
endif;
