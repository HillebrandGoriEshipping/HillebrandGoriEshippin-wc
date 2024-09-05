<?php

require_once("../../../../../wp-load.php");

$curl = curl_init();

$key = get_option('VINW_ACCESS_KEY');
curl_setopt_array($curl, array(
  CURLOPT_URL => " https://test.eshipping.hillebrandgori.app/api/package/get-sizes?nbBottles=" . $_GET['nbBottles'] . "&nbMagnums=" . $_GET['nbMagnums'],
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
$choices = json_decode($response, true);
$pack = array();

for ($j = 0; $j < count($choices["packages"]); $j++) {
  $pack[$j] = array();
  $ind = 0;

  for ($i = 0; $i < count($choices["packages"][$j]); $i++) {
    $ind = $i + 1;
    $nbPack = 0;
    for ($k = 0; $k < count($choices["packages"][$j]['choice1']); $k++) {
      $nbPack = $nbPack + $choices["packages"][$j]['choice1'][$k]["nbPackages"];
    }

    $pack[$j]['choice1'] = $nbPack;
  }
}
$choice = array();
$comp = 1;
$inc = 0;
for ($j = 0; $j < count($choices["packages"]); $j++) {
  $ind = $i + 1;
  if ($pack[$j]['choice1'] == $_GET['nbPack']) {
    $choice["packages"][$inc]['choice'] = $choices["packages"][$j]['choice1'];
    $comp = $comp + 1;
    $inc = $inc + 1;
  }
}

$choice = json_encode($choice, true);

header('Content-type: application/json');
echo $choice;
die;
