<?php

/**
 * Contains code for the label override class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method\Parcel_Point
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method\Parcel_Point;

use Vignoblexport\VignoblexportConnectWoocommerce\Util\Misc_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Shipping_Rate_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Product_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Order_Util;

/**
 * Label_Override class.
 *
 * Adds relay map link if configured.
 *
 * @class       Label_Override
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method\Parcel_Point
 * @category    Class
 * @author      API Vignoblexport
 */
class Label_Override
{
	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run()
	{
		add_action('woocommerce_before_checkout_form', array($this, 'action_woocommerce_before_checkout_form'), 10, 1);
		add_action('woocommerce_before_cart', array($this, 'action_woocommerce_before_checkout_form'), 10, 1);
		add_action('woocommerce_after_checkout_billing_form', array($this, 'action_woocommerce_after_checkout_billing_form'), 10, 1);
		add_action('woocommerce_checkout_order_processed', array($this, 'is_express_delivery'),  1, 1);
		add_filter('woocommerce_package_rates', array($this, 'wf_hide_shipping_method_based_on_shipping_class'), 10, 2);
		add_filter('woocommerce_cart_shipping_method_full_label', array($this, 'change_shipping_label'), 10, 2);
		add_filter('woocommerce_after_checkout_billing_form', array($this, 'action_woocommerce_after_checkout_billing_form'), 10, 1);
		add_action('woocommerce_customer_save_address', array($this, 'delete_parce_point'), 10, 1);
		add_filter('woocommerce_checkout_fields',  array($this, 'custom_override_checkout_fields'), 10, 1);
		add_filter('woocommerce_get_order_item_totals', array($this, 'rename_shipping_order_item_totals'), 10, 3);
		add_action('woocommerce_checkout_update_order_review', array($this, 'bbloomer_save_checkout_values'), 9999);
		add_filter('woocommerce_checkout_get_value', array($this, 'bbloomer_get_saved_checkout'), 9999, 2);
		add_filter('woocommerce_ship_to_different_address_checked', array($this, 'bbloomer_get_saved_ship_to_different'));
		add_action('wp_head', array($this, 'reinit_offer_at_leave_checkout'), 10, 1);
		add_action("wp_ajax_nopriv_add_ajax", array($this, "add_ajax"));
		add_action('wp_ajax_nopriv_update_cart_prices', array($this, 'update_cart_prices'));
		add_action('wp_ajax_update_cart_prices', array($this, 'update_cart_prices'));
		add_action('wp_ajax_nopriv_update_pickup_relay', array($this, 'update_pickup_relay'));
		add_action('wp_ajax_update_pickup_relay', array($this, 'update_pickup_relay'));
		add_action('wp_ajax_get_tax_and_duties', array($this, 'get_tax_and_duties'));
		add_action('wp_ajax_nopriv_get_tax_and_duties', array($this, 'get_tax_and_duties'));
		add_action('wp_ajax_calculate_tax_duties', array($this, 'calculate_tax_duties'));
		add_action('wp_ajax_nopriv_calculate_tax_duties', array($this, 'calculate_tax_duties'));
		add_action('woocommerce_checkout_process', array($this, 'is_offer_set_checkout_field_process'));
		if (get_option("VINW_TAX_RIGHTS") == "dest") {
			add_action('woocommerce_cart_totals_before_shipping', array($this, 'add_estimated_tax_and_duties_row'), 99);
			add_action('woocommerce_review_order_before_shipping', array($this, 'add_estimated_tax_and_duties_row'), 99);
		}
	}

	function add_estimated_tax_and_duties_row()
	{

?>
		<tr>
			<th><?php _e("Estimated tax & duties amount", "Vignoblexport"); ?></th>
			<td id="tax-and-duties-amount""><?php _e("Select an offer", "Vignoblexport"); ?></td>
			</tr>

		<?php
	}

	/**
	 * Process the checkout
	 */

	function is_offer_set_checkout_field_process()
	{
		// Check if set, if its not set add an error.
		if (!isset($_POST['offer']))
			wc_add_notice(__('Please select a shipping offer'), 'error');
	}

	function rename_shipping_order_item_totals($order, $order_glob)
	{
		$order_glo = json_decode($order_glob, true);
		$id = (float)$order_glo["id"];
		$shipping = $order["shipping"]['value'];

		if (preg_match("/Vignoblexport/i", $shipping)) {
			global $wpdb;
			$query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $id . "'";
			$result = $wpdb->get_results($query, ARRAY_A);
			$offres = json_decode(trim(stripslashes(stripslashes($result[0]["offre"] ?? '')), '"'), true);
			$bodytag = str_replace("Vignoblexport", $offres["name"] ?? '', $shipping);
			$order["shipping"]['value'] = $bodytag;
		}

		return $order;
	}

	function wf_hide_shipping_method_based_on_shipping_class($available_shipping_methods, $package)
	{
		$nbr_Bottles_magnums = $this->get_nbr_Bottles_magnums();
		foreach ($available_shipping_methods as $key => $value) {
			if ($value->get_method_id() !== null) {
				if (($value->get_method_id() == 'Vignoblexport_connect' && WC()->cart->cart_contents_count < (int)get_option('VINW_NBR_MIN')) || ($value->get_method_id() == 'Vignoblexport_connect' && $nbr_Bottles_magnums == false)) {
					unset($available_shipping_methods[$key]);
				}
			}
		}

		return $available_shipping_methods;
	}

	function is_express_delivery($order_id)
	{
		$sessionDetails = WC()->session->get('VINW_CONF_EXP');
		$sessionDetails = explode(';', $sessionDetails);
		d($sessionDetails);
		if (WC()->session->get('VINW_CONF_Colis') !== null) {
			$package_co = array();
			$arrayssesion = explode(";", WC()->session->get('VINW_CONF_Colis'));
			$package_co[0] = urldecode($arrayssesion[1]);
		}

		if (!empty($arrayssesion[0])) {
			$package = $arrayssesion[0];
		} else {
			$package = $sessionDetails[1];
		}

		$session_tax_amount = WC()->session->get('tax_amount');

		$session_insurance = WC()->session->get('insurance');

		global $wpdb;
		$tablename = $wpdb->prefix . 'VINW_order_expidition';

		$wpdb->insert(
			$tablename,
			array(
				'order_id' => (int) $order_id,
				'package' => urldecode($package),
				'type_liv' => urldecode($sessionDetails[4]),
				'offre' => urldecode($sessionDetails[0]),
				'charge_type' => get_option('VINW_TAX_RIGHTS') == 'dest'  ? get_option('VINW_TAX_RIGHTS') : 'exp',
				'package_type' => 'colis',
				'nbr_bottles' => urldecode($sessionDetails[8]),
				'nbr_Magnums' => urldecode($sessionDetails[9]),
				'tax_amount' => urldecode($session_tax_amount),
				'insurance' => urldecode($session_insurance),
			),
			array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f')
		);
		if (WC()->session->get('VINW_CONF_EXP') !== null) {
			WC()->session->set('VINW_CONF_EXP', null);
		}
		if (WC()->session->get('VINW_CONF_Colis') !== null) {
			WC()->session->set('VINW_CONF_Colis', null);
		}
	}

