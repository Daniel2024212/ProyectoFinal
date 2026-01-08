<?php
// 1. CONEXIÓN A LA BASE DE DATOS
// Ajusta la ruta ../../ según donde guardes este archivo
$ruta_db = __DIR__ . '/../../includes/database.php';
if (file_exists($ruta_db)) {
    include_once $ruta_db;
} else {
    // Intento de conexión manual si falla el include
    $db = mysqli_connect('localhost', 'root', 'root', 'web_salon_db');
}

$alertas = [];
$usuario = [
    'nombre' => '',
    'apellido' => '',
    'telefono' => '',
    'email' => ''
];

// 2. PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Sanitizar entradas
    $usuario['nombre'] = mysqli_real_escape_string($db, $_POST['nombre']);
    $usuario['apellido'] = mysqli_real_escape_string($db, $_POST['apellido']);
    $usuario['email'] = mysqli_real_escape_string($db, $_POST['email']);
    $usuario['telefono'] = mysqli_real_escape_string($db, $_POST['telefono']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // Validaciones básicas
    if(!$usuario['nombre']) $alertas[] = "El nombre es obligatorio";
    if(!$usuario['apellido']) $alertas[] = "El apellido es obligatorio";
    if(!$usuario['email']) $alertas[] = "El email es obligatorio";
    if(strlen($password) < 6) $alertas[] = "El password debe tener al menos 6 caracteres";

    // Si no hay errores
    if(empty($alertas)) {
        // Verificar si ya existe
        $query = "SELECT * FROM usuarios WHERE email = '" . $usuario['email'] . "'";
        $resultado = mysqli_query($db, $query);

        if(mysqli_num_rows($resultado) > 0) {
            $alertas[] = "El usuario ya está registrado";
        } else {
            // Hashear password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // INSERTAR COMO ADMIN (admin = 1, confirmado = 1)
            $query = "INSERT INTO usuarios (nombre, apellido, email, password, telefono, admin, confirmado) 
                      VALUES ('{$usuario['nombre']}', '{$usuario['apellido']}', '{$usuario['email']}', '$passwordHash', '{$usuario['telefono']}', 1, 1)";
            
            $resultado = mysqli_query($db, $query);

            if($resultado) {
                header('Location: /login'); // Redirigir al login
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #000000;
            --bg-card: #1a1a1a;
            --text-main: #ffffff;
            --primary: #0da6f3;
            --border: #333;
            --error: #e74c3c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .contenedor-form {
            background-color: var(--bg-card);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid var(--border);
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        h1 { text-align: center; margin-bottom: 10px; color: var(--text-main); }
        p.descripcion { text-align: center; color: #aaa; margin-bottom: 30px; font-size: 14px; }

        .campo { margin-bottom: 20px; }
        .campo label { display: block; margin-bottom: 8px; font-size: 14px; color: #ccc; }
        .campo input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background-color: #222;
            color: white;
            outline: none;
        }
        .campo input:focus { border-color: var(--primary); }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            margin-top: 10px;
            transition: 0.3s;
        }
        .btn-submit:hover { background-color: #0b8acb; }

        .alerta {
            background-color: var(--error);
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 13px;
        }
        
        .acciones { margin-top: 20px; text-align: center; font-size: 14px; }
        .acciones a { color: var(--primary); text-decoration: none; }
    </style>
</head>
<body>

    <div class="contenedor-form">
        <h1>Crear Administrador</h1>
        <p class="descripcion">Llena el formulario para crear una cuenta de acceso total</p>

        <?php foreach($alertas as $alerta): ?>
            <div class="alerta"><?php echo $alerta; ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" placeholder="Tu Nombre" value="<?php echo $usuario['nombre']; ?>">
            </div>

            <div class="campo">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" placeholder="Tu Apellido" value="<?php echo $usuario['apellido']; ?>">
            </div>

            <div class="campo">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Tu Teléfono" value="<?php echo $usuario['telefono']; ?>">
            </div>

            <div class="campo">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Tu Email" value="<?php echo $usuario['email']; ?>">
            </div>

            <div class="campo">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Tu Password">
            </div>

            <input type="submit" value="Crear Cuenta Admin" class="btn-submit">
        </form>

        <div class="acciones">
            <a href="/login">¿Ya tienes cuenta? Iniciar Sesión</a>
        </div>
    </div>

</body>
</html>