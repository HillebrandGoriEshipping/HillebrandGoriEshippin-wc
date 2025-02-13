<?php
require_once("../../../../../wp-load.php");
header('Content-Type: application/json');

if (WC()->session) {
  header("HTTP/1.1 200 OK");
  echo json_encode([[
    "message" => "OK",
    "value" => WC()->session->get('VINW_CONF_Colis'),
  ]]);
  die;
} else {
  wp_send_json_error(array('message' => 'Woocommerce sessions are not enabled!'));
}
