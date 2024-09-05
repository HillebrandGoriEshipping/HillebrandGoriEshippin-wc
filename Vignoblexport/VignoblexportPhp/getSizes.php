<?php

require_once("../../../../../wp-load.php");

$curl = curl_init();

$key = get_option('VINW_ACCESS_KEY');

curl_setopt_array($curl, array(
  CURLOPT_URL => " https://test.eshipping.hillebrandgori.app//api/package/get-sizes?nbBottles=" . $_GET['nbBottles'] . "&nbMagnums=" . $_GET['nbMagnums'],
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

header('Content-type: application/json');
echo $response;
die;
