<?php

global $router;

$router->home(function ($View) {
    global $title;
    $title = '404';
    http_response_code(200);
    $View->config(array('layout' => 'main'));
    $View->display('404');
});

$router->otherwise(function ($View, $Request) {
    global $title;
    $title = '404';
    http_response_code(200);
    $View->config(array('layout' => '404'));
    $View->display('404');
});

/* API */
require_once('routes/api/index.php');
