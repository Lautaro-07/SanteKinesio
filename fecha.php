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

$imagenes_profesionales = [
    'Florencia Goñi' => 'img/florencia.jpg',
    'Constanza Marinello' => 'img/constanza.jpg',     
    'Maria Paz' => 'img/maria.jpg',
    'Lucia Foricher' => 'img/lucia.jpg',
    'Mariana' => 'img/mariana.jpg',
    'Miriam Rossello' => 'img/miriam.jpg',
    'Mauro Robert' => 'img/mauro.jpg',
    'German Fernandez' => 'img/german.jpg',
    'Gastón Olgiati' => 'img/gastonO.jpg',
    'Melina Thome' => 'img/melina.jpg',
    'Hernán López' => 'img/hernan.jpg',
    'Alejandro Perez' => 'img/alejandro.jpg',
];

$servicio = isset($_SESSION['servicio']) ? $_SESSION['servicio'] : 'Servicio no definido';

$profesionalesDescripciones = [
    'Hernan Lopez' => [
        'nombre' => 'Hernan Lopez',
        'imagen' => '../img/hernan.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Área de abordaje: traumatología y neurorehabilitación en adultos mayores.'
        ]
    ],
    'Maria Paz' => [
        'nombre' => 'Maria Paz',
        'imagen' => '../img/maria.jpg',
        'descripciones' => [
            'Lic. Nutrición',
            'Área de abordaje: traumatología y neurorehabilitación en adultos mayores.'
        ]
    ],
    'Alejandro Perez' => [
        'nombre' => 'Alejandro Perez Etcheber',
        'imagen' => '../img/alejandro.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Curso de abordaje integral del adulto mayor',
            'Curso de kinefilaxia, flexibilidad y movilidad.'
        ]
    ],
    'Melina Thome' => [
        'nombre' => 'Melina Thome',
        'imagen' => '../img/melina.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Área de abordaje: rehabilitación traumatológica de adultos y adultos mayores.'
        ]
    ],
    'Mauro Robert' => [
        'nombre' => 'Dr. Mauro Robert',
        'imagen' => '../img/mauro.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Rehabilitación traumatológica deportiva',
            'Método Busquet en formación',
            'MEP sports',
            'Tapping neuromuscular',
            'Punción seca'
        ]
    ],
    'Gastón Olgiati' => [
        'nombre' => 'Gastón Olgiati',
        'imagen' => 'img/gastonO.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Método Busquet en formación',
            'Tapping neuromuscular',
            'MEP Sports',
            'Vendaje deportivo',
            'Reprogramación propioceptiva'
        ]
    ],
    'German Fernandez' => [
        'nombre' => 'Dr. German Fernandez',
        'imagen' => '../img/german.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Orientación: rehabilitación traumatológica y deportiva, atención de adultos mayores',
            'Cursos:',
            'Abordaje integral del adulto mayor - AAK',
            'Gimnasia postural',
            'Reprogramación propioceptiva Busquet',
            'Actualización en tendinopatías'
        ]
    ],
    'Mariana Ilari' => [
        'nombre' => 'Dra. Mariana Ilari',
        'imagen' => '../img/mariana.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Método Busquet, reprogramación propioceptiva',
            'Rehabilitación traumatológica y postural',
            'Concepto Mulligan',
            'TMR'
        ]
    ],
    'Lucia Foricher' => [
        'nombre' => 'Lucia Foricher Castellon',
        'imagen' => '../img/lucia.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP (2018)',
            'Formada en:',
            'TMR-Técnicas Metaméricas Reflejas, formación completa',
            'Método Busquet, formación completa y reprogramación propioceptiva',
            'Métodos globales de corrección postural',
            'Mulligan Concept',
            'Punción seca',
            'Drenaje linfático manual, método Leduc'
        ]
    ],
    'Constanza Marinello' => [
        'nombre' => 'Constanza Marinello',
        'imagen' => '../img/constanza.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Posgrado en kinesiología dermatofuncional y estética',
            'Curso superior de Flebología y Linfología, Método Leduc'
        ]
    ],
    'Florencia Goñi' => [
        'nombre' => 'Florencia Goñi',
        'imagen' => '../img/florencia.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Posgrado en kinesiología dermatofuncional y estética',
            'Curso superior de Flebología y Linfología, Método Leduc'
        ]
    ]
];

