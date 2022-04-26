<?php
global $lrvdb;

/** @var \Tipsy\Request $Request */
$user = (object)$Request->user;

$safe_keys = ['email', 'username', 'name', 'lastname'];
$role = 'administrador';
$datos_safe = [];

if(empty($user)){
	to_json(['error' => TRUE, 'message' => __('No se recibió información')]);
}

if(!empty($user->password)){
	lrv_change_password($user->password, $user->id_user);
}

foreach($safe_keys as $key){
	$datos_safe[$key] = $user->{$key};
}

$res = $lrvdb->update('users', $datos_safe, ['id' => $user->id_user]);

//User response
$db_user = $lrvdb->get_row("SELECT * FROM users WHERE id = {$user->id_user} AND role = '{$role}'");
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

if($res === FALSE){
	to_json(['error' => TRUE, 'message' => __("Ocurrió un error al actualizar el usuario")]);
} else {
	to_json(['error' => FALSE, 'message' => __("El usuario ha sido actualizado correctamente"), 'user' => $user]);
}