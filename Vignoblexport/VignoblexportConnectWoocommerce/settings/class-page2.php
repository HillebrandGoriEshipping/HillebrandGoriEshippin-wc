<?php

/**
 * Contains code for the settings page class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Settings
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Settings;

use Vignoblexport\VignoblexportConnectWoocommerce\Util\Misc_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Shipping_Method_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Configuration_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Auth_Util;
use Vignoblexport\VignoblexportPhp\RestClient;
use Vignoblexport\VignoblexportPhp\ApiClient;

/**
 * Settings page class.
 *
 * Manages settings for the Vignoblexport plugin.
 *
 * @class       Page
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Settings
 * @category    Class
 * @author      API Vignoblexport
 */
class Page2
{

	/**
	 * Construct function.
	 *
	 * @param array $plugin plugin array.
	 * @void
	 */
	public function __construct($plugin)
	{
		$this->plugin_url     = $plugin['url'];
		$this->plugin_version = $plugin['version'];
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run()
	{

		// add_action( 'admin_menu', array( $this, 'Vignoblexport_expedition_parametre') );  

		// add_action( 'admin_menu', array( $this, 'add_menu2' ) );
		//phpcs:ignore
		if (isset($_GET['page']) && 'Vignoblexport-settings' === $_GET['page']) {
			add_action('admin_enqueue_scripts', array($this, 'settings_page_scripts'));
			add_action('admin_enqueue_scripts', array($this, 'settings_page_styles'));
		}
	}

	/**
	 * Enqueue settings page scripts
	 *
	 * @void
	 */
	public function settings_page_scripts()
	{
		wp_enqueue_script('VINW_tail_select', $this->plugin_url . 'Vignoblexport/VignoblexportConnectWoocommerce/assets/js/tail.select-full.min.js', array(), $this->plugin_version);
		wp_enqueue_script('VINW_settings_page', $this->plugin_url . 'Vignoblexport/VignoblexportConnectWoocommerce/assets/js/settings-page.min.js', array('VINW_tail_select'), $this->plugin_version);
		wp_localize_script('VINW_settings_page', 'VINWLocale', array(substr(get_locale(), 0, 2)));
	}

	/**
	 * Enqueue settings page styles
	 *
	 * @void
	 */
	public function settings_page_styles()
	{
		wp_enqueue_style('VINW_tail_select', $this->plugin_url . 'Vignoblexport/VignoblexportConnectWoocommerce/assets/css/tail.select-bootstrap3.css', array(), $this->plugin_version);
		wp_enqueue_style('VINW_parcel_point', $this->plugin_url . 'Vignoblexport/VignoblexportConnectWoocommerce/assets/css/settings.css', array(), $this->plugin_version);
	}

	/**
	 * Register settings.
	 *
	 * @void
	 */
	function Vignoblexport_expedition_parametre()
	{
		$page_title = 'expedition_parametre';
		$menu_title = 'Vignoblexport parameter';
		$capability = 'manage_options';
		$menu_slug  = 'expedition_parametre';
		$function   = 'render_page_setting';
		$icon_url   = '';
		$position   = 40;

		add_menu_page($page_title, $menu_title, $capability,  $menu_slug,  $function,  $icon_url,  $position);
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this, 'render_page_setting'), $icon_url, $position);
		add_action('admin_init', array($this, 'register_settings_reference'));
	}

	public function register_settings_reference()
	{
		// MINIMUM NUMBER OF BOTTLES
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_NBR_MIN',
			array(
				'type'              => 'string',
				'description'       => __('VINW_NBR_MIN', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);

		// EXPEDITION TIME DELAY
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_EXP_DAYS_MIN',
			array(
				'type'              => 'string',
				'description'       => __('VINW_EXP_DAYS_MIN', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);

		// TRANSPORTER PREFERENCES
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_PREF_TRANSP',
			array(
				'type'              => 'string',
				'description'       => __('VINW_PREF_TRANSP', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);

		// DELIVERY PREFERENCES
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_PREF_STAT',
			array(
				'type'              => 'string',
				'description'       => __('VINW_PREF_STAT', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);

		// TAX RIGHTS
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_TAX_RIGHTS',
			array(
				'type'              => 'string',
				'description'       => __('VINW_TAX_RIGHTS', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);

		// INSURANCE
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_ASSURANCE',
			array(
				'type'              => 'string',
				'description'       => __('VINW_ASSURANCE', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);
	}

	/**
	 * Render settings page.
	 *
	 * @void
	 */

	public function render_page_setting()
	{
		$lib = new ApiClient(Auth_Util::get_access_key());
		//phpcs:ignore
		$getadress = $lib->restClient->request(RestClient::$GET, $lib->getApiUrl() . 'address/get-addresses?typeAddress=exp');

		// $getadress= $restClient->get_adress();

		$get_pallet_size = 	$lib->restClient->request(RestClient::$GET, $lib->getApiUrl() . 'package/get-pallet-size');


		$help_center_url = Configuration_Util::get_help_center_link();
		$tuto_url = Configuration_Util::get_tuto_link();
		include_once dirname(__DIR__) . '/assets/views/html-settings-expedition-page.php';
	}

	/**
	 * Sanitize status option.
	 *
	 * @param string $input status value.
	 *
	 * @return string
	 */
	public function sanitize_status($input)
	{
		return 'none' === $input ? null : $input;
	}
}
