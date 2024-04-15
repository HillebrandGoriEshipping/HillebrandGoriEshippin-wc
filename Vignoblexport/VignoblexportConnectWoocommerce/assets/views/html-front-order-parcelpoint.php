<?php

/**
 * Front order tracking rendering
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Assets\Views
 */

if (!defined('ABSPATH')) {
  exit;
}
global $wpdb;
$query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . (string) $order->get_id() . "' LIMIT 1";
//phpcs:ignore
$result = $wpdb->get_results($query, ARRAY_A);
$expId = isset($result[0]['id_exp']) ? $result[0]['id_exp'] : NULL;

if ($expId !== NULL) {
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
  d($response);
  $trackingLink = json_decode($response, true)['trackingLink'] ? json_decode($response, true)['trackingLink'] : null;
  curl_close($curl2);

  if ($trackingLink == null) {
    $trackingCode = $result[0]['tracking_code'];
  }
}
?>
<?php if (isset($status)) : ?>
  <h2><?php esc_html_e('Status: ', 'Vignoblexport'); ?></h2>
  <p><span class="CalendarDay__hovered_span"><?php echo  $status ?></span></p>
<?php endif; ?>
<?php if (isset($trackingLink)) : ?>
  <h2><?php esc_html_e('Track your order:', 'Vignoblexport'); ?></h2>
  <p><a href="<?php echo  $trackingLink ?>" target="_blank"><?php esc_html_e('Click here to track the status of your order', 'Vignoblexport'); ?></a> </p>
<?php endif; ?>
<?php if (isset($trackingCode)) : ?>
  <h2><?php esc_html_e('Track your order:', 'Vignoblexport'); ?></h2>
  <p><a href="<?php echo  $trackingCode ?>" target="_blank"><?php esc_html_e('Click here to track the status of your order', 'Vignoblexport'); ?></a> </p>
<?php endif; ?>
<div class="VINW-order-parcelpoint">
  <h2><?php esc_html_e('Chosen pickup point', 'Vignoblexport'); ?></h2>
  <?php
  require 'html-order-parcelpoint.php';
  ?>
</div>