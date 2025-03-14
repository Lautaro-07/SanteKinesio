<?php
session_start(); // Continuar la sesión

// Deshabilitar la visualización de errores
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'path/to/error_log.log');

// Si no se ha seleccionado una fecha y hora, redirigir a fecha.php
if (!isset($_SESSION['fecha']) || !isset($_SESSION['hora'])) {
    header('Location: fecha.php');
    exit();
}

// Conectar a la base de datos
$conn = new mysqli('localhost', 'root', '', 'sante');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Preparar la consulta
$stmt = $conn->prepare("INSERT INTO turnos (servicio, profesional, fecha, hora, nombre, telefono, gmail, obra_social, numero_sesion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Contar cuántas veces el nombre, el teléfono, el servicio y el profesional han sido registrados
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $gmail = $_POST['gmail'];
    $servicio = $_SESSION['servicio'];
    $profesional = $_SESSION['profesional'];
    
    $count_query = $conn->prepare("SELECT COUNT(*) AS count FROM turnos WHERE nombre = ? AND telefono = ? AND servicio = ? AND profesional = ?");
    $count_query->bind_param("ssss", $nombre, $telefono, $servicio, $profesional);
    $count_query->execute();
    $count_result = $count_query->get_result();
    $count_row = $count_result->fetch_assoc();
    
    $numero_sesion = $count_row['count'] + 1; // Incrementar el contador para el nuevo registro
    
    $stmt->bind_param(
        "sssssssss",
        $servicio,
        $profesional,
        $_SESSION['fecha'],
        $_SESSION['hora'],
        $nombre,
        $telefono,
        $gmail,
        $_POST['obra_social'],
        $numero_sesion
    );

    if ($stmt->execute()) {
        // Guardar el teléfono y gmail en la sesión para usarlo en la confirmación
        $_SESSION['telefono'] = $telefono;
        $_SESSION['gmail'] = $gmail;

        // Redirigir a la página de confirmación con los detalles del turno
        header('Location: confirmacion.php');
        exit();
    } else {
        echo "Error al registrar el turno: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <title>Sante - Paciente</title>
    <link rel="icon" href="img/santeLogo.jpg">

    <style>
        .footer_container{
            display: flex; 
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 75px;  
            color: #000000;
            position: absolute;
            top: 97%;
        }

        .iconos_contianer{
            font-size: 30px;
            position: relative;
            right: 0px;
            top: -35px;
            height: 0px !important;
        }

        .iconos_contianer i{
            margin: 10px;
            color:#96B394;
            height: auto;
            padding: 10px;
            border-radius: 100px;
            border: 4px solid #F6EBD5;
        }

        .iconos_contianer i:hover{
            transform: scale(1.1);
            background-color: #F6EBD5;
            transition: .5s;
        }
    </style>
</head>
<body>
<header>
    <nav class="nav_container navbar navbar-dark">
      <div class="logo_container">
        <img class="logo" src="img/santeLogo.jpg" alt="Logo">
      </div>
    </nav>
</header>
    <h1 class="servicio_title">TUS DATOS</h1>
    <hr style="position: relative; top: 40px; width: 40%; margin: auto;">

    <form class="paciente_container" action="paciente.php" method="POST">
        <label for="nombre"></label>
        <input type="text" name="nombre" placeholder="Nombre completo" required><br>

        <label for="telefono"></label>
        <input type="text" name="telefono" placeholder="Teléfono" required><br>

        <label for="gmail"></label>
        <input type="email" name="gmail" placeholder="Gmail" required><br>

        <label for="obra_social"></label>
        <input type="text" name="obra_social" placeholder="Obra Social" required><br>

        <button type="submit">Confirmar Turno</button>
    </form>
    <footer class="footer_container">
        <div class="iconos_contianer">
            <a href="https://www.instagram.com/santecentrodesalud/"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://wa.me/5492915204351"><i class="fa-solid fa-phone"></i></a>
            <a href=""><i class="fa-solid fa-envelope"></i></a>
        </div>
    </footer>
</body>
</html>