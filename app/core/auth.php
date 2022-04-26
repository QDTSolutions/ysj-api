<?php

//Register
use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\InvalidSelectorTokenPairException;
use Delight\Auth\NotLoggedInException;
use Delight\Auth\TokenExpiredException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UnknownUsernameException;
use Delight\Auth\UserAlreadyExistsException;

function lrv_register($email, $pass, $role = 'user', $username = null, $verify = false, $extra = [])
{
    global $lrvdb, $lrvauth, $token;
    try {
        if ($verify) {
            $id = $lrvauth->register($email, $pass, $username, function ($selector, $token) {
                $GLOBALS['token'] = urlencode(base64_encode(json_encode(compact('selector', 'token'))));
            });
        } else {
            $id = $lrvauth->admin()->createUser($email, $pass, $username);
        }
        if (count($extra)) {
            $extra['role'] = $role;
            $lrvdb->update('users', $extra, compact('id'));
        } else {
            $lrvdb->update('users', compact('role'), compact('id'));
        }
        return ($verify) ? array('error' => FALSE, 'id' => $id, 'token' => $token) : array('error' => FALSE, 'id' => $id);
    } catch (InvalidEmailException $e) {
        return array('error' => TRUE, 'type' => 'email', 'message' => __('Invalid e-mail'));
    } catch (InvalidPasswordException $e) {
        return array('error' => TRUE, 'type' => 'password', 'message' => __('Invalid password'));
    } catch (UserAlreadyExistsException $e) {
        return array('error' => TRUE, 'type' => 'username_exists', 'message' => __('The username already exists'));
    } catch (TooManyRequestsException $e) {
        return array('error' => TRUE, 'type' => 'too_many_requests', 'message' => __('Too many requests, try again in %s', gmdate("H:i:s", $e->getCode())));
    } catch (Exception $e) {
        return array('error' => TRUE, 'type' => 'unknow', 'message' => __('Unknow error: %s', $e->getMessage()), 'e' => $e);
    }
}

//E-mail confirm
function lrv_confirm($token)
{
    global $lrvauth;
    try {
        $partes = json_decode(base64_decode(urldecode($token)));
        $lrvauth->confirmEmail($partes->selector, $partes->token);
        return array('error' => FALSE);
    } catch (InvalidSelectorTokenPairException $e) {
        return array('error' => TRUE, 'type' => 'invalid_token', 'message' => __('Invalid token selector'));
    } catch (TokenExpiredException $e) {
        return array('error' => TRUE, 'type' => 'expired_token', 'message' => __('Token expired'));
    } catch (TooManyRequestsException $e) {
        return array('error' => TRUE, 'type' => 'too_many_requests', 'messages' => __('Too many requests, try again in %s', gmdate("H:i:s", $e->getCode())));
    } catch (Exception $e) {
        return array('error' => TRUE, 'type' => 'unknow', 'message' => __('Unknow error: %s', $e->getMessage()), 'e' => $e);
    }
}

//password Perdida
function lrv_recover($email)
{
    global $lrvauth, $token;
    try {
        $token = '';
        $lrvauth->forgotPassword($email, function ($selector, $token) {
            $GLOBALS['token'] = urlencode(base64_encode(json_encode(compact('selector', 'token'))));
        });
        return array('error' => FALSE, 'token' => $token);
    } catch (InvalidEmailException $e) {
        return array('error' => TRUE, 'type' => 'email', 'message' => __('Invalid e-mail'));
    } catch (TooManyRequestsException $e) {
        return array('error' => TRUE, 'type' => 'too_many_requests', 'message' => __('Too many requests, try again in %s', gmdate("H:i:s", $e->getCode())));
    } catch (Exception $e) {
        return array('error' => TRUE, 'type' => 'unknow', 'message' => __('Unknow error: %s', $e->getMessage()), 'e' => $e);
    }
}

//Reset Password
function lrv_reset_password($token, $password)
{
    global $lrvauth;
    try {
        $partes = json_decode(base64_decode(urldecode($token)));
        $lrvauth->resetPassword($partes->selector, $partes->token, $password);
        return array('error' => FALSE);
    } catch (InvalidSelectorTokenPairException $e) {
        return array('error' => TRUE, 'type' => 'invalid_token', 'message' => __('Invalid token selector'));
    } catch (TokenExpiredException $e) {
        return array('error' => TRUE, 'type' => 'expired_token', 'message' => __('Token expired'));
    } catch (InvalidPasswordException $e) {
        return array('error' => TRUE, 'type' => 'password', 'message' => __('Invalid password'));
    } catch (TooManyRequestsException $e) {
        return array('error' => TRUE, 'type' => 'too_many_requests', 'message' => __('Too many requests, try again in %s', gmdate("H:i:s", $e->getCode())));
    } catch (Exception $e) {
        return array('error' => TRUE, 'type' => 'unknow', 'message' => __('Unknow error: %s', $e->getMessage()), 'e' => $e);
    }
}

//Change current user password
function lrv_change_current_password($antigua, $nueva)
{
    global $lrvauth;
    try {
        $lrvauth->changePassword($antigua, $nueva);
        return array('error' => FALSE);
    } catch (NotLoggedInException $e) {
        return array('error' => TRUE, 'type' => 'not_logged_in', 'message' => __('Not logged in'));
    } catch (InvalidPasswordException $e) {
        return array('error' => TRUE, 'type' => 'password', 'message' => __('Invalid password'));
    } catch (Exception $e) {
        return array('error' => TRUE, 'type' => 'unknow', 'message' => __('Unknow error: %s', $e->getMessage()), 'e' => $e);
    }
}