	/**
	 * Format a parcelpoint address into a one line string
	 *
	 * @param \StdClass $parcelpoint in object format.
	 * @return string one line address
	 */

	public function action_woocommerce_after_checkout_billing_form($method)
	{
		$chaine = WC()->session->get('VINW_CONF_EXP');
		if (isset($chaine)) {
			$tab = explode(";", $chaine);
			if ($tab[4] == "pointRelais") {
				$chosen_parcel_point = Controller::get_chosen_point(Shipping_Rate_Util::get_id($method));

				if ($chosen_parcel_point !== null) {
					$style = '';
					$name = $chosen_parcel_point->name;
				} else {
					$style = '';
					$name = " ";
				}
			}
		}
	}

	public function action_woocommerce_before_checkout_form($wccm_autocreate_account)
	{
		$chaine = WC()->session->get('VINW_CONF_EXP');
		$colis = WC()->session->get('VINW_CONF_Colis');
		if (isset($colis)) {
			$tab = explode(";", $colis);
			echo '<input type="hidden" id="exp-colis" value="' . $colis . '">';
		} else {
			echo '<input type="hidden" id="exp-colis">';
		}
		if (isset($chaine)) {
			$tab = explode(";", $chaine);

			echo '<input type="hidden" id="exp-details">';
		}

		echo '<style>
		.active-relay{   
			background: #b4b4b4;
			color:black;
			border-radius: 10px;
			padding: 7px;
		}
		.active-relay:hover{
			background: #a1a1a1;
		}
		.offers{
			background: gainsboro;
			padding: 5%;
			min-width: fit-content;
		}
		#streetinput{
			margin-top: 8px auto;
		}
		#point_relais_field{
			display:none;
		}
		.notif{
			border: 1px solid #43454b;
			color: #43454b;
			padding: .202em .6180469716em;
			font-size: .875em;
			text-transform: uppercase;
			font-weight: 600;
			display: inline-block;
			margin-bottom: 1em;
			border-radius: 3px;
		
		}
		</style>
		
		<script src="' . get_option('siteurl') . '/wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportConnectWoocommerce/assets/js/expedition-picker.js"></script>';
	}

	private function get_parcelpoint_address($parcelpoint)
	{
		$address = $parcelpoint->address;

		$ziptown = [];

		if (null !== $parcelpoint->zipcode) {
			$ziptown[] = $parcelpoint->zipcode;
		}

		if (null !== $parcelpoint->city) {
			$ziptown[] = $parcelpoint->city;
		}
		$ziptown = implode(', ', $ziptown);

		$result = implode(' ', [$address, $ziptown]);

		if (null !== $parcelpoint->distance) {
			$distance = round($parcelpoint->distance / 100) / 10;
			$result  .= ' (' . sprintf(__('%skm away', 'Vignoblexport'), $distance) . ')';
		}

		return $result;
	}

	function getOfferLogo($offer, $service)
	{
		$url = get_option('siteurl') . '/wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportConnectWoocommerce/assets/img/';
		switch ($offer) {
			case 'ups':
				$url .= "ups.png";
				break;
			case 'fedex':
				$url .= "fedex.png";
				break;
			case 'tnt':
				$url .= "tnt.png";
				break;
			case 'dhl':
				$url .= "dhl.png";
				break;
			case 'chronopost':
				$url .= "chronopost.png";
				break;
			case 'groupage vignoblexport':
				if ($service == "Maritime") {
					$url .= "seafreight.png";
				} else {
					$url .= "airfreight.png";
				}
				break;
			default:
				break;
		}
		return $url;
	}

	public function delete_parce_point($method)
	{
		$chosen_parcel_point = Controller::get_chosen_point(Shipping_Rate_Util::get_id($method));

		if (null !== $chosen_parcel_point) {
			foreach (WC()->session->get_session_data() as $key => $value) {

				if (strstr($key, 'vinw_chosen_parcel_point_')) {
					WC()->session->set($key, null);
				}
			}
		}
	}

	/**
	 * Add relay map link to shipping method label.
	 *
	 * @param string            $full_label shipping method label.
	 * @param \WC_Shipping_Rate $method shipping rate.
	 * @return string $full_label
	 */

	// Our hooked in function - $fields is passed via the filter!
	function custom_override_checkout_fields($fields)
	{
		if (WC()->session->get('VINW_CONF_EXP') !== null) {
			$chaine = WC()->session->get('VINW_CONF_EXP');
			if (isset($chaine)) {
				$tab = explode(";", $chaine);
				if ($tab[4] == "pointRelais" && (get_option("VINW_PREF_STAT") == "pointRelais" || get_option("VINW_PREF_STAT") == "les deux") && in_array('ups', get_option('VINW_PREF_TRANSP'))) {
					$fields['billing']['point_relais'] = array(
						'type'      => 'hidden',
						'label'     => __('Pickup point:', 'Vignoblexport'),
						'required'  => true,
						'class'     => array(''),
						'clear'     => true
					);
				}
			}
		}
		return $fields;
	}

	function get_nbr_Bottles_magnums()
	{
		$cart = WC()->cart->get_cart();
		$nbr_Bottles_magnums = [
			'nbr_mg' => 0,
			'nbr_bot' => 0
		];

		foreach ($cart as $cart_item_key => $cart_item) {
			// $weight = get_post_meta($cart_item['product_id'], '_weight', true);
			$bottle_size = get_post_meta($cart_item['product_id'], '_custom_bottle_size', true);
			if ($bottle_size == "") {
				$bottle_size = "bottle";
			}
			$quantity = 1;

			if ($cart_item['variation_id'] != 0) {
				$bottle_size = get_post_meta($cart_item['product_id'], '_custom_bottle_size', true);
				$quantity = get_post_meta($cart_item['variation_id'], '_variation_quantity', true);
				$quantity = (int)$quantity;
			} else {
				$bottle_size = get_post_meta($cart_item['product_id'], '_custom_bottle_size', true);
				$quantity = get_post_meta($cart_item['product_id'], '_custom_number_bottle', true);
				$quantity = (int)$quantity;
			}

			if ($cart_item['quantity'] > 1) {
				$bottle_size = get_post_meta($cart_item['product_id'], '_custom_bottle_size', true);
				if ($cart_item['variation_id'] != 0) {
					$quantity = get_post_meta($cart_item['variation_id'], '_variation_quantity', true) * (int)$cart_item['quantity'];
				} else {
					$quantity = get_post_meta($cart_item['product_id'], '_custom_number_bottle', true) * (int)$cart_item['quantity'];
				}
				$quantity = (int)$quantity;
			}

			if ($bottle_size == "magnum") {
				$nbr_Bottles_magnums["nbr_mg"] += $quantity;
			} else {
				$nbr_Bottles_magnums["nbr_bot"] += $quantity;
			}
		}
		return $nbr_Bottles_magnums;
	}

	function has_sparkling()
	{
		$cart = $cart = WC()->cart->get_cart();
		foreach ($cart as $cart_item_key => $cart_item) {
			$type = get_post_meta($cart_item['product_id'], '_custom_type', true);
			if ($type == "sparkling") {
				return true;
				break;
			} else {
				return false;
			}
		}
	}

	/**
	 * Calculate tax and duties for a given carrier and return the result.
	 *
	 * @param string $carrier The carrier for which the tax and duties are being calculated.
	 * @return array The tax and duties informations in an associative array format.
	 */
	function get_tax_and_duties($carrier)
	{
		$nbm = $this->get_nbr_Bottles_magnums();
		$country = WC()->customer->get_shipping_country();
		$cartTotalWeight = WC()->cart->cart_contents_weight;
		$shippingPrice = WC()->cart->cart_contents_total;
		$quantity = $nbm['nbr_mg'] + $nbm['nbr_bot'];
		if ($quantity == 0) {
			$quantity = 1;
		}

		$unitPrice = round($shippingPrice / $quantity, 2);

		$allTypes = [];
		$allCapacities = [];
		$allAlcoolDegrees = [];
		foreach (WC()->cart->get_cart() as $cart_item) {
			$allTypes[] = get_post_meta($cart_item['product_id'], '_custom_type', true);
			$allCapacities[] = get_post_meta($cart_item['product_id'], '_custom_capacity', true);
			$allAlcoolDegrees[] = get_post_meta($cart_item['product_id'], '_custom_alcohol_degree', true);
		}

		$highestAlcoolDegree = max($allAlcoolDegrees);
		$highestCapacity = max($allCapacities);
		$priorityType = [
			'spirits',
			'sparkling',
			'wine'
		];

		$type = 'wine';

		foreach ($priorityType as $prio) {
			if (in_array($prio, $allTypes)) {
				$type = $prio;
				break;
			}
		}

		// api request
		$curl = curl_init();
		$url = "https://test.extranet.vignoblexport.com/api/shipment/get-duty-and-tax?";
		$url .= "country=" . $country;
		$url .= "&carrier=" . $carrier;
		$url .= "&weight=" . (string)$cartTotalWeight;
		$url .= "&shippingPrice=" . (string)$shippingPrice;
		$url .= "&wineType=" . $type; // prendre le plus cher
		$url .= "&quantity=" . (string)$quantity;
		$url .= "&capacity=" . (string)$highestCapacity; // en ML/unité
		$url .= "&alcoholDegree=" . (string)$highestAlcoolDegree; // prendre le plus haut
		$url .= "&unitPrice=" . (string)$unitPrice; // faire moyenne

		$url = str_replace(" ", "%20", $url);

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
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
		$getTaxDuties = curl_exec($curl);
		$getTaxDuties = json_decode($getTaxDuties, true);
		curl_close($curl);

		return $getTaxDuties;
	}

	function calculate_tax_duties()
	{
		$carrier = $_GET['selected_offer_value'];
		$result = $this->get_tax_and_duties($carrier);
		wp_send_json($result);
	}

	/**
	 * Calculates the tax category based on the origin and destination countries.
	 *
	 * @param string $exp_country
	 * @param string $dest_country
	 * @return string
	 */
	function get_tax_category($exp_country, $dest_country)
	{

		if ($exp_country == $dest_country) {
			$tax_category = "standard";
		} elseif ($this->is_fiscal_rep($exp_country) && $this->is_fiscal_rep($dest_country)) {
			$tax_category = "intra_eu";
		} else {
			$tax_category = "inter";
		}

		return $tax_category;
	}

	/**
	 * Retrieves the  for each offer in the cart.
	 *
	 * @param array $offers The list of offers in the cart
	 * @return array The array of extra costs for each offer
	 */
	function get_charges_ue($offer, $exp_country)
	{
		$cart = WC()->session->cart;
		$allHsCodes = $this->get_hscode();

		$curlCharges = curl_init();
		$chargesURL = "https://test.extranet.vignoblexport.fr/api/shipment/get-charges?";
		$counter = 0;
		// loop through all cart items
		foreach ($cart as $cart_item) {
			$chargesURL .= "details%5B" . urlencode($counter) . "%5D%5Bcapacity%5D=" . get_post_meta($cart_item['product_id'], '_custom_capacity', true);
			$chargesURL .= "&details%5B" . urlencode($counter) . "%5D%5BalcoholDegree%5D=" . get_post_meta($cart_item['product_id'], '_custom_alcohol_degree', true);
			$chargesURL .= "&details%5B" . urlencode($counter) . "%5D%5BhsCode%5D=" . $allHsCodes[$counter];
			$chargesURL .= "&details%5B" . urlencode($counter) . "%5D%5BunitValue%5D=" . get_post_meta($cart_item['product_id'], '_price', true);
			$chargesURL .= "&details%5B" . urlencode($counter) . "%5D%5Bquantity%5D=" . get_post_meta($cart_item['product_id'], '_custom_number_bottle', true);
			$chargesURL .= "&";
			$counter++;
		}
		$chargesURL .= "expCountryAlpha2=" . $exp_country;
		$chargesURL .= "&destCountryAlpha2=" . WC()->session->customer['country'];
		$chargesURL .= "&shippingPrice=" . $offer;

		$chargesURL = str_replace(" ", "%20", $chargesURL);

		curl_setopt_array($curlCharges, array(
			CURLOPT_URL => $chargesURL,
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

		$responseCharges = json_decode(curl_exec($curlCharges), true);
		$extraCosts = $responseCharges['total'];
		curl_close($curlCharges);

		return $extraCosts;
	}

	/**
	 * Check if country is from eligibles countries to fiscal representation
	 *
	 * @void
	 */
	function is_fiscal_rep($currentCountry)
	{
		$curl = curl_init();
		$url = "https://test.extranet.vignoblexport.fr/api/address/get-countries";

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
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
		$allCountries = curl_exec($curl);
		curl_close($curl);
		$arrayCountries = json_decode($allCountries, true);

		$countries = array();
		foreach ($arrayCountries as $country) {
			if ($country['fiscalRep']['bToC'] == true && $country['fiscalRep']['bToB'] == true) {
				array_push($countries, $country);
			}
		}

		if (in_array($currentCountry, $countries)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Retrieve vat rate with expedition country
	 *
	 * @void
	 */
	function get_vat_from_dest_country($dest_country)
	{
		// api request
		$curl = curl_init();
		$url = "https://test.extranet.vignoblexport.fr/api/address/get-countries";

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
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
		$allCountries = curl_exec($curl);
		curl_close($curl);
		$arrayCountries = json_decode($allCountries, true);

		foreach ($arrayCountries as $value) {
			if ($value['iso2'] && $value['iso2'] === $dest_country && $value['vat_rate'] !== "") {
				return $value['vat_rate'];
			}
		}
		return null;
	}

	/**
	 * Get the sizes of packages based on the number of bottles and magnums.
	 *
	 * @param int $nbr_bottle The number of bottles.
	 * @param int $nbr_magnum The number of magnums.
	 * @return array The response containing the package sizes.
	 */
	function get_sizes($nbr_bottle, $nbr_magnum)
	{
		$curlsize = curl_init();

		curl_setopt_array($curlsize, array(
			CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/package/get-sizes?nbBottles=" . (string)$nbr_bottle . "&nbMagnums=" . (string)$nbr_magnum,
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

		$response = json_decode(curl_exec($curlsize), true);
		curl_close($curlsize);

		return $response;
	}

	/**
	 * Verify is a date isn't a workable day
	 *
	 * @void
	 */
	function isNotWorkable($date)
	{

		if ($date === null) {
			$date = time();
		}

		$date = strtotime(date('m/d/Y', strtotime($date)));

		$year = date('Y', $date);

		$easterDate  = easter_date($year);
		$easterDay   = date('j', $easterDate);
		$easterMonth = date('n', $easterDate);
		$easterYear   = date('Y', $easterDate);

		$holidays = array(
			// Dates fixes
			mktime(0, 0, 0, 1,  1,  $year),  // 1er janvier
			mktime(0, 0, 0, 5,  1,  $year),  // Fête du travail
			mktime(0, 0, 0, 5,  8,  $year),  // Victoire des alliés
			mktime(0, 0, 0, 7,  14, $year),  // Fête nationale
			mktime(0, 0, 0, 8,  15, $year),  // Assomption
			mktime(0, 0, 0, 11, 1,  $year),  // Toussaint
			mktime(0, 0, 0, 11, 11, $year),  // Armistice
			mktime(0, 0, 0, 12, 25, $year),  // Noel

			// Dates variables
			mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear),
			mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear),
			mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear),
		);

		return in_array($date, $holidays);
	}

	/**
	 * @snippet       Retain Field Values | WooCommerce Checkout
	 * @how-to        Get CustomizeWoo.com FREE
	 * @author        Rodolfo Melogli
	 * @testedwith    WooCommerce 6
	 */

	function bbloomer_save_checkout_values($posted_data)
	{
		parse_str($posted_data, $output);
		WC()->session->set('checkout_data', $output);
	}

	function bbloomer_get_saved_checkout($value, $index)
	{
		$data = WC()->session->get('checkout_data');
		if (!$data || empty($data[$index])) return $value;
		return is_bool($data[$index]) ? (int) $data[$index] : $data[$index];
	}

	function bbloomer_get_saved_ship_to_different($checked)
	{
		$data = WC()->session->get('checkout_data');
		if (!$data || empty($data['ship_to_different_address'])) return $checked;
		return true;
	}

	function add_ajax()
	{
		wp_localize_script(
			'testAjax',
			'ajax_script',
			array('ajaxurl' => admin_url('admin-ajax.php'))
		);
	}

	function update_cart_prices($data)
	{
		$products_tax = WC()->cart->get_taxes_total();
		if (get_option('VINW_VAT_CHOICE') == 'yes') {
			$shipping_tax = $_GET['tax'];
		} else {
			$shipping_tax = 0;
		}
		$total_vat = $products_tax + $shipping_tax;
		$total_vat = urldecode($total_vat);
		$total_vat = number_format($total_vat, 2, '.', ' ');

		if (get_option('VINW_ASSURANCE') == 'yes') {
			$insurance = $_GET['insurance'];
		} else {
			$insurance = 0;
		}

		$jsonData = [
			'shippingTotal' => number_format(WC()->cart->get_shipping_total(), 2, ',', ' ') . ' €',
			'total' => number_format(WC()->cart->total, 2, ',', ' ') . ' €',
			'totalVat' => number_format($total_vat, 2, ',', ' '),
		];
		$response = json_encode($jsonData);
		echo $response;

		if (isset($_POST['offer'])) {
			$data = urldecode($_POST['offer']);
		}
		echo $data;
		WC()->session->__set('offer', $data);
		WC()->session->set('tax_amount', $shipping_tax);
		WC()->session->set('insurance', $insurance);
		die();
	}

	function update_pickup_relay($data)
	{
		$chosen_parcel_point = Controller::get_chosen_point('Vignoblexport_connect');
		$name = '';
		if ($chosen_parcel_point !== null) {
			$name = $chosen_parcel_point->name;
		}

		$full_label = '<div class="relay">';
		$full_label .= '<br><br><span class="VINW-select-parcel active-relay" id="relayaction">' . __('Choose pickup point', 'Vignoblexport') . '</span>';
		$full_label .= '<br><br><span class="VINW-parcel-client">' . __('Your pickup point:', 'Vignoblexport') . ' <span class="VINW-parcel-name">' . $name . '</span></span>';
		$full_label .= '</div>';
		echo json_encode($full_label);
		die;
	}

	/**
	 * Reinitializes the offer at the moment the user leaves the checkout page.
	 *
	 * @throws Some_Exception_Class A description of the exception that can be thrown.
	 */
	function reinit_offer_at_leave_checkout()
	{
		//we used the jquery beforeunload function to detect if the user leaves that page
		?>

		<script>
			jQuery(document).ready(function($) {

				$(window).bind('beforeunload', function() {
					<?php
					//empty the cart
					WC()->session->__unset('offer');
					WC()->session->__unset('VINW_CONF_EXP');
					?>
				});

			});
		</script>

<?php }

	/**
	 * Retrieves HS codes for products in the cart.
	 *
	 * @return array The array of HS codes for the products.
	 */
	function get_hscode()
	{
		$allHsCodes = [];
		$cart = WC()->session->cart;

		foreach ($cart as $cart_item) {
			$curlHscode = curl_init();
			$hscodeURL = "https://test.extranet.vignoblexport.fr/api/get-hscode";
			$hscodeURL .= "?appellationName=" . htmlspecialchars_decode(get_post_meta($cart_item['product_id'], '_custom_appelation', true));
			$hscodeURL .= "&capacity=" . get_post_meta($cart_item['product_id'], '_custom_capacity', true);
			$hscodeURL .= "&alcoholDegree=" . get_post_meta($cart_item['product_id'], '_custom_alcohol_degree', true);
			$hscodeURL .= "&color=" . get_post_meta($cart_item['product_id'], '_custom_color', true);

			$hscodeURL = str_replace(" ", "%20", $hscodeURL);

			curl_setopt_array($curlHscode, array(
				CURLOPT_URL => $hscodeURL,
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
			$responseHscode = json_decode(curl_exec($curlHscode), true);
			array_push($allHsCodes, $responseHscode);
			curl_close($curlHscode);
		}

		return $allHsCodes;
	}

	/**
	 * Retrieves the feasibility of a given country, client type, and quantity.
	 *
	 * @param string $country The country to check feasibility for.
	 * @param string $clientType The client type to check feasibility for.
	 * @param int $quantity The quantity to check feasibility for.
	 */
	function get_feasability($country, $clientType, $quantity)
	{
		$curlsize = curl_init();

		curl_setopt_array($curlsize, array(
			CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/shipment/get-feasability?country=" . $country . "&clientType=" . $clientType . "&quantity=" . $quantity,
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

		$feasability = json_decode(curl_exec($curlsize), true);
		curl_close($curlsize);
		if (is_array($feasability)) {
			$feasability = false;
		}

		return $feasability;
	}

	public function change_shipping_label($full_label, $method)
	{
		global $wpdb;

		$nbr_Bottles_magnums = $this->get_nbr_Bottles_magnums();
		$totalBttles = $nbr_Bottles_magnums["nbr_bot"] + $nbr_Bottles_magnums["nbr_mg"];
		$totalBttles = (string)$totalBttles;
		$currentCountry = WC()->customer->get_shipping_country();
		$total_products_ex_tax = WC()->cart->get_subtotal();

		wp_localize_script('VINW_shipping_EXP', 'baseurl', array(get_option('siteurl')));

		if ($method->method_id  == 'Vignoblexport_connect' && $nbr_Bottles_magnums != false && is_checkout()) {
			WC()->cart->calculate_totals();
			WC()->cart->calculate_shipping();
			$packages = WC()->cart->get_shipping_packages();

			foreach ($packages as $package_key => $package) {
				$session_key  = 'shipping_for_package_' . $package_key;
				$stored_rates = WC()->session->__unset($session_key);
			}

			$responsesize = $this->get_sizes($nbr_Bottles_magnums["nbr_bot"], $nbr_Bottles_magnums["nbr_mg"]);
			$var = "";
			$var .= urlencode(json_encode($responsesize["packages"][0]["choice1"]));
			$var .= ";" . "1";

			//If woocommerce session is enabled, sets value in session
			if (WC()->session) {
				WC()->session->set('VINW_CONF_Colis', $var);
			} else {
				// else send error message
				wp_send_json_error(array('message' => 'could not set point. Woocommerce sessions are not enabled!'));
			}

			//If session in not null, store the value in session -> nbr of bottles, nbr of packages & sizes
			if (WC()->session->get('VINW_CONF_Colis') !== null) {
				$package_co = array();
				$arrayssesion = explode(";", WC()->session->get('VINW_CONF_Colis'));
				$package_co[0] = urldecode($arrayssesion[0]);
				$package_colis = json_decode(trim(stripslashes(stripslashes($package_co[0])), '"'), true);
			}

			//Retrieves a value from a session variable. If the value exists, it splits it into an array
			$chaine = WC()->session->get('VINW_CONF_EXP');
			if (isset($chaine)) {
				$tab = explode(";", $chaine);
			}
			$chosen_parcel_point = Controller::get_chosen_point(Shipping_Rate_Util::get_id($method));

			//checks if the variable $chosen_parcel_point is not null
			if (null !== $chosen_parcel_point) {
				//iterates over the session data and clears any session keys that start with 'vinw_chosen_parcel_point_'
				foreach (WC()->session->get_session_data() as $key => $value) {
					if (strstr($key, 'vinw_chosen_parcel_point_')) {
						WC()->session->set($key, null);
					}
				}
			}

			if (WC()->session->customer['country'] && (WC()->session->customer['shipping_postcode'] || WC()->session->customer['country'] == 'AE') && WC()->session->customer['shipping_city']) {
				if (WC()->session->get('VINW_CONF_Colis') !== null) {

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

					$response = json_decode(curl_exec($curlExp), true);
					curl_close($curlExp);

					$Exp_societe = isset($response[0]['typeclient']) ? $response[0]['typeclient'] : "company";
					$Exp_company = isset($response[0]['company']) && strlen($response[0]['company']) > 0  ? $response[0]['company'] : "none";
					$Exp_contact = isset($response[0]['contact']) ? $response[0]['contact'] : "";
					$Exp_address = isset($response[0]['address']) ? $response[0]['address'] : "";
					$Exp_postCode = isset($response[0]['zipCode']) ? $response[0]['zipCode'] : "";
					$Exp_city = isset($response[0]['city']) ? $response[0]['city'] : "";
					$Exp_country = isset($response[0]['country']['countryAlpha2']) ? $response[0]['country']['countryAlpha2'] : "";
					$Exp_email = isset($response[0]['email']) ? $response[0]['email'] : "";
					$Exp_phone = isset($response[0]['telephone']) ? $response[0]['telephone'] : "";

					$data = WC()->session->get('checkout_data');
					$current_destiType = $data['billing_company'];
					$destiType = ($current_destiType == "") ? "individual" : "company";
					$isFeasable = $this->get_feasability($currentCountry, $destiType, $totalBttles);

					if (!$isFeasable) {
						$full_label .= '<br><span class="notif">' . __("Max number of bottles exceeded to deliver to a private individual in this destination country.", "Vignoblexport") . ' </span>';
						return $full_label;
					}

					$curl = curl_init();
					$url = "https://test.extranet.vignoblexport.fr/api/shipment/get-rates";
					$url .= "?expAddress%5BaddressType%5D=" . urlencode($Exp_societe);
					$url .= "&expAddress%5BzipCode%5D=" . urlencode($Exp_postCode);
					$url .= "&expAddress%5Bcity%5D=" . urlencode($Exp_city);
					$url .= "&expAddress%5Bcountry%5D=" . urlencode($Exp_country);
					$url .= "&destAddress%5BaddressType%5D=" . urlencode($destiType);
					$url .= "&destAddress%5BzipCode%5D=" . urlencode(WC()->session->customer['shipping_postcode']);
					$url .= "&destAddress%5Bcity%5D=" . urlencode(WC()->session->customer['shipping_city']);
					$url .= "&destAddress%5Bcountry%5D=" . urlencode(WC()->session->customer['country']);



					if (is_array($package_colis)) {
						$packageNumber = 0;
						foreach ($package_colis as $key => $parcel_type) {
							$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bnb%5D=" . (string)$parcel_type['nbPackages'];
							if ($this->has_sparkling()) {
								$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bweight%5D=" . (string)$parcel_type['sizes']['weightSparkling'];
							} else {
								$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bweight%5D=" . (string)$parcel_type['sizes']['weightStill'];
							}
							$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bwidth%5D=" . $parcel_type['sizes']['width'];
							$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bheight%5D=" . $parcel_type['sizes']['height'];
							$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Blength%5D=" . $parcel_type['sizes']['length'];
							$packageNumber++;
						}
					}

					$customer_country = WC()->customer->get_shipping_country();
					if ($customer_country == "US" || $customer_country == "CA") {
						$url .= "&destAddress%5Bstate%5D=" . urlencode(WC()->customer->get_shipping_state());
					}

					// If today's date is a weekend or a non workable day
					$date = date('l');
					$checkTime = '1900';
					$date2 = date(strtotime($date));

					if (date('Hi') >= $checkTime || $date == 'Saturday' || $date == 'Sunday' || $this->isNotWorkable($date)) {
						$date2 = strtotime($date . ' +2 Weekday');
					} elseif ($this->isNotWorkable($date) && $date == 'Friday') {
						$date2 = strtotime($date . ' +3 Weekday');
					} else {
						$date2 = strtotime($date);
					}

					if (get_option('VINW_EXP_DAYS_MIN') != "") {
						$date2 = date(strtotime($date . ' +' . get_option('VINW_EXP_DAYS_MIN') . ' Weekday'));
						$url .= "&pickupDate=" . date("Y-m-d", $date2);
					} else {
						$url .= "&pickupDate=" . date(strtotime($date . ' +1 Weekday'));
					}
					$url .= "&minHour=09:10:00";
					$url .= "&cutoff=19:00:00";

					$totalBttles =  $nbr_Bottles_magnums['nbr_bot'] + $nbr_Bottles_magnums['nbr_mg'];

					$url .= "&nbBottles=" . (string)$totalBttles;

					if (get_option('VINW_ASSURANCE') == "yes") {
						$url .= "&commodityValue=" . (string)$total_products_ex_tax;
					}

					curl_setopt_array($curl, array(
						CURLOPT_URL => $url,
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

					$responses = json_decode(curl_exec($curl), true);
					curl_close($curl);

					// Filter on offers in response  with carriers preferences
					$filtered_responses = array();
					if (is_countable($responses) && count($responses) > 0) {
						foreach ($responses as $response) {
							if (in_array($response['name'], get_option('VINW_PREF_TRANSP')) || $response['name'] == 'seur' || $response['name'] == 'groupage vignoblexport') {
								array_push($filtered_responses, $response);
							}
						}
					}

					$data = WC()->session->get('checkout_data');
					$current_destiType = $data['billing_company'];
					$destiType = ($current_destiType == "") ? "individual" : "company";
				}

				$reponse = array();
				$compOffreUps = 0;
				$compOffreChrono = 0;

				if (WC()->session->get('VINW_CONF_Colis') !== null) {
					if (is_countable($filtered_responses) && count($filtered_responses) > 0) {
						$reponse = $filtered_responses;
					} else {
						$offre3 = '<div class="offers" style="display:none;" id="offrenotexiste"> ';
						$offre3 .= '<p>' . __("No offers exist", "Vignoblexport") . '</p> ';
						$offre3 .= '<div class="offers" style="display:none;" id="offrenotexiste"> ';
					}
				}

				// -------------------------------- OFFERS LIST--------------------------------------
				if (WC()->session->get('VINW_CONF_Colis') !== null) {
					$offre1 = '<div class="offers" style="display:none;" id="offer1"> ';
					$offre1 .= '<span style="color: black;font-weight: 600;margin-left: 10px;">' . __('Available offers', 'Vignoblexport') . ':</span>';
					foreach ($reponse as $key => $offer) {
						// if(	preg_match('/UPS Standard/', $offer['service'] )==1){
						// if (preg_match('/UPS Access Point Economy/', $offer['service']) == 1) {
						// 	$compOffreUps = $compOffreUps + 1;
						// }
						if (preg_match('/Chrono Relais 13H/', $offer['service']) == 1) {
							$compOffreChrono = $compOffreChrono + 1;
						}
					}
					$i = 0;

					foreach ($reponse as $key => $offer) {
						$offerValue = $offer;
						$oldDate   = $offer['deliveryDate'];
						if (get_option('VINW_EXP_DAYS_MIN')) {
							$date1 = date("d-m-Y", strtotime($oldDate . '+ ' . (int)get_option('VINW_EXP_DAYS_MIN') . 'days'));
						} else {
							$date1 = $oldDate;
						}

						unset($offerValue['surcharges']);
						$encoded_value = urlencode(json_encode($offerValue));
						$offerLogo = $this->getOfferLogo($offer['name'], $offer['service']);
						$price_excl_vat = (float)$offer['price'];

						$tax_category = $this->get_tax_category($Exp_country, $currentCountry);
						$current_dest_country = WC()->customer->get_shipping_country();
						$vat_choice = get_option('VINW_VAT_CHOICE');
						$tax_duties_choice = get_option('VINW_TAX_RIGHTS');

						if ($tax_category == "standard") {
							if ($vat_choice == 'yes') {
								$vat_rate = $this->get_vat_from_dest_country($current_dest_country);
								$finalPrice = $price_excl_vat + (($price_excl_vat * $vat_rate) / 100);
								$finalPrice = round($finalPrice, 2);
								$tax_amount = ($price_excl_vat * $vat_rate) / 100;
								$tax_amount = round($tax_amount, 2);
							} else {
								$finalPrice = round($price_excl_vat, 2);
								$tax_amount = 0;
							}
						} elseif ($tax_category == "intra_eu") {
							if ($vat_choice == 'yes') {
								$vat_rate = $this->get_vat_from_dest_country($current_dest_country);
								$fiscal_rep = $this->get_charges_ue($price_excl_vat, $Exp_country);
								$finalPrice = (float)$fiscal_rep + (($price_excl_vat * $vat_rate) / 100);
								$finalPrice = round($finalPrice, 2);
								$tax_amount = (($price_excl_vat * $vat_rate) / 100) - $price_excl_vat;
								$tax_amount = round($tax_amount, 2);
							} else {
								$fiscal_rep = $this->get_charges_ue($price_excl_vat, $Exp_country);
								$finalPrice = round((float)$fiscal_rep, 2);
								$tax_amount = 0;
							}
						} else { // $tax_category == "inter"
							$tax_and_duties = $this->get_tax_and_duties($offer['name']);
							if ($tax_duties_choice == 'exp') {
								$finalPrice = $price_excl_vat + $tax_and_duties['price'];
								$tax_amount = round($tax_and_duties['price'], 2);
							} else {
								$finalPrice = $price_excl_vat;
								$tax_amount = round($tax_and_duties['price'], 2);
							}
						}

						if (get_option('VINW_ASSURANCE') == 'yes') {
							$finalPrice = $finalPrice + $offer['insurancePrice'];
						}

						if (preg_match('/UPS Access Point Economy/', $offer['service']) == 0 && preg_match('/Chrono Relais 13H/', $offer['service']) == 0) {
							$offre1 .= '<br><div class="offer-cont" ><input type="radio" name="offer[]" data-index="0" data-name="' . $offer['name'] . '" id="shipping_method_offer_' . $key . '" value="' . $encoded_value . '" data-taxamount="' . $tax_amount . '" data-insurance="' . $offer['insurancePrice'] . '" class="shipping_method">';
							$offre1 .= '<input type="hidden" name="priceOffre" id="priceOffre_' . $key . '"  value="' . sprintf("%01.2f", $finalPrice) . '" >';
							$offre1 .= '<img style="display:unset; max-width: 55px; margin-right: 8px;" src="' . $offerLogo . '" />';
							$offre1 .= '<p  id="offer-cont_' . $key . '" for="shipping_method_offer_' . $key . '"><strong>' . $offer['service'] . '</strong><br>  ' . __('Price: ', 'Vignoblexport') . ' <strong>' . sprintf("%01.2f", $finalPrice) . '€</strong> <br> | ' . __('Estimated delivery', 'Vignoblexport') . ' ' . $date1 . '<br> </p></div>';
						}
						$i++;
					}

					$offre1 .= '</div>';

					// ----------------------------------------------------------------------
					$offre2 = '<div class="offers" style="display:none;" id="offer2"> ';
					$offre2 .= '<span style="color: black;font-weight: 600;margin-left: 10px;">Available offers:</span>';
					$i = 0;
					foreach ($reponse as $key => $offer) {
						// $subject = $offer['service'];
						$offerValue = $offer;
						unset($offerValue['surcharges']);
						$price_excl_vat = (float)$offer['price'];
						$encoded_value = urlencode(json_encode($offerValue));

						if (preg_match('/UPS Access Point Economy/', $offer['service']) == 1 || preg_match('/Chrono Relais 13H/', $offer['service']) == 1) {
							// if(	preg_match('/UPS Standard/', $offer['service'] )==1){
							$oldDate2   = $offer['deliveryDate'];

							if (get_option('VINW_EXP_DAYS_MIN')) {
								$date2 = date("d-m-Y", strtotime($oldDate2 . '+ ' . (int)get_option('VINW_EXP_DAYS_MIN') . 'days'));
							} else {
								$date2  = $oldDate2;
							}

							$tax_category = $this->get_tax_category($Exp_country, $currentCountry);
							$offerLogo = $this->getOfferLogo($offer['name'], $offer['service']);

							if ($tax_category == "standard") {
								if ($vat_choice == 'yes') {
									$vat_rate = $this->get_vat_from_dest_country($current_dest_country);
									$finalPrice = $price_excl_vat + (($price_excl_vat * $vat_rate) / 100);
									$finalPrice = round($finalPrice, 2);
									$tax_amount = ($price_excl_vat * $vat_rate) / 100;
									$tax_amount = round($tax_amount, 2);
								} else {
									$finalPrice = round($price_excl_vat, 2);
									$tax_amount = 0;
								}
							} elseif ($tax_category == "intra_eu") {
								if ($vat_choice == 'yes') {
									$vat_rate = $this->get_vat_from_dest_country($current_dest_country);
									$fiscal_rep = $this->get_charges_ue($price_excl_vat, $Exp_country);
									$finalPrice = (float)$fiscal_rep + (($price_excl_vat * $vat_rate) / 100);
									$finalPrice = round($finalPrice, 2);
									$tax_amount = (($price_excl_vat * $vat_rate) / 100) - $price_excl_vat;
									$tax_amount = round($tax_amount, 2);
								} else {
									$fiscal_rep = $this->get_charges_ue($price_excl_vat, $Exp_country);
									$finalPrice = round((float)$fiscal_rep, 2);
									$tax_amount = 0;
								}
							} else { // $tax_category == "inter"
								$tax_and_duties = $this->get_tax_and_duties($offer['name']);
								if ($tax_duties_choice == 'exp') {
									$finalPrice = $price_excl_vat + $tax_and_duties['price'];
									$tax_amount = round($tax_and_duties['price'], 2);
								} else {
									$finalPrice = $price_excl_vat;
									$tax_amount = round($tax_and_duties['price'], 2);
								}
							}

							if (get_option('VINW_ASSURANCE') == 'yes') {
								$finalPrice = $finalPrice + $offer['insurancePrice'];
							}

							$offre2 .= '<br><div class="offer-cont" ><input type="radio" name="offer[]" data-index="0" data-name="' . $offer['name'] . '" id="shipping_method_offer_' . $key . '" value="' . $encoded_value . '" data-taxamount="' . $tax_amount . '"  data-insurance="' . $offer['insurancePrice'] . 'class="shipping_method" >';
							$offre2 .= '<input type="hidden" name="priceOffre" id="priceOffre_' . $key . '"  value="' . sprintf("%01.2f", $finalPrice) . '">';
							$offre2 .= '<img style="display:unset; max-width: 55px;    margin-right: 8px;" src="' . $offerLogo . '" />';
							$offre2 .= '<p  id="offer-cont_' . $key . '" for="shipping_method_offer_' . $key . '"><strong>' . $offer['service'] . '</strong><br>  ' . __('Price: ', 'Vignoblexport') . '  <strong>' . sprintf("%01.2f", $finalPrice) . '€</strong> <br>| ' . __('Estimated delivery', 'Vignoblexport') . ' ' . $date2 . '<br> </p></div>';
						}
						$i++;
					}

					$offre2 .= '</div>';
				}

				$full_label .= '<div class="appointment-link" style="margin-left: 10px;" id="container-method-Vignoblexport-link">';
				$full_label .= '<input type="hidden" id="form_nb_bouteilles" name="form[nb_bouteilles]" value="' . (string) $nbr_Bottles_magnums["nbr_bot"] . '">';
				$full_label .= '<input type="hidden" id="form_nb_magnums" name="form[nb_magnums]" value="' . (string) $nbr_Bottles_magnums["nbr_mg"] . '">';

				if (WC()->session->get('VINW_CONF_Colis') !== null) {
					//render offers
					if (get_option("VINW_PREF_STAT") == "domicile") {
						if (!empty($reponse)) {
							$full_label .= '<input type="radio" name="shipping_mettype[]" id="shipping_method_0_Vignoblexport_connect_dom" value="domicile" class="shipping_method">';
							$full_label .= '<label for="shipping_method_0_Vignoblexport_connect_dom">' . __('Domestic', 'Vignoblexport') . '</label>';

							if (is_countable($responses) && count($responses) > 0) {
								$full_label .= $offre1;
							} else {
								$full_label .= $offre3;
							}
						}
					}

					if (get_option("VINW_PREF_STAT") == "les deux") {
						if (!empty($reponse)) {
							if (($compOffreUps > 0 || $compOffreChrono > 0) && get_option("VINW_MAPBOX_ACCESS_KEY") && get_option("mapbox-api-key-validate") == "1") {
								$full_label .= '<input type="radio" name="shipping_mettype[]"  id="shipping_method_0_Vignoblexport_connect_pr" value="pointRelais" class="shipping_method">';
								$full_label .= '<label for="shipping_method_0_Vignoblexport_connect_pr">' . __('Pickup point', 'Vignoblexport') . '</label>';

								if (is_countable($responses) && count($responses) > 0) {
									$full_label .= $offre2;
								}
							}

							$full_label .= '<br><input type="radio" name="shipping_mettype[]"  id="shipping_method_0_Vignoblexport_connect_dom" value="domicile"   class="shipping_method" >';
							$full_label .= '<label for="shipping_method_0_Vignoblexport_connect_dom">' . __('Domestic', 'Vignoblexport') . '</label>';

							if (is_countable($responses) && count($responses) > 0) {
								$full_label .= $offre1;
							}
						}
					}
					if (get_option("VINW_PREF_STAT") == "pointRelais" && (in_array('ups', get_option('VINW_PREF_TRANSP')) || in_array('chronopost', get_option('VINW_PREF_TRANSP'))) && get_option("VINW_MAPBOX_ACCESS_KEY") && get_option("mapbox-api-key-validate") == "1") {
						if ($compOffreUps > 0 || $compOffreChrono > 0) {
							$full_label .= '<br><input type="radio" name="shipping_mettype[]"  id="shipping_method_0_Vignoblexport_connect_pr" value="pointRelais" class="shipping_method">';
							$full_label .= '<label for="shipping_method_0_Vignoblexport_connect_pr">' . __('Pickup point', 'Vignoblexport') . '</label>';

							if (is_countable($responses) && count($responses) > 0) {
								$full_label .= $offre2;
							} else {
								$full_label .= $offre3;
							}
						}

						if (is_checkout() && (get_option("VINW_PREF_STAT") == "domicile"	|| get_option("VINW_PREF_STAT") == "les deux")) {
							if ($tab[4] == "domicile") {
								foreach (WC()->session->get_session_data() as $key => $value) {
									if (strstr($key, 'vinw_chosen_parcel_point_')) {
										WC()->session->set($key, null);
									}
								}
							}
						}
					}
					// }
				}
				if (WC()->session->get('VINW_CONF_Colis')) {
					if (!empty($reponse)) {
						$chosen_parcel_point = Controller::get_chosen_point(Shipping_Rate_Util::get_id($method));

						if ($chosen_parcel_point !== null) {
							$style = '';
							$name = $chosen_parcel_point->name;
						} else {
							$style = '';
							$name = " ";
						}

						if (get_option("VINW_PREF_STAT") == "pointRelais" && (in_array('ups', get_option('VINW_PREF_TRANSP')) || in_array('chronopost', get_option('VINW_PREF_TRANSP'))) && ($compOffreUps > 0 || $compOffreChrono > 0) && get_option("VINW_MAPBOX_ACCESS_KEY") && get_option("mapbox-api-key-validate") == "1") {
							// $full_label .= '<br><input type="button" value="' . __('Confirm my shipping choice', 'Vignoblexport') . '"  id="validate">';
							if (is_checkout() && isset($chaine)) {
								$tab = explode(";", $chaine);

								if ($tab[4] == "pointRelais") {
									$full_label .= '<div class="relay"><br><br><span class="VINW-select-parcel active-relay" id="relayaction">' . __('Choose pickup point', 'Vignoblexport') . '</span>';
									$full_label .= '<br><br><span class="VINW-parcel-client" ' . $style . '>' . __('Your pickup point:', 'Vignoblexport') . ' <span class="VINW-parcel-name">' . $name . '</span></span></div>';
								}
							}
						} elseif (get_option("VINW_PREF_STAT") == "domicile" || get_option("VINW_PREF_STAT") == "les deux") {
							// $full_label .= '<br><br><input type="button" value="' . __('Confirm my shipping choice', 'Vignoblexport') . '" id="validate">';
							if (is_checkout() && isset($chaine)) {
								$tab = explode(";", $chaine);

								if ($tab[4] == "pointRelais") {
									$full_label .= '<div class="relay"><br><br><span class="VINW-select-parcel active-relay" id="relayaction">' . __('Choose pickup point', 'Vignoblexport') . '</span>';
									$full_label .= '<br><br><span class="VINW-parcel-client" ' . $style . '>' . __('Your pickup point:', 'Vignoblexport') . ' <span class="VINW-parcel-name">' . $name . '</span></span></div>';
								}
							}
						} else {
							WC()->session->set('VINW_CONF_EXP', null);
							WC()->cart->calculate_totals();
							WC()->cart->calculate_shipping();
							$packages = WC()->cart->get_shipping_packages();

							foreach ($packages as $package_key => $package) {
								$session_key  = 'shipping_for_package_' . $package_key;
								$stored_rates = WC()->session->__unset($session_key);
							}

							$full_label .= '<br><span class="notif">' . __("No shipping offer found for this address, please check your shipping informations or choose another shipping method.", "Vignoblexport") . ' </span>';
						}
					} else {
						WC()->session->set('VINW_CONF_EXP', null);
						WC()->cart->calculate_totals();
						WC()->cart->calculate_shipping();
						$packages = WC()->cart->get_shipping_packages();

						foreach ($packages as $package_key => $package) {
							$session_key  = 'shipping_for_package_' . $package_key;
							$stored_rates = WC()->session->__unset($session_key);
						}

						if (null !== $chosen_parcel_point) {
							foreach (WC()->session->get_session_data() as $key => $value) {
								if (strstr($key, 'vinw_chosen_parcel_point_')) {
									WC()->session->set($key, null);
								}
							}
						}
						$full_label .= '<br><span class="notif">' . __("No shipping offer found for this address, please check your shipping informations or choose another shipping method.", "Vignoblexport") . ' </span>';
					}
				}
				$full_label .= '</div>';
			} else {
				WC()->session->set('VINW_CONF_EXP', null);
				WC()->cart->calculate_totals();
				WC()->cart->calculate_shipping();
				$packages = WC()->cart->get_shipping_packages();

				foreach ($packages as $package_key => $package) {
					$session_key  = 'shipping_for_package_' . $package_key;
					$stored_rates = WC()->session->__unset($session_key);
				}

				if (null !== $chosen_parcel_point) {
					foreach (WC()->session->get_session_data() as $key => $value) {
						if (strstr($key, 'vinw_chosen_parcel_point_')) {
							WC()->session->set($key, null);
						}
					}
				}
				$full_label .= '<br><span class="notif"> ' . __('Please enter your adress', 'Vignoblexport') . ' </span>';
			}
		}

		//Checks if the $method object's method_id is equal to 'Vignoblexport_connect', $nbr_Bottles_magnums is not false, and is_checkout() is false. If all conditions are met, it appends a notification message to the $full_label variable.
		if ($method->method_id  == 'Vignoblexport_connect' && $nbr_Bottles_magnums != false && !is_checkout()) {
			$full_label .= '<br><br><span class="notif"> ' . __('You can choose your shipment on the next page', 'Vignoblexport') . ' </span>';
		}
		return $full_label;
	}
}
