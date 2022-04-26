<?php
global $lrvdb;
/** @var \Tipsy\Request $Request */
$id_user = $Request->id_user;
$role = 'administrador';
//User response
$db_user = $lrvdb->get_row("SELECT * FROM users WHERE id = {$id_user} AND role = '{$role}'");

if(empty($db_user)){
    to_json(['error' => TRUE, 'message' => __("El usuario que seleccionaste no existe")]);
}

$user = [
    'id_user' => $db_user->id,
    'username' => $db_user->username,
    'name' => $db_user->name,
    'lastname' => $db_user->lastname,
    'role' => $db_user->role,
    'email' => $db_user->email,
    'registered' => unix2local($db_user->registered),
    'last_login' => !empty($db_user->last_login) ? unix2local($db_user->last_login) : 'Nunca'
];
to_json(['error' => FALSE, 'user' => $user]);