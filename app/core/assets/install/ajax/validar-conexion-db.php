<?php

$lrvconfig = $_POST;

$db_user = $lrvconfig['db']['user'];
$db_pass = $lrvconfig['db']['pass'];
$db_name = $lrvconfig['db']['name'];
$db_host = $lrvconfig['db']['host'];

try {
    $pdo = new PDO("mysql:dbname={$db_name};host={$db_host};charset=utf8", $db_user, $db_pass);
    to_json(['error' => FALSE, 'message' => 'La conexión con la base de datos se realizó correctamente']);
} catch (Exception $ex) {
    to_json(['error' => TRUE, 'message' => 'Ocurrió un error al conectarse con la base de datos']);
}

function to_json($arr)
{
    header('Content-Type: application/json');
    die(json_encode($arr));
}
