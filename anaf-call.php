<?php
	//check if post is done
	if (isset($_POST["billing_b_cif"]) && !empty($_POST["billing_b_cif"])) {
		$url = 'https://webservicesp.anaf.ro/PlatitorTvaRest/api/v6/ws/tva';
		$ch = curl_init($url);
		$jsonData = array(
        'cui' => preg_replace("/[^0-9]/", "", $_POST['billing_b_cif']),
        'data' => date('Y-m-d')
		);
		$jsonDataEncoded = json_encode([$jsonData]);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		echo $result;
	}	