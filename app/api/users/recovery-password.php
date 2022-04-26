<?php

global $lrvdb;
/** @var \Tipsy\Request $Request */
$token = $Request->token;
$pass = $Request->pass;

if (empty($token) || empty($pass)) {
	to_json(['error' => TRUE, 'message' => __('No se recibieron los datos necesarios')]);
}

$res = lrv_reset_password($token, $pass);

if ($res['error']) {
	to_json(['error' => TRUE, 'message' => $res['message']]);
} else {
	//Respuesta AJAX
	to_json(['error' => FALSE, 'message' => __('La contraseña fue cambiada con éxito, ahora puedes ingresar nuevamente.')]);
}
