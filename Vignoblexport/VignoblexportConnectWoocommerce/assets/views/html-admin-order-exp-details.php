<?php

/**
 * Admin order edit page tracking rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if (!defined('ABSPATH')) {
  exit;
}
global $wpdb;
$query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . (string) $order->ID . "' LIMIT 1";
//phpcs:ignore
$result = $wpdb->get_results($query, ARRAY_A);

$expId = $result[0]['id_exp'];
$trackingCode = $result[0]['tracking_code'];

$curl1 = curl_init();

curl_setopt_array($curl1, array(
  CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/shipment/get-status?shipmentId=" . $expId,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY')
  ),
));

$response = curl_exec($curl1);
$status = json_decode($response, true)['status'];
$status_id = json_decode($response, true)['statusId'];
curl_close($curl1);

$curl2 = curl_init();

curl_setopt_array($curl2, array(
  CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/shipment/get-tracking-link?shipmentId=" . $expId,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY')
  ),
));

$response = curl_exec($curl2);
$decoded_response = json_decode($response, true);
$trackingLink = isset($decoded_response['trackingLink']) ? $decoded_response['trackingLink'] : null;
curl_close($curl2);

$curl3 = curl_init();

curl_setopt_array($curl3, array(
  CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/shipment/get-label?shipmentId=" . $expId,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY')
  ),
));

$response = curl_exec($curl3);
if (array_key_exists("directLink", json_decode($response, true))) {
  $labelLink = json_decode($response, true)['directLink'] ? json_decode($response, true)['directLink'] : null;
  curl_close($curl3);
}

?>
<p><strong><?php esc_html_e('Status :', 'Vignoblexport'); ?></strong>
  <span class="CalendarDay__hovered_span">
    <?php
    if (get_locale() == 'en_GB' || get_locale() == 'en_US') {
      if ($status_id == 1) {
        echo 'Draft';
      } elseif ($status_id == 2) {
        echo 'Pending pick up';
      } elseif ($status_id == 3) {
        echo 'Picked up';
      } elseif ($status_id == 4) {
        echo 'Delivered';
      } elseif ($status_id == 5) {
        echo 'Cancelled';
      } elseif ($status_id == 6) {
        echo 'Anomaly';
      } elseif ($status_id == 7) {
        echo 'To cancel';
      } elseif ($status_id == 8) {
        echo 'Partial delivery';
      } else {
        echo $status;
      }
    } elseif (get_locale() == 'es_ES') {
      if ($status_id == 1) {
        echo 'Borrador';
      } elseif ($status_id == 2) {
        echo 'Para recoger';
      } elseif ($status_id == 3) {
        echo 'Recopilado';
      } elseif ($status_id == 4) {
        echo 'Entregado';
      } elseif ($status_id == 5) {
        echo 'Cancelado';
      } elseif ($status_id == 6) {
        echo 'Anomalía';
      } elseif ($status_id == 7) {
        echo 'Para cancelar';
      } elseif ($status_id == 8) {
        echo 'Entrega parcial';
      } elseif ($status_id == 9) {
        echo 'Excepción';
      } else {
        echo $status;
      }
    } elseif (get_locale() == 'it_IT') {
      if ($status_id == 1) {
        echo 'Bozza';
      } elseif ($status_id == 2) {
        echo 'Per raccogliere';
      } elseif ($status_id == 3) {
        echo 'Raccolta';
      } elseif ($status_id == 4) {
        echo 'Consegnato';
      } elseif ($status_id == 5) {
        echo 'Annullato';
      } elseif ($status_id == 6) {
        echo 'Anomalia';
      } elseif ($status_id == 7) {
        echo 'Annullamento';
      } elseif ($status_id == 8) {
        echo 'Consegna parziale';
      } elseif ($status_id == 9) {
        echo 'Eccezione';
      } else {
        echo $status;
      }
    } else {
      echo $status;
    }
    ?>
  </span>
</p>
<?php if (isset($trackingLink)) { ?>
  <p><strong><?php esc_html_e('Track your order :', 'Vignoblexport'); ?></strong> <a href="<?php echo  $trackingLink ?>" target="_blank"><?php esc_html_e('Click here', 'Vignoblexport'); ?></a> </p>
<?php } ?>
<?php if (isset($trackingCode)) { ?>
  <p><strong><?php esc_html_e('Tracking code(s) :', 'Vignoblexport'); ?></strong>
  <p><?php echo  str_replace(';', '<br>', $trackingCode) ?></p>
<?php } ?>

<?php if (array_key_exists("directLink", json_decode($response, true))) { ?>
  <p><strong><?php esc_html_e('Download labels :', 'Vignoblexport'); ?></strong> <a href="<?php echo  $labelLink ?>" target="_blank"><?php esc_html_e('Click here', 'Vignoblexport'); ?></a> </p>
<?php } ?>