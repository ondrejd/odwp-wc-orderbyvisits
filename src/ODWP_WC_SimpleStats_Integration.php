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
 * Implementation of Woocommerce integration.
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
	 * Init and hook in the integration.
   *
   * @since 0.1.0
   * @return void
	 */
	public function __construct() {
		global $woocommerce;
		$this->id = 'odwpwcss';
		$this->method_title = __('Simple Stats Plugin for Woocommerce', ODWP_WC_SIMPLESTATS);
		$this->method_description = __('Options for <b>Simple Stats Plugin for Woocommerce</b> plugin.',ODWP_WC_SIMPLESTATS);

		$this->init_form_fields();
		$this->init_settings();

		$this->enable = $this->get_option('enable');

		add_action('woocommerce_update_options_integration_'. $this->id, array($this, 'process_admin_options'));
		add_filter('woocommerce_settings_api_sanitized_fields_'.$this->id, array($this, 'sanitize_settings'));
	} // end __construct()

	/**
	 * Initialize integration settings form fields.
	 *
   * @since 0.1.0
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'api_key' => array(
				'title'             => __('Enable simple stats', ODWP_WC_SIMPLESTATS),
				'type'              => 'checkbox',
				'description'       => __('Check if you want to start using <b>Simple Stats plugin for Woocommerce</b>.', ODWP_WC_SIMPLESTATS),
				'desc_tip'          => true,
				'default'           => true
			)
		);
	} // end init_form_fields()

	/**
	 * Santize our settings
   * 
	 * @param array $settings
   * @return array 
   * @see process_admin_options()
   * @since 0.1.0 
	 */
	public function sanitize_settings($settings) {
		if (isset($settings) && !isset($settings['enable'])) {
      $settings['enable'] = false;
    }

    return false;
	}
} // End of ODWP_WC_SimpleStats_Integration

endif;
