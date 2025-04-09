<?php
require 'vendor/autoload.php'; // Incluir el autoloader de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start(); // Continuar la sesión

// Verificar que existan los datos necesarios en la sesión
if (!isset($_SESSION['fecha']) || !isset($_SESSION['hora']) || !isset($_SESSION['profesional']) || !isset($_SESSION['servicio']) || !isset($_SESSION['telefono']) || !isset($_SESSION['gmail'])) {
    header('Location: fecha.php');
    exit();
}

// Datos del turno
$servicio = $_SESSION['servicio'];
$profesional = $_SESSION['profesional'];
$fecha = $_SESSION['fecha'];
$hora = $_SESSION['hora'];
$telefono = $_SESSION['telefono'];
$gmail = $_SESSION['gmail'];
$to_email = $gmail;
$subject = 'Confirmación de Turno - Santé Centro De Salud';

// Crear el mensaje de WhatsApp
$body =  "¡Hola! Tu turno ha sido confirmado.\nDetalles del turno:\nServicio: $servicio\nProfesional: $profesional\nFecha: $fecha\nHora: $hora\nPor favor, recuerda traer ropa deportiva cómoda para una óptima sesión.\n¡Te esperamos!";
$from_email = 'oligiatielizondo@gmail.com';

// Crear el mensaje de correo electrónico
$mensaje_email = "
<html>
<head>
    <title>Detalles de tu turno</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dddddd;
            border-radius: 10px;
        }
        .email-header {
            text-align: center;
            padding-bottom: 20px;
        }
        .email-header img {
            max-width: 100px;
        }
        .email-body {
            padding: 20px;
        }
        .email-body h2 {
            color: #333333;
        }
        .email-body p {
            color: #555555;
        }
        .email-footer {
            text-align: center;
            padding-top: 20px;
            color: #888888;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='email-header'>
            <img src='cid:logo_img' alt='Santé Centro De Salud'>
        </div>
        <div class='email-body'>
            <h2>¡Turno Confirmado con Éxito!</h2>
            <p>Gracias por confiar en Santé Centro De Salud. Aquí están los detalles de tu turno:</p>
            <p><strong>Servicio:</strong> $servicio</p>
            <p><strong>Profesional:</strong> $profesional</p>
            <p><strong>Fecha:</strong> $fecha</p>
            <p><strong>Hora:</strong> $hora</p>
            <p>¡Recuerda! No contestes este mensaje. Ante cualuier duda escribenos - +542915204351</p>
        </div>
        <div class='email-footer'>
            <p>Santé Centro De Salud</p>
            <p>Zapiola 723, Santé Centro De Salud ✨</p>
        </div>
    </div>
</body>
</html>
";

// Enviar el correo
$mail = new PHPMailer(true);
try {
    // Configuración del servidor
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
    $mail->SMTPAuth = true;
    $mail->Username = $from_email; // Tu dirección de correo
    $mail->Password = 'ostc ewyt kjhy firp'; // Tu contraseña de aplicación generada
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configuración del correo
    $mail->setFrom($from_email, 'Santé Centro De Salud');
    $mail->addAddress($to_email);
    $mail->Subject = $subject;
    $mail->Body = $mensaje_email;
    $mail->isHTML(true); // Establecer el formato del correo a HTML
    $mail->CharSet = PHPMailer::CHARSET_UTF8; // Asegurarse de que la codificación sea UTF-8

    // Adjuntar imagen
    $mail->addEmbeddedImage('img/santeLogo.jpg', 'logo_img'); // Cambia a la imagen correcta

    $mail->send();
    $message = 'El correo ha sido enviado con éxito.';
} catch (Exception $e) {
    $message = 'El correo no pudo ser enviado. Error: ' . $mail->ErrorInfo;
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
    <link rel="icon" href="img/santeLogo.jpg">
    <title>Turno Confirmado</title>
    <style>
        .whatsapp-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #25D366;
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
            text-decoration: none;
        }
        .whatsapp-button:hover {
            background-color: rgb(16, 153, 66);
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
    <div class="color"></div>
    <div class="tarjeta-confirmacion">
        <h2>¡Turno Confirmado con Éxito!</h2>
        <p>Gracias por confiar en nosotros. Aquí están los detalles de tu turno:</p>
        <p><strong>Servicio:</strong> <?= htmlspecialchars($servicio); ?></p>
        <p><strong>Profesional:</strong> <?= htmlspecialchars($profesional); ?></p>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($fecha); ?></p>
        <p><strong>Hora:</strong> <?= htmlspecialchars($hora); ?></p>
        <p><?= htmlspecialchars($message); ?></p>
        <a href="index.php" class="btn-volver">Volver al Inicio</a>
    </div>
</body>
</html>
