<?php
global $lrvdb;

try {
    $accion = $Request->prtrAccion;
    $model = (object)$Request->model;

    switch ($accion) {
        case 'acceder':
            /** @var \Tipsy\Request $Request */
            $username = $model->nombre_usuario;
            $pass = $model->contrasenia;
            $recordarme = false;

            $res = lrv_login($username, $pass, $recordarme);

            if ($res['error']) {
                $response["status"] = "error";
                $response["message"] = [
                    "title" => "Error",
                    "content" => $res['message'],
                    "type" => "error",
                ];
                $response["token"] = NULL;
                $response["user"] = NULL;

                to_json($response);
            } else {
                $current_user = lrv_current_user();

                $data = [
                    'id_user' => $current_user->id,
                    'username' => $current_user->username,
                    'name' => $current_user->name,
                    'lastname' => $current_user->lastname,
                    'role' => $current_user->role,
                    'email' => $current_user->email
                ];

                $token = AuthJWT::SignIn($data);

                $configuracion = $lrvdb->get_row("SELECT * FROM configuracion WHERE user = {$current_user->id}");

                if ($configuracion == null) {
                    $respuesta = $lrvdb->insert(
                        "configuracion",
                        [
                            "color" => "blue",
                            "complemento" => "",
                            "modo_oscuro" => 2,
                            "modo_pantalla_completa" => 2,
                            "modo_input" => 'filled',
                            "fecha_registro" => date("Y-m-d H:i:s"),
                        ]
                    );

                    if (!($lrvdb->insert_id > 0)) {
                        $response["status"] = "error";
                        $response["data"] = [];
                        $response["message"] = [
                            "title" => "Información",
                            "content" => "Ocurrió un error al crear la configuración del usuario",
                            "type" => "error",
                        ];

                        to_json($response);
                    }
                }

                $user = lrv_get_user_data($current_user->id);

                $response["status"] = "success";
                $response["message"] = [
                    "title" => "Correcto",
                    "content" => __('Datos correctos, redireccionando a la pantalla principal...'),
                    "type" => "success",
                ];
                $response["token"] = $token;
                $response["user"] = $user;

                to_json($response);
            }
            break;
        default:
            $response["status"] = "unknown";
            $response["message"] = [
                "title" => "Error",
                "content" => "La acción: {$accion}, no esta definida en la API.",
                "type" => "error",
            ];
            $response["token"] = NULL;
            $response["user"] = NULL;

            to_json($response);
            break;
    }
} catch (\Throwable $th) {
    $response["status"] = "error";
    $response["message"] = [
        "title" => "Error",
        "content" => "Ocurrio una excepción en la aplicación: {$th->getMessage()}",
        "type" => "error",
    ];
    $response["token"] = NULL;
    $response["user"] = NULL;

    to_json($response);
}
