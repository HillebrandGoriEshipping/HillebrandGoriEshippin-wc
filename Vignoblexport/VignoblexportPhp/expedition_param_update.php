<?php

require_once("../../../../../wp-load.php");

header('Content-Type: application/json');

$var = "";

$order_id = $_GET['order_id'];
$tax_amount = $_GET['taxAmount'];
if ($_GET['insurance']) {
  $insurance = $_GET['insurance'];
} else {
  $insurance = 0;
}
$expedition_type = $_GET['expedition_type'];

$order = wc_get_order($order_id);

$var .= urlencode(json_encode($_GET['offer']));
$var .= ";" . $_GET['offerIndex'];

$sessionDetails = $var;
$sessionDetails = explode(';', $sessionDetails);

global $wpdb;

$tablename = $wpdb->prefix . 'VINW_order_expidition';
try {
  $wpdb->update($tablename, array(
    'offre' => urldecode($sessionDetails[0]),
    'tax_amount' => (float)$tax_amount,
    'insurance' => (float)$insurance,
    'expedition_type' => $expedition_type
  ), array('order_id' => (int) $order_id));
  header("HTTP/1.1 200 OK");
  echo json_encode([["message" => "OK"]]);
  die;
} catch (Exception $e) {
  header("HTTP/1.1 400 Bad Request");
  echo json_encode([["message" => "Error update  data base "]]);
  die;
}
