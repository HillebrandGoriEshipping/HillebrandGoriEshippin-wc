<?php
require_once("../../../../../wp-load.php");

use Vignoblexport\VignoblexportConnectWoocommerce\Util\Order_Util;

$order_id = $_GET["order_id"];
$order = wc_get_order($order_id);
$order_data = $order->get_data();
$shippingDetails = $order_data['shipping'];
$societe = isset($shippingDetails['company']) && strlen($shippingDetails['company']) > 0  ? "company" : "individual";
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
$type_liv = $result[0]['type_liv'];
$expedition_type = $result[0]['expedition_type'];

$packageNumber = 0;
$packages = [];

foreach ($packageArr as $key => $choice) {
  $nb = $choice['nbPackages'];

  array_push($packages, [

    'nb' => $nb,
    'weight' => $choice['sizes']['weightStill'],
    'width' => $choice['sizes']['width'],
    'height' => $choice['sizes']['height'],
    'length' => $choice['sizes']['length']
  ]);
  $packageNumber++;
}

$nbBottles = 0;
foreach ($packageArr as $key => $valueBottles) {
  $nbBottles += $valueBottles['nbBottles'];
}

if ($type_liv == "pointRelais") {
  $parcelpoint = Order_Util::get_parcelpoint($order);

  $accessPoint = array(
    "id" => $parcelpoint->code,
    "name" => $parcelpoint->name,
    "addLine1" => $parcelpoint->address,
    "city" => $parcelpoint->city,
    "postal" => $parcelpoint->zipcode,
    "country" => $parcelpoint->country,
  );
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
  'vatNumber' => "200",
);

$curl = curl_init();
$encodeBody = '';
$postBody = json_decode($encodeBody, true);
$postBody['expAddress'] = $Exp_destAddress;
$postBody['destAddress'] = $destAddress;
$postBody['packages'] = $packages;

if ($country != $Exp_country) {
  $details = [];
  $i = 0;
  foreach ($order->get_items() as $item_id => $item) {
    $appellation = get_post_meta($item['product_id'], '_custom_appelation', true);
    $capacity = get_post_meta($item['product_id'], '_custom_capacity', true);
    $alcohol_degree = get_post_meta($item['product_id'], '_custom_alcohol_degree', true);
    $color = get_post_meta($item['product_id'], '_custom_color', true);
    if ($color === 'Red') {
      $color_hscode = 'red';
    } elseif ($color === 'Rose') {
      $color_hscode = 'rose';
    } else {
      $color_hscode = 'no-color';
    }
    $vintage = get_post_meta($item['product_id'], '_custom_vintage', true);
    d($appellation);
    d($capacity);
    d($alcohol_degree);
    d($color);
    $curlHscode = curl_init();
    $hscodeURL = "https://test.extranet.vignoblexport.fr/api/get-hscode";
    $hscodeURL .= "?appellationName=" . $appellation;
    $hscodeURL .= "&capacity=" . $capacity;
    $hscodeURL .= "&alcoholDegree=" . $alcohol_degree;
    $hscodeURL .= "&color=" . $color_hscode;

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
    $hs_code = json_decode(curl_exec($curlHscode), true);
    curl_close($curlHscode);
    var_dump($hs_code);
    //"vintage":"2018",
    $product  = $item->get_product();
    $unit_value = $product->get_price();
    d($unit_value);
    $quantity = $item->get_quantity();
    d($quantity);
    $details[] = [
      "appellation" => $appellation,
      "capacity" => $capacity,
      "alcoholDegree" => $alcohol_degree,
      "color" => $color,
      "hsCode" => $hs_code,
      "vintage" => (string)$vintage,
      "unitValue" => $unit_value,
      "quantity" => $quantity
    ];
  }
  $postBody['details'] = $details;
}
var_dump($postBody['details']);

$postBody['carrier'] = $offreArr;

if (isset($nbBottles)) {
  $postBody['nbBottles'] = (string)$nbBottles;
}
if (isset($choice['nbMagnums'])) {
  $postBody['nbMagnums'] = (string)$choice['nbMagnums'];
}

$postBody['minHour'] = "08:00:00";
$postBody['cutoff'] = "20:00:00";
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

$insurance = $result[0]['insurance'];
$postBody['insurance'] = get_option('VINW_ASSURANCE') == 'yes' ? "1" : "0";
if ($postBody['insurance'] == "1" && isset($result[0]['insurance'])) {
  $postBody['insurancePrice'] = (float)$insurance;
}

$postBody['dutiesTaxes'] = get_option('VINW_TAX_RIGHTS') == 'exp' ? "exp" : "dest";

