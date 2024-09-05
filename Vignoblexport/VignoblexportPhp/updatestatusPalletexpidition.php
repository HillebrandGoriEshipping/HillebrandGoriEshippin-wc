<?php
require_once("../../../../../wp-load.php");

$order_id = $_GET["order_id"];
$order = wc_get_order($order_id);
$order_data = $order->get_data();
$shippingDetails = $order_data['shipping'];
$societe = "company";
$company = isset($shippingDetails['company']) && strlen($shippingDetails['company']) > 0  ? $shippingDetails['company'] : "none";
$contact = isset($shippingDetails['first_name']) ? $shippingDetails['first_name'] : "";
$contact .= isset($shippingDetails['last_name']) ? " " . $shippingDetails['last_name'] : "";
$address = isset($shippingDetails['address_1']) ? $shippingDetails['address_1'] : "";
$postCode = isset($shippingDetails['postcode']) ? $shippingDetails['postcode'] : "";
$city = isset($shippingDetails['city']) ? $shippingDetails['city'] : "";
$country = isset($shippingDetails['country']) ? $shippingDetails['country'] : "";
$email = isset($order_data['billing']['email']) && strlen($order_data['billing']['email']) > 0  ? $order_data['billing']['email'] : "example@email.com";
$phone = isset($order_data['billing']['phone']) ? $order_data['billing']['phone'] : "";
$destAddress = array(
  'addressType' => $societe,
  'company' => $company,
  'contact' => $contact,
  'telephone' => $phone,
  'address' => $address,
  'zipCode' => $postCode,
  'city' => $city,
  'country' => $country,
  'email' => $email,
);

$itemsCount = $order->get_item_count();

$query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $order_id . "'";
//phpcs:ignore
$result = $wpdb->get_results($query, ARRAY_A);
$count  = $order->get_item_count();
$package = trim(stripslashes(stripslashes($result[0]['package'])), '"');
$packageArr = json_decode($package, true);

$offre = trim(stripslashes(stripslashes($result[0]['offre'])), '"');

$offreArr = json_decode($offre, true);
if ($offreArr['saturdayDelivery'] == null) {
  $offreArr['saturdayDelivery'] = 0;
} else {
  $offreArr['saturdayDelivery'] = 1;
}

$packageNumber = 0;
$packages = [];

array_push($packages, [
  'nb' => 1,
  'weight' => $packageArr['weightStill'],
  'width' => $packageArr['width'],
  'height' => $packageArr['height'],
  'length' => $packageArr['length']
]);

$curlExp = curl_init();

