<?php
/**
 * Simple Stats for WooCommerce
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-wc-simplestats for the canonical source repository
 * @license https://www.mozilla.org/MPL/2.0/ Mozilla Public License 2.0
 * @package odwp-wc-simplestats
 */

if (!class_exists('WC_Integration_Demo_Integration')):

/**
 * Implementation of WooCommerce integration.
 *
 * @link https://github.com/BFTrick/woocommerce-integration-demo
 * @since 0.1.0
 */
class ODWP_WC_SimpleStats_Integration extends WC_Integration {
  /**
   * @since 0.1.0
   * @var boolean $enable
   */
  protected $enable = false;

  /**
   * @since 0.2.5
   * @var boolean $enable_random
   */
  protected $enable_random = false;

	/**
	 * Init and hook in the integration.
   *
   * @since 0.1.0
   * @return void
   * @uses add_action()
   * @uses add_filter()
	 */
	public function __construct() {
		global $woocommerce;
		$this->id = ODWP_WC_SIMPLESTATS;
		$this->method_title = __('Simple Stats Plugin for WooCommerce', ODWP_WC_SIMPLESTATS);
		$this->method_description = __('Options for <b>Simple Stats Plugin for WooCommerce</b> plugin.',ODWP_WC_SIMPLESTATS);

		$this->init_form_fields();
		$this->init_settings();

		$this->enable = $this->get_option('enable');
		$this->enable_random = $this->get_option('enable_random');

		add_action('woocommerce_update_options_integration_'. $this->id, array($this, 'process_admin_options'));
		add_filter('woocommerce_settings_api_sanitized_fields_'.$this->id, array($this, 'sanitize_settings'));
	} // end __construct()

  /**
   * Returns `TRUE` if our custom ordering is enabled.
   *
   * @return boolean
   * @since 0.2.5
   */
  public function is_enabled() {
    return ($this->enable === 'yes');
  } // end is_enabled()

  /**
   * Returns `TRUE` is random ordering for products with same count 
   * of visits is enabled.
   *
   * @return boolean
   * @since 0.2.5
   */
  public function is_enabled_random() {
    return ($this->enable_random === 'yes');
  } // end is_enabled_random()

	/**
	 * Initialize integration settings form fields.
	 *
   * @since 0.1.0
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enable' => array(
				'title'             => __('Enable simple stats', ODWP_WC_SIMPLESTATS),
				'type'              => 'checkbox',
				'description'       => __('Check if you want to start using <b>Simple Stats plugin for WooCommerce</b>.', ODWP_WC_SIMPLESTATS),
				'desc_tip'          => true,
				'default'           => 'yes'
			),
      'enable_random' => array(
        'title'             => __('Enable random ordering', ODWP_WC_SIMPLESTATS),
        'type'              => 'checkbox',
        'description'       => __('Check if you want random ordering for products with same count of visits.', ODWP_WC_SIMPLESTATS),
        'desc_tip'          => true,
        'default'           => 'yes'
      ),
      'generate_btn' => array(
        'title'             => __( 'Generate order', ODWP_WC_SIMPLESTATS),
        'type'              => 'button',
        'custom_attributes' => array(),
        'description'       => __('Generate random order values for all products. This can be time consuming according to total count of products.', ODWP_WC_SIMPLESTATS),
        'desc_tip'          => true,
        'default'           => __( 'Generate order', ODWP_WC_SIMPLESTATS),
      )
		);
	} // end init_form_fields()

  /**
   * Generates HTML for the button.
   *
   * @link https://docs.woothemes.com/document/implementing-wc-integration/
   * @param string $key
   * @param array $data
   * @return void
   * @since 0.2.5
   * @uses wp_parse_args()
   * @uses wp_kses_post()
   */
  public function generate_button_html($key, $data) {
    $field    = ODWP_WC_SIMPLESTATS . $this->id . '_' . $key;
    $defaults = array(
      'class'             => 'button-secondary',
      'css'               => '',
      'custom_attributes' => array(),
      'desc_tip'          => false,
      'description'       => '',
      'title'             => '',
    );
    $data = wp_parse_args($data, $defaults);

    ob_start();
?>
  <tr valign="top">
    <th scope="row" class="titledesc">
      <label for="<?= esc_attr($field)?>"><?= wp_kses_post($data['title'])?></label>
      <?= $this->get_tooltip_html($data)?>
    </th>
    <td class="forminp">
      <fieldset>
        <legend class="screen-reader-text"><span><?= wp_kses_post($data['title'])?></span></legend>
        <button class="<?= esc_attr($data['class'])?>" type="button" name="<?= esc_attr($field)?>" id="<?= esc_attr($field)?>" style="<?= esc_attr($data['css'])?>" <?= $this->get_custom_attribute_html($data)?>><?= wp_kses_post($data['title'])?></button>
        <?= $this->get_description_html($data)?>
        <img id="<?= ODWP_WC_SIMPLESTATS.'_progress_img'?>" src="<?= get_site_url().'/wp-admin/images/wpspin_light'?>" style="display:none;position:relative;top:6px;"/>
        <p id="<?= ODWP_WC_SIMPLESTATS.'_progress_msg'?>" class="description" style="display:none;"><?= __('Please wait until the button is ready again and the result message is displayed.')?></p>
      </fieldset>
    </td>
  </tr>
<?php
    return ob_get_clean();
  } // end generate_button_html($key, $data)

	/**
	 * Santize our settings
   * 
	 * @param array $settings
   * @return array 
   * @see process_admin_options()
   * @since 0.1.0 
	 */
	public function sanitize_settings($settings) {
    $opts = array(
      'enable' => 'no',
      'enable_random' => 'no'
    );

    if (!is_array($settings)) {
      return $opts;
    }

    foreach ($opts as $key => $val) {
      if (array_key_exists($key, $settings)) {
        $opts[$key] = (strtolower($settings[$key]) === 'yes') ? 'yes' : 'no';
      }
    }

    return $opts;
	}
} // End of ODWP_WC_SimpleStats_Integration

endif;