$price_excl_vat = (float)$order->get_subtotal();
$postBody['totalValue'] = (string)$price_excl_vat;
if ($expedition_type == "fiscal_rep") {
  $postBody['fiscalRepresentation'] = 1;
}
var_dump($postBody);

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/shipment/create",
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
  $email = "";
  $order->set_status('wc-awaiting-shipment');
  $email = $order->get_billing_email();

  $order->save();

  // updating the database expedition ID
  UpdateOrderExpId($order, $response2['shipment']['id']);

  // updating the database tracking link
  UpdateOrderTrackingLink($order, $response2['shipment']['id']);

  // updating the database tracking code
  UpdateOrderTrackingCode($order, $response2['shipment']['id']);

  if ($email != "") {
    //sending the confirmation email
    sendConfirmationMail($order_id, $order->get_billing_email(), $order->get_billing_first_name(), $type_liv);
  }

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

function sendConfirmationMail($order_id, $to, $name, $type_liv)
{
  global $wpdb;
  $order = wc_get_order($order_id);
  $order_data = $order->get_data();
  $shippingDetails = $order_data['shipping'];
  $societe = isset($shippingDetails['company']) && strlen($shippingDetails['company']) > 0  ? "company" : "individual";
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

  $query = "SELECT * FROM {$wpdb->prefix}VINW_order_expidition WHERE order_id = '" . $order_id . "'";
  //phpcs:ignore
  $result = $wpdb->get_results($query, ARRAY_A);
  $offer = trim(stripslashes(stripslashes($result[0]['offre'])), '"');
  $offer_array = json_decode($offer, true);
  $offer_name = $offer_array['name'];

  $parcelpoint = Order_Util::get_parcelpoint($order);

  $subject = '[' . get_option('woocommerce_email_from_name') . '] ' . __('Your order is ready for shipment.', 'Vignoblexport');
  $body = '<h5>' . __('Hello, ', 'Vignoblexport') . $name . '</h5>';
  $body .= '<p>' . __('Thanks for your order.', 'Vignoblexport') . '</p>';
  $body .= '<p>' . __('Your order is now awaiting for shipment :', 'Vignoblexport') . '</p>';
  $body .= '<p>' . __('Chosen carrier :', 'Vignoblexport') . ' ' . strtoupper($offer_name) . '</p>';
  $body .= '<p>' . __('Order tracking number(s) :', 'Vignoblexport') . '<ul><li>' . str_replace(';', '</li><li>', $result[0]['tracking_code']) . '</li></ul></p>';
  if ($result[0]['tracking_link']) {
    $body .= '<p>' . __('You can follow your order by clicking on ', 'Vignoblexport') . ' <a href="' . $result[0]['tracking_link'] . '">' . __('this link', 'Vignoblexport') . '</a></p>';
  }
  $body .= '<table><th>' . __('Billing address', 'Vignoblexport') . '</th>';
  $body .= '<th>' . __('Shipping address', 'Vignoblexport') . '</th>';
  $body .= '<tr><td>' . $shippingDetails['first_name'] . ' ' . $shippingDetails['last_name'] . "\n" . $destAddress['address'] . "\n" . $destAddress['zipCode'] . ' ' . $destAddress['city'] . '</td>';
  if ($type_liv == "pointRelais") {
    $body .= '<td>' . $parcelpoint->name . "\n" . $parcelpoint->address . "\n" . $parcelpoint->zipcode . ' ' . $parcelpoint->city . '</td></tr></table>';
  } else {
    $body .= '<td>' . $destAddress['address'] . "\n" . $destAddress['zipCode'] . ' ' . $destAddress['city'] . '</td></tr></table>';
  }
  $body .= '<p>' . __('Regards,', 'Vignoblexport') . '<br>' . get_option('woocommerce_email_from_name') . '.</p>';

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

function UpdateOrderTrackingLink($order, $idExp)
{
  global $wpdb;
  $tablename = $wpdb->prefix . 'VINW_order_expidition';

  // get tracking link with idExp
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/shipment/get-tracking-link?shipmentId=" . $idExp,
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

  $response = json_decode(curl_exec($curl), true);

  if (!isset($response['trackingLink'])) {
    $tracking_link = null;
  } else {
    $tracking_link = $response['trackingLink'];
  }
  curl_close($curl);

  $wpdb->update($tablename, array(
    'tracking_link' => $tracking_link,
  ), array('order_id' => (string) $order->get_id()));
}

function UpdateOrderTrackingCode($order, $idExp)
{
  global $wpdb;
  $tablename = $wpdb->prefix . 'VINW_order_expidition';

  // get tracking code with idExp
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://test.extranet.vignoblexport.fr/api/shipment/get-shipment/" . $idExp,
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
  $response = json_decode(curl_exec($curl), true);
  $tracking_code = $response['codeTracking'] ? $response['codeTracking'] : null;
  curl_close($curl);

  $wpdb->update($tablename, array(
    'tracking_code' => $tracking_code,
  ), array('order_id' => (string) $order->get_id()));
}
