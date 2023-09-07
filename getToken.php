<?php
//echo 'here';
//getTokenByUser();
	function getTokenByUser( $path ){
		//Prepar petición
		$data = array(
				'user' => 'admin',
					'password' => 'oscarmendoza'
		);
		$post_data = json_encode($data);
		error_log('CL - LOG Petición: '. $post_data);
		// Inicializa curl request
		$crl = curl_init('localhost/GeneralDesarrollo2022/rest/v1/token');
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($crl, CURLINFO_HEADER_OUT, true);
		curl_setopt($crl, CURLOPT_POST, true);
		curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($crl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'token: ' . $token)
		);
		// Ejecuta petición
		$result = curl_exec($crl);
		//error_log('CL - LOG Respuesta: '.$result);
		// Cierra curl sesión
		curl_close($crl);
		$response = json_decode($result);
		return $response->result->access_token;
	}
?>