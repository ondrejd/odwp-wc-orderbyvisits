<?php
/**
 * Plugin for WordPress with WooCommerce installed that enables simple 
 * visits statistics on e-shop products and add custom products sorting 
 * based on them.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-wc-orderbyvisits for the canonical source repository
 * @license https://www.mozilla.org/MPL/2.0/ Mozilla Public License 2.0
 * @package odwp-wc-orderbyvisits
 */

if (!class_exists('WC_Integration_Demo_Integration')):

/**
 * Implementation of WooCommerce integration.
 *
 * @link https://github.com/BFTrick/woocommerce-integration-demo
 * @since 0.1.0
 */
class ODWP_WC_OrderByVisits_Integration extends WC_Integration {
  /**
   * @since 0.1.0
   * @var boolean $enable Possible values ['yes', 'no'].
   */
  protected $enable = 'no';

  /**
   * @since 0.2.5
   * @var boolean $enable_random Possible values ['yes', 'no'].
   */
  protected $enable_random = 'no';

  /**
   * @since 0.2.9
   * @var boolean $enable_cron
   */
  protected $enable_cron = 'no';

  /**
   * Init and hook in the integration.
   *
   * @since 0.1.0
   * @return void
   * @uses add_action()
   * @uses add_filter()
   */
  public function __construct() {
    $this->id = ODWP_WC_ORDERBYVISITS;
    $this->method_title = __('Order By Visits', ODWP_WC_ORDERBYVISITS);
    $this->method_description = __('Options for <b>Order By Visits</b> plugin.', ODWP_WC_ORDERBYVISITS);

    $this->init_form_fields();
    $this->init_settings();

    $this->enable = $this->get_option('enable');
    $this->enable_random = $this->get_option('enable_random');
    $this->enable_cron = $this->get_option('enable_cron');

    if ($this->is_enabled_cron() === true) {
      if (!wp_next_scheduled(ODWP_WC_ORDERBYVISITS . '-cron_event_hook')) {
        wp_schedule_event(time(), 'daily', ODWP_WC_ORDERBYVISITS . '-cron_event_hook');
      }
    } else {
      $timestamp = wp_next_scheduled(ODWP_WC_ORDERBYVISITS . '-cron_event_hook');

      if ($timestamp) {
        wp_unschedule_event($timestamp, ODWP_WC_ORDERBYVISITS . '-cron_event_hook', array());
      }
    }

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
   * Returns `TRUE` if updating of order is not made directly but after
   * CRON event is called.
   *
   * @return boolean
   * @since 0.2.9
   */
  public function is_enabled_cron() {
    return ($this->enable_cron === 'yes');
  } // end is_enabled_cron()

  /**
   * Initialize integration settings form fields.
   * 
   * @since 0.1.0
   * @return void
   */
  public function init_form_fields() {
    $this->form_fields = array(
        'enable' => array(
            'title'             => __('Enable Order By Visits', ODWP_WC_ORDERBYVISITS),
            'type'              => 'checkbox',
            'description'       => __('Check if you want to start using <b>Order By Visits</b> plugin.', ODWP_WC_ORDERBYVISITS),
            'desc_tip'          => true,
            'default'           => 'yes'
        ),
        'enable_random' => array(
          'title'             => __('Enable random ordering', ODWP_WC_ORDERBYVISITS),
          'type'              => 'checkbox',
          'description'       => __('Check if you want random ordering for products with same count of visits.', ODWP_WC_ORDERBYVISITS),
          'desc_tip'          => true,
          'default'           => 'yes'
        ),
        'enable_cron' => array(
          'title'             => __('Enable CRON', ODWP_WC_ORDERBYVISITS),
          'type'              => 'checkbox',
          'description'       => __('Check if you want to update ordering via <em>wp_cron</em> once per day instead of immediatelly.', ODWP_WC_ORDERBYVISITS),
          'desc_tip'          => true,
          'default'           => 'no'
        ),
        'generate_btn' => array(
          'title'             => __( 'Generate order', ODWP_WC_ORDERBYVISITS),
          'type'              => 'button',
          'custom_attributes' => array(),
          'description'       => __('Generate random order values for all products. This can be time consuming according to total count of products.', ODWP_WC_ORDERBYVISITS),
          'desc_tip'          => true,
          'default'           => __( 'Generate order', ODWP_WC_ORDERBYVISITS),
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
    $field    = ODWP_WC_ORDERBYVISITS . $this->id . '_' . $key;
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
        <img id="<?= ODWP_WC_ORDERBYVISITS.'_progress_img'?>" src="<?= get_site_url().'/wp-admin/images/wpspin_light'?>" style="display:none;position:relative;top:6px;"/>
        <p id="<?= ODWP_WC_ORDERBYVISITS.'_progress_msg'?>" class="description" style="display:none;"><?= __('Please wait until the button is ready again and the result message is displayed.', ODWP_WC_ORDERBYVISITS)?></p>
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
      'enable_random' => 'no',
      'enable_cron' => 'no'
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
} // End of ODWP_WC_OrderByVisits_Integration

endif;
