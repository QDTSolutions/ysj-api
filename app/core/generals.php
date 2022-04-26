<?php

//Redirect
function lrv_redirect($url, $code = 303)
{
    header('Location: ' . $url, true, $code);
    die();
}

//JSON response
function to_json($arr)
{
    header('Content-Type: application/json');
    die(json_encode($arr));
}

//Site URL
function site_url($page = '')
{
    global $site_url;
    return trim($site_url . '/' . $page, '/');
}

//Assets URL
function assets_url($asset = '', $type = ADMIN_URL)
{
    return site_url('assets/' . $type . '/' . $asset);
}

//Convert string to slug
function to_slug($str, $delimiter = '-')
{
    $unwanted_array = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n'];
    $str = strtr(mb_strtolower($str), $unwanted_array);
    $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
    return $slug;
}

//Check if current connections is SSL
function isSSL()
{
    if (isset($_SERVER['HTTPS'])) {
        if ('on' == strtolower($_SERVER['HTTPS']))
            return true;
        if ('1' == $_SERVER['HTTPS'])
            return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        return true;
    }
    return false;
}

//Put Cors on Windows & Linux
function putCORS()
{
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        @header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        @header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        @header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests (WINDOWS)
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            @header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            @header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}
