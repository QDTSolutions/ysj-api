<?php

global $lrvdb;

/** @var \Tipsy\Request $Request */
$user = (object)$Request->user;

$safe_keys = ['name', 'lastname', 'username', 'email', 'password'];
$datos_safe = [];
$role = 'administrador';

foreach($safe_keys as $key){
    $datos_safe[$key] = $user->{$key};
}

$res = lrv_register($datos_safe['email'], $datos_safe['password'], $role, $datos_safe['username'], false, ['name' => $datos_safe['name'], 'lastname' => $datos_safe['lastname']]);

if($res['error']){
	to_json(['error' => TRUE, 'message' => $res['message']]);
} else {
	to_json(['error' => FALSE, 'message' => 'El usuario ha sido registrado correctamente']);
}