<?php

/**
 * Settings expedition page rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if (!defined('ABSPATH')) {
	exit;
}
$arr = ["VING_NETWORK" => ["Vingoblexport"]];
$NETWORKS = htmlentities(serialize($arr));
$adress = json_decode($getadress->response, true);
// $pallet = json_decode($get_pallet_size->response, true);

?>
<style>
	select {
		width: 150px;
	}

	input[type=date],
	input[type=datetime-local],
	input[type=datetime],
	input[type=email],
	input[type=month],
	input[type=number],
	input[type=password],
	input[type=search],
	input[type=tel],
	input[type=text],
	input[type=time],
	input[type=url],
	input[type=week] {
		width: 150px;
	}

	table,
	td,
	tr,
	th {
		border: 1px solid black !important;
		padding: 1rem !important;
	}
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<div class="wrap" id="VINW-connect">
	<h3><?php esc_html_e('Please fill the information below in order to be able to use the plugin', 'Vignoblexport'); ?></h3>
	<form method="post" action="options.php">
		<?php settings_fields('Vignoblexport-settings-group'); ?>
		<?php do_settings_sections('Vignoblexport-settings-group'); ?>
		<h2>1. <?php esc_html_e('Key settings', 'Vignoblexport'); ?></h2>
		<input type="hidden" name="VINW_PP_NETWORKS[VING_NETWORK][]" value="Vingoblexport">

		<table class="form-table states">
			<tr valign="top">
				<td scope="row" class="titledesc">
					<label for="order_shipped"><?php esc_html_e('Insert your VignoblExport API token', 'Vignoblexport'); ?></label>
				</td>
				<td>
					<input type="text" name="VINW_ACCESS_KEY" id="api-input" value="<?php if (get_option('VINW_ACCESS_KEY')) {
																						echo get_option('VINW_ACCESS_KEY');
																					} ?>" style="<?php if (get_option('VINW_ACCESS_KEY') && get_option('acces-key-validate') == "1") {
																										echo 'border: 3px solid green; ';
																									} else {
																										echo 'border: 3px solid red; ';
																									} ?>">
					<input type="hidden" id="acces-key-validate" name="acces-key-validate" value="<?php if (get_option('acces-key-validate')) {
																										echo get_option('acces-key-validate');
																									} else {
																										echo "0";
																									} ?>">
					<button type="button" class="button" id="validate-api" disabled><?php esc_html_e('Test my acces token', 'Vignoblexport'); ?></button>
				</td>
			</tr>
			<tr valign="top">
				<td scope="row" class="titledesc">
					<label for="order_shipped"><?php esc_html_e('Insert your MapBox API token', 'Vignoblexport'); ?></label>
				</td>
				<td>
					<input type="text" name="VINW_MAPBOX_ACCESS_KEY" id="api-input-mapbox" value="<?php if (get_option('VINW_MAPBOX_ACCESS_KEY')) {
																										echo get_option('VINW_MAPBOX_ACCESS_KEY');
																									} ?>" style="<?php if (get_option('VINW_MAPBOX_ACCESS_KEY') && get_option("mapbox-api-key-validate") == "1") {
																														echo 'border: 3px solid green; ';
																													} else {
																														echo 'border: 3px solid red; ';
																													} ?>">
					<input type="hidden" id="mapbox-api-key-validate" name="mapbox-api-key-validate" value="<?php if (get_option('mapbox-api-key-validate')) {
																												echo get_option('mapbox-api-key-validate');
																											} else {
																												echo "0";
																											} ?>">

					<button type="button" class="button" id="validate-mapbox-api"><?php esc_html_e('Test my Mapbox acces token', 'Vignoblexport'); ?></button>
				</td>
			</tr>
		</table>
		<p id="alert-api-text" style="font-weight:900;background-color:#ffb900;width:fit-content;"><?php esc_html_e("Please validate the API access before saving the modifications", 'Vignoblexport'); ?></p>
		<?php submit_button($text = "Save ", $type = 'primary', $name = 'submitkey'); ?>
	</form>

	<?php if (null !== $tuto_url) { ?>
		<h2>2. <?php esc_html_e('Shipping method settings', 'Vignoblexport'); ?></h2>
		<table class="form-table">
			<tr valign="top">
				<td scope="row" class="titledesc large">
					<label for="order_shipped"><?php esc_html_e('Just one last step, it will only take a few minutes, let us guide you: ', 'Vignoblexport'); ?></label>
				</td>
			</tr>
		</table>
		<p class="submit">
			<a href="<?php echo esc_url($tuto_url); ?>" target="_blank" class="button button-primary"><?php esc_html_e('Go to the tutorial', 'Vignoblexport'); ?></a>
		</p>
	<?php } ?>
</div>
<?php if (get_option('VINW_ACCESS_KEY')  && get_option("acces-key-validate") == "1") { ?>
	<div class="wrap" id="VINW-settings">
		<h1><?php esc_html_e('VignoblExport shipping settings', 'Vignoblexport') ?></h1>
		<h2 style="color: #e2011b;"><?php esc_html_e("The weight and size of a standard bottle are used by default to calculate shipping charges if you leave the width, height and weight fields blank for your products. You need to fill in this information for magnums.", 'Vignoblexport') ?></h2>
		<h2><?php esc_html_e('Address', 'Vignoblexport') ?></h2>
		<table class="form-table states">
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Address', 'Vignoblexport'); ?> :</th>
				<td>
					<label for="Adresse"><?php echo $adress[0]['adresseNom'] ?></label>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php esc_html_e('Company', 'Vignoblexport'); ?> :</th>
				<td>
					<label for="Adresse"><?php echo $adress[0]['societe'] ?></label>

				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Contact', 'Vignoblexport'); ?> :</th>
				<td>
					<label for="Adresse"><?php echo $adress[0]['contact'] ?></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Phone', 'Vignoblexport'); ?> :</th>
				<td>
					<label for="Adress"><?php echo $adress[0]['telephone'] ?></label>

				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Adress', 'Vignoblexport'); ?> :</th>
				<td>
					<label for="Adresse"><?php echo $adress[0]['adresse'] ?></label>

				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('Postal code', 'Vignoblexport'); ?> :</th>
				<td>
					<label for="Adresse"><?php echo $adress[0]['zipCode'] ?></label>

				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e('City', 'Vignoblexport'); ?> :</th>
				<td>
					<label for="Adresse"><?php echo $adress[0]['ville'] ?></label>
				</td>
			</tr>
		</table>

		<h2>Configuration</h2>
		<form method="post" action="options.php">
			<?php settings_fields('Vignoblexport-settings-group2'); ?>
			<?php do_settings_sections('Vignoblexport-settings-group2'); ?>
			<table class="form-table states">
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Minimum number of bottles', 'Vignoblexport'); ?>:</th>
					<td>
						<input type="number" name="VINW_NBR_MIN" value="<?php if (get_option('VINW_NBR_MIN')) {
																			echo get_option('VINW_NBR_MIN');
																		} ?>">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Preparation time of the package before shipment', 'Vignoblexport'); ?> :</th>
					<td>

						<input type="number" name="VINW_EXP_DAYS_MIN" value="<?php if (get_option('VINW_EXP_DAYS_MIN')) {
																					echo get_option('VINW_EXP_DAYS_MIN');
																				} ?>">
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e('Carrier preference', 'Vignoblexport'); ?> :</th>
					<td>
						<select id="form_trans" name="VINW_PREF_TRANSP[]" class="form-control" multiple="multiple">
							<?php if (get_option('VINW_PREF_TRANSP')) { ?>
								<option value="fedex" <?php if (in_array('fedex', get_option('VINW_PREF_TRANSP'))) echo 'selected' ?>>FEDEX</option>
								<option value="ups" <?php if (in_array('ups', get_option('VINW_PREF_TRANSP'))) echo 'selected' ?>>UPS</option>
								<option value="dhl" <?php if (in_array('dhl', get_option('VINW_PREF_TRANSP'))) echo 'selected' ?>>DHL</option>
								<option value="tnt" <?php if (in_array('tnt', get_option('VINW_PREF_TRANSP'))) echo 'selected' ?>>TNT</option>
								<option value="chronopost" <?php if (in_array('chronopost', get_option('VINW_PREF_TRANSP'))) echo 'selected' ?>>CHRONOPOST</option>

							<?php	} else { ?>
								<option value="fedex">FEDEX</option>
								<option value="ups">UPS</option>
								<option value="dhl">DHL</option>
								<option value="tnt">TNT</option>
								<option value="chronopost">CHRONOPOST</option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Delivery preference', 'Vignoblexport'); ?> :</th>
					<td>
						<select id="form_trans" name="VINW_PREF_STAT" class="form-control" style="min-width: 250px;">
							<option value="les deux" <?php if (get_option('VINW_PREF_STAT') == 'les deux') echo 'selected' ?>>
								<?php esc_html_e('Domestic express / Pickup point', 'Vignoblexport'); ?>
							</option>
							<option value="domicile" <?php if (get_option('VINW_PREF_STAT') == 'domicile') echo 'selected' ?>><?php esc_html_e('Domestic', 'Vignoblexport'); ?></option>
							<option value="pointRelais" <?php if (get_option('VINW_PREF_STAT') == 'pointRelais') echo 'selected' ?>><?php esc_html_e('Pickup point', 'Vignoblexport'); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Duties and taxes at destination', 'Vignoblexport'); ?> :</th>
					<td>
						<select id="form_trans" name="VINW_TAX_RIGHTS" class="form-control" style="min-width: 250px;">
							<?php if (get_option('VINW_TAX_RIGHTS')) { ?>
								<option value="" disabled <?php if (get_option('VINW_TAX_RIGHTS') !== null) echo 'selected' ?>><?php esc_html_e('Select your option', 'Vignoblexport') ?></option>
								<option value="exp" <?php if (get_option('VINW_TAX_RIGHTS') == 'exp') echo 'selected' ?>><?php esc_html_e("At the sender's expense", 'Vignoblexport') ?></option>
								<option value="dest" <?php if (get_option('VINW_TAX_RIGHTS') == 'dest') echo 'selected' ?>><?php esc_html_e("At the recipient's expense", 'Vignoblexport') ?></option>
							<?php	} else { ?>
								<option value="" disabled><?php esc_html_e('Select your option', 'Vignoblexport') ?></option>
								<option value="exp"><?php esc_html_e("At the sender's expense", 'Vignoblexport') ?></option>
								<option value="dest"><?php esc_html_e("At the recipient's expense", 'Vignoblexport') ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e('Insurance', 'Vignoblexport') ?></h2>
			<table class="form-table states" role="presentation">

				<tr valign="top">
					<th scope="row"><?php esc_html_e('Activate insurance:', 'Vignoblexport') ?></th>
					<td>
						<select name="VINW_ASSURANCE">
							<option value="no"><?php esc_html_e('no', 'Vignoblexport') ?></option>
							<option value="yes"><?php esc_html_e('yes', 'Vignoblexport') ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e('Minimum amount to be insured:', 'Vignoblexport') ?></th>
					<td>
						<input type="number" name="VINW_ASSURANCE_VALMIN" value="">
					</td>
				</tr>
			</table>


			<!-- <h2><?php //esc_html_e('Pallet size', 'Vignoblexport') 
						?></h2>
			<table class="form-table states">
				<tr valign="top">
					<th scope="row"><?php //esc_html_e('Width', 'Vignoblexport'); 
									?> :</th>
					<td>
						<label for="Adresse"><?php //echo $pallet[0]['width'] 
												?> cm</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php //esc_html_e('Height', 'Vignoblexport'); 
									?> :</th>
					<td>
						<label for="Adresse"><?php //echo $pallet[0]['height'] 
												?> cm</label>
					</td>
				</tr>
			</table> -->
			<?php submit_button(); ?>
		</form>

		<?php if (null !== $tuto_url) { ?>
			<table class="form-table">
				<tr valign="top">
					<td scope="row" class="titledesc large">
						<label for="order_shipped"><?php esc_html_e('Just one last step, it will only take a few minutes, let us guide you: ', 'Vignoblexport'); ?></label>
					</td>
				</tr>
			</table>
			<p class="submit">
				<a href="<?php echo esc_url($tuto_url); ?>" target="_blank" class="button button-primary"><?php esc_html_e('Go to the tutorial', 'Vignoblexport'); ?></a>
			</p>
		<?php } ?>
	</div>
<?php } ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
	jQuery(document).ready(function() {
		jQuery('#form_trans').select2();
		//setup before functions
		let typingTimer; //timer identifier
		let doneTypingInterval = 800; //time in ms, 10 second for example

	})
</script>
<script>
	function checkButtonAPI() {
		if (jQuery('#api-input').val() != '') {
			jQuery('#validate-api').prop('disabled', false);
		} else {
			jQuery('#validate-api').prop('disabled', true);
		}
	}
	jQuery(document).ready(function() {
		let i = true;
		jQuery('#submitkey').prop('disabled', true);
		checkButtonAPI();
		jQuery('#api-input').on('input', function() {
			checkButtonAPI();
		});
		jQuery('#api-input-mapbox').on('change', function() {
			jQuery('#submitkey').prop('disabled', true);
			i = false;


		});
		let validationAPI = false;
		let validationMapbox = false;
		jQuery("#validate-api").click(function(e) {
			e.preventDefault();
			jQuery("#validate-api").css("border", "");
			var api_key = jQuery('#api-input').val();
			jQuery.get("<?php echo $this->plugin_url . "Vignoblexport/VignoblexportPhp/validateAuth.php" ?>?apikey=" + encodeURI(jQuery('#api-input').val()), function(data) {
				if (data == "OK") {
					validationAPI = true;
					jQuery("#acces-key-validate").val("1");

					alert("<?php esc_html_e('Success! The access token is valid!', 'Vignoblexport'); ?>")
					if (validationAPI) {
						jQuery('#alert-api-text').hide();
						if (i == true) {
							jQuery('#submitkey').prop('disabled', false);
						}

						jQuery("#api-input").css("border", "3px solid green");
					}
				} else {
					jQuery("#acces-key-validate").val("0");

					jQuery("#api-input").css("border", "3px solid red");
					alert("<?php esc_html_e('The provided access token is invalid! Please retry', 'Vignoblexport'); ?>")
				};
			});
			return false;
		});

		//validating the mapboxAPI
		jQuery("#validate-mapbox-api").click(function(e) {
			e.preventDefault();
			i = true;
			jQuery("#api-input-mapbox").css("border", "");
			jQuery('#submitkey').prop('disabled', false);

			try {
				var requestOptions = {
					method: 'GET',
					redirect: 'follow'
				};

				fetch("https://api.mapbox.com/geocoding/v5/mapbox.places/?access_token=" + encodeURI(jQuery('#api-input-mapbox').val() + "&country=FR"), requestOptions)
					.then(response => {
						if (response.status == 401) {
							jQuery("#mapbox-api-key-validate").val("0");
							jQuery("#api-input-mapbox").css("border", "3px solid red");
							alert("<?php esc_html_e('The provided access token is invalid! Please retry', 'Vignoblexport'); ?>")
						} else {
							jQuery("#api-input-mapbox").css("border", "3px solid green");
							jQuery("#mapbox-api-key-validate").val("1");

							alert("<?php esc_html_e('Success! The access token is valid!', 'Vignoblexport'); ?>")
							validationMapbox = true;
							if (validationMapbox && validationAPI) {
								jQuery('#alert-api-text').hide();
							}
							if (i == true && validationAPI) {
								jQuery(':submitkey').prop('disabled', false);
							}
						}
						return response.status
					})
					.catch(error => console.log('error', error));
			} catch (error) {
				console.log(error);
			}
			return false;
		});
	})
</script>