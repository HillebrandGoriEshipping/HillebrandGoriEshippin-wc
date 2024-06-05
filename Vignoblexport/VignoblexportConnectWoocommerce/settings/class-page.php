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
use Vignoblexport\VignoblexportPhp\RestClient;

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
class Page
{
	public $plugin_url;
	public $plugin_version;

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
		add_action('admin_menu', array($this, 'add_menu'));
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
	 * Add settings page.
	 *
	 * @void
	 */
	public function add_menu()
	{
		add_submenu_page('woocommerce', __('VignoblExport', 'Vignoblexport'), __('VignoblExport', 'Vignoblexport'), 'manage_woocommerce', 'Vignoblexport-settings', array($this, 'render_page'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_init', array($this, 'register_settings_reference'));
	}

	/**
	 * Register settings.
	 *
	 * @void
	 */
	public function register_settings()
	{
		// VIGNOBLEXPORT API KEY
		register_setting(
			'Vignoblexport-settings-group',
			'VINW_ACCESS_KEY',
			array(
				'type'              => 'string',
				'description'       => __('ACCESS KEY', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);
		register_setting(
			'Vignoblexport-settings-group',
			'acces-key-validate',
			array(
				'type'              => 'string',
				'description'       => __('acces-key-validate', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);

		// MAPBOX API KEY
		register_setting(
			'Vignoblexport-settings-group',
			'VINW_MAPBOX_ACCESS_KEY',
			array(
				'type'              => 'string',
				'description'       => __('VINW_MAPBOX_ACCESS_KEY', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);
		register_setting(
			'Vignoblexport-settings-group',
			'mapbox-api-key-validate',
			array(
				'type'              => 'string',
				'description'       => __('mapbox-api-key-validate', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);
		register_setting(
			'Vignoblexport-settings-group',
			'VINW_PP_NETWORKS',
			array(
				'type'              => 'string',
				'description'       => __('NETWORKS', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);
		register_setting(
			'Vignoblexport-settings-group',
			'VINW_ORDER_SHIPPED',
			array(
				'type'              => 'string',
				'description'       => __('Package shipped', 'Vignoblexport'),
				'default'           => "wc-awaiting-shipment",
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);
		register_setting(
			'Vignoblexport-settings-group',
			'VINW_ORDER_DELIVERED',
			array(
				'type'              => 'string',
				'description'       => __('Package delivered', 'Vignoblexport'),
				'default'           => 'wc-delivered',
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);
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

		// VAT CHOICE
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_VAT_CHOICE',
			array(
				'type'              => 'string',
				'description'       => __('VINW_VAT_CHOICE', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);

		// VAT NUMBER
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_VAT_NUMBER',
			array(
				'type'              => 'string',
				'description'       => __('Vat number', 'Vignoblexport'),
				'default'           => null,
				'sanitize_callback' => array($this, 'sanitize_status'),
			)
		);
		// EORI NUMBER
		register_setting(
			'Vignoblexport-settings-group2',
			'VINW_EORI_NUMBER',
			array(
				'type'              => 'string',
				'description'       => __('EORI number', 'Vignoblexport'),
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
	function get_pallet_size()
	{
		$curl = curl_init();
		$key = get_option('VINW_ACCESS_KEY');
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://test.extranet.vignoblexport.fr/api/package/get-pallet-size',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'X-AUTH-TOKEN:' . $key
			),
		));
		$response = json_decode(curl_exec($curl), true);
		curl_close($curl);
		return $response;
	}
	public function render_page()
	{
		$curlExp = curl_init();
		curl_setopt_array($curlExp, array(
			CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/address/get-addresses?typeAddress=exp",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY'),
			),
		));
		$getadress = json_decode(curl_exec($curlExp), true);
		curl_close($curlExp);
		$get_pallet_size = $this->get_pallet_size();
		$help_center_url = Configuration_Util::get_help_center_link();
		$order_statuses  = wc_get_order_statuses();
		$help_center_url = Configuration_Util::get_help_center_link();
		$tuto_url = Configuration_Util::get_tuto_link();
		include_once dirname(__DIR__) . '/assets/views/html-settings-page.php';
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
