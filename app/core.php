<?php

define('ABSPATH', dirname(__DIR__));
define('APPPATH', dirname(__FILE__));
define('ADMIN_URL', 'admin');
define('LRV_VERSION', '1.0.5');

if (!file_exists(ABSPATH . '/vendor/autoload.php')) {
    require_once('views/no-composer.phtml');
    exit;
}

require_once ABSPATH . '/vendor/autoload.php';

/**
 * Namespace
 */

use Tipsy\Tipsy;
use Gettext\Translator;
use Gettext\TranslatorFunctions;
use Gettext\Loader\PoLoader;
use Delight\Auth\Auth;

/**
 * Tipsy
 * Se verifica si existe la configuración
 */
Tipsy::config(ABSPATH . '/config/config.ini');

/**
 * Global variables
 * El uso de las variables globales
 */
global $lrvdb, $lrvauth, $lrvconfig, $title, $site_url, $site_name, $current_url, $current_user, $current_lang, $router;

$lrvconfig = Tipsy::config();

//i18n (Docs: https://github.com/oscarotero/Gettext)
$current_lang = !empty($lrvconfig['site']['lang']) ? $lrvconfig['site']['lang'] : 'es_MX';

$loader = new PoLoader();
$translations = $loader->loadFile(APPPATH . '/i18n/' . $current_lang . '/LC_MESSAGES/messages.po');
$t = Translator::createFromTranslations($translations);
TranslatorFunctions::register($t);

/**
 * Se cargan las funciones generales
 */
require_once('core/generals.php');
require_once('core/mail.php');
require_once('functions.php');

/**
 * nstall
 * En caso de que no este especificado el config.ini se procede a instalar
 */
if (empty($lrvconfig['site']['path'])) {
    require_once('core/assets/install/index.php');
    exit;
}

/**
 * debug
 */
if ($lrvconfig['debug']['show_errors']) {
    @ini_set('display_errors', 1);
    @ini_set('display_startup_errors', 1);
    @error_reporting(E_ALL);
} else {
    @ini_set('display_errors', 0);
    @ini_set('display_startup_errors', 0);
    @error_reporting(0);
}

/**
 * Cookie params (Auth)
 */
$cookie_params = [
    'lifetime' => ((int) (60 * 60 * 24 * 365.25) + 100),
    'path' => $lrvconfig['site']['path'],
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isSSL(),
    'httponly' => true
];
session_set_cookie_params($cookie_params['lifetime'], $cookie_params['path'], $cookie_params['domain'], $cookie_params['secure'], $cookie_params['httponly']);

/**
 * DB Config
 * Se configura la conexión a la base de datos
 */
$db_user = $lrvconfig['db']['user'];
$db_pass = $lrvconfig['db']['pass'];
$db_name = $lrvconfig['db']['name'];
$db_host = $lrvconfig['db']['host'];
$lrvdb = new ezSQL_mysqli($db_user, $db_pass, $db_name, $db_host, 'utf8mb4');

/**
 * Site URL
 */
$scheme = isSSL() ? 'https' : 'http';
if (!in_array($_SERVER['SERVER_PORT'], [80, 443])) {
    $site_url = trim("{$scheme}://" . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $lrvconfig['site']['path'], '/');
} else {
    $site_url = trim("{$scheme}://" . $_SERVER['SERVER_NAME'] . $lrvconfig['site']['path'], '/');
}

/**
 * Site Name
 */
$site_name = $lrvconfig['site']['name'];

/**
 * PDO Driver MySQL
 */
try {
    $pdo = new PDO("mysql:dbname=$db_name;host=$db_host;charset=utf8mb4", $db_user, $db_pass, []);
} catch (Exception $ex) {
    require_once('views/no-db.phtml');
    exit;
}

/**
 * Authentication global variable (Docs: https://github.com/delight-im/PHP-Auth)
 */
$lrvauth = new Auth($pdo);

//Authentication functions
require_once('core/auth.php');

$current_user = lrv_current_user();
if (!$current_user) {
    $current_user = (object)['id_user' => 0, 'role' => 'guest'];
}

/**
 * AuthJWT Class
 */
require_once(APPPATH . '/lib/auth/AuthJWT.php');

/**
 * Router
 */
$router = Tipsy::router();
require_once('routes.php');

/**
 * Ruta Actual
 */
$current_url = !empty($_GET['__url']) ? $_GET['__url'] : '';

/**
 * Middlewares
 */
require_once('routes/middlewares/index.php');

/**
 * Una vez cargadas todas las opciones se inicializa la app
 */
Tipsy::run();
