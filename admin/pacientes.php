<?php
session_start([
    'cookie_lifetime' => 0, // La sesión se cierra cuando se cierra el navegador
]);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

$profesional = $_SESSION['profesional'];

// Horarios disponibles por profesional en Sante
if (!isset($_SESSION['disponibilidadProfesionales'])) {
    $_SESSION['disponibilidadProfesionales'] = [
        'Lucia Foricher' => [
            'Monday' => ['08:00', '09:00', '10:00', '11:00'],
            'Wednesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Friday' => ['08:00', '09:00', '10:00', '11:00'],
        ],
        'Mauro Robert' => [
            'Monday' => ['13:00', '14:00', '15:00', '16:00'],
            'Tuesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Wednesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Thursday' => ['13:00', '14:00', '15:00', '16:00'],
            'Friday' => ['13:00', '14:00', '15:00', '16:00']
        ],
        'German Fernandez' => [
            'Monday' => ['17:30', '18:30', '19:30'],
            'Tuesday' => ['17:30', '18:30', '19:30'],
            'Wednesday' => ['17:30', '18:30', '19:30'],
            'Thursday' => ['17:30', '18:30', '19:30'],
            'Friday' => ['17:30', '18:30', '19:30']
        ],
        'Gastón Olgiati' => [
            'Monday' => ['13:00', '14:00', '15:00', '16:00'],
            'Wednesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Friday' => ['13:00', '14:00', '15:00', '16:00']
        ],
        'Hernán López' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Alejandro Perez' => [
            'Monday' => ['08:00', '09:00', '10:00', '11:00'],
            'Wednesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Friday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Melina Thome' => [
            'Monday' => ['17:00', '18:00', '19:00'],
            'Wednesday' => ['17:00', '18:00', '19:00'],
            'Friday' => ['17:00', '18:00', '19:00']
        ],
        'Maria Paz' => [
            'Wednesday' => ['17:00', '18:00', '19:00'],
            'Saturday' => ['12:00']
        ],
        'Miriam' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Florencia' => [
            'Monday' => ['17:00', '18:00'],
            'Tuesday' => ['17:00', '18:00'],
            'Thursday' => ['17:00']
        ],
        'Constanza' => [
            'Monday' => ['15:00'],
            'Tuesday' => ['16:00', '17:00'],
            'Thursday' => ['13:00', '14:00', '15:00'],
            'Friday' => ['15:00', '16:00']
        ],
        'Mariana' => [
            'Thursday' => ['09:30', '10:30'],
            'Friday' => ['08:30', '09:30', '10:30']
        ]
    ];
}

$disponibilidadProfesionales = $_SESSION['disponibilidadProfesionales'];

// Función para deshabilitar horarios solo para la semana actual
function deshabilitarHorarios(&$disponibilidad, $profesional) {
    $diasSemana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $inicioSemana = new DateTime();
    $inicioSemana->modify('this week'); // Obtén el inicio de la semana actual
    $finSemana = clone $inicioSemana;
    $finSemana->modify('+6 days'); // Obtén el final de la semana actual
    
    foreach ($diasSemana as $dia) {
        $fechaDia = clone $inicioSemana;
        $fechaDia->modify($dia);
        if ($fechaDia >= $inicioSemana && $fechaDia <= $finSemana) {
            if (isset($disponibilidad[$profesional][$dia])) {
                foreach ($disponibilidad[$profesional][$dia] as &$hora) {
                    $hora = "No disponible"; // Marcamos la hora como no disponible
                }
            }
        }
    }
}