curl_setopt_array($curlExp, array(
  CURLOPT_URL => " https://test.eshipping.hillebrandgori.app/api/address/get-addresses?typeAddress=exp",
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
$Exp_address = isset($response[0]['address']) ? $response[0]['address'] : "";
$Exp_postCode = isset($response[0]['zipCode']) ? $response[0]['zipCode'] : "";
$Exp_city = isset($response[0]['city']) ? $response[0]['city'] : "";
$Exp_country = isset($response[0]['country']['countryAlpha2']) ? $response[0]['country']['countryAlpha2'] : "";
$Exp_email = isset($response[0]['email']) ? $response[0]['email'] : "";
$Exp_phone = isset($response[0]['telephone']) ? $response[0]['telephone'] : "";
$Exp_destAddress = array(
  'addressType' => $Exp_societe,
  'company' => $Exp_company,
  'contact' => $Exp_contact,
  'telephone' => $Exp_phone,
  'address' => $Exp_address,
  'zipCode' => $Exp_postCode,
  'city' => $Exp_city,
  'country' => $Exp_country,
  'vatNumber' => '200',
);

$price_excl_vat = (float)$order->get_subtotal();
// $encodeBody = '{"expAddress":{"addressType":"company","company":"PUBLISH IT","contact":"PUBLISH IT","telephone":"0606060606","address":"367 rue de Saint Denis","zipCode":"45590","city":"Saint Cyr en Val","country":"FR"},"destAddress":{"addressType":"company","company":"TOTO","contact":"TOTO","telephone":"0606060606","address":"4 rue des totos","zipCode":"27000","city":"Evreux","country":"FR"},"packages":[{"nb":2,"weight":10,"width":20,"height":30,"length":40}],"carrier":{"pickupDate":"2019-07-18","name":"ups","service":"UPS Standard","serviceCode":"11","price":"26.78","cutoff":"20:00:00","pickupTime":"15:00:00","deliveryDate":"2019-07-22","deliveryTime":"23:30:00","pickupAccessDelay":120,"local":"null","saturdayDelivery":1},"minHour":"12:00:00","cutoff":"20:00:00","nbBottles":"6","wineType":"wine","detailsType":"echantillon","totalValue":"100","circulation":"CRD"}';

$postBody = json_decode($encodeBody, true);
$postBody['expAddress'] = $Exp_destAddress;
$postBody['destAddress'] = $destAddress;
$postBody['packages'] = $packages;
$postBody['carrier'] = $offreArr;
$postBody['minHour'] = "12:00:00";
$postBody['cutoff'] = "18:00:00";
// get wine type
$allTypes = [];
foreach ($order->get_items() as $item_id => $item) {
  $product_id = $item->get_product_id();
  $allTypes[] = get_post_meta($product_id, '_custom_type', true);
}

$priorityType = [
  'spirits',
  'sparkling',
  'wine'
];

$postBody['wineType'] = "wine";

foreach ($priorityType as $prio) {
  if (in_array($prio, $allTypes)) {
    $postBody['wineType'] = $prio;
    break;
  }
}

$postBody['nbBottles'] = (string)$itemsCount;
$postBody['totalValue'] = (string)$price_excl_vat;
if ($postBody['carrier']['name'] == "tnt") {
  $postBody['carrier']['saturdayDelivery'] = "1";
}
// print_r($postBody);

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => " https://test.eshipping.hillebrandgori.app/api/shipment/create-pallet",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode($postBody),
  CURLOPT_HTTPHEADER => array(
    "X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY'),
    "Content-Type: application/json",
  ),
));
$response2 = json_decode(curl_exec($curl), true);
header('Content-Type: application/json');

if (isset($response2['shipment']['id'])) {
  // changing the status of the order
  $email = "";
  $order->set_status('wc-awaiting-shipment');
  $email = $order->get_billing_email();

  if ($email != "") {
    sendConfirmationMail($order->get_billing_email(), $order->get_billing_first_name());
  }
  $order->save();

  //sending the confirmation email


  // updating the database expedition ID
  UpdateOrderExpId($order, $response2['shipment']['id']);

  header("HTTP/1.1 200 OK");
  echo json_encode([[
    "message" => "OK",
    "id" => $response2['shipment']['id'],
    "labelName" => $response2['shipment']['label']['name'],
    "labelFile" => $response2['shipment']['label']['file'],
    "codeTracking" => $response2['shipment']['codeTracking'],
  ]]);
  die;
} else {
  header("HTTP/1.1 400 Bad Request");
  echo json_encode([[
    "message" => "Error submitting data",
    "error" => $response2,
  ]]);
  die;
}
curl_close($curl);

function sendConfirmationMail($to, $name)
{
  $subject = '[' . get_option('woocommerce_email_from_name') . '] Votre Palette est prête pour expédition';
  $body = '<h5>Bonjour, ' . $name . '</h5>';
  $body .= '<p>Votre palette est maintenant prete pour expédition</p>';
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

function UpdateOrderExpId($order, $idExp)
{
  global $wpdb;
  $tablename = $wpdb->prefix . 'VINW_order_expidition';
  $wpdb->update($tablename, array(
    'id_exp' => $idExp,

  ), array('order_id' => (string) $order->get_id()));
}
