<?php

global $lrvconfig;

/** @var \Tipsy\Request $Request */
$user = (object)$Request->user;

if (empty($user)) {
	to_json(['error' => TRUE, 'message' => __('No se recibieron los datos necesarios')]);
}

$usuario = filter_var($user->username, FILTER_SANITIZE_STRING);
$email = filter_var($user->email, FILTER_VALIDATE_EMAIL);
$nombre = filter_var($user->name, FILTER_SANITIZE_STRING);
$apellidos = filter_var($user->lastname, FILTER_SANITIZE_STRING);
$pass = $user->password;
$recordarme = TRUE;
$role = 'usuario';

$res = lrv_register($email, $pass, $role, $usuario, TRUE, ['name' => $nombre, 'lastname' => $apellidos]);

if ($res['error']) {
	to_json(['error' => TRUE, 'message' => $res['message']]);
} else {
	$token = $res['token'];
	//Variables Correo Confirmación
	/** @var \Tipsy\Scope $Scope */
	$Scope->usuario = $usuario;
	$Scope->email = $email;
	$Scope->token = $token;
	$Scope->titulo = __("Confirma tu correo electrónico");
	$Scope->fecha = date('d/m/Y h:i A');
	$Scope->url_activacion = site_url('api/users/activate/' . $Scope->token);
	$Scope->nombre_proyecto = $lrvconfig['site']['name'];

	//Render Correo
	/** @var \Tipsy\View $View */
	$View->config(array('layout' => 'templates/mail'));
	$html = $View->render('mail/sign-up');

	//Envio de correo
	$res2 = lrv_mail($email, $Scope->titulo . ' | ' . $lrvconfig['site']['name'], $html);

	//Respuesa AJAX
	to_json(['error' => FALSE, 'message' => __('Se te envió un correo de confirmación a ' . $email . ', revisa tu bandeja de entrada por favor.')]);
}
