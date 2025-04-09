<?php
session_start([
    'cookie_lifetime' => 0, // La sesión se cierra cuando se cierra el navegador
]);

$error = ''; // Inicializar la variable $error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si los campos 'email' y 'pass' están definidos en $_POST
    if (isset($_POST['email']) && isset($_POST['pass'])) {
        $email = $_POST['email'];
        $pass = $_POST['pass'];

        // Verificar si el usuario es administrador
        if ($email === 'administrador@gmail.com' && $pass === 'administrador') {
            $_SESSION['logged_in'] = true;
            $_SESSION['profesional'] = 'Administrador';
            header('Location: administrador.php');
            exit();
        }

        // Conexión a la base de datos
        $conn = new mysqli('localhost', 'root', '', 'sante');
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }

        // Buscar el usuario en la base de datos
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $expected_pass = $user['pass'];
            if ($pass === $expected_pass) {
                $_SESSION['logged_in'] = true;
                $_SESSION['profesional'] = $user['name']; // Guardar el nombre del profesional en la sesión
                header('Location: pacientes.php');
                exit();
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'Email incorrecto.';
        }

        $conn->close();
    } else {
        $error = 'Por favor, complete todos los campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="../bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <link rel="icon" href="../img/santeLogo.jpg">
    <title>Login</title>
    <style>
        body {
            background-color: #F6EBD5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        .nav_container {
            width: 100%;
            background-color: #F6EBD5;
            padding: 10px 0;
            text-align: center;
        }

        .logo {
            width: 70px;
        }

        .title_cont {
            text-align: center;
            margin: 20px 0;
            font-size: 30px;
        }

        .login-container {
            background-color: #e7e4e4;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn_login {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 12px;
            cursor: pointer;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn_login:hover {
            background-color: #7d9a7d;
        }

        @media (max-width: 768px) {
            .title_cont h2 {
                font-size: 24px;
            }

            .login-container {
                padding: 15px;
            }

            .login-container input {
                padding: 10px;
                font-size: 14px;
            }

            .btn_login {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="nav_container">
    <img src="../img/santeLogo.jpg" alt="Logo" class="logo">
</div>
<div class="title_cont">
    <h2>Inicia Sesión</h2>
</div>
<div class="login-container">
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form action="index.php" method="post">
        <input type="email" name="email" required placeholder="Email"><br>
        <input type="password" name="pass" required placeholder="Contraseña"><br>
        <input type="submit" class="btn_login" value="Login">
    </form>
</div>
</body>
</html>