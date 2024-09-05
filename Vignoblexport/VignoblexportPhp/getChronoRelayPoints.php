<?php

require_once("../../../../../wp-load.php");
header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo json_encode([["message" => "Method Not Allowed"]]);
    die;
} else {
    if (!empty($_POST)) {
        $curl = curl_init();
        $key = get_option('VINW_ACCESS_KEY');
        curl_setopt_array($curl, array(
            CURLOPT_URL => " https://test.eshipping.hillebrandgori.app//api/relay/get-chronopost-relay-points?street=" . rawurlencode($_POST['street']) . "&zipCode=" . rawurlencode($_POST['zipCode']) . "&city=" . rawurlencode($_POST['city']) . "&productCode=" . rawurlencode($_POST['productCode']) . "&shipmentDate=" . rawurlencode($_POST['shipmentDate']) . "&country=" . rawurlencode($_POST['country']),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "X-AUTH-TOKEN: " . $key
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
        die;
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode([["message" => "Missing required params"]]);
        die;
    }
}
