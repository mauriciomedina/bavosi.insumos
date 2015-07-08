<?php
/**
 * Created by PhpStorm.
 * User: gmartin
 * Date: 30/06/2015
 * Time: 04:07 PM
 */
require 'controller/class.mvc.php';

$mvc = new mvc();

$actionGet = $_GET['action'];
$actionPost = $_POST['action'];

if($mvc->authenticate()){
    switch($actionGet){
        case "salir":
            $mvc->exit_session();
            break;
        case "tonerConsulta":
            $mvc->tonerConsulta();
            break;
        case "retiroConsulta":
            $mvc->retiroConsulta();
            break;
        case "impresoraConsulta":
            $mvc->impresoraConsulta();
            break;
        default:
            $mvc->principal();
    }
}else{
    if($actionGet == "registrarse"){
        $mvc->sign_up();
    }else{
        switch($actionPost) {
            case "login":
                $mvc->login_session($_POST['user_name'], $_POST['password']);
                break;
            case "registrarse":
                if ($_POST['password'] == $_POST['password_confirm']) {
                    $user_data = array('name' => $_POST['name'], 'user_name' => $_POST['user_name'], 'password' => $_POST['password'], 'email' => $_POST['email'],);
                    if ($mvc->new_user($user_data)) {
                        $mvc->sign_up("", "Usuario creado correctamente, revise su casilla de correo electronico.");
                    } else {
                        $mvc->sign_up("Hubo un problema durante la creacion del usuario.", "");
                    }
                } else {
                    $mvc->sign_up("Las contrase&ntilde;as deben coincidir.", "");
                }
                break;
            default:
                $mvc->login();
        }
    }
}
?>