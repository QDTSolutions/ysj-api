<?php

define('WEBHOOK_USER', 'PROQSY');
define('WEBHOOK_PASS', 'PROQSY22#');

/* Configuración Hora y Fecha */
date_default_timezone_set('America/Mexico_City');

/* Configuración de Moneda */
setlocale(LC_MONETARY, 'es_MX');

function unix2local($time, $format = 'd/m/Y h:i A')
{
    $dt = new DateTime("@{$time}");
    $dt->setTimeZone(new DateTimeZone('America/Mexico_City'));
    return $dt->format($format);
}

//Auth Basico
function require_auth()
{
    @header('Cache-Control: no-cache, must-revalidate, max-age=0');
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
    $is_not_authenticated = (!$has_supplied_credentials ||
        $_SERVER['PHP_AUTH_USER'] != WEBHOOK_USER ||
        $_SERVER['PHP_AUTH_PW']   != WEBHOOK_PASS);
    if ($is_not_authenticated) {
        @header('HTTP/1.1 401 Authorization Required');
        @header('WWW-Authenticate: Basic realm="Access denied"');
        die("Credenciales incorrectas");
    }
}

function get_file($id_archivo)
{
    global $lrvdb;
    $file = $lrvdb->get_row("SELECT * FROM archivo WHERE id_archivo = '{$id_archivo}'");
    if (!empty($file)) {
        $file->path = ABSPATH . $file->path;
        return $file;
    } else {
        return false;
    }
}

function remove_file($id_archivo)
{
    global $lrvdb;
    $lrvdb->query("BEGIN");
    $file = get_file($id_archivo);
    $res = $lrvdb->delete('archivo', ['id_archivo' => $id_archivo]);
    if ($res !== false) {
        if (unlink($file->path)) {
            $lrvdb->query("COMMIT");
            return true;
        } else {
            $lrvdb->query("ROLLBACK");
            return false;
        }
    } else {
        $lrvdb->query("ROLLBACK");
        return false;
    }
}

function get_file_url($archivo)
{
    $relative_path = get_relative_path($archivo->path);
    return site_url($relative_path);
}

function get_file_path($archivo)
{
    return $archivo->path;
}

function get_relative_path($path)
{
    return str_replace(ABSPATH, '', $path);
}

function get_user_role($id_user)
{
    global $lrvdb;
    return $lrvdb->get_var("SELECT role FROM users WHERE id = {$id_user}");
}

function get_autor_archivo($archivo)
{
    return get_user_fullname($archivo->id_usuario);
}

function get_autorizacion_archivo($archivo)
{
    if ($archivo->estatus == 'Pendiente' || empty($archivo->id_autoriza)) {
        return '';
    } else {
        return get_user_fullname($archivo->id_autoriza);
    }
}

function get_user_fullname($id_user)
{
    global $lrvdb;
    return $lrvdb->get_var("SELECT CONCAT(name, ' ', lastname) FROM users WHERE id = {$id_user}");
}

function get_emails_administradores()
{
    global $lrvdb;
    $emails = $lrvdb->get_col("SELECT email FROM users WHERE role = 'administrador'");
    return !empty($emails) ? $emails : [];
}

function notify_change_estatus_empleado($Scope, $View, $id_empleado, $estatus, $id_usuario)
{
    //Variables Correo Confirmación
    /** @var \Tipsy\Scope $Scope */
    $emails = get_emails_administradores();
    $Scope->titulo = "Estatus de empleado actualizado";
    $Scope->fecha = date('d/m/Y h:i A');
    $nombre_usuario = '';
    $nombre_empleado = '';
    $Scope->mensaje = mb_strtoupper("<p>El empleado <i>#{$id_empleado} {$nombre_empleado}</i>, ha sido actualizado al estatus <strong><i>{$estatus}</i></strong>.</p><br /><p>Este cambio fue realizado el {$Scope->fecha}, por <i>{$nombre_usuario}</i>.</p>");

    //Render Correo
    /** @var \Tipsy\View $View */
    $View->config(array('layout' => 'templates/mail'));
    $html = $View->render('mail/empleados/estatus');

    //Envio de correo
    try {
        lrv_mail(['to' => $emails], $Scope->titulo, $html);
        return true;
    } catch (Exception $ex) {
        return false;
    }
}

function to_up($str)
{
    return mb_strtoupper($str);
}
