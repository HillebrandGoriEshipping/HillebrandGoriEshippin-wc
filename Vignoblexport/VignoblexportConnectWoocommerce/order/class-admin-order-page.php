<?php

/**
 * Contains code for order item shipping util class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Util
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Order;

use Vignoblexport\VignoblexportConnectWoocommerce\Util\Order_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Util;

// use WC_Order;

/**
 * Admin_Order_Page class.
 *
 * Adds additional info to order page.
 *
 * @class       Admin_Order_Page
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Order
 * @category    Class
 * @author      API Vignoblexport
 */
class Admin_Order_Page
{
	public $plugin_url;
	public $plugin_version;
	public $tracking;
	public $parcelpoint;

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
		$this->tracking       = null;
		$this->parcelpoint    = null;
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run()
	{
		$controller = new Controller(
			array(
				'url'     => $this->plugin_url,
				'version' => $this->plugin_version,
			)
		);
		add_action('woocommerce_admin_order_items_after_line_items', array($this, 'action_before_save_order_item_boutton'));
		add_action('admin_enqueue_scripts', array($controller, 'tracking_styles'));
		add_filter('add_meta_boxes_shop_order', array($this, 'add_tracking_to_admin_order_page'), 10, 2);
		add_filter('add_meta_boxes_shop_order', array($this, 'add_parcelpoint_to_admin_order_page'), 10, 2);
		add_filter('woocommerce_admin_order_preview_get_order_details', array($this, 'order_view_modal_details'));
		add_filter('woocommerce_admin_order_preview_end', array($this, 'order_view_modal'));
		add_action('woocommerce_order_before_calculate_totals', array($this, 'order_update_item_number'));
		add_action('woocommerce_saved_order_items', array($this, 'update_shipping_cost_and_tax'));
	}

	function order_update_item_number()
	{
		global $wpdb;
		if (isset($_GET['post'])) {
			$order_id = $_GET['post'];
			$order = wc_get_order($order_id);
		} else {
			return;
		}

		// get the package in DB
		$query = "SELECT `package` FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $order_id . "'";
		$result = $wpdb->get_results($query, ARRAY_A);

		$package = json_decode($result[0]['package'], true);

		// check if order exist
		if (isset($order_id)) {
			$product_quantity = 0;
			foreach ($order->get_items() as $item_id => $item) {
				$product_quantity += $item->get_quantity();
			}

			// update nbr of bottles and weight
			$package[0]['nbBottles'] = $product_quantity;
			$package[0]['sizes']['weightStill'] = round($product_quantity * 1.2, 1);
			$package[0]['sizes']['weightSparkling'] = round($product_quantity * 1.6, 1);
			$updated_package = $package;
			$updated_package = json_encode($updated_package);

			$table = $wpdb->prefix . "VINW_order_expidition";

			// update DB
			$wpdb->update(
				$table,
				array('nbr_bottles' => $product_quantity),
				array('order_id' => $order_id),
				array('%d'),
			);

			$wpdb->update(
				$table,
				array('package' => $updated_package),
				array('order_id' => $order_id),
				array('%s'),
			);
		}
	}

	function get_weight_order($order_id)
	{
		$order = wc_get_order($order_id);
		$order_items = $order->get_items();
		$weightfin = 0;
		foreach ($order_items as $item_id => $item) {
			$product_id = version_compare(WC()->version, '3.0', '<') ? $item['product_id'] : $item->get_product_id();
			$_weight = get_post_meta($product_id, '_weight', true);
			if ($_weight == 0) {
				$_weight = (float)1.9 * (float)$item->get_quantity();
			} else {
				$_weight = (float)$_weight * (float)$item->get_quantity();
			}
			$weightfin += $_weight;
		}
		return $weightfin;
	}

	function get_weight_max($order_id)
	{
		$order = wc_get_order($order_id);
		$order_items = $order->get_items();
		$weightmax = 0;
		foreach ($order_items as $item_id => $item) {
			$product_id = version_compare(WC()->version, '3.0', '<') ? $item['product_id'] : $item->get_product_id();
			$_weight = get_post_meta($product_id, '_weight', true);
			if ($_weight == 0) {
				$_weight = (float)1.9;
			} else {
				$_weight = (float)$_weight;
			}
			if ($_weight >= $weightmax) {
				$weightmax = $_weight;
			}
		}
		return $weightmax;
	}

	function update_shipping_cost_and_tax($order_id)
	{
		global $wpdb;
		$order = wc_get_order($order_id);
		if (!$order) {
			return;
		}

		$query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $order_id . "'";
		$result = $wpdb->get_results($query, ARRAY_A);
		$shipping_total = json_decode(trim(stripslashes(stripslashes($result[0]["offre"])), '"'), true);
		$shipping_total = $shipping_total["price"];

		$shipping_tax = $result[0]["tax_amount"];
		$shipping_tax = (string)$shipping_tax;

		$insurance = $result[0]["insurance"];
		$insurance = (string)$insurance;

		$shipping_total = (float)$shipping_total + (float)$shipping_tax + (float)$insurance;

		$subtotal = $order->get_subtotal();
		$cart_items_tax = $order->get_total_tax();

		$total = (float)$shipping_total + (float)$subtotal + (float)$cart_items_tax;

		$order->set_shipping_total($shipping_total);
		$order->set_shipping_tax($shipping_tax);

		$order->set_total($total);

		$order->save();
	}

