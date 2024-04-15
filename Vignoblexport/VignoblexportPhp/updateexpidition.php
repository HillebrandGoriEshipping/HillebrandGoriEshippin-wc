<?php

require_once("../../../../../wp-load.php");

$var = "";
$var .= urlencode(json_encode($_GET['offer']));
$var .= ";" . $_GET['offerIndex'];

echo  WC()->session->get('VINW_CONF_Colis');

if (WC()->session) {
    echo  WC()->session->get('VINW_CONF_Colis');
    $package_co = array();
    $arrayssesion = explode(";", WC()->session->get('VINW_CONF_Colis'));
    $package_co[0] = urldecode($arrayssesion[0]);

    $package_colis = json_decode(trim(stripslashes(stripslashes($package_co[0])), '"'), true);
}

$var .= ";" . $arrayssesion[0];
$var .= ";1";
$var .= ";" . $_GET['methode'];
$var .= ";" . $_GET['methodIndex'];
$var .= ";" . $_GET['priceOffre'];
$var .= ";" . "colis";

if (isset($_GET['nbr_bottles'])) {
    $var .= ";" . $_GET['nbr_bottles'];
} else {
    $var .= ";0";
}
if (isset($_GET['nb_magnums'])) {
    $var .= ";" . $_GET['nb_magnums'];
} else {
    $var .= ";0";
}
echo $var;

if (WC()->session) {
    WC()->session->set('VINW_CONF_EXP', $var);
    if (isset($_GET['offer'])) {
        WC()->session->set('OFFER', $_GET['offer']);
    }

    WC()->cart->calculate_totals();
    WC()->cart->calculate_shipping();
    $packages = $woocommerce->cart->get_shipping_packages();
    foreach ($packages as $package_key => $package) {
        $session_key  = 'shipping_for_package_' . $package_key;
        $stored_rates = WC()->session->__unset($session_key);
    }
} else {
    wp_send_json_error(array('message' => 'could not set point. Woocommerce sessions are not enabled!'));
}

echo WC()->session->get('VINW_CONF_EXP');
