<?php
session_start();

if (!isset($_SESSION['disponibilidadProfesionales'])) {
    die("La disponibilidad de los profesionales no está definida.");
}

$disponibilidadProfesionales = $_SESSION['disponibilidadProfesionales'];

// Conectar a la base de datos
$mysqli = new mysqli("localhost", "root", "", "sante");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Si no se ha seleccionado un profesional, redirigir a profesionales.php
if (!isset($_SESSION['profesional'])) {
    header('Location: profesionales.php');
    exit();
}

// Nombre del profesional
$profesional = $_SESSION['profesional'];

// Verificar disponibilidad del profesional
if (!isset($disponibilidadProfesionales[$profesional])) {
    die("El profesional $profesional no tiene horarios definidos.");
}

// Si el formulario es enviado, guardar fecha y hora en variables de sesión y redirigir a paciente.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    // Guardar fecha y hora en la sesión
    $_SESSION['fecha'] = $fecha;
    $_SESSION['hora'] = $hora;

    // Redirigir a paciente.php
    header('Location: paciente.php');
    exit();
}

// Obtener las horas ocupadas para el profesional en una fecha específica (vía AJAX)
if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];
    $diaSemana = date('l', strtotime($fecha)); // Obtener el día de la semana de la fecha seleccionada
    
    // Verificar si el día de la semana tiene disponibilidad definida
    if (!isset($disponibilidadProfesionales[$profesional][$diaSemana])) {
        die("El profesional $profesional no tiene horarios definidos para el día $diaSemana.");
    }

    $horasDisponibles = $disponibilidadProfesionales[$profesional][$diaSemana];

    // Obtener las horas ocupadas de la base de datos
    if ($_SESSION['servicio'] == 'Kinesiología') {
        $stmt = $mysqli->prepare("SELECT TIME_FORMAT(hora, '%H:%i') as hora, COUNT(*) as count FROM turnos WHERE profesional = ? AND fecha = ? AND servicio = 'Kinesiología' GROUP BY hora HAVING count >= 4");
    } else {
        $stmt = $mysqli->prepare("SELECT TIME_FORMAT(hora, '%H:%i') as hora FROM turnos WHERE profesional = ? AND fecha = ?");
    }
    $stmt->bind_param("ss", $profesional, $fecha);
    $stmt->execute();
    $result = $stmt->get_result();
    $horasOcupadas = [];

    while ($row = $result->fetch_assoc()) {
        $horasOcupadas[] = $row['hora'];
    }

    // Filtrar horarios disponibles
    $horasFinales = array_values(array_diff($horasDisponibles, $horasOcupadas));
    
    echo json_encode($horasFinales);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sante - Horario</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="bootstrap-5.1.3-dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <link rel="icon" href="img/santeLogo.jpg">

    <style>
        .fecha {
            position: relative;
            top: 80px;
        }
        
        .calendar-container {
            max-width: 400px;
            margin: 0px auto;
            position: relative;
            top: 50px;
        }
        .flatpickr-calendar {
            font-size: 16px;
            padding: 10px;
        }
        .time-card {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .time-slot {
            display: inline-block;
            margin: 5px;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            background-color: #f1f1f1;
        }
        .time-slot.disabled {
            background-color: #e0e0e0;
            cursor: not-allowed;
        }
        .time-slot.available:hover {
            background-color: #8A2BE2;
            color: white;
        }
        #modal-content {
            background-color: #fff;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        #confirmarTurno {
            display: none;
            background-color: #8A2BE2;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            bottom: 15px;
        }
        #closeModal {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #45a049;
        }
        .flatpickr-clear {
            display: none;
        }
        .servicio_title {
            text-align: center;
            margin: auto;
            letter-spacing: 9px;
            font-weight: 700;
            position: relative;
            top: 30px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav_container navbar navbar-dark">
            <div class="logo_container container-fluid">
                <img class="logo" src="img/santeLogo.jpg" alt="Logo">
            </div>
        </nav>
    </header>
    <div class="color"></div>

    <h1 class="servicio_title">NUESTRO HORARIO</h1>
    <hr style="position: relative; top: 40px; width: 60%; margin: auto;">
    <div class="calendar-container">
        <form action="fecha.php" method="POST" class="fecha_container" id="fechaForm">
            <input type="text" id="fecha" class="fecha" name="fecha" placeholder="Selecciona la fecha" required><br><br>
            <input type="hidden" id="hora" name="hora">
            <button type="submit" id="confirmarTurno" style="display:none;">Confirmar Fecha y Hora</button>
        </form>
    </div>

    <!-- Modal de selección de hora -->
    <div id="horaModal" class="time-card">
        <div id="modal-content">
            <h2>Selecciona la Hora</h2>
            <div id="horaContenedor"></div><br><br>
            <button type="submit" id="confirmarTurno" form="fechaForm">Confirmar Fecha y Hora</button>
            <br>
            <button id="closeModal">Cerrar</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    // Inicializar el calendario
    flatpickr("#fecha", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        inline: true,
        disable: [
            function(date) {
                const diaSemana = date.toLocaleString("en", { weekday: "long" }); // Obtener el día en inglés
                // Días disponibles para el profesional
                const diasDisponibles = <?= json_encode(array_keys($disponibilidadProfesionales[$profesional])) ?>;
                return !diasDisponibles.includes(diaSemana); // Deshabilitar días no disponibles
            }
        ],
        onChange: function(selectedDates, dateStr, instance) {
            const fecha = dateStr;
            document.getElementById('horaModal').style.display = 'flex';
            
            // Realizar la llamada AJAX para obtener las horas disponibles
            fetch(`fecha.php?fecha=${fecha}`)
            .then(response => response.json())
            .then(data => {
                const horaContenedor = document.getElementById('horaContenedor');
                horaContenedor.innerHTML = ''; // Limpiar el contenedor de horas
                
                if (data.length === 0) {
                    const noDisponibles = document.createElement('div');
                    noDisponibles.textContent = 'No hay horarios disponibles';
                    horaContenedor.appendChild(noDisponibles);
                } else {
                    data.forEach(hora => {
                        const div = document.createElement('div');
                        div.classList.add('time-slot');
                        div.textContent = hora;

                        if (!hora.includes('No disponible')) {
                            div.classList.add('available'); // Si la hora está disponible
                            div.addEventListener('click', function() {
                                document.getElementById('hora').value = hora;
                                document.getElementById('confirmarTurno').style.display = 'inline-block';
                                document.getElementById('horaModal').style.display = 'none'; // Cerrar el modal
                            });
                        } else {
                            div.classList.add('disabled'); // Si la hora está ocupada
                        }

                        horaContenedor.appendChild(div);
                    });
                }
            });
        }
    });

    // Cerrar el modal de selección de hora
    document.getElementById("closeModal").addEventListener('click', function() {
        document.getElementById('horaModal').style.display = 'none';
    });

    </script>
</body>
</html>