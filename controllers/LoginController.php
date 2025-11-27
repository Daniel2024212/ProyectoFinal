<?php

namespace Controllers;

use Classes\Email;
use Models\Usuario;
use Models\Token;
use MVC\Router;
use Classes\AuthService;

class LoginController {

    public static function login(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $args = $_POST;
            $auth = new Usuario($args);
            
            $alertas = $auth->validar_login();
            
            if(empty($alertas)) {
                // Comprobar que exista el usuario:
                $usuario = Usuario::where('email', $auth->email);

                if($usuario) {
                    // Verficar password:
                    if($usuario-> comprobar_password_and_verificado($auth->password)) {
                        // Autenticar al usuario:
                        isSession();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . ' ' . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionamiento:
                        if($usuario->admin === '1') {
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('Location: /admin');
                        } else {
                            header('Location: /cita');
                        }
                    }
                    
                } else {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login', [
            'alertas' => $alertas
        ]);
    }

    public static function logout(Router $router) {
        isSession();

        $_SESSION = [];
        
        header('location: /');
    }
    
    // Debe de quitarse:
    /*
    public static function olvide(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validar_email();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email);

                if($usuario) {
                    if($usuario->usuario_comprobado()) {
                        // Generar token:
                        $usuario->crear_token();
                        $usuario->guardar();

                        // Enviar el email:
                        $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                        $email->enviar_instrucciones();

                        // Alerta de exito:
                        Usuario::setAlerta('exito', 'Revisa tu email');

                    }
                } else {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }
    */

    public static function olvide(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validar_email();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email);

                if($usuario) {
                    if($usuario->usuario_comprobado()) {
                        // Generar token:
                        $token = new Token(['usuarioId' => $usuario->id]);
                        $token->crear_token();
                        $token->guardar();
                        $usuario->guardar();

                        // Enviar el email:
                        $email = new Email($usuario->nombre, $usuario->email, $token->token);
                        $email->enviar_instrucciones();

                        // Alerta de éxito:
                        Usuario::setAlerta('éxito', 'Revisa tu email');
                    }
                } else {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }
    
    // Debe de quitarse:
    /*
    public static function recuperar(Router $router) {
        $alertas = [];
        $error = false;
        $token = s($_GET['token']);

        // Buscar usuario por su token:
        $usuario = Usuario::where('token', $token);
        
        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // leer el nuevo password y guardarlo:
            $password = new Usuario($_POST);
            $alertas = $password->validar_password();

            if(empty($alertas)) {
                $usuario->password = null;
                $usuario->password = $password->password;
                $usuario->hash_password();
                $usuario->token = null;
                
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /');
                }
            }
        }
        
        $alertas = Usuario::getAlertas();

        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]);        
    }
    */

    public static function recuperar(Router $router) {
        $alertas = [];
        $error = false;
        $token_param = s($_GET['token']);

        // Buscar el token:
        $token = Token::where('token', $token_param);
        
        if(empty($token)) {
            Usuario::setAlerta('error', 'Token no válido');
            $error = true;
        } else {
            // Buscar el usuario:
            $usuario = Usuario::find($token->usuarioId);
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // leer el nuevo password y guardarlo:
            $password = new Usuario($_POST);
            $alertas = $password->validar_password();

            if(empty($alertas)) {
                $usuario->password = null;
                $usuario->password = $password->password;
                $usuario->hash_password();
                $token->eliminar();
                
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('Location: /');
                }
            }
        }
        
        $alertas = Usuario::getAlertas();

        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]);        
    }
    
    // Debe de quitarse:
    /*
    public static function crear(Router $router) {
        $usuario = new Usuario;
        // Alertas vacias:
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $args = $_POST;
            $usuario->sincronizar($args);
            $alertas = $usuario->validar_nueva_cuenta();

            // Revisar que alertas este vacio:
            if(empty($alertas)) {
                // Verificar que el usuario no este registrado:
                $resultado = $usuario->exite_usuario();

                if($resultado->num_rows) {
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password:
                    $usuario->hash_password();

                    // Generar un token único:
                    $usuario->crear_token();

                    // Enviar el email:
                    $nombre = $usuario->nombre;
                    $email_usuario = $usuario->email;
                    $token = $usuario->token;
                    //debuguear($usuario);

                    $email = new Email($nombre, $email_usuario, $token);
                    $email->enviar_confirmacion();

                    // Crear el usuario:
                    $resultado = $usuario->guardar();

                    if($resultado) {
                        header('Location: /mensaje');
                    }

                    //debuguear($usuario);
                    //$usuario->guardar();
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }
    */

    public static function crear(Router $router) {
        $usuario = new Usuario;
        // Alertas vacias:
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $args = $_POST;
            $usuario->sincronizar($args);
            $alertas = $usuario->validar_nueva_cuenta();

            // Revisar que alertas este vacio:
            if(empty($alertas)) {
                // Verificar que el usuario no este registrado:
                $resultado = $usuario->exite_usuario();

                if($resultado->num_rows) {
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear el password:
                    $usuario->hash_password();

                    // Generar un token único:
                    $token = new Token();
                    $token->crear_token();

                    // Enviar el email:
                    $nombre = $usuario->nombre;
                    $email_usuario = $usuario->email;
                    
                    //debuguear($usuario);

                    $email = new Email($nombre, $email_usuario, $token->token);
                    $email->enviar_confirmacion();

                    // Crear el usuario:
                    $resultado = $usuario->guardar();

                    $token->usuarioId = $resultado['id'];
                    $token->guardar();

                    if($resultado) {
                        header('Location: /mensaje');
                    }

                    
                    //$usuario->guardar();
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje');
    }

    // Debe de quitarse:
    /*
    public static function confirmar(Router $router) {
        $alertas = [];
        $columna = 'token';
        $token = s($_GET[$columna]);
        $usuario = Usuario::where($columna, $token);

        if(empty($usuario)) {
            // Motrar mensaje de error:
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            // Modificar el usuario confirmado:
            $usuario->confirmado = '1';
            $usuario->token = null;
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
        }

        // Obtener alertas:
        $alertas = Usuario::getAlertas();

        // Rendereizar la vista:
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }
    */

    public static function confirmar(Router $router) {
        $alertas = [];
        $columna = 'token';
        $token_param = s($_GET[$columna]);
        $token = Token::where($columna, $token_param);

        if(empty($token)) {
            // Motrar mensaje de error:
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            // Buscar el usuario:
            $usuario = Usuario::find($token->usuarioId);
            // Modificar el usuario confirmado:
            $usuario->confirmado = '1';
            $usuario->guardar();
            $token->eliminar();
            Usuario::setAlerta('éxito', 'Cuenta comprobada correctamente');
        }

        // Obtener alertas:
        $alertas = Usuario::getAlertas();

        // Rendereizar la vista:
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }
}