	function action_before_save_order_item_boutton($order_id)
	{
		global $wpdb;
		$order = wc_get_order($order_id);
		$order_data = $order->get_data();
		$weight_max = $this->get_weight_max($order_id);
		$weight = $this->get_weight_order($order_id);
		$item_count = $order->get_item_count();
		$total = $order->get_total();
		$order_status = $order_data['status'];
		$destAddressType = ($order_data['billing']['company'] == "") ? "individual" : "company";
		$city = $order_data['shipping']['city'];
		$expcity = $order_data['billing']['city'];
		$exppostalCode = $order_data['shipping']['postcode'];
		$expcountry = $order_data['shipping']['country'];
		$postalCode = $order_data['shipping']['postcode'];
		$country = $order_data['shipping']['country'];
		$packageNumber = 0;
		$nbr_bottles = 0;
		$nbr_Magnums = 0;
		$whopaystaxesduties = get_option('VINW_TAX_RIGHTS');

		// Get tax category
		$currentCountry = $order->get_shipping_country();
		$wc_shop_country = isset($response[0]['country']['countryAlpha2']) ? $response[0]['country']['countryAlpha2'] : "";

		foreach ($order->get_items('shipping') as $item_id => $shipping_item_obj) {
			$shipping_method_id = $shipping_item_obj->get_method_id();
			if ($shipping_method_id == 'Vignoblexport_connect') {
				$query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $order_id . "'";
				//phpcs:ignore
				$result = $wpdb->get_results($query, ARRAY_A);
				$exptax = $result[0]['charge_type'];
				$tax_amount = $result[0]['tax_amount'];
				$nbr_bottles = (float)$result[0]['nbr_bottles'];
				$nbr_Magnums = (float)$result[0]['nbr_Magnums'];
				$fraischarge = null;
				$type_package = $result[0]['package_type'];
				$vat_rate = $this->get_vat_from_country($country);
				$count  = $order->get_item_count();
				$package = json_decode(trim(stripslashes(stripslashes($result[0]["package"])), '"'), true);
				$expedition_type = $result[0]['expedition_type'];
				$currency = $result[0]['currency'];
				$vat_transport = $result[0]['vat_transport'];
				$vat_accises = $result[0]['vat_accises'];

				$get_size = json_decode($this->get_size($nbr_bottles, $nbr_Magnums), true);

				$get_pallet_size = json_decode($this->get_pallet_size(), true);

				$pack = array();

				for ($j = 0; $j < count((is_countable($get_size["packages"]) ? $get_size["packages"] : [])); $j++) {
					$pack[$j] = array();
					$ind = 0;

					for ($i = 0; $i < count($get_size["packages"][$j]); $i++) {
						$ind = $i + 1;
						$nbPack = 0;
						for ($k = 0; $k < count($get_size["packages"][$j]['choice1']); $k++) {
							$nbPack = $nbPack + $get_size["packages"][$j]['choice1'][$k]["nbPackages"];
						}

						$pack[$j]['choice1'] = $nbPack;
					}
				}

				$offres = json_decode(trim(stripslashes(stripslashes($result[0]["offre"])), '"'), true);

				if ($type_package == "pallet") {
					// $get_pallet_rates = json_decode($this->get_pallet_rates($nbr_Magnums, $nbr_bottles, $count, $package, $weight, get_option('VINW_ACCESS_KEY'), $destAddressType, $postalCode, $city, $country), true);
				} else {
					$get_rates = $this->get_rates($order_id);
				}

				$get_rate = array();

				if ($type_package == "colis") {
					if (is_countable($get_rates) && count($get_rates) > 0) {
						$get_rate = $get_rates;
					} else {
						$offre3 = '<tr><td colspan="5"> ';
						$offre3 .= '<p>' . __("No offer available", "Vignoblexport") . '</p> ';
						$offre3 .= '</td></tr> ';
					}
				}
				$type_livraison = $result[0]["type_liv"];
				$packageNumber1 = 0;

				if ($type_package == "colis") {
					$fields1 = '<style>
				.littleInput{    width:90px !important;}
				.woocommerce_order_items_wrapper table.woocommerce_order_items td{padding: 1.5em 0.5em 1em !important;}
				.woocommerce_order_items_wrapper table.woocommerce_order_items th{padding: 1.5em 0.5em 1em !important;}</style><tbody>
				<tr>
					<th>' . __('Nb.Package', 'Vignoblexport') . '</th>
					<th>' . __('Nb.Bottles', 'Vignoblexport') . '</th>
					<th>' . __('Width', 'Vignoblexport') . '</th>
					<th>' . __('Height', 'Vignoblexport') . '</th>
					<th>' . __('length', 'Vignoblexport') . '</th>
					<th>' . __('weight', 'Vignoblexport') . '</th>
				</tr>';

					for ($l = 0; $l < count($package); $l++) {
						$fields1 .= '<tr class="shipping" id="shipping' . $l . '">';
						$fields1 .= "<td>" . $package[$l]['nbPackages'] . "</td>";
						if (isset($package[$l]['nbBottles'])) {
							$fields1 .= "<td>" . $package[$l]['nbBottles'] . ' ' . __('Bottles', 'Vignoblexport') . '</td>';
						} else {
							$fields1 .= "<td>" . $package[$l]['nbMagnums'] . ' Magnums</td>';
						}
						$fields1 .= "<td>" . $package[$l]['sizes']['width'] . ' cm</td>';
						$fields1 .= "<td>" . $package[$l]['sizes']['height'] . ' cm</td>';
						$fields1 .= "<td>" . $package[$l]['sizes']['length'] . ' cm</td>';
						$fields1 .= "<td>" . $package[$l]['sizes']['weightStill'] . " Kg</td>";

						$fields1 .= '</tr>';
						$packageNumber1++;
					}
				} else {
					$fields1 = '<style>
				.woocommerce_order_items_wrapper table.woocommerce_order_items td{padding: 1.5em 0.5em 1em !important;}
				.woocommerce_order_items_wrapper table.woocommerce_order_items th{padding: 1.5em 0.5em 1em !important;}</style><tbody>
				<tr>
				<th>' . __('Nb.Bottles', 'Vignoblexport') . '</th>
				<th>' . __('Width', 'Vignoblexport') . '</th>
				<th>' . __('Height', 'Vignoblexport') . '</th>
				<th>' . __('length', 'Vignoblexport') . '</th>
				<th>' . __('weight', 'Vignoblexport') . '</th>
				
				</tr>';
					$fields1 .= '<tr class="shipping">';

					$fields1 .= "<td>" . $nbr_bottles . ' Bottles / ' . $nbr_Magnums . ' Magnums </td>';
					$fields1 .= "<td>" . $package['width'] . ' cm</td>';
					$fields1 .= "<td>" . $package['height'] . ' cm</td>';
					$fields1 .= "<td>" . $package['length'] . " cm</td>";
					$fields1 .= "<td>" . $weight . ' Kg</td>';
					$fields1 .= '</tr>';
					$packageNumber1++;
				}

				if (isset($offres['price']) && isset($tax_amount) && $offres['price'] != $order->get_shipping_total()) {
					$diffrence = ($offres['price'] + $tax_amount) - $order->get_shipping_total();
				} else {
					$diffrence = 0;
				}

				// if ($diffrence != 0) {
				// 	$this->sendDifferenceMail(get_option('admin_email'), "Admin", $diffrence);
				// }
?>
				<tbody id="offre">
					<!----------------------------------------------------------------
					<tr class="shipping" data-order_item_id="19">
						<td colspan="3">
							<strong><?php esc_html_e('Price difference:', 'Vignoblexport') ?></strong>
							<?php
							if ($diffrence > 0) { ?>
								<span class="VINW-price-difference" style="color: orange;">
								<?php } else if ($diffrence < 0) {  ?>
									<span class="VINW-price-difference" style="color: green;">
									<?php } else { ?>
										<span class="VINW-price-difference">
										<?php }
									echo $diffrence . " €";  ?>
										</span>
						</td>
						<td colspan="2">

						</td>
					</tr>
					----------------------------------------------------------------->
					<?php if ($offres != null) { ?>
						<tr class="shipping" data-order_item_id="19">
							<td colspan="3">
								<strong><?php esc_html_e('Your expedition method:', 'Vignoblexport') ?> </strong>
								<input type="hidden" name="shipping_mettype" id="shipping_method_type" value="<?php echo $type_livraison ?>" class="shipping_method">
								<input type="hidden" id="form_nb_bouteilles" name="form_nb_bouteilles" value="<?php echo $nbr_bottles ?>">
								<input type="hidden" id="form_nb_magnums" name="form_nb_magnums" value="<?php echo $nbr_Magnums ?>">
								<?php
								if ($type_livraison == "domicile") {
									esc_html_e('Domestic', 'Vignoblexport');
								} else {
									esc_html_e('Relay point', 'Vignoblexport');
								}	 ?>
								<br /><br />
								<strong> <?php esc_html_e('Chosen offer:', 'Vignoblexport') ?> </strong><?php echo $offres['service'] ?>
								<br /><br />
								<strong><?php esc_html_e('Delivery date:', 'Vignoblexport') ?> </strong><?php echo $offres['deliveryDate'] ?> at <?php echo $offres['pickupTime'] ?>
								<?php if (get_option('VINW_TAX_RIGHTS') == "dest"  && $expedition_type == "export") { ?>
									<br /><br />
									<strong><?php esc_html_e('Estimated tax and duties:', 'Vignoblexport'); ?></strong>
								<?php echo $tax_amount . " " . $currency;
									esc_html_e(' at the recipient expense', 'Vignoblexport');
								}
								?>
							</td>
							<td colspan="3">
								<table id="shipping-details-table" style="width: 100%; border: none;">
									<?php if (get_option('VINW_OSS') == "yes") {
										$vat_rate = $this->get_vat_from_country($country);
									} else {
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
										$shop_country = isset($response[0]['country']['countryAlpha2']) ? $response[0]['country']['countryAlpha2'] : "";
										$vat_rate = $this->get_vat_from_country($shop_country);
									} ?>
									<thead>
										<tr>
											<td style="border-top: none;"><strong><?php esc_html_e('Shipping costs details:', 'Vignoblexport') ?></strong></td>
										</tr>
									</thead>
									<tbody>
										<!-- EXPORT -->
										<?php if ($expedition_type == "export") { ?>
											<tr>
												<th><strong> <?php esc_html_e('Transport:', 'Vignoblexport') ?> </strong></th>
												<td style="border: none;"><?php echo number_format($offres['price'], 2, ',', ' ') . " €"; ?></td>
											</tr>
											<?php if (get_option('VINW_TAX_RIGHTS') == "exp") { ?>
												<tr>
													<td><strong> <?php esc_html_e('Estimated tax and duties:', 'Vignoblexport') ?> </strong></td>
													<td style="border: none;"><?php echo $tax_amount . " " . $currency; ?>
														<?php
														if (get_option('VINW_TAX_RIGHTS') == "exp") {
															esc_html_e(' at your expense (included in shipping price)', 'Vignoblexport');
														} else {
															esc_html_e(' at the recipient expense', 'Vignoblexport');
														}
														?>
													</td>
													<?php $total = $offres['price'] + $tax_amount; ?>
												</tr>
											<?php } else {

												$total = $offres['price'];
											} ?>

											<!-- FISCAL REP.-->
										<?php } elseif ($expedition_type == "fiscal_rep") { ?>
											<tr>
												<th><strong> <?php esc_html_e('Transport:', 'Vignoblexport'); ?> </strong></th>
												<td style="border: none;"><?php echo number_format($offres['price'], 2, ',', ' ') . " €"; ?></td>
											</tr>
											<tr>
												<th><strong> <?php esc_html_e('VAT on transport:', 'Vignoblexport'); ?> </strong></th>
												<td style="border: none;"><?php echo number_format($vat_transport, 2, ',', ' ') . " €"; ?></td>
											</tr>
											<tr>
												<?php $accises_excl_tax = $tax_amount - $vat_transport - $vat_accises; ?>
												<th><strong> <?php esc_html_e('Excise duty:', 'Vignoblexport'); ?> </strong></th>
												<td style="border: none;"><?php echo number_format($accises_excl_tax, 2, ',', ' ') . " €"; ?></td>
											</tr>
											<tr>
												<th><strong> <?php esc_html_e('VAT on excise duty:', 'Vignoblexport'); ?> </strong></th>
												<td style="border: none;"><?php echo number_format($vat_accises, 2, ',', ' ') . " €"; ?></td>
											</tr>
											<?php $total = $offres['price'] + $vat_transport + $accises_excl_tax + $vat_accises; ?>
										<?php } else { ?>
											<!-- DOMESTIC -->
											<tr>
												<th><strong> <?php esc_html_e('Transport:', 'Vignoblexport'); ?> </strong></th>
												<td style="border: none;"><?php echo number_format($offres['price'], 2, ',', ' ') . " €"; ?></td>
											</tr>
											<tr>
												<th><strong> <?php esc_html_e('VAT', 'Vignoblexport'); ?> </strong></th>
												<td style="border: none;"><?php echo number_format($tax_amount, 2, ',', ' ') . " €"; ?></td>
											</tr>
											<?php $total = $offres['price'] + $tax_amount; ?>
										<?php } ?>
										<?php if (get_option('VINW_ASSURANCE') == "yes" && $result[0]['insurance'] > 0) { ?>
											<tr>
												<th><strong> <?php esc_html_e('Insurance:', 'Vignoblexport') ?> </strong></th>
												<?php $total += $result[0]['insurance']; ?>
												<td style="border: none;"><?php echo number_format($result[0]['insurance'], 2, ',', ' ') . " €"; ?></td>
											</tr>
										<?php } ?>
									</tbody>
									<tfoot>
										<th style="border-bottom: none;"><?php esc_html_e('Total:', 'Vignoblexport') ?></th>
										<td style="border: none;"><?php echo number_format($total, 2, ',', ' ') . " €"; ?></td>
									</tfoot>
								</table>
							</td>
						</tr>
					<?php } ?>
				</tbody>
				<tbody id="packagin">
					<tr class="shipping" data-order_item_id="16">
						<td colspan="3"><strong><?php esc_html_e('Chosen package:', 'Vignoblexport') ?> </strong>
							<?php
							switch ($type_package) {
								case 'pallet':
									echo __('pallet', 'Vignoblexport');
									break;
								case 'colis':
									echo __('colis',  'Vignoblexport');
									break;
							} ?></td>
						<td colspan="2">

						</td>
					</tr>
				</tbody>

				<?php echo $fields1;
				?>

				<?php $field2 = '<tbody  id="editColisage" style="display:none">
						<tr class="shipping" data-order_item_id="16">
					<td ><strong>' . __('Packing:', 'Vignoblexport') . '</strong> </td>

					<td class="name">
				
					<select id="type_colissage" name="type_colissage" class="form-control">
					<option value="choisir" >' . __('select the type', 'Vignoblexport') . '</option>		
					<option value="colis" >' . __('Parcels', 'Vignoblexport') . '</option>
					'/*<option value="pallet">' . __('Pallet', 'Vignoblexport') . </option> option pallet disabled*/
					. '</select>
				
				    </td>
					<td class="name"></td>
					<td class="name"></td>
					<td class="name"></td>
					<td class="name"></td>
					</tr></tbody>';
				echo $field2;
				if ($offres != null || $type_package == "pallet") {
				?>
					<tbody id="packaging-body" style="display:none">
						<tr valign="top">
							<td style="border-right: none !important;">
								<label><?php esc_html_e('quantity', 'Vignoblexport'); ?></label>
								<div class="tooltip-container" data-html="true" title="" data-original-title="<p style='padding: 10px;'>Attention, veillez à utiliser le colis adapté au nombre de bouteilles : un carton de 6 est prévu pour 6 bouteilles et pas moins, etc.</p>">
									<?php echo $nbr_bottles; ?> <span style="display: inline-block;">bottles</span>
								</div>
								<div>
									<?php echo $nbr_Magnums; ?> <span style="display: inline-block;">magnums</span>
								</div>
								</div>
							</td>
							<td style="border-left: none !important;">
								<div>
									<label for="pack-select">Choose Number of packages:</label>

									<select name="form[nb_pack]" id="form_nb_pack">
										<option value="">Choose number</option>
										<?php for ($j = 0; $j < count($pack); $j++) { ?>

											<option value="<?php echo $pack[$j]['choice1'] ?>"><?php echo $pack[$j]['choice1']; ?></option>

										<?php } ?>

									</select>
								</div>
							</td>
							<td colspan="4" style="border-left: none !important;">
								<ul id="choices">
								</ul>
							</td>

						</tr>

					</tbody>
					<tbody id="form_colis">

					</tbody>
				<?php
				}

				if ($offres != null || $type_package == "colis") {
					$fields = '<tbody  id="editPallet" style="display:none" >
					
				 <tr>

				 </tr>

					 <tr>
						 <th>' . __('Choice', 'Vignoblexport') . '</th>
					
						 <th>' . __('Width', 'Vignoblexport') . '</th>
						 <th>' . __('Height', 'Vignoblexport') . '</th>
						 <th>' . __('length', 'Vignoblexport') . '</th>
						 <th>' . __('weightStill', 'Vignoblexport') . '</th>

						 
					 </tr>';
					$indP =  count((is_countable($get_size["packages"]) ? $get_size["packages"] : [])) + 1;
					for ($j = 0; $j < count($get_pallet_size); $j++) {
						$indP = $indP + 1;
						$get_pallet_size[$j]["nb"] = $item_count;
						$get_pallet_size[$j]["weightStill"] = $weight;
						$get_pallet_size[$j]["length"] = 20;
						$choiseValue = $get_pallet_size[$j];

						$encoded_value_choice = urlencode(json_encode($choiseValue));
						$fields .= '<th rowspan="2" > <input type="radio" id="choices' . $indP . '" name="choix" value="' . $encoded_value_choice . '"></th>';
						$fields .= '<tr class="shipping">';
						$fields .= "<td>" . $get_pallet_size[$j]["width"] . ' cm</td>';
						$fields .= "<td>" . $get_pallet_size[$j]["height"] . ' cm</td>';
						$fields .= '<td>
						<input type="text" id="length' . $indP . '" name="length" maxlength="4" size="4" value=""> cm</td>';
						$fields .= "<td>" .	$get_pallet_size[$j]["weightStill"] . ' kg</td>';
						$fields .= '</tr>';
					}

					$fields .= '<tr><td colspan="4"></td><td colspan="2">
					<input type="button" class="button " value="' . __('Valider Pallet', 'Vignoblexport') . '" id="validPallet"></td></tr>';
				?>

				<?php echo $fields;
				}

				$offrePal1 = "";
				$offrePal2 = "";
				if ($type_package == "pallet" &&  $offres == null) {
				?> <tbody id="offredispoPallet" style="display:none">
						<tr class="shipping" data-order_item_id="19">

							<td colspan="3"><span style="color: black;font-weight: 600;margin-left: 10px;"><?php esc_html_e('Available offers :', 'Vignoblexport') ?></span></td>

						</tr>
						<tr class="shipping" data-order_item_id="19">
							<?php
							if ((is_countable($get_pallet_rates) && count($get_pallet_rates) > 0)) {

								if ($type_livraison == "domicile") {

									foreach ($get_pallet_rates as $key => $offer) {
										$offerValue = $offer;
										unset($offerValue['surcharges']);
										$encoded_value = urlencode(json_encode($offerValue));
										$offrePal1 .= '<tr><td colspan="5">';
										$offrePal1 .= '<br><input type="radio" name="offer[]" data-index="0" id="shipping_method_offer_' . $key . '" value="' . $encoded_value . '" class="shipping_method" >';
										$offrePal1 .= '<input type="hidden" name="priceOffre" id="priceOffre_' . $key . '"  value="' . $offer['price'] . '"  >';
										$offrePal1 .= $offer['service'] . ': <strong>' . $offer['price'] . '€</strong> | le ' . $offer['deliveryDate'] . ' at' . $offer['pickupTime'];
										$offrePal1 .= '</td></tr>';
									}
									echo $offrePal1;
								} else {

									foreach ($get_pallet_rates as $key => $offer) {

										$subject = $offer['service'];
										$offerValue = $offer;
										unset($offerValue['surcharges']);
										$encoded_value = urlencode(json_encode($offerValue));
										if (preg_match("/UPS Access Point Economy/i", $offer['service'])) {

											$offrePal2 .= '<tr><td colspan="5">';
											$offrePal2 .= '<br><input type="radio" name="offer[]" data-index="0" id="shipping_method_offer_' . $key . '" value="' . $encoded_value . '" class="shipping_method" >';
											$offrePal2 .= '<input type="hidden" name="priceOffre" id="priceOffre_' . $key . '"  value="' . $offer['price'] . '"  >';
											$offrePal2 .= '<label test for="shipping_method_offer_' . $key . '">' . $offer['service'] . ': <strong>' . $offer['price'] . '€</strong> | le ' . $offer['deliveryDate'] . ' at' . $offer['pickupTime'] . '</label>';
											$offrePal2 .= '</td></tr>';
										}
									}
									echo $offrePal2;
								}
							} else {

								echo $offre3;
							} ?>
						</tr>
						<tr>
							<td colspan="4"></td>
							<td colspan="2">
								<input type="button" class="button " value="<?php esc_html_e('Validate configuration', 'Vignoblexport') ?>" id="validEditPallet">
							</td>
						</tr>
					</tbody>
				<?php
				}
				?>

				<?php
				$offre1 = "";
				$offre2 = "";

				if ($type_package == "colis" && $offres == null) {
				?>
					<tbody id="offredispo" style="display:none">
						<tr class="shipping" data-order_item_id="19">
							<td colspan="3"><span style="color: black;font-weight: 600;margin-left: 10px;"><?php esc_html_e('Available offers:', 'Vignoblexport') ?></span></td>
						</tr>
						<tr class="shipping" data-order_item_id="19">
							<?php

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
							$shop_country = isset($response[0]['country']['countryAlpha2']) ? $response[0]['country']['countryAlpha2'] : "";

							$order_id = $order->get_id();
							$tax_duties_choice = get_option('VINW_TAX_RIGHTS');

							if ((is_countable($get_rates) && count($get_rates) > 0) /*|| (is_countable($get_rateJplus) && count($get_rateJplus) > 0)*/) {
								if ($type_livraison == "domicile") {
									$i = 0;
									foreach ($get_rate as $key => $offer) {
										$offerValue = $offer;
										unset($offerValue['surcharges']);
										$encoded_value = urlencode(json_encode($offerValue));
										$vat_choice = get_option('VINW_VAT_CHOICE');

										if (preg_match('/Chrono Relais 13H/', $offer['service']) == 0) {
											$dest_country = $order->get_shipping_country();
											$tax_category = $this->get_tax_category($shop_country, $dest_country);
											if ($tax_category == "standard") {
												$expedition_type = "standard";
												if ($vat_choice == "yes") {
													$vat_rate = $this->get_vat_from_country($dest_country);
													$finalPrice = $offer['price'] + (($offer['price'] * $vat_rate) / 100);
													$finalPrice = round($finalPrice, 2);
													$tax_amount = ($offer['price'] * $vat_rate) / 100;
													$tax_amount = round($tax_amount, 2);
												} else {
													$finalPrice = round($offer['price'], 2);
													$tax_amount = 0;
												}
											} elseif ($tax_category == "intra_eu") {
												$expedition_type = "fiscal_rep";
												if ($vat_choice == "yes") {
													if (get_option('VINW_VAT_OSS') == "yes") {
														$vat_rate = $this->get_vat_from_country($dest_country);
													} else {
														$vat_rate = $this->get_vat_from_country($shop_country);
													}
													$charges = $this->get_charges($offer['price'], $order_id);
													$fiscal_forfeit = $charges - $offer['price'];
													$charges_vat = ($fiscal_forfeit * $vat_rate) / 100;
													$charges_TTC = $charges + $charges_vat; // frais rep fiscal avec TVA
													$charges_TTC = round($charges_TTC, 2);
													$transport_vat = ($offer['price'] * $vat_rate) / 100;
													$finalPrice = $charges_TTC + $transport_vat;
													$finalPrice = round($finalPrice, 2);
													$tax_amount = $finalPrice - $offer['price'];
													$tax_amount = round($tax_amount, 2);
													$vat_transport = round($transport_vat, 2);
													$vat_accises = round($charges_vat, 2);
												} else {
													$charges = $this->get_charges($offer['price'], $order_id);
													$finalPrice = round((float)$charges, 2);
													$tax_amount = 0;
												}
											} else { // $tax_category == "inter"
												$expedition_type = "export";
												$tax_and_duties = $this->get_tax_and_duties($offer['name'], $order_id, false);
												if ($tax_and_duties['price'] == 0) {
													$tax_and_duties = $this->get_tax_and_duties($offer['name'], $order_id, true);
												}
												if ($tax_duties_choice == "exp") {
													$finalPrice = $offer['price'] + $tax_and_duties['price'];
													$tax_amount = round($tax_and_duties['price'], 2);
												} else {
													$finalPrice = $offer['price'];
													$tax_amount = round($tax_and_duties['price'], 2);
												}
											}

											if (get_option('VINW_ASSURANCE') == "yes") {
												$finalPrice = $finalPrice + $offer['insurancePrice'];
											}
											$offre1 .= '<tr><td colspan="5">';
											$offre1 .= '<br><input type="radio" name="offer[]" data-index="0" id="shipping_method_offer_' . $key . '" value="' . $encoded_value . '" class="shipping_method" >';
											$offre1 .= '<input type="hidden" name="priceOffre" id="priceOffre_' . $key . '"  value="' . $finalPrice . '"';
											if ($tax_amount) {
												$offre1 .= 'data-tax_amount="' . $tax_amount . '"';
											}
											if (get_option('VINW_ASSURANCE') == "yes") {
												$offre1 .= 'data-insurance="' . $offer['insurancePrice'] . '"';
											}
											$offre1 .= 'data-typeexp= "' . $expedition_type . '"';
											$offre1 .= 'data-vat_transport="' . $vat_transport . '"';
											$offre1 .= 'data-vat_accises="' . $vat_accises . '"';
											$offre1 .= '>';
											$offre1 .= $offer['service'] . ': <strong>' . $finalPrice . '€</strong> | le ' . $offer['deliveryDate'] . ' at' . $offer['pickupTime'];
											if ($expedition_type == "export") {
												if ($currency == "EUR") {
													$currency_symbol = "€";
												} else {
													$currency_symbol = "$";
												}
												$offre1 .= '<p>' . __('Estimated tax and duties that will be invoiced by carrier:', 'Vignoblexport') . ' <strong>' . sprintf("%01.2f", $tax_amount) . ' ' . $currency_symbol . '</strong></p>';
											}
											if ($result[0]['created_at'] != $result[0]['updated_at']) {
												$offre1 .= '<p>' . __('Price difference:', 'Vignoblexport') . ' ' . $finalPrice - $result[0]['initial_price'] . ' €' . '</p>';
											}
											$offre1 .= '</td></tr>';
										}
										$i++;
									}
									echo $offre1;
								} else {
									foreach ($get_rate as $key => $offer) {
										$offerValue = $offer;
										unset($offerValue['surcharges']);
										$encoded_value = urlencode(json_encode($offerValue));

										if (preg_match('/Chrono Relais 13H/', $offer['service']) == 1) {
											$vat_choice = get_option('VINW_VAT_CHOICE');
											$currentCountry = $order->get_shipping_country();

											if ($vat_choice == 'yes') {
												$vat_rate = $this->get_vat_from_country($currentCountry);
												$tax_amount = round(($offer['price'] * $vat_rate) / 100, 2);
												$finalPrice = $offer['price'] + $tax_amount;
												$finalPrice = round($finalPrice, 2);
											} else {
												$finalPrice = round($offer['price'], 2);
											}

											if (get_option('VINW_ASSURANCE') == "yes") {
												$finalPrice = $finalPrice + $offer['insurancePrice'];
											}

											$offre2 .= '<tr><td colspan="5">';
											$offre2 .= '<br><input type="radio" name="offer[]" data-index="0" id="shipping_method_offer_' . $key . '" value="' . $encoded_value . '" class="shipping_method" >';
											$offre2 .= '<input type="hidden" name="priceOffre" id="priceOffre_' . $key . '"  value="' . $finalPrice . '"';
											if ($tax_amount) {
												$offre2 .= 'data-tax_amount="' . $tax_amount . '"';
											}
											if (get_option('VINW_ASSURANCE') == "yes") {
												$offre1 .= 'data-insurance="' . $offer['insurancePrice'] . '"';
											}
											$offre2 .= '>';
											$offre2 .= '<label for="shipping_method_offer_' . $key . '">' . $finalPrice . ': <strong>' . $finalPrice . '€</strong> | le ' . $offer['deliveryDate'] . ' at' . $offer['pickupTime'] . '</label>';
											if ($result[0]['created_at'] != $result[0]['updated_at']) {
												$offre2 .= '<p>' . __('Price difference:', 'Vignoblexport') . ' ' . $finalPrice - $result[0]['initial_price'] . ' €' . '</p>';
											}
											$offre2 .= '</td></tr>';
										} else {
											$offre2 = '<tr><td colspan="5">' .  __('No offer available for this parcel point. Please contact Vignoblexport to solve the problem.', 'Vignoblexport') . '</td></tr>';
										}
									}
									echo $offre2;
								}
							} else {

								echo $offre3;
							} ?>
						</tr>
						<tr>
							<td colspan="4"></td>
							<td colspan="2">
								<input type="button" class="button " value="<?php esc_html_e('Validate configuration', 'Vignoblexport') ?>" id="validEdit">
							</td>
						</tr>
					</tbody>
				<?php 	}
				?>

				<?php
				if (($order_status === "completed") || ($order_status === "awaiting-shipment")) {
					$etatcom = false;
				} else {
				?>
					<tbody id="validate-body">
						<?php $query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $order_id . "'";
						$current_order = $wpdb->get_results($query, ARRAY_A);
						?>
						<tr class="shipping" data-order_item_id="19">
							<td></td>
							<td>
								<?php if ($current_order[0]['offre'] == " ") { ?>
									<p style="color:red; font-weight: bold;"><?php esc_html_e("Carrier offer have been deleted. Please select a new one.", 'Vignoblexport') ?> </p>
								<?php } ?>
							</td>
							<td></td>
							<td>
								<?php if ($current_order[0]['offre'] == " ") { ?>
									<input type="button" class="button " value="<?php esc_html_e('Select carrier offer', 'Vignoblexport') ?>" id="editoffre">
								<?php } else { ?>
									<input type="button" class="button " value="<?php esc_html_e('Edit expedition', 'Vignoblexport') ?>" id="editoffre">
								<?php } ?>
							</td>

							<td>
								<input type="hidden" value="<?php echo $order_id; ?>" id="order_id">
								<?php if ($offres == null) {
									$disbled = "disabled";
								} else {
									$disbled = "";
								}

								?>
								<?php if ($type_package == "colis") { ?>
									<input type="button" class="button button-primary" value="<?php esc_html_e('Validate expedition', 'Vignoblexport') ?>" <?php echo $disbled ?> id="Expvalidate" style="color:#fff!important">
								<?php } else { ?>
									<input type="button" class="button button-primary" value="<?php esc_html_e('Validate pallet expedition', 'Vignoblexport') ?>" <?php echo $disbled ?> id="ExpvalidatePallet" style="color:#fff!important">

								<?php } ?>
							</td>
						</tr>
						<tr class="shipping" data-order_item_id="19">

						</tr>
					</tbody>
<?php
				}
			}
		}
	}

