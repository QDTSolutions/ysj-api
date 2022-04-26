<?php

global $lrvdb;

/** @var \Tipsy\Request $Request */
$token = $Request->token;

try {
    $user_data = AuthJWT::GetData($token);
    $current_user = $lrvdb->get_row("SELECT * FROM users WHERE id = {$user_data->id_user}");
    $user = [
        'id_user' => $current_user->id,
        'username' => $current_user->username,
        'name' => $current_user->name,
        'lastname' => $current_user->lastname,
        'role' => $current_user->role,
        'email' => $current_user->email
    ];
    to_json(['error' => false, 'user' => $user]);
} catch (\Firebase\JWT\ExpiredException $ex) {
    to_json(['error' => true, 'message' => 'Tu sesión ha expirado, ingresa nuevamente por favor', 'reauth' => true]);
} catch (\Firebase\JWT\SignatureInvalidException $ex) {
    to_json(['error' => true, 'message' => 'Token inválido, ingresa nuevamente por favor', 'reauth' => true]);
} catch (Exception $ex) {
    to_json(['error' => true, 'message' => $ex->getMessage(), 'reauth' => true]);
}