// Función para habilitar horarios
function habilitarHorarios(&$disponibilidad, $profesional) {
    $originalDisponibilidad = [
        'Lucia Foricher' => [
            'Monday' => ['08:00', '09:00', '10:00', '11:00'],
            'Wednesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Friday' => ['08:00', '09:00', '10:00', '11:00'],
        ],
        'Mauro Robert' => [
            'Monday' => ['13:00', '14:00', '15:00', '16:00'],
            'Tuesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Wednesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Thursday' => ['13:00', '14:00', '15:00', '16:00'],
            'Friday' => ['13:00', '14:00', '15:00', '16:00']
        ],
        'German Fernandez' => [
            'Monday' => ['17:30', '18:30', '19:30'],
            'Tuesday' => ['17:30', '18:30', '19:30'],
            'Wednesday' => ['17:30', '18:30', '19:30'],
            'Thursday' => ['17:30', '18:30', '19:30'],
            'Friday' => ['17:30', '18:30', '19:30']
        ],
        'Gastón Olgiati' => [
            'Monday' => ['13:00', '14:00', '15:00', '16:00'],
            'Wednesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Friday' => ['13:00', '14:00', '15:00', '16:00']
        ],
        'Hernán López' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Alejandro Perez' => [
            'Monday' => ['08:00', '09:00', '10:00', '11:00'],
            'Wednesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Friday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Melina Thome' => [
            'Monday' => ['17:00', '18:00', '19:00'],
            'Wednesday' => ['17:00', '18:00', '19:00'],
            'Friday' => ['17:00', '18:00', '19:00']
        ],
        'Maria Paz' => [
            'Wednesday' => ['17:00', '18:00', '19:00'],
            'Saturday' => ['12:00']
        ],
        'Miriam' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Florencia' => [
            'Monday' => ['17:00', '18:00'],
            'Tuesday' => ['17:00', '18:00'],
            'Thursday' => ['17:00']
        ],
        'Constanza' => [
            'Monday' => ['15:00'],
            'Tuesday' => ['16:00', '17:00'],
            'Thursday' => ['13:00', '14:00', '15:00'],
            'Friday' => ['15:00', '16:00']
        ],
        'Mariana' => [
            'Thursday' => ['09:30', '10:30'],
            'Friday' => ['08:30', '09:30', '10:30']
        ]
    ];

    $disponibilidad[$profesional] = $originalDisponibilidad[$profesional];
}

// Manejar las solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deshabilitar'])) {
        deshabilitarHorarios($disponibilidadProfesionales, $profesional);
        $_SESSION['disponibilidadProfesionales'] = $disponibilidadProfesionales;
        echo "<script>alert('Horarios deshabilitados.'); window.location.href='pacientes.php';</script>";
    } elseif (isset($_POST['habilitar'])) {
        habilitarHorarios($disponibilidadProfesionales, $profesional);
        $_SESSION['disponibilidadProfesionales'] = $disponibilidadProfesionales;
        echo "<script>alert('Horarios habilitados.'); window.location.href='pacientes.php';</script>";
    }
}

// Verificar si se ha presionado el botón para ver pacientes anteriores o siguientes
$ver_anteriores = isset($_GET['ver_anteriores']) && $_GET['ver_anteriores'] == '1';
$ver_siguientes = isset($_GET['ver_siguientes']) && $_GET['ver_siguientes'] == '1';

// Variables para búsqueda
$busqueda_nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$busqueda_obra_social = isset($_GET['obra_social']) ? $_GET['obra_social'] : '';
$profesional = $_SESSION['profesional'];

// Inicializar la consulta para obtener pacientes
$conn = new mysqli('localhost', 'root', '', 'sante'); // Definir la conexión a la base de datos
$sql = "SELECT * FROM turnos WHERE profesional = ?";
$params = [$profesional];
$param_types = 's';

// Agregar filtros de búsqueda por nombre y obra social
if ($busqueda_nombre != '') {
    $sql .= " AND nombre LIKE ?";
    $params[] = "%$busqueda_nombre%";
    $param_types .= 's';
}

if ($busqueda_obra_social != '') {
    $sql .= " AND obra_social LIKE ?";
    $params[] = "%$busqueda_obra_social%";
    $param_types .= 's';
}

// Filtrar por el mes actual, meses anteriores o el mes siguiente
if ($ver_anteriores) {
    $sql .= " AND (MONTH(fecha) < MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())) 
              OR (YEAR(fecha) < YEAR(CURDATE()))";
} elseif ($ver_siguientes) {
    $sql .= " AND (MONTH(fecha) > MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())) 
              OR (YEAR(fecha) > YEAR(CURDATE()))";
} else {
    $sql .= " AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
}

$sql .= " ORDER BY fecha";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$pacientes = $stmt->get_result();

// Verificar si la consulta fue exitosa
if ($pacientes === false) {
    echo "Error al obtener los pacientes: " . $conn->error;
    exit();
}

// Mapear días de la semana
$dias_semana = [
    0 => 'Domingo',
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
];

// Agrupar pacientes por día de la semana
$pacientes_por_dia = [];
while ($row = $pacientes->fetch_assoc()) {
    $dia_semana = date('N', strtotime($row['fecha'])) % 7; // 0 (para domingo) a 6 (para sábado)
    if (!isset($pacientes_por_dia[$dia_semana])) {
        $pacientes_por_dia[$dia_semana] = [];
    }
    $pacientes_por_dia[$dia_semana][] = $row;
}