	function sendDifferenceMail($to, $name, $diffrence)
	{
		$subject = '[Admin] Différence de prix d\'expédition';
		$body = '<h5>Bonjour, ' . $name . '</h5>';
		$body .= '<p>Le différence prix d\'expédition est :' . $diffrence . '</p>';
		$body .= '<p>Cordialement,<br>' . get_option('woocommerce_email_from_name') . '.</p>';
		$mailer = WC()->mailer();
		$mailer->send(
			$to,
			$subject,
			$mailer->wrap_message(
				$subject,
				$body
			),
			'',
			''
		);
	}

	function get_size($nbBottles, $nbrMagnums)
	{
		$curl = curl_init();
		$key = get_option('VINW_ACCESS_KEY');
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/package/get-sizes?nbBottles=" . (string)$nbBottles . "&nbMagnums=" . (string)$nbrMagnums,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"X-AUTH-TOKEN: " . $key
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

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
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	function get_rates($order_id)
	{
		global $wpdb;
		$query = "SELECT `package` FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $order_id . "'";
		$result = $wpdb->get_results($query, ARRAY_A);
		$package = json_decode($result[0]['package'], true);

		$order = wc_get_order($order_id);
		$total_products_ex_tax = $order->get_subtotal();

		if (isset($package[0]['nbMagnums'])) {
			$nbr_bottles = $package[0]['nbBottles'] + $package[0]['nbMagnums'];
		} else {
			$nbr_bottles = $package[0]['nbBottles'];
		}

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
		$Exp_societe = "company";
		$Exp_company = isset($response[0]['company']) && strlen($response[0]['company']) > 0  ? $response[0]['company'] : "none";
		$Exp_contact = isset($response[0]['contact']) ? $response[0]['contact'] : "";
		$Exp_address = isset($response[0]['adresse']) ? $response[0]['adresse'] : "";
		$Exp_postCode = isset($response[0]['zipCode']) ? $response[0]['zipCode'] : "";
		$Exp_city = isset($response[0]['city']) ? $response[0]['city'] : "";
		$Exp_country = isset($response[0]['country']['countryAlpha2']) ? $response[0]['country']['countryAlpha2'] : "";
		$Exp_email = isset($response[0]['email']) ? $response[0]['email'] : "";
		$Exp_phone = isset($response[0]['telephone']) ? $response[0]['telephone'] : "";

		$order = wc_get_order($order_id);

		if ($order->get_shipping_company() == null && $order->get_billing_company() == null) {
			$destAddressType = "individual";
		} else {
			$destAddressType = "company";
		}

		$postalCode = $order->get_shipping_postcode();
		$city = $order->get_shipping_city();
		$country = $order->get_shipping_country();
		$state = $order->get_shipping_state();

		$curl = curl_init();
		$url = "https://test.extranet.vignoblexport.fr/api/shipment/get-rates";
		$url .= "?expAddress%5BaddressType%5D=" . $Exp_societe;
		$url .= "&expAddress%5BzipCode%5D=" . $Exp_postCode;
		$url .= "&expAddress%5Bcity%5D=" . urlencode($Exp_city);
		$url .= "&expAddress%5Bcountry%5D=" . $Exp_country;
		$url .= "&destAddress%5BaddressType%5D=" . $destAddressType;
		$url .= "&destAddress%5BzipCode%5D=" . $postalCode;
		$url .= "&destAddress%5Bcity%5D=" . urlencode($city);
		$url .= "&destAddress%5Bcountry%5D=" . $country;

		if ($country == "US" || $country == "CA") {
			$url .= "&destAddress%5Bstate%5D=" . urlencode($state);
		}

		$packageNumber = 0;
		foreach ($package as $key => $parcel_type) {
			$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bnb%5D=" . (string)$parcel_type['nbPackages'];
			if ($this->has_sparkling($order_id)) {
				$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bweight%5D=" . (string)$parcel_type['sizes']['weightSparkling'];
			} else {
				$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bweight%5D=" . (string)$parcel_type['sizes']['weightStill'];
			}
			$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bwidth%5D=" . $parcel_type['sizes']['width'];
			$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bheight%5D=" . $parcel_type['sizes']['height'];
			$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Blength%5D=" . $parcel_type['sizes']['length'];
			$packageNumber++;
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
			$date2 = strtotime($date . ' +1 Weekday');
		}

		$url .= "&pickupDate=" . date("Y-m-d", $date2);

		$url .= "&minHour=09:10:00";
		$url .= "&cutoff=19:00:00";

		$url .= "&nbBottles=" . (string)$nbr_bottles;

		if (get_option('VINW_ASSURANCE') == "yes") {
			$url .= "&commodityValue=" . (string)$total_products_ex_tax;
		}

		if (in_array('fedex', get_option('VINW_PREF_TRANSP'))) {
			$url .= "&fedex=1";
		}
		if (in_array('ups', get_option('VINW_PREF_TRANSP'))) {
			$url .= "&ups=1";
		}
		if (in_array('dhl', get_option('VINW_PREF_TRANSP'))) {
			$url .= "&dhl=1";
		}
		if (in_array('tnt', get_option('VINW_PREF_TRANSP'))) {
			$url .= "&tnt=1";
		}
		if (in_array('chronopost', get_option('VINW_PREF_TRANSP'))) {
			$url .= "&chronopost=1";
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
		$response2 = curl_exec($curl);
		$response2 = json_decode($response2, true);
		curl_close($curl);
		// Filter on offers in response  with carriers preferences
		// $filtered_responses = array();
		// if (is_countable($response2) && count($response2) > 0 && !isset($response2['status'])) {
		// 	foreach ($response2 as $response) {
		// 		if (in_array($response['name'], get_option('VINW_PREF_TRANSP')) || $response['name'] == 'seur' || $response['name'] == 'groupage vignoblexport') {
		// 			array_push($filtered_responses, $response);
		// 		}
		// 	}
		// }
		// return $filtered_responses;
		return $response2;
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

	function has_sparkling($order_id)
	{
		global $wpdb;
		$order = wc_get_order($order_id);
		$order_items = $order->get_items();

		foreach ($order_items as $item_id => $item) {
			$type = get_post_meta($item['product_id'], '_custom_type', true);
			if ($type == "sparkling") {
				return true;
				break;
			} else {
				return false;
			}
		}
	}

	function get_pallet_rates($nbr_Magnums, $nbr_bottles, $count, $package, $weight, $token, $destAddressType, $postalCode, $city, $country)
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
		$response = json_decode(curl_exec($curlExp), true);
		curl_close($curlExp);
		$Exp_societe = "company";
		$Exp_company = isset($response[0]['company']) && strlen($response[0]['company']) > 0  ? $response[0]['company'] : "none";
		$Exp_contact = isset($response[0]['contact']) ? $response[0]['contact'] : "";
		$Exp_address = isset($response[0]['adress']) ? $response[0]['adress'] : "";
		$Exp_postCode = isset($response[0]['zipCode']) ? $response[0]['zipCode'] : "";
		$Exp_city = isset($response[0]['city']) ? $response[0]['city'] : "";
		$Exp_country = isset($response[0]['country']['countryAlpha2']) ? $response[0]['country']['countryAlpha2'] : "";
		$Exp_email = isset($response[0]['email']) ? $response[0]['email'] : "";
		$Exp_phone = isset($response[0]['telephone']) ? $response[0]['telephone'] : "";
		$curl = curl_init();
		$url = "https://test.extranet.vignoblexport.fr/api/shipment/get-rates";
		$url .= "?expAddress%5BaddressType%5D=" . $Exp_societe;
		$url .= "&expAddress%5BzipCode%5D=" . $Exp_postCode;
		$url .= "&expAddress%5Bcity%5D=" . urlencode($Exp_city);
		$url .= "&expAddress%5Bcountry%5D=" . $Exp_country;
		$url .= "&destAddress%5BaddressType%5D=" . $destAddressType;
		$url .= "&destAddress%5BzipCode%5D=" . $postalCode;
		$url .= "&destAddress%5Bcity%5D=" . urlencode($city);
		$url .= "&destAddress%5Bcountry%5D=" . $country;
		$packageNumber = 0;
		$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bnb%5D=" . $count;
		$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bweight%5D=" . $weight;
		$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bwidth%5D=" . $package['width'];
		$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Bheight%5D=" . $package['height'];
		$url .= "&packages%5B" . (string)$packageNumber . "%5D%5Blength%5D=" . $package['length'];
		$url .= "&pickupDate=" . Date('Y-m-d');
		$url .= "&minHour=09:10:00";
		$url .= "&cutoff=19:00:00";
		$url .= "&nbBottles=" . (string)$count;
		$url .= "&pallets=1";
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
				"X-AUTH-TOKEN: " . $token,
			),
		));
		$response2 = curl_exec($curl);
		curl_close($curl);

