<?php
$countryCode = 'RO';
$vatNo = '24251690';

$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");

$result = $client->checkVat(array(
  'countryCode' => $countryCode,
  'vatNumber' => $vatNo
));

// Print the result using print_r or echo
//print_r($result);
//var_dump($result);


$array = (array) $result;

echo $array['name'];








				//API Url
				$url = 'https://webservicesp.anaf.ro/PlatitorTvaRest/api/v6/ws/tva';
				
				//Initiate cURL.
				$ch = curl_init($url);
				
				//The JSON data.
				$jsonData = array(
				'cui' => preg_replace("/[^0-9]/", "", $vatNo),
				'data' => date('Y-m-d')
				);
				
				//Encode the array into JSON.
				$jsonDataEncoded = json_encode([$jsonData]);
				
				//Tell cURL that we want to send a POST request.
				curl_setopt($ch, CURLOPT_POST, 1);
				
				//Attach our encoded JSON string to the POST fields.
				curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
				
				//Set the content type to application/json
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
				
				//Return JSON content
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
				
				//Execute the request
				$result = curl_exec($ch);
				
				//close curl
				curl_close($ch);
				
				//decode result
				$json = json_decode($result, true);
				
				//echo json_encode($json, JSON_PRETTY_PRINT);
				
				echo $json["cod"];
				
				
				echo $json['found'][0]['sdenumire_Strada'].' '.$json['found'][0]['dnumar_Strada'];
				
				if(!$json['found'][0]['denumire']){
					echo 'NU EXISTA';
					}
			