<?php
global $router;

/* API */
$base = 'api/users';

$router->post("{$base}/login", function($Request) use ($base){
    require_once(APPPATH."/{$base}/login.php");
});

$router->post("{$base}/validate", function($Request) use ($base){
    require_once(APPPATH."/{$base}/validate.php");
});

$router->post("{$base}/sign-up", function($Request) use ($base){
    require_once(APPPATH."/{$base}/sign-up.php");
});

$router->post("{$base}/request-recovery", function($Scope, $View, $Request) use ($base){
    require_once(APPPATH."/{$base}/request-recovery.php");
});

$router->post("{$base}/recovery-password", function($Request) use ($base){
    require_once(APPPATH."/{$base}/recovery-password.php");
});