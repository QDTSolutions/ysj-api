<?php

global $lrvdb;
/** @var \Tipsy\Request $Request */
$id_user = $Request->id_user;
$role = 'administrador';

$res = $lrvdb->delete('users', ['id' => $id_user, 'role' => $role]);

if($res === FALSE){
	to_json(['error' => TRUE, 'message' => __("Ocurrió un error al eliminar el usuario")]);
} else {
	to_json(['error' => FALSE, 'message' => __("El usuario ha sido eliminado correctamente")]);
}
