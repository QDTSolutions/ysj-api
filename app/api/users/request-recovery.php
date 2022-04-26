<?php

global $lrvdb, $lrvconfig;

/** @var \Tipsy\Request $Request */

$email = filter_var($Request->email, FILTER_VALIDATE_EMAIL);

$res = lrv_recover($email);

if ($res['error']) {
	to_json(['error' => TRUE, 'message' => $res['message'], 'res' => $res]);
} else {
	$token = $res['token'];
	//Variables Correo Confirmación
	/** @var \Tipsy\Scope $Scope */
	$Scope->email = $email;
	$Scope->token = $token;
	$Scope->titulo = "Recuperar contraseña";
	$Scope->fecha = date('d/m/Y h:i A');
	$Scope->usuario = $lrvdb->get_var("SELECT username FROM users WHERE email = '{$email}'");
	$Scope->url_activacion = site_url('api/users/recovery/' . $Scope->token);
	$Scope->nombre_proyecto = $lrvconfig['site']['name'];

	//Render Correo
	/** @var \Tipsy\View $View */
	$View->config(array('layout' => 'templates/mail'));
	$html = $View->render('mail/recovery');

	//Envio de correo
	$res2 = lrv_mail($email, $Scope->titulo . ' | ' . $lrvconfig['site']['name'], $html);

	//Respuesa AJAX
	to_json(['error' => FALSE, 'message' => __('Se te envió un correo de recuperación, revisa tu bandeja de entrada por favor.')]);
}
