<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    header('Content-Type: application/json');

    // Deshabilitar la visualización de errores
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', 'path/to/error_log.log');

    // Conectar a la base de datos
    $conn = new mysqli('localhost', 'root', '', 'sante');
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $servicio = $data['servicio'];
    $profesional = $data['profesional'];
    $fecha = $data['fecha'];
    $hora = $data['hora'];
    $nombre = $data['nombre'];
    $telefono = $data['telefono'];
    $gmail = $data['gmail'];
    $obra_social = $data['obra_social'];

    // Verificar si ya existe un registro con los mismos datos
    $duplicate_query = $conn->prepare("SELECT COUNT(*) AS count FROM turnos WHERE nombre = ? AND telefono = ? AND profesional = ? AND fecha = ? AND hora = ?");
    if ($duplicate_query === false) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de duplicados: ' . $conn->error]);
        exit();
    }
    $duplicate_query->bind_param("sssss", $nombre, $telefono, $profesional, $fecha, $hora);
    $duplicate_query->execute();
    $duplicate_result = $duplicate_query->get_result();
    if ($duplicate_result === false) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener el resultado de la consulta de duplicados: ' . $duplicate_query->error]);
        exit();
    }
    $duplicate_row = $duplicate_result->fetch_assoc();

    if ($duplicate_row['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'No puedes registrarte dos veces en el mismo horario.']);
    } else {
        // Contar cuántas veces el nombre, el teléfono, el servicio y el profesional han sido registrados
        $count_query = $conn->prepare("SELECT COUNT(*) AS count FROM turnos WHERE nombre = ? AND telefono = ? AND servicio = ? AND profesional = ?");
        if ($count_query === false) {
            echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de conteo: ' . $conn->error]);
            exit();
        }
        $count_query->bind_param("ssss", $nombre, $telefono, $servicio, $profesional);
        $count_query->execute();
        $count_result = $count_query->get_result();
        if ($count_result === false) {
            echo json_encode(['success' => false, 'message' => 'Error al obtener el resultado de la consulta de conteo: ' . $count_query->error]);
            exit();
        }
        $count_row = $count_result->fetch_assoc();
        
        $numero_sesion = $count_row['count'] + 1; // Incrementar el contador para el nuevo registro
        
        $stmt = $conn->prepare("INSERT INTO turnos (servicio, profesional, fecha, hora, nombre, telefono, gmail, obra_social, numero_sesion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de inserción: ' . $conn->error]);
            exit();
        }
        $stmt->bind_param("sssssssss", $servicio, $profesional, $fecha, $hora, $nombre, $telefono, $gmail, $obra_social, $numero_sesion);

        if ($stmt->execute()) {
            // Enviar correo de confirmación
            require 'vendor/autoload.php';

            $to_email = $gmail;
            $subject = 'Confirmación de Turno - Santé Centro De Salud';
            $body = "¡Hola! Tu turno ha sido confirmado.\nDetalles del turno:\nServicio: $servicio\nProfesional: $profesional\nFecha: $fecha\nHora: $hora\nPor favor, recuerda traer ropa deportiva cómoda para una óptima sesión.";
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
                        <p>Por favor, recuerda traer ropa deportiva cómoda para una óptima sesión.</p>
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
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $from_email;
                $mail->Password = 'ostc ewyt kjhy firp';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom($from_email, 'Santé Centro De Salud');
                $mail->addAddress($to_email);
                $mail->Subject = $subject;
                $mail->Body = $mensaje_email;
                $mail->isHTML(true);
                $mail->CharSet = PHPMailer::CHARSET_UTF8;

                $mail->addEmbeddedImage('img/santeLogo.jpg', 'logo_img');

                $mail->send();
                $message = 'El correo ha sido enviado con éxito.';
            } catch (Exception $e) {
                $message = 'El correo no pudo ser enviado. Error: ' . $mail->ErrorInfo;
            }

            echo json_encode(['success' => true, 'message' => 'Paciente registrado con éxito.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar el paciente: ' . $stmt->error]);
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Paciente</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="../bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <link rel="icon" href="../img/santeLogo.jpg">
    <style>
        body {
            background-color: #F6EBD5;
            font-family: 'Poppins', sans-serif;
        }

        .content {
            padding: 20px;
            margin: auto;
            width: 90%;
            max-width: 600px;
        }

        h1, h2 {
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

        .confirmation-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 20px;
            display: none;
        }

        .confirmation-card h2 {
            color: #96B394;
            margin-bottom: 20px;
        }

        .confirmation-card button {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }

        .confirmation-card button:hover {
            background-color: #7d9a7d;
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav_container navbar navbar-dark navbar-expand-lg">
            <div class="container-fluid">
                <div class="logo_container">
                    <img class="logo" src="../img/santeLogo.jpg" alt="Logo">
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <ul class="ul_container navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="agendar_link nav_link nav-link" href="pacientes.php">Volver</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="content">
        <h1>Agendar Paciente</h1>
        <div class="form-container">
            <form id="agendarPacienteForm">
                <div class="form-group">
                    <label for="servicio">Servicio</label>
                    <select id="servicio" name="servicio" class="form-control" required>
                        <option value="">Seleccione un servicio</option>
                        <option value="Kinesiología">Kinesiología</option>
                        <option value="Terapia manual">Terapia manual</option>
                        <option value="Drenaje Linfático">Drenaje Linfático</option>
                        <option value="Nutrición">Nutrición</option>
                        <option value="Traumatología">Traumatología</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="profesional">Profesional</label>
                    <select id="profesional" name="profesional" class="form-control" required>
                        <option value="">Seleccione un profesional</option>
                    </select>
                </div>
                <div class="form-group" style="position: relative; bottom: 70px;">
                    <input type="text" id="fecha" name="fecha" class="form-control fecha" placeholder="Seleccione la fecha" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora</label>
                    <select id="hora" name="hora" class="form-control" required>
                        <option value="">Seleccione una hora</option>
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
                <div id="message" class="error-message"></div>
            </form>
        </div>

        <div class="confirmation-card" id="confirmationCard">
            <h2>Paciente registrado con éxito</h2>
            <button id="agendarOtroPacienteBtn">Agendar otro paciente</button>
            <button id="volverBtn">Volver</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('agendarPacienteForm');
            const servicioSelect = document.getElementById('servicio');
            const profesionalSelect = document.getElementById('profesional');
            const fechaInput = document.getElementById('fecha');
            const horaSelect = document.getElementById('hora');
            const messageDiv = document.getElementById('message');
            const confirmationCard = document.getElementById('confirmationCard');
            const agendarOtroPacienteBtn = document.getElementById('agendarOtroPacienteBtn');
            const volverBtn = document.getElementById('volverBtn');

            const todosLosProfesionales = {
                'Kinesiología': ['Lucia Foricher', 'Gastón Olgiati'],
                'Terapia manual': ['Mauro Robert', 'German Fernandez'],
                'Drenaje Linfático': ['Melina Thome', 'Maria Paz'],
                'Nutrición': ['Alejandro Perez'],
                'Traumatología': ['Hernán López']
            };

            const todasLasHoras = [
                '08:00', '09:00', '10:00', '11:00',
                '13:00', '14:00', '15:00', '16:00',
                '17:00', '18:00', '19:00', '20:00'
            ];

            function updateProfesionales(servicio) {
                profesionalSelect.innerHTML = '<option value="">Seleccione un profesional</option>';
                if (todosLosProfesionales[servicio]) {
                    todosLosProfesionales[servicio].forEach(profesional => {
                        const option = document.createElement('option');
                        option.value = profesional;
                        option.textContent = profesional;
                        profesionalSelect.appendChild(option);
                    });
                }
            }

            function updateHorasDisponibles() {
                horaSelect.innerHTML = '<option value="">Seleccione una hora</option>';
                todasLasHoras.forEach(hora => {
                    const option = document.createElement('option');
                    option.value = hora;
                    option.textContent = hora;
                    horaSelect.appendChild(option);
                });
            }

            servicioSelect.addEventListener('change', function() {
                updateProfesionales(servicioSelect.value);
                updateHorasDisponibles();
            });

            flatpickr(fechaInput, {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                minDate: "today"
            });

            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                // Enviar datos al servidor para registrar el turno
                fetch('agendar_paciente_backend.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        form.style.display = 'none';
                        confirmationCard.style.display = 'block';
                    } else {
                        messageDiv.textContent = result.message;
                        messageDiv.classList.remove('success-message');
                        messageDiv.classList.add('error-message');
                    }
                })
                .catch(error => {
                    messageDiv.textContent = 'Error al registrar el paciente. Por favor, inténtelo de nuevo.';
                    messageDiv.classList.remove('success-message');
                    messageDiv.classList.add('error-message');
                });
            });

            agendarOtroPacienteBtn.addEventListener('click', function() {
                form.style.display = 'block';
                confirmationCard.style.display = 'none';
                form.reset();
                messageDiv.textContent = '';
            });

            volverBtn.addEventListener('click', function() {
                window.location.href = 'pacientes.php';
            });
        });
    </script>
</body>
</html>
