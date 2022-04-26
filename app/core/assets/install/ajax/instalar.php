<?php

define('ABSPATH', realpath(__DIR__ . '/../../../../../'));

if (file_exists(ABSPATH . '/config/config.ini')) {
    die('LRVTipsy Security');
}

global $lrvdb, $lrvauth, $lrvconfig;

require_once ABSPATH . '/vendor/autoload.php';

$lrvconfig = $_POST;

$user = $lrvconfig['admin'];

unset($lrvconfig['admin']);

//Modo booleano debug
$lrvconfig['debug']['show_errors'] = boolval(!empty($lrvconfig['debug']['show_errors']));
//Ambiente (DEV, PRO)
$lrvconfig['debug']['environment'] = !empty($lrvconfig['debug']['environment']) ? 'PRO' : 'DEV';

//Idioma por defecto
$lrvconfig['site']['lang'] = 'es_MX';

//Escribir en el archivo
use \Matomo\Ini\IniWriter;

$writer = new IniWriter();

try {
    @$writer->writeToFile(ABSPATH . '/config/config.ini', $lrvconfig);
} catch (Exception $ex) {
    to_json(['error' => TRUE, 'message' => $ex->getMessage()]);
}

//Url del sitio (automática)
$site_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $lrvconfig['site']['path'];

//Crear Usuario
$db_user = $lrvconfig['db']['user'];
$db_pass = $lrvconfig['db']['pass'];
$db_name = $lrvconfig['db']['name'];
$db_host = $lrvconfig['db']['host'];
$lrvdb = new ezSQL_mysqli($db_user, $db_pass, $db_name, $db_host, 'uft8');

//DB driver para conexión a MySQL
try {
    $pdo = new PDO("mysql:dbname={$db_name};host={$db_host};charset=utf8", $db_user, $db_pass);
} catch (Exception $ex) {
    to_json(['error' => TRUE, 'message' => 'Ocurrió un error al conectarse con la base de datos']);
}

//Configurar variable global de autentificación (Docs: https://github.com/delight-im/PHP-Auth)
$lrvauth = new \Delight\Auth\Auth($pdo);

//Funciones lrvAuth
require_once(ABSPATH . '/app/core/auth.php');

//Crear primer usuario
if (empty($existe)) {
    lrv_register($user['correo'], $user['pass'], 'superadmin', $user['usuario']);
}

//Asingar permisos a carpeta config
@chmod(ABSPATH . '/config', 0755);

to_json(['error' => FALSE, 'message' => 'Admin instalado correctamente', 'url' => $site_url]);

function to_json($arr)
{
    header('Content-Type: application/json');
    die(json_encode($arr));
}
