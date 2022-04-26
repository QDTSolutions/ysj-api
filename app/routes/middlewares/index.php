<?php

use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Tipsy\Tipsy;

//Header de acceso para ruta cualquier ruta /api/*
Tipsy::middleware(function ($Request) {
	global $ruta, $jwt_token;
	//URL PATH
	$ruta = $Request->path();
	$public_paths = ['i18n', 'users'];
	if ($Request->loc() == 'api') {
		//CORS Headers
		putCORS();
		//Public Paths
		if (!in_array($Request->loc(1), $public_paths)) {
			$headers = $Request->headers();
			//Fix empty authorization
			if (!empty($headers['authorization'])) {
				$headers['Authorization'] = $headers['authorization'];
			}
			if (!empty($headers['Authorization'])) {
				list($token) = sscanf($headers['Authorization'], 'Bearer %s');
				//Check Token
				try {
					AuthJWT::Check($token);
					//Si el token es correcto, permite el acceso a la ruta y lo guarda decodificado en $jwt_token
					$jwt_token = AuthJWT::GetData($token);
				} catch (ExpiredException $ex) {
					$response["status"] = "error";
					$response["reauth"] = true;
					$response["message"] = [
						"title" => "session",
						"content" => "Tu sesión ha expirado, ingresa nuevamente por favor",
						"type" => "error",
					];

					to_json($response);
				} catch (SignatureInvalidException $ex) {
					$response["status"] = "session";
					$response["reauth"] = true;
					$response["message"] = [
						"title" => "Error",
						"content" => "Token inválido, ingresa nuevamente por favor",
						"type" => "error",
					];

					to_json($response);
				} catch (Exception $ex) {
					$response["status"] = "session";
					$response["reauth"] = true;
					$response["message"] = [
						"title" => "Error",
						"content" => $ex->getMessage(),
						"type" => "error",
					];

					to_json($response);
				}
			} else {
				$response["status"] = "session";
				$response["reauth"] = true;
				$response["message"] = [
					"title" => "Error",
					"content" => "Authorization token required",
					"type" => "error",
				];

				to_json($response);
			}
		}
	}
});
