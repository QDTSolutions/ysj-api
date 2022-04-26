<?php
/** @var \Tipsy\Router $router */
$router->get('api/i18n/po2json', function(){
	require_once(APPPATH.'/api/i18n/po2json.php');
});