//Change password by username
function lrv_change_password($password, $username = FALSE)
{
    global $lrvdb;
    $id = ($username) ? $username : lrv_id();
    $password = password_hash($password, PASSWORD_DEFAULT);
    $res = $lrvdb->update('users', compact('password'), compact('id'));
    if ($res !== FALSE) {
        return array('error' => FALSE, 'message' => __('Password changed successfully'));
    } else {
        return array('error' => TRUE, 'message' => __('An error occurred when changing the password'));
    }
}

//Change role
function lrv_change_role($id, $role)
{
    global $lrvdb;
    $lrvdb->update('users', compact('role'), compact('id'));
    return array('error' => FALSE, 'message' => __('Role changed successfully'));
}

//Login
function lrv_login($email, $pass, $remember = FALSE)
{
    global $lrvauth;
    $remember = ($remember) ? ((int) (60 * 60 * 24 * 365.25)) : null;
    try {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $lrvauth->login($email, $pass, $remember);
        } else {
            $lrvauth->loginWithUsername($email, $pass, $remember);
        }
        return array('error' => FALSE, 'id' => $lrvauth->getUserId());
    } catch (InvalidEmailException $e) {
        return array('error' => TRUE, 'type' => 'email', 'message' => __('Invalid e-mail'));
    } catch (InvalidPasswordException $e) {
        return array('error' => TRUE, 'type' => 'password', 'message' => __('Invalid password'));
    } catch (EmailNotVerifiedException $e) {
        return array('error' => TRUE, 'type' => 'email_not_verified', 'message' => __('Your user has not yet been verified, check your inbox'));
    } catch (TooManyRequestsException $e) {
        return array('error' => TRUE, 'type' => 'too_many_requests', 'message' => __('Too many requests, try again in %s', gmdate("H:i:s", $e->getCode())));
    } catch (UnknownUsernameException $e) {
        return array('error' => TRUE, 'type' => 'unknow_user', 'message' => __('Incorrect username or password'));
    } catch (Exception $e) {
        return array('error' => TRUE, 'type' => 'unknow', 'message' => __('Unknow error: %s', $e->getMessage()), 'e' => $e);
    }
}

//Log out
function lrv_logout()
{
    global $lrvauth;
    $lrvauth->logout();
}

//Get current user id
function lrv_id()
{
    global $lrvauth;
    if ($lrvauth->isLoggedIn()) {
        return $lrvauth->getUserId();
    } else {
        return 0;
    }
}

//Alias
function get_current_user_id()
{
    return lrv_id();
}

//Get current user
function lrv_current_user()
{
    global $lrvdb;
    $id = lrv_id();
    return $lrvdb->get_row("SELECT * FROM users WHERE id={$id}");
}

//Get current user role
function get_current_user_role()
{
    global $lrvdb;
    return $lrvdb->get_var("SELECT role FROM users WHERE id=" . lrv_id());
}

//Check if password is correct
function lrv_check_password($password, $id_user = 0)
{
    global $lrvdb;
    $id_user = ($id_user) ? $id_user : lrv_id();
    if ($id_user) {
        $passwordInDatabase = $lrvdb->get_var("SELECT password FROM users WHERE id = {$id_user}");
        return password_verify($password, $passwordInDatabase);
    } else {
        return false;
    }
}

//Get current user
function lrv_get_user_data($id_user)
{
    global $lrvdb;

    $user = $lrvdb->get_row("SELECT id AS id_user, username, name, lastname, email FROM users WHERE id = {$id_user}");
    $user->f_perfil = $lrvdb->get_var("SELECT persona.f_perfil FROM users INNER JOIN colaborador ON users.id = colaborador.`user` INNER JOIN persona ON persona._id = colaborador.persona WHERE users.id = {$id_user}");
    $user->f_portada = $lrvdb->get_var("SELECT persona.f_portada FROM users INNER JOIN colaborador ON users.id = colaborador.`user` INNER JOIN persona ON persona._id = colaborador.persona WHERE users.id = {$id_user}");

    $file_keys = ['f_perfil', 'f_portada'];;
    foreach ($file_keys as $key) {
        $archivo = get_file($user->{$key});
        $user->{$key} = !empty($archivo) ? [
            'id_archivo' => $archivo->id_archivo,
            'estatus' => $archivo->estatus,
            'url' => get_file_url($archivo),
            'subio' => get_autor_archivo($archivo),
            'autorizo' => get_autorizacion_archivo($archivo),
            'nombre' => $archivo->nombre
        ] : [
            'id_archivo' => false,
            'estatus' => 'Pendiente',
            'url' => '#',
            'subio' => '',
            'autorizo' => '',
            'nombre' => ''
        ];
    }

    $user->aplicacion = $lrvdb->get_row("SELECT nombre FROM aplicacion WHERE _id = 1");
    $user->configuracion = $lrvdb->get_row("SELECT color, complemento, modo_oscuro, modo_pantalla_completa, modo_input FROM configuracion WHERE user = {$id_user}");

    return $user;
}