// Colores por servicio
$colores_servicio = [
    'Kinesiología' => '#E2C6C2',
    'Terapia manual' => '#A6DA9C',
    'Drenaje Linfático' => '#BBFFFF',
    'Nutrición' => '#EE976A',
    'Traumatología' => '#A9B0F4'
];



$profesional = ucwords(strtolower($_SESSION['profesional']));

// Horarios disponibles por profesional en Sante
if (!isset($_SESSION['horasProfesionales'])) {
    $_SESSION['horasProfesionales'] = [
        'Lucia Foricher' => [
            'lunes' => ['08:00', '09:00', '10:00', '11:00'],
            'miércoles' => ['08:00', '09:00', '10:00', '11:00'],
            'viernes' => ['08:00', '09:00', '10:00', '11:00'],
        ],
        'Mauro Robert' => [
            'lunes' => ['13:00', '14:00', '15:00', '16:00'],
            'martes' => ['13:00', '14:00', '15:00', '16:00'],
            'miércoles' => ['13:00', '14:00', '15:00', '16:00'],
            'jueves' => ['13:00', '14:00', '15:00', '16:00'],
            'viernes' => ['13:00', '14:00', '15:00', '16:00']
        ],
        'German Fernandez' => [
            'lunes' => ['17:30', '18:30', '19:30'],
            'martes' => ['17:30', '18:30', '19:30'],
            'miércoles' => ['17:30', '18:30', '19:30'],
            'jueves' => ['17:30', '18:30', '19:30'],
            'viernes' => ['17:30', '18:30', '19:30']
        ],
        'Gastón Olgiati' => [
            'lunes' => ['13:00', '14:00', '15:00', '16:00'],
            'miércoles' => ['13:00', '14:00', '15:00', '16:00'],
            'viernes' => ['13:00', '14:00', '15:00', '16:00']
        ],
        'Hernán López' => [
            'martes' => ['08:00', '09:00', '10:00', '11:00'],
            'jueves' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Alejandro Perez' => [
            'lunes' => ['08:00', '09:00', '10:00', '11:00'],
            'miércoles' => ['08:00', '09:00', '10:00', '11:00'],
            'viernes' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Melina Thome' => [
            'lunes' => ['17:00', '18:00', '19:00'],
            'miércoles' => ['17:00', '18:00', '19:00'],
            'viernes' => ['17:00', '18:00', '19:00']
        ],
        'Maria Paz' => [
            'miércoles' => ['17:00', '18:00', '19:00'],
            'sábado' => ['12:00']
        ],
        'Miriam' => [
            'martes' => ['08:00', '09:00', '10:00', '11:00'],
            'jueves' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Florencia' => [
            'lunes' => ['17:00', '18:00'],
            'martes' => ['17:00', '18:00'],
            'jueves' => ['17:00']
        ],
        'Constanza' => [
            'lunes' => ['15:00'],
            'martes' => ['16:00', '17:00'],
            'jueves' => ['13:00', '14:00', '15:00'],
            'viernes' => ['15:00', '16:00']
        ],
        'Mariana' => [
            'jueves' => ['09:30', '10:30'],
            'viernes' => ['08:30', '09:30', '10:30']
        ]
    ];
}

$horasProfesionales = $_SESSION['horasProfesionales'];

// Función para obtener pacientes por hora y día
function obtenerPacientesPorHora($conn, $profesional, $hora, $dia) {
    $sql = "SELECT * FROM turnos WHERE profesional = ? AND hora = ? AND DAYOFWEEK(fecha) = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conn->error);
    }
    $stmt->bind_param('ssi', $profesional, $hora, $dia);
    $stmt->execute();
    return $stmt->get_result();
}

