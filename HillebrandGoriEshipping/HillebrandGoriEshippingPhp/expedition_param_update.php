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
$expedition_type = $_GET['typeExpedition'];

$vat_transport = $_GET['vatTransport'];
$vat_accises = $_GET['vatAccises'];

$order = wc_get_order($order_id);

$var .= urlencode(json_encode($_GET['offer']));
$var .= ";" . $_GET['offerIndex'];
$sessionDetails = $var;

// Séparer les parties si nécessaire
$sessionDetails = explode(';', $var);
// Décoder les caractères URL
$encodedJson = urldecode($sessionDetails[0]);
// Supprimer les guillemets doubles en début et fin de la chaîne JSON
$cleanJson = trim($encodedJson, '"');
// Nettoyer la chaîne JSON en supprimant les barres obliques inverses échappées
$cleanJson = stripslashes($cleanJson);
// Remplacer les séquences de guillemets échappés par des guillemets normaux
$cleanJson = str_replace('\\"', '"', $cleanJson);
// Convertir la chaîne JSON en tableau associatif
$data_array = json_decode($cleanJson, true);

$price_excl_tax = $data_array['price'];


$total = $price_excl_tax + $tax_amount + $insurance + $vat_transport + $vat_accises;

global $wpdb;

$tablename = $wpdb->prefix . 'VINW_order_expidition';
try {
  $wpdb->update($tablename, array(
    'offre' => urldecode($sessionDetails[0]),
    'tax_amount' => (float)$tax_amount,
    'insurance' => (float)$insurance,
    'expedition_type' => $expedition_type,
    'vat_transport' => (float)$vat_transport,
    'vat_accises' => (float)$vat_accises,
    'current_price' => (float)$total,
  ), array('order_id' => (int) $order_id));
  header("HTTP/1.1 200 OK");
  echo json_encode([["message" => "OK"]]);
  die;
} catch (Exception $e) {
  header("HTTP/1.1 400 Bad Request");
  echo json_encode([["message" => "Error update  data base "]]);
  die;
}
