<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Paciente</title>
    <link rel="stylesheet" href="../css/bootstrap.css">
    <link rel="icon" href="../img/santeLogo.jpg">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #F6EBD5;
            font-family: 'Poppins', sans-serif;
        }

        .btn_horarios{
            background-color: #96B394;
            border: none;
            padding: 8px;
            cursor: pointer;
            border-radius: 10px !important;
            position: relative;
            bottom: 9px;
        }

        .btn_horarios a{
            color: white;
            text-decoration: none;
        }

        .content {
            padding: 20px;
            margin: auto;
            width: 90%;
            max-width: 600px;
        }
        h1 {
            color: #96B394;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group button {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .form-group button:hover {
            background-color: #7d9a7d;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
        .success-message {
            color: green;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Agendar Paciente</h1>
        <div class="form-container">
            <?php
            use PHPMailer\PHPMailer\PHPMailer;
            use PHPMailer\PHPMailer\Exception;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                require '../vendor/autoload.php';

                // Conectar a la base de datos
                $conn = new mysqli('localhost', 'root', '', 'sante');
                if ($conn->connect_error) {
                    die("Error de conexión: " . $conn->connect_error);
                }

                $servicio = $conn->real_escape_string($_POST['servicio']);
                $profesional = $conn->real_escape_string($_POST['profesional']);
                $fecha = $conn->real_escape_string($_POST['fecha']);
                $hora = $conn->real_escape_string($_POST['hora']);
                $nombre = $conn->real_escape_string($_POST['nombre']);
                $telefono = $conn->real_escape_string($_POST['telefono']);
                $gmail = $conn->real_escape_string($_POST['gmail']);
                $obra_social = $conn->real_escape_string($_POST['obra_social']);

                // Verificar la cantidad de turnos en la misma hora para Kinesiología
                if ($servicio === 'Kinesiología') {
                    $sql = "SELECT COUNT(*) AS count FROM turnos WHERE servicio = 'Kinesiología' AND profesional = '$profesional' AND fecha = '$fecha' AND hora = '$hora'";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();

                    if ($row['count'] >= 4) {
                        echo "<div class='error-message'>No puedes registrar más de 4 pacientes en la misma hora para Kinesiología.</div>";
                        exit();
                    }
                } else {
                    // Verificar si ya existe un registro con los mismos datos para otros servicios
                    $sql = "SELECT COUNT(*) AS count FROM turnos WHERE nombre = '$nombre' AND telefono = '$telefono' AND profesional = '$profesional' AND fecha = '$fecha' AND hora = '$hora'";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();

                    if ($row['count'] > 0) {
                        echo "<div class='error-message'>No puedes registrarte dos veces en el mismo horario.</div>";
                        exit();
                    }
                }

                // Insertar nuevo registro
                $sql = "INSERT INTO turnos (servicio, profesional, fecha, hora, nombre, telefono, gmail, obra_social) VALUES ('$servicio', '$profesional', '$fecha', '$hora', '$nombre', '$telefono', '$gmail', '$obra_social')";
                if ($conn->query($sql) === TRUE) {
                    // Enviar correo de confirmación
                    $to_email = $gmail;
                    $subject = 'Confirmación de Turno - Santé Centro De Salud';
                    $from_email = 'oligiatielizondo@gmail.com';

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

                    $mail = new PHPMailer(true);
                    try {
                        // Configuración del servidor
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = $from_email;
                        $mail->Password = 'ostc ewyt kjhy firp';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Configuración del correo
                        $mail->setFrom($from_email, 'Santé Centro De Salud');
                        $mail->addAddress($to_email);
                        $mail->Subject = $subject;
                        $mail->Body = $mensaje_email;
                        $mail->isHTML(true);
                        $mail->CharSet = PHPMailer::CHARSET_UTF8;

                        // Adjuntar imagen
                        $mail->addEmbeddedImage('../img/santeLogo.jpg', 'logo_img');

                        $mail->send();
                        echo "<div class='success-message'>Paciente registrado con éxito.</div>";
                    } catch (Exception $e) {
                        echo "<div class='success-message'>Paciente registrado con éxito, pero el correo no pudo ser enviado. Error: " . $mail->ErrorInfo . "</div>";
                    }
                } else {
                    echo "<div class='error-message'>Error al registrar el paciente: " . $conn->error . "</div>";
                }

                $conn->close();
            }
            ?>
            <form id="agendarPacienteForm" method="POST" action="">
                <button class="btn_horarios"><a href="administrador.php">Volver</a></button>
                <div class="form-group">
                    <label for="servicio">Servicio</label>
                    <select id="servicio" name="servicio" class="form-control" required>
                        <option value="">Seleccione un servicio</option>
                        <option value="Kinesiología">Kinesiología</option>
                        <option value="Terapia Manual - RPG">Terapia Manual - RPG</option>
                        <option value="Drenaje Linfático">Drenaje Linfático</option>
                        <option value="Nutrición">Nutrición</option>
                        <option value="Traumatología">Traumatología</option>
                        <option value="Psicología">Psicología</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="profesional">Profesional</label>
                    <select id="profesional" name="profesional" class="form-control" required>
                        <option value="">Seleccione un profesional</option>
                        <option value="Lucia Foricher">Lucia Foricher</option>
                        <option value="Alejandro Perez">Alejandro Perez</option>
                        <option value="Mauro Robert">Mauro Robert</option>
                        <option value="Gastón Olgiati">Gastón Olgiati</option>
                        <option value="German Fernandez">German Fernandez</option>
                        <option value="Melina Thome">Melina Thome</option>
                        <option value="Hernan Lopez">Hernan Lopez</option>
                        <option value="Leila Heguilein">Leila Heguilein</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha</label>
                    <input type="text" id="fecha" name="fecha" class="form-control fecha" placeholder="Seleccione la fecha" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora</label>
                    <select id="hora" name="hora" class="form-control" required>
                        <option value="">Seleccione una hora</option>
                        <option value="08:00">08:00</option>
                        <option value="09:00">09:00</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="13:00">13:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                        <option value="17:00">17:00</option>
                        <option value="18:00">18:00</option>
                        <option value="19:00">19:00</option>
                        <option value="20:00">20:00</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre completo" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Teléfono" required>
                </div>
                <div class="form-group">
                    <label for="gmail">Gmail</label>
                    <input type="email" id="gmail" name="gmail" class="form-control" placeholder="Gmail" required>
                </div>
                <div class="form-group">
                    <label for="obra_social">Obra Social</label>
                    <input type="text" id="obra_social" name="obra_social" class="form-control" placeholder="Obra Social" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Registrar Paciente</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#fecha', {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                minDate: "today"
            });
        });
    </script>
</body>
</html>