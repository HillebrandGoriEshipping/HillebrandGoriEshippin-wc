<?php
require_once("../../../../../wp-load.php");
header('Content-Type: application/json');
$var = "";
$order_id = $_GET['order_id'];

$var .= urlencode(json_encode($_GET['choix']));


$var .= ";" . $_GET['choixIndex'];

$var .= ";" . $_GET['package_type'];


$sessionDetails = $var;

$sessionDetails = explode(';', $sessionDetails);
if ($_GET['package_type'] == "pallet") {
  $package_co[0] = urldecode($sessionDetails[0]);
  $package_colis = json_decode(trim(stripslashes(stripslashes($package_co[0])), '"'), true);
  $package_colis["length"] = (float)$_GET['length'];
  $package = urlencode(json_encode($package_colis));
} else {

  $parmPackedit = json_decode(trim(stripslashes($_GET["val"])), true);
  $package = $sessionDetails[0];
  $package_co[0] = urldecode($sessionDetails[0]);
  $package_colis = json_decode(trim(stripslashes(stripslashes($package_co[0])), '"'), true);
  for ($l = 0; $l < count($package_colis); $l++) {
    $parmPackarr = explode(",", $parmPackedit[$l]);
    $package_colis[$l]['sizes']['weightStill'] = (float)$parmPackarr[4];
    $package_colis[$l]['sizes']['width'] = (float)$parmPackarr[5];
    $package_colis[$l]['sizes']['height'] = (float)$parmPackarr[6];
    $package_colis[$l]['sizes']['length'] = (float)$parmPackarr[7];
  }
  $package = urlencode(json_encode($package_colis));
}
global $wpdb;
$tablename = $wpdb->prefix . 'VINW_order_expidition';

try {
  $wpdb->update($tablename, array(
    'package' => urldecode($package),
    'package_type' => urldecode($sessionDetails[2]),
    'offre' => " "
  ), array('order_id' => (int) $order_id));
  header("HTTP/1.1 200 OK");
  echo json_encode([["message" => "OK"]]);
  die;
} catch (Exception $e) {
  header("HTTP/1.1 400 Bad Request");
  echo json_encode([["message" => "Error update  data base "]]);
  die;
}