// Inicializar la consulta para obtener pacientes
$conn = new mysqli('localhost', 'root', '', 'sante'); // Definir la conexión a la base de datos
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$pacientesPorHora = [];
if (isset($horasProfesionales[$profesional])) {
    foreach ($horasProfesionales[$profesional] as $dia => $horas) {
        foreach ($horas as $hora) {
            $dia_num = array_search($dia, ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado']) + 1;
            $pacientesPorHora[$hora][$dia] = obtenerPacientesPorHora($conn, $profesional, $hora, $dia_num)->fetch_all(MYSQLI_ASSOC);
        }
    }
} else {
    // Manejar el caso donde el profesional no tiene horarios definidos
    $pacientesPorHora = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="../bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <link rel="icon" href="../img/santeLogo.jpg">
    <title>Sante - Pacientes</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
            box-sizing: border-box;
        }

        h1, h2 {
            color:#96B394;
            text-align: center;
        }

        .content {
            padding: 20px;
            margin: auto;
            width: 90%;
            max-width: 1200px;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #F6EBD5;
            color: #333;
        }

        .patient-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
            margin: 5px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-block;
            width: 140px;
            border-radius: 10px;
        }

        .patient-card p {
            margin: 0px 0;
        }

        .patient-card:hover {
            background-color: #e0e0e0;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .button-container form {
            flex-grow: 1;
            text-align: center;
        }

        .button-container button {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }

        .button-container button:hover {
            background-color: #96B394;
        }

        .btn_horarios button:hover {
            color: #ddd;
        }

        .navbar-collapse {
            justify-content: flex-end; /* Asegura que los enlaces se alineen a la derecha */
        }

        .navbar-nav {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 15px;
        }

        .agendar_link {
            background-color: #96B394;
            color: white !important;
            border: none;
            padding: 8px;
            cursor: pointer;
            border-radius: 10px !important;
        }

        .btn_horarios button {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 8px;
            cursor: pointer;
            border-radius: 10px !important;
        }

        .btn_horarios button:hover {
            background-color: rgb(113, 139, 111);
        }

        /* Asegurar que el navbar siempre se muestre en pantallas grandes */
        @media (max-width: 992px) {
            .ul_container {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                align-items: flex-start; /* Alinea los elementos a la derecha */
                margin-top: 10px; /* Agrega un pequeño espacio debajo del navbar */
            }

            .btn_horarios button {
                background-color: #F6EBD5;
                color: white;
                border: none;
                padding: 8px;
                cursor: pointer;
                width: 200px;
                margin: 0px;
                border-radius: 10px !important;
            }

            .agendar_link {
                position: relative;
                background-color: #F6EBD5;
                width: 200px;
                padding: 8px;
                border-radius: 10px !important;
                color: #fff !important;
                text-align: center;
            }
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
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" 
            aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <ul class="ul_container navbar-nav ms-auto"> <!-- ms-auto empuja los elementos a la derecha -->
                <li class="nav-item">
                    <a class="agendar_link nav_link nav-link" href="../index.php">Agendar Paciente</a>
                </li>
                <li class="nav-item">
                    <form method="POST" class="btn_horarios" action="pacientes.php">
                        <input type="hidden" name="deshabilitar" value="1">
                        <button type="submit">Deshabilitar Horarios</button>
                    </form>                       
                </li>
                <li class="nav-item">
                    <form method="POST" class="btn_horarios" action="pacientes.php">
                        <input type="hidden" name="habilitar" value="1">
                        <button type="submit">Habilitar Horarios</button>
                    </form>                      
                </li>
            </ul>
        </div>
    </div>
</nav>
</header>

<div class="content">
    <h1 style="font-weight: 600; letter-spacing: 10px;">Bienvenido <?php echo $profesional ?></h1>
    <hr>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Lunes</th>
                    <th>Martes</th>
                    <th>Miércoles</th>
                    <th>Jueves</th>
                    <th>Viernes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Obtener todas las horas únicas
                $horasUnicas = [];
                foreach ($pacientesPorHora as $hora => $dias) {
                    if (!in_array($hora, $horasUnicas)) {
                        $horasUnicas[] = $hora;
                    }
                }
                // Ordenar las horas
                sort($horasUnicas);
                
                // Mostrar las horas y los pacientes por día
                foreach ($horasUnicas as $hora):
                ?>
                    <tr>
                        <td><?php echo $hora; ?></td>
                        <?php
                        $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
                        foreach ($diasSemana as $dia):
                        ?>
                        <td>
                            <?php
                            if (isset($pacientesPorHora[$hora][$dia])):
                                foreach ($pacientesPorHora[$hora][$dia] as $paciente):
                                    // Obtener el servicio del paciente
                                    $servicio = $paciente['servicio']; // Asumimos que cada paciente tiene un campo 'servicio'
                                    // Verificar si el servicio tiene un color asignado en el array $colores_servicio
                                    $color_fondo = isset($colores_servicio[$servicio]) ? $colores_servicio[$servicio] : '#FFFFFF'; // Color por defecto si no se encuentra el servicio
                                    ?>
                                    <div class="patient-card" style="background-color: <?php echo $color_fondo; ?>;" onclick="location.href='diagnostico.php?id=<?php echo $paciente['id']; ?>'">
                                        <p><strong><?php echo $paciente['nombre']; ?></strong></p>
                                        <p><?php echo $paciente['numero_sesion']; ?></p>
                                        <p><?php echo $paciente['fecha']; ?></p>
                                    </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <p>-</p>
                            <?php endif; ?>
                        </td>

                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>