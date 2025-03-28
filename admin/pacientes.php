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
            'Friday' => ['08:00', '09:00', '10:00', '11:00']
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
        'Miriam Rossello' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Florencia Goñi' => [
            'Monday' => ['17:00', '18:00'],
            'Tuesday' => ['17:00', '18:00'],
            'Thursday' => ['17:00']
        ],
        'Constanza Marinello' => [
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
                    $hora = "No disponible"; // Marca la hora como no disponible
                }
            }
        }
    }
}

function habilitarHorarios(&$disponibilidad, $profesional) {
    $originalDisponibilidad = [
        'Lucia Foricher' => [
            'Monday' => ['08:00', '09:00', '10:00', '11:00'],
            'Wednesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Friday' => ['08:00', '09:00', '10:00', '11:00']
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
        'Miriam Rossello' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Florencia Goñi' => [
            'Monday' => ['17:00', '18:00'],
            'Tuesday' => ['17:00', '18:00'],
            'Thursday' => ['17:00']
        ],
        'Constanza Marinello' => [
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

// Inicializar la conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'sante');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
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
    } elseif (isset($_POST['asistencia_id'])) {
        $asistio = isset($_POST['asistio']) ? 1 : 0;
        $asistencia_id = $_POST['asistencia_id'];

        $sql_update = "UPDATE turnos SET asistio = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param('ii', $asistio, $asistencia_id);

        if ($stmt->execute()) {
            echo "<script>alert('Estado de asistencia actualizado correctamente.'); window.location.href = 'pacientes.php';</script>";
        } else {
            echo "Error al actualizar el estado de asistencia: " . $conn->error;
        }
    }
}

// Verificar si se ha presionado el botón para ver pacientes de un mes anterior
$mes_actual = date('m');
$anio_actual = date('Y');
$mes = isset($_GET['mes']) ? $_GET['mes'] : $mes_actual;
$anio = isset($_GET['anio']) ? $_GET['anio'] : $anio_actual;

// Variables para búsqueda
$busqueda_nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$busqueda_telefono = isset($_GET['telefono']) ? $_GET['telefono'] : '';
$profesional = $_SESSION['profesional'];

// Construir la consulta SQL con los filtros de búsqueda
$sql = "SELECT * FROM turnos WHERE profesional = ?";
$params = [$profesional];
$param_types = 's';

if (!empty($busqueda_nombre)) {
    $sql .= " AND nombre LIKE ?";
    $params[] = '%' . $busqueda_nombre . '%';
    $param_types .= 's';
}

if (!empty($busqueda_telefono)) {
    $sql .= " AND telefono LIKE ?";
    $params[] = '%' . $busqueda_telefono . '%';
    $param_types .= 's';
}

$sql .= " AND MONTH(fecha) = ? AND YEAR(fecha) = ?";
$params[] = $mes;
$params[] = $anio;
$param_types .= 'ii';

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

// Verificar los resultados obtenidos
$pacientes_array = $pacientes->fetch_all(MYSQLI_ASSOC);

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

// Agrupar pacientes por día de la semana y hora
$pacientes_por_dia_y_hora = [];
foreach ($pacientes_array as $row) {
    $dia_semana = date('N', strtotime($row['fecha'])) % 7; // 0 (para domingo) a 6 (para sábado)
    $hora = date('H:i', strtotime($row['hora'])); // Formato HH:MM
    if (!isset($pacientes_por_dia_y_hora[$dia_semana])) {
        $pacientes_por_dia_y_hora[$dia_semana] = [];
    }
    if (!isset($pacientes_por_dia_y_hora[$dia_semana][$hora])) {
        $pacientes_por_dia_y_hora[$dia_semana][$hora] = [];
    }
    $pacientes_por_dia_y_hora[$dia_semana][$hora][] = $row;
}

// Colores por servicio
$colores_servicio = [
    'Kinesiología' => '#E2C6C2',
    'Terapia manual' => '#A6DA9C',
    'Drenaje Linfático' => '#BBFFFF',
    'Nutrición' => '#EE976A',
    'Traumatología' => '#A9B0F4'
];

function obtenerMesAnterior($mes, $anio) {
    if ($mes == 1) {
        return [12, $anio - 1];
    }
    return [$mes - 1, $anio];
}

function obtenerMesSiguiente($mes, $anio) {
    if ($mes == 12) {
        return [1, $anio + 1];
    }
    return [$mes + 1, $anio];
}

list($mes_anterior, $anio_anterior) = obtenerMesAnterior($mes, $anio);
list($mes_siguiente, $anio_siguiente) = obtenerMesSiguiente($mes, $anio);
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

        h1,
        h2 {
            color: #96B394;
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

        th,
        td {
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
            width: 130px;
            border-radius: 10px;
        }

        .patient-card div {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            margin: auto;
        }

        .patient-card div p {
            margin: 0;
            flex: 1;
            width: 100%;
            text-align: center;
            margin: auto;
        }

        .patient-card:hover {
            background-color: #e0e0e0;
        }

        .button-container {
            display: flex;
            justify-content: start;
            align-items: start;
            margin-top: 20px;
            width: 50%;
        }

        .button-container form {
            flex-grow: 1;
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
            background-color: #7d9a7d;
        }

        .navbar-collapse {
            justify-content: flex-end;
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
            position: relative;
            top: 8px;
        }

        .btn_horarios button:hover {
            background-color: rgb(113, 139, 111);
        }

        @media (max-width: 992px) {
            .ul_container {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                align-items: flex-start;
                margin-top: 10px;
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

            .search-container button {
                background-color: #96B394;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                position: relative;
                left: 0px;
                top: 10px;
                width: 100px;
                cursor: pointer;
            }
        }

        .search-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            width: 100%;
            flex-direction: column;
        }

        .search-container input {
            width: 45%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .search-container button {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            position: relative;
            left: 5px;
            width: 100px;
            cursor: pointer;
        }

        .search-container button:hover {
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
        <h1 style="font-weight: 600; letter-spacing: 10px;">¡Hola de nuevo, <?php echo htmlspecialchars($profesional); ?>!</h1>
        <hr>

        <div class="button-container" style="position: relative; bottom: 0px;">
            <form method="GET" action="pacientes.php">
                <input type="hidden" name="mes" value="<?php echo $mes_anterior; ?>">
                <input type="hidden" name="anio" value="<?php echo $anio_anterior; ?>">
                <button type="submit">Mes Anterior</button>
            </form>
            <form method="GET" action="pacientes.php">
                <input type="hidden" name="mes" value="<?php echo $mes_actual; ?>">
                <input type="hidden" name="anio" value="<?php echo $anio_actual; ?>">
                <button type="submit">Mes Actual</button>
            </form>
            <form method="GET" action="pacientes.php">
                <input type="hidden" name="mes" value="<?php echo $mes_siguiente; ?>">
                <input type="hidden" name="anio" value="<?php echo $anio_siguiente; ?>">
                <button type="submit">Mes Siguiente</button>
            </form>
        </div>

        <div class="search-container">
            <form method="GET" action="pacientes.php">
                <input type="text" name="nombre" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($busqueda_nombre); ?>">
                <input type="text" name="telefono" placeholder="Buscar por teléfono" value="<?php echo htmlspecialchars($busqueda_telefono); ?>">
                <input type="hidden" name="mes" value="<?php echo $mes; ?>">
                <input type="hidden" name="anio" value="<?php echo $anio; ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

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
                    // Obtener todas las horas únicas incluyendo las de disponibilidad
                    $horasUnicas = [];
                    if (isset($disponibilidadProfesionales[$profesional])) {
                        foreach ($disponibilidadProfesionales[$profesional] as $dia => $horas) {
                            foreach ($horas as $hora) {
                                if (!in_array($hora, $horasUnicas)) {
                                    $horasUnicas[] = $hora;
                                }
                            }
                        }
                    }
                    foreach ($pacientes_por_dia_y_hora as $dia_num => $horas) {
                        foreach ($horas as $hora => $pacientes) {
                            if (!in_array($hora, $horasUnicas)) {
                                $horasUnicas[] = $hora;
                            }
                        }
                    }
                    // Ordenar las horas
                    sort($horasUnicas);

                    // Mostrar las horas y los pacientes por día
                    foreach ($horasUnicas as $hora) :
                    ?>
                        <tr>
                            <td><?php echo $hora; ?></td>
                            <?php
                            $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
                            foreach ($diasSemana as $dia) :
                                // Convertir el nombre del día a número de la semana
                                $dia_num = array_search($dia, array_map('strtolower', array_slice($dias_semana, 1, 5))) + 1;
                            ?>
                                <td>
                                    <?php
                                    if (isset($pacientes_por_dia_y_hora[$dia_num][$hora])) :
                                        foreach ($pacientes_por_dia_y_hora[$dia_num][$hora] as $paciente) :
                                            // Obtener el servicio del paciente
                                            $servicio = $paciente['servicio']; // Asumimos que cada paciente tiene un campo 'servicio'
                                            // Verificar si el servicio tiene un color asignado en el array $colores_servicio
                                            $color_fondo = isset($colores_servicio[$servicio]) ? $colores_servicio[$servicio] : '#FFFFFF'; // Color por defecto si no se encuentra el servicio
                                    ?>
                                            <div class="patient-card" style="background-color: <?php echo $color_fondo; ?>;">
                                                <div>
                                                    <p style="margin: 0; width: 0px; padding: 0px;"><?php echo htmlspecialchars($paciente['numero_sesion']); ?></p>
                                                    <p><strong style="text-align: center; margin-right: 17px;"><?php echo htmlspecialchars($paciente['nombre']); ?></strong></p>
                                                    <form method="POST" action="pacientes.php?id=<?php echo $paciente['id']; ?>">
                                                        <input type="hidden" name="asistencia_id" value="<?php echo $paciente['id']; ?>">
                                                        <input type="checkbox" name="asistio" <?php echo $paciente['asistio'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                    </form>
                                                </div>
                                                <div>
                                                    <p><?php echo htmlspecialchars($paciente['fecha']); ?></p>
                                                </div>
                                            </div>
                                        <?php
                                        endforeach;
                                    else :
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
