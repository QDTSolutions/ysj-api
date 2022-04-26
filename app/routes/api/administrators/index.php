<?php
global $router;

/* API */
$base = 'api/administrators';

$router->post("{$base}/get-all", function($Request) use ($base){
    require_once(APPPATH."/{$base}/get-all.php");
});

$router->post("{$base}/get", function($Request) use ($base){
    require_once(APPPATH."/{$base}/get.php");
});

$router->post("{$base}/new", function($Request) use ($base){
    require_once(APPPATH."/{$base}/new.php");
});

$router->post("{$base}/edit", function($Request) use ($base){
    require_once(APPPATH."/{$base}/edit.php");
});

$router->post("{$base}/delete", function($Request) use ($base){
    require_once(APPPATH."/{$base}/delete.php");
});