<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;
class loginController {
    public static function login(Router $router){
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            $alertas = $auth->validarLogin();

            if(empty($alertas)){
                // Comprobar que existe el usuario
                $usuario = Usuario::where('email', $auth->email);

                if($usuario){
                    if($usuario->comprobarPasswordAndVerificado($auth->password)){
                        // Autenticar al usuario
                        if(!isset($_SESSION)){
                        session_start();
                        }

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . ' ' . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionamiento
                        if($usuario->admin === '1'){
                            $_SESSION['admin'] = $usuario->admin ?? null;

                            header('Location: /admin');
                        }else{
                            header('Location: /cita');
                        }

                        // debuguear($_SESSION);
                    }
                }else{
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

       $router->render('auth/login', [
        'alertas' => $alertas,
       ]);
    }
    public static function logout(){
        if(!isset($_SESSION)){
            session_start();
            }
        $_SESSION = [];
        header('Location: /');
    }
    public static function olvide(Router $router){

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)){
                $usuario = Usuario::where('email', $auth->email);

                if($usuario && $usuario->confirmado === '1'){
                    $usuario->crearToken();
                    $usuario->guardar();
                    header('Location: /recuperar?token='.$usuario->token );
                }else{
                    Usuario::setAlerta('error', 'el usuario no Existe o no esta Confirmado');
                }

                // debuguear($usuario);
            }
            // debuguear($auth);
        }
        $alertas = Usuario::getAlertas();
        $router->render('auth/olvide', [
            'alertas' => $alertas,
        ]);
    }
    public static function recuperar(Router $router){
        $alertas = [];
        $error = false;
        $token = s($_GET['token']);
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)){
            Usuario::setAlerta('error', 'Token No Válido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();
            if(empty($alertas)){
                $usuario->password = null;
                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;
                $resultado = $usuario->guardar();
                if($resultado){
                    header('Location: /');
                }
                // debuguear($usuario);
            }
        }
        
        $alertas = Usuario::getAlertas();
        $router->render('auth/recuperar', [
            'alertas' => $alertas,
            'error' => $error,
        ]);
    }
    public static function crear(Router $router){
        $usuario = new Usuario;

        // Alertas vacias
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            //Revisar que alerta este vacio
            if(empty($alertas)){
                // Verificar que el usuario no este registrado
                $resultado = $usuario->existeUsuario();

                if($resultado->num_rows){
                    $alertas = Usuario::getAlertas();
                }else{
                    //El usuario no esta registrado
                    // Hashear el password
                    $usuario->hashPassword();

                    // Generar uh token único
                    $usuario->crearToken();

                    // Enviar el email
                    // $email = new Email($usuario->email, $usuario->nombre, $usuario->apellido, $usuario->token);

                    // $email->enviarConfirmacion();
                    // Confirmar usuario
                    

                    // Crear usuario o cliente
                    $resultado = $usuario->guardar();

                    if($resultado){
                        header('Location: /mensaje?token='.$usuario->token );
                    }


                    // debuguear($usuario);
                    
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas,
        ]);
    }

    public static function mensaje(Router $router){
        $token = s($_GET['token']);
        $usuario = Usuario::where('token', $token);
        $router->render('auth/mensaje', [
            'usuario' => $usuario,
        ]);
    }
    public static function mesage(Router $router){
        $token = s($_GET['token']);
        $usuario = Usuario::where('token', $token);
        $router->render('auth/mesage', [
            'usuario' => $usuario,
        ]);
    }
    public static function confirmar(Router $router){
        $alertas = [];
        $email = s($_GET['email']);
        $usuario = Usuario::where('email', $email);
    
        if(empty($usuario)){
            //Mensaje de error
            Usuario::setAlerta('error', 'El Token no es Valido');
        }else{
            //Modificar el confirmado del usuario
            
            Usuario::setAlerta('exito', 'Cuenta Confirmada Correctamente');
            $usuario->confirmado = '1';
            $usuario->token = '';
            $usuario->guardar();
        }
        $alertas = Usuario::getAlertas();
        $router->render('auth/confirmar', [
            'alertas' => $alertas,
        ]);
    }
}