$descripcionCorta = isset($profesionalesDescripciones[$profesional]['descripciones'][0]) ? $profesionalesDescripciones[$profesional]['descripciones'][0] : 'Descripción no disponible';

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
        body{
            background-color: rgb(246, 241, 238) !important;
        }

        .calendar-container {
            width: 00px;
            margin:  auto;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            top: 20px;

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
            background-color: #9DBC98;
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
            background-color: #9DBC98;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            position: relative;
            top: 10px;
            margin: auto;
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
            background-color: #fff;
            max-width: 600px ;
            padding: 10px;
            border: 2px solid #9DBC98;
            border-radius: 50px;
        }

        .profesionalesIMG{
            width: 200px;
            height: 85vh;
            background-color: rgba(99, 136, 137, 1);
            position: absolute;
            top: 10;
            left: 0 ;
            border-radius: 16px;
        }

        .profesional_img {
            width: 200px;
            height: 300px;
            border-radius: 0%;
            overflow: hidden;
            margin-bottom: 10px;
            margin: auto;
            position: relative;
            top: 40px;
            padding: 10px;
        }

        .profesional_img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profesional-info h2 {
            font-size: 25px;
            text-align: center;
            margin-top: 5px;
            color: white;
            position: relative;
            top: 50px;
            font-weight: 600;
        }

        .profesional-info p {
            font-size: 17px;
            text-align: center;
            margin-top: 5px;
            color:rgb(190, 190, 190);
            position: relative;
            top: 50px;
        }

        .btn-vermas{
            z-index: 300;
            position: relative;
            top: 50px;
            margin: 0 auto;
            text-align: center;
            background-color: #9DBC98;
            height: 40px;
            width: 100px;
            padding: 10px;
            text-decoration: none;
            color: #fff;
            position: absolute;
            top: 90%;
            left: 23%;
            border-radius: 10px;
        }

        .btn-vermas:hover{
            color: #fff;
            background-color:rgb(131, 165, 126);
            transition: .5s;
        }

        @media (max-width: 768px) {
    .calendar-container {
        width: 100%;
        padding: 10px;
    }

    .profesionalesIMG {
        width: 90%;
        height: 300px;
        position: relative;
        border-radius: 10px;
        margin: auto;
        top: 10px;
    }

    .profesional_img {
        width: 180px;
        height: 180px;
        position: relative;
        bottom: 0px;
    }

    .profesional_img img{
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 100%;
        position: relative;
        bottom: 0px;
    }

    .profesional-info h2,
    .profesional-info p {
        text-align: center;
        font-size: 18px;
        position: relative;
        top: 40px;
    }

    .servicio_title {
        max-width: 100%;
        font-size: 20px;
        letter-spacing: 4px;
        padding: 8px;
    }

    .time-card {
        width: 100%;
        height: 100vh;
        justify-content: center;
        align-items: center;
    }

    #modal-content {
        width: 90%;
        max-width: 350px;
    }

    #confirmarTurno {
        width: 100%;
        padding: 12px;
        position: relative;
        top: 0;
    }
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

    <div class="container-profesional">
       <div class="profesionalesIMG">
       <div class="profesional-info">
            <div class="profesional_img">
            <img src="<?= $imagenes_profesionales[$profesional] ?>" alt="<?= $profesional ?>">
            </div>
            <h2><?= $profesionalesDescripciones[$profesional]['nombre'] ?></h2>
            <p><?php echo $descripcionCorta; ?></p>
            <a href="pages/detalle.php?nombre=<?= urlencode($profesional) ?>" class="btn-vermas">Ver Más</a> <!-- Botón Ver Más -->
        </div>
    </div>

    <h1 class="servicio_title"><?= $servicio ?></h1>

    <section>
    <div class="calendar-container">
        <form action="fecha.php" method="POST" class="fecha_container" id="fechaForm">
            <input type="text" id="fecha" class="fecha" name="fecha" placeholder="Selecciona la fecha" required><br><br>
            <input type="hidden" id="hora" name="hora">
            <button type="submit" id="confirmarTurno" style="display:none;">Confirmar Fecha y Hora</button>
        </form>
    </div>
    </section>
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
