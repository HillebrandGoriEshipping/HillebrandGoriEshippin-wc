<?php

$curl = curl_init();

$key = str_replace(' ', '+', urldecode($_GET['apikey']));
curl_setopt_array($curl, array(
  CURLOPT_URL => " https://test.eshipping.hillebrandgori.app/api/package/get-sizes",
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
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
$res = json_decode($response, true);

if ($http_status == 200) {
  die('OK');
} elseif ($http_status == 403) {
  echo 'La clé api entrée est incorrecte';
} else {
  echo 'Une erreur système est survenue, contactez le support Vignoblexport si le problème persiste.';
}