		// Filter on offers in response  with carriers preferences
		$filtered_responses = array();
		if (is_countable($response2) && count($response2) > 0) {
			foreach ($response2 as $response) {
				if (in_array($response['name'], get_option('VINW_PREF_TRANSP')) || $response['name'] == 'seur' || $response['name'] == 'groupage vignoblexport') {
					array_push($filtered_responses, $response);
				}
			}
		}

		return $filtered_responses;
	}

	/**
	 * Retrieves HS codes for products in the cart.
	 *
	 * @return array The array of HS codes for the products.
	 */
	function get_hscode($order_id)
	{
		$allHsCodes = [];
		$order = wc_get_order($order_id);
		$order_items = $order->get_items();

		foreach ($order_items as $item) {
			$hs_code_color = get_post_meta($item['product_id'], '_custom_color', true);
			if ($hs_code_color == 'Red') {
				$hs_code_color = 'red';
			} elseif ($hs_code_color == 'White') {
				$hs_code_color = 'white';
			} elseif ($hs_code_color == 'Rose') {
				$hs_code_color = 'rose';
			} else {
				$hs_code_color = 'no-color';
			}
			$curlHscode = curl_init();
			$hscodeURL = "https://test.extranet.vignoblexport.fr/api/get-hscode";
			$hscodeURL .= "?appellationName=" . htmlspecialchars_decode(get_post_meta($item['product_id'], '_custom_appelation', true));
			$hscodeURL .= "&capacity=" . get_post_meta($item['product_id'], '_custom_capacity', true);
			$hscodeURL .= "&alcoholDegree=" . get_post_meta($item['product_id'], '_custom_alcohol_degree', true);
			$hscodeURL .= "&color=" . $hs_code_color;

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
				$countries[] = $country;
			}
		}

		foreach ($countries as $country) {
			if (in_array($currentCountry, $country, true)) {
				$return = true;
				break;
			} else {
				$return = false;
			}
		}
		return $return;
	}

	function get_charges($offer_price, $order_id)
	{
		$allHsCodes = $this->get_hscode($order_id);

		$curlCharges = curl_init();
		$chargesURL = "https://test.extranet.vignoblexport.fr/api/shipment/get-charges?";
		$counter = 0;
		$order = wc_get_order($order_id);
		$order_items = $order->get_items();
		// loop through all order items
		foreach ($order_items as $item) {
			$chargesURL .= "details%5B" . urlencode($counter) . "%5D%5Bcapacity%5D=" . get_post_meta($item['product_id'], '_custom_capacity', true);
			$chargesURL .= "&details%5B" . urlencode($counter) . "%5D%5BalcoholDegree%5D=" . get_post_meta($item['product_id'], '_custom_alcohol_degree', true);
			$chargesURL .= "&details%5B" . urlencode($counter) . "%5D%5BhsCode%5D=" . $allHsCodes[$counter];
			$chargesURL .= "&details%5B" . urlencode($counter) . "%5D%5BunitValue%5D=" . get_post_meta($item['product_id'], '_price', true);
			$chargesURL .= "&details%5B" . urlencode($counter) . "%5D%5Bquantity%5D=" . get_post_meta($item['product_id'], '_custom_number_bottle', true);
			$chargesURL .= "&";
			$counter++;
		}

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

		$exp_country = $response[0]['country']['countryAlpha2'];
		$dest_country = $order->get_shipping_country();

		$chargesURL .= "expCountryAlpha2=" . $exp_country;
		$chargesURL .= "&destCountryAlpha2=" . $dest_country;
		$chargesURL .= "&shippingPrice=" . $offer_price;

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
		curl_close($curlCharges);
		$extraCost = $responseCharges['total'];

		return $extraCost;
	}

	/**
	 * Calculate tax and duties for a given carrier and return the result.
	 *
	 * @param string $carrier The carrier for which the tax and duties are being calculated.
	 * @return array The tax and duties informations in an associative array format.
	 */
	function get_tax_and_duties($carrier, $order_id,  bool $is_zero = false)
	{
		global $wpdb;
		$query = "SELECT `package` FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $order_id . "'";
		$result = $wpdb->get_results($query, ARRAY_A);
		$package = json_decode($result[0]['package'], true);

		if (isset($package[0]['nbMagnums'])) {
			$nbr_bottles = $package[0]['nbBottles'] + $package[0]['nbMagnums'];
		} else {
			$nbr_bottles = $package[0]['nbBottles'];
		}
		$order = wc_get_order($order_id);
		$country = $order->get_shipping_country();
		if ($this->has_sparkling($order_id)) {
			$cartTotalWeight = $package[0]['sizes']['weightSparkling'];
		} else {
			$cartTotalWeight = $package[0]['sizes']['weightStill'];
		}
		$shippingPrice = $order->get_subtotal();

		$unitPrice = round($shippingPrice / $nbr_bottles, 2);

		$allTypes = [];
		$allCapacities = [];
		$allAlcoolDegrees = [];
		foreach ($order->get_items() as $item) {
			$allTypes[] = get_post_meta($item['product_id'], '_custom_type', true);
			$allCapacities[] = get_post_meta($item['product_id'], '_custom_capacity', true);
			$allAlcoolDegrees[] = get_post_meta($item['product_id'], '_custom_alcohol_degree', true);
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

		if ($is_zero == true) {
			$type = 'wine';
		}

		// api request
		$curl = curl_init();
		$url = "https://test.extranet.vignoblexport.com/api/shipment/get-duty-and-tax?";
		$url .= "country=" . $country;
		$url .= "&carrier=" . $carrier;
		$url .= "&weight=" . (string)$cartTotalWeight;
		$url .= "&shippingPrice=" . (string)$shippingPrice;
		$url .= "&wineType=" . $type; // prendre le plus cher
		$url .= "&quantity=" . (string)$nbr_bottles;
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

	/**
	 * Retrieve vat rate with expedition country
	 *
	 * @void
	 */
	function get_vat_from_country($country)
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
			if ($value['iso2'] && $value['iso2'] === $country && $value['vat_rate'] !== "") {
				return $value['vat_rate'];
			}
		}
		return null;
	}


	/**
	 * Add parcelpoint info to front order page
	 *
	 * @void
	 */
	public function add_parcelpoint_to_admin_order_page()
	{
		$order             = Order_Util::admin_get_order();
		$this->parcelpoint = Order_Util::get_parcelpoint($order);
		global $wpdb;
		$query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . (string) $order->get_id() . "' LIMIT 1";
		//phpcs:ignore
		$result = $wpdb->get_results($query, ARRAY_A);

		if (isset($result[0]['id_exp'])) {
			add_meta_box('Vignoblexport-order-details', __('Vignoblexport - Shipment details', 'Vignoblexport'), array($this, 'order_details_box'), 'shop_order', 'side', 'default', [$order]);
		}

		if (null === $this->parcelpoint) {
			return;
		}

		if (function_exists('wc_get_order_types')) {
			foreach (wc_get_order_types('order-meta-boxes') as $type) {
				add_meta_box('Vignoblexport-order-parcelpoint', __('Vignoblexport - Shipment pickup point', 'Vignoblexport'), array($this, 'order_edit_page_parcelpoint'), $type, 'side', 'default');
			}
		} else {
			add_meta_box('Vignoblexport-order-parcelpoint', __('Vignoblexport - Shipment pickup point', 'Vignoblexport'), array($this, 'order_edit_page_parcelpoint'), 'shop_order', 'side', 'default');
		}
	}


	/**
	 * Add tracking info to front order page.
	 *
	 * @void
	 */
	public function add_tracking_to_admin_order_page()
	{
		$controller     = new Controller(
			array(
				'url'     => $this->plugin_url,
				'version' => $this->plugin_version,
			)
		);

		$this->tracking = $controller->get_order_tracking(Order_Util::get_id(Order_Util::admin_get_order()));

		if (null === $this->tracking || !property_exists($this->tracking, 'shipmentsTracking') || empty($this->tracking->shipmentsTracking)) {
			return;
		}

		if (function_exists('wc_get_order_types')) {
			foreach (wc_get_order_types('order-meta-boxes') as $type) {
				add_meta_box('Vignoblexport-order-tracking', __('Vignoblexport - Shipment tracking', 'Vignoblexport'), array($this, 'order_edit_page_tracking'), $type, 'normal', 'high');
			}
		} else {
			add_meta_box('Vignoblexport-order-tracking', __('Vignoblexport - Shipment tracking', 'Vignoblexport'), array($this, 'order_edit_page_tracking'), 'shop_order', 'normal', 'high');
		}
	}

	/**
	 *
	 * Display the parcel point metabox content
	 *
	 * @Void
	 */
	public function order_details_box($order, $args)
	{

		require_once realpath(plugin_dir_path(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'html-admin-order-exp-details.php';
	}

	function action_woocommerce_admin_order_item_values($null, $item, $absint)
	{
		var_dump('$item');
	}

	/**
	 *
	 * Display the parcel point metabox content
	 *
	 * @Void
	 */
	public function order_edit_page_parcelpoint()
	{
		$parcelpoint          = $this->parcelpoint;
		$parcelpoint_networks = \Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method\Parcel_Point\Controller::get_network_list();
		require_once realpath(plugin_dir_path(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'html-admin-order-edit-page-parcelpoint.php';
	}

	/**
	 * Order edit page output.
	 *
	 * @void
	 */
	public function order_edit_page_tracking()
	{
		$tracking = $this->tracking;
		include realpath(plugin_dir_path(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'html-admin-order-edit-page-tracking.php';
	}

	/**
	 * Order view modal details.
	 *
	 * @param array $order_details order details sent to template.
	 *
	 * @return array
	 */
	public function order_view_modal_details($order_details)
	{
		$controller = new Controller(
			array(
				'url'     => $this->plugin_url,
				'version' => $this->plugin_version,
			)
		);

		if (!isset($order_details['order_number'])) {
			return $order_details;
		}
		$tracking = $controller->get_order_tracking($order_details['order_number']);
		//phpcs:ignore

		if (null === $tracking || !property_exists($tracking, 'shipmentsTracking') || empty($tracking->shipmentsTracking)) {
			return $order_details;
		}
		ob_start();
		include realpath(plugin_dir_path(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'html-admin-order-view-modal-tracking.php';
		$html                           = ob_get_clean();
		$order_details['tracking_html'] = $html;
		return $order_details;
	}

	/**
	 * Order view modal.
	 *
	 * @void
	 */
	public function order_view_modal()
	{
		include realpath(plugin_dir_path(__DIR__)) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'html-admin-order-view-modal-print-tracking.php';
	}
}
