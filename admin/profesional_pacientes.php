<?php
session_start([
    'cookie_lifetime' => 0,
]);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_POST['profesional']) && !isset($_GET['profesional'])) {
    echo "Profesional no especificado.";
    exit();
}

$profesional = isset($_POST['profesional']) ? $_POST['profesional'] : $_GET['profesional'];

$conn = new mysqli('localhost', 'root', '', 'sante');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener la semana seleccionada
$semana = isset($_GET['semana']) ? $_GET['semana'] : 'actual';
switch ($semana) {
    case 'anterior':
        $inicio_semana = (new DateTime())->modify('last week')->format('Y-m-d');
        $fin_semana = (new DateTime())->modify('last week +6 days')->format('Y-m-d');
        break;
    case 'siguiente':
        $inicio_semana = (new DateTime())->modify('next week')->format('Y-m-d');
        $fin_semana = (new DateTime())->modify('next week +6 days')->format('Y-m-d');
        break;
    case 'actual':
    default:
        $inicio_semana = (new DateTime())->modify('this week')->format('Y-m-d');
        $fin_semana = (new DateTime())->modify('this week +6 days')->format('Y-m-d');
}

$actual_inicio_semana = (new DateTime())->modify('this week')->format('Y-m-d');
$actual_fin_semana = (new DateTime())->modify('this week +6 days')->format('Y-m-d');
$anterior_inicio_semana = (new DateTime())->modify('last week')->format('Y-m-d');
$anterior_fin_semana = (new DateTime())->modify('last week +6 days')->format('Y-m-d');
$siguiente_inicio_semana = (new DateTime())->modify('next week')->format('Y-m-d');
$siguiente_fin_semana = (new DateTime())->modify('next week +6 days')->format('Y-m-d');

$sql = "SELECT * FROM turnos WHERE profesional = ? AND fecha BETWEEN ? AND ? ORDER BY fecha, hora";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $profesional, $inicio_semana, $fin_semana);
$stmt->execute();
$pacientes = $stmt->get_result();

$pacientes_por_dia_y_hora = [];
while ($row = $pacientes->fetch_assoc()) {
    $dia_semana = date('N', strtotime($row['fecha'])) % 7; // 0 (para domingo) a 6 (para sábado)
    $hora = date('H:i', strtotime($row['hora']));
    if (!isset($pacientes_por_dia_y_hora[$dia_semana])) {
        $pacientes_por_dia_y_hora[$dia_semana] = [];
    }
    if (!isset($pacientes_por_dia_y_hora[$dia_semana][$hora])) {
        $pacientes_por_dia_y_hora[$dia_semana][$hora] = [];
    }
    $pacientes_por_dia_y_hora[$dia_semana][$hora][] = $row;
}

// Obtener la disponibilidad del profesional desde la sesión
$disponibilidadProfesionales = $_SESSION['disponibilidadProfesionales'];
$disponibilidad = isset($disponibilidadProfesionales[$profesional]) ? $disponibilidadProfesionales[$profesional] : [];

// Si el profesional es 'Lucia Foricher', agregar los horarios de 'Terapia Manual - RPG'
$disponibilidadTerapiaManual = [];
if ($profesional === 'Lucia Foricher') {
    $disponibilidadTerapiaManual = [
        'Monday' => ['16:00', '17:00', '18:00', '19:00'],
        'Wednesday' => ['16:00', '17:00', '18:00', '19:00'],
        'Tuesday' => ['11:00', '12:00', '13:00', '14:00', '15:00'],
        'Thursday' => ['11:00', '12:00', '13:00', '14:00', '15:00'],
        'Friday' => ['12:00', '13:00', '14:00', '15:00']
    ];
}

$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    0 => 'Domingo'
];

// Colores por servicio
$colores_servicio = [
    'Kinesiología' => '#E2C6C2',
    'Terapia Manual - RPG' => '#A6DA9C',
    'Drenaje Linfático' => '#BBFFFF',
    'Nutrición' => '#EE976A',
    'Traumatología' => '#A9B0F4'
];

$conn->close();

// Función para traducir días de la semana al español
function traducirDia($diaIngles) {
    $traducciones = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    return isset($traducciones[$diaIngles]) ? $traducciones[$diaIngles] : $diaIngles;
}

// Obtener horarios disponibles y ocupados
$horariosDisponibles = [];
$horariosOcupados = [];

foreach ($disponibilidad as $dia => $horas) {
    foreach ($horas as $hora) {
        if (!isset($pacientes_por_dia_y_hora[date('N', strtotime($dia)) % 7][$hora])) {
            $horariosDisponibles[] = traducirDia($dia) . ' ' . $hora;
        } else {
            $horariosOcupados[] = traducirDia($dia) . ' ' . $hora;
        }
    }
}

$horariosDisponiblesTerapia = [];
$horariosOcupadosTerapia = [];

foreach ($disponibilidadTerapiaManual as $dia => $horas) {
    foreach ($horas as $hora) {
        if (!isset($pacientes_por_dia_y_hora[date('N', strtotime($dia)) % 7][$hora])) {
            $horariosDisponiblesTerapia[] = traducirDia($dia) . ' ' . $hora;
        } else {
            $horariosOcupadosTerapia[] = traducirDia($dia) . ' ' . $hora;
        }
    }
}

// Definir las horas únicas
$horasUnicas = [];
if (isset($disponibilidad)) {
    foreach ($disponibilidad as $dia => $horas) {
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

// Definir las horas únicas para Terapia Manual - RPG
$horasUnicasTerapia = [];
foreach ($disponibilidadTerapiaManual as $dia => $horas) {
    foreach ($horas as $hora) {
        if (!in_array($hora, $horasUnicasTerapia)) {
            $horasUnicasTerapia[] = $hora;
        }
    }
}
foreach ($pacientes_por_dia_y_hora as $dia_num => $horas) {
    foreach ($horas as $hora => $pacientes) {
        if (!in_array($hora, $horasUnicasTerapia)) {
            $horasUnicasTerapia[] = $hora;
        }
    }
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
    <title>Pacientes de <?php echo htmlspecialchars($profesional); ?></title>
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

        th, td {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #F6EBD5;
            color: #333;
        }

        .patient-card, .available-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
            margin: 5px;
            text-align: center;
            display: inline-block;
            width: 90px;
            font-size: 12px;
            cursor: pointer; /* Añadido para indicar que es clickeable */
        }

        .patient-card {
            background-color: #f4f4f4; /* Color de fondo por defecto */
        }

        .available-card {
            background-color: #d4edda; /* Color de fondo para disponible */
        }

        .patient-card div, .available-card div {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            margin: auto;
        }

        .patient-card div p, .available-card div p {
            margin: 0;
            flex: 1;
            width: 100%;
            text-align: center;
            margin: auto;
        }

        .button-container {
            display: flex;
            justify-content: start;
            align-items: start;
            margin-top: 20px;
            width: 70%;
            flex-wrap: wrap;
        }

        .button-container form {
            flex-grow: 0;
        }

        .button-container button {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
            width: 195px;
        }

        .button-container button:hover {
            background-color: #7d9a7d;
        }

        .button-container span {
            margin-left: 10px;
            align-self: center;
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

        .btn_horarios {
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

        .indice_semana{
            border: 1px solid #000;
            background-color: #F6EBD5;
            padding: 10px;
            border-radius: 16px;
            width: 200px;
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

            .indice_semana{
            position: relative;
            right: 5px;
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

        .button-container {
            display: flex;
            justify-content: center;
            align-items: start;
            margin-top: 20px;
            width: 100%;
            flex-wrap: wrap;
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

        /* Estilo para la tarjeta del formulario de agregar paciente */
        .card {
            width: 50%;
            margin: auto;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
            position: absolute;
            display: none;
        }

        .card-header {
            background-color: #96B394;
            color: white;
            padding: 10px;
            border-radius: 5px 5px 0 0;
            text-align: center;
            font-size: 1.5em;
        }

        .card-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" 
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <ul class="ul_container navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="btn_horarios nav_link nav-link" style="color: #fff;" href="agendar_paciente.php">Agendar Paciente</a>
                    </li>
                    <li class="nav-item">
                        <form method="GET" action="administrador.php">
                            <button class="btn_horarios" type="submit">Volver</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<div class="content" style="color: #000 !important;">
    <h1>Pacientes de <?php echo htmlspecialchars($profesional); ?></h1>

    <div class="button-container">
        <form method="GET" action="profesional_pacientes.php">
            <input type="hidden" name="profesional" value="<?php echo $profesional; ?>">
            <input type="hidden" name="semana" value="anterior">
            <button type="submit">Semana Anterior</button>
        </form>
        <form method="GET" action="profesional_pacientes.php">
            <input type="hidden" name="profesional" value="<?php echo $profesional; ?>">
            <input type="hidden" name="semana" value="actual">
            <button type="submit">Semana Actual</button>
        </form>
        <form method="GET" action="profesional_pacientes.php">
            <input type="hidden" name="profesional" value="<?php echo $profesional; ?>">
            <input type="hidden" name="semana" value="siguiente">
            <button type="submit">Semana Siguiente</button>
        </form>
        <span class="indice_semana"><?php echo $inicio_semana . " - " . $fin_semana; ?></span>
    </div>

    <div class="table-container">
        <hr>
        <h2>Horarios</h2>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <?php foreach ($dias_semana as $dia) : ?>
                        <th><?php echo $dia; ?></th>
                    <?php endforeach; ?>
                    <th>Horarios Disponibles</th>
                    <th>Horarios Ocupados</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Obtener todas las horas únicas para los horarios normales
                $horasUnicasNormales = array_diff($horasUnicas, $horasUnicasTerapia);
                if ($profesional === 'Lucia Foricher') {
                    $horasUnicasNormales[] = '11:00'; // Incluir la hora 11:00 solo para Lucia Foricher
                }
                $horasUnicasNormales = array_unique($horasUnicasNormales);
                sort($horasUnicasNormales);

                // Mostrar las horas y los pacientes por día para los horarios normales
                foreach ($horasUnicasNormales as $hora) :
                ?>
                <tr>
                    <td><?php echo $hora; ?></td>
                    <?php
                    foreach ($dias_semana as $dia_num => $dia) :
                    ?>
                    <td>
                        <?php
                        if (isset($pacientes_por_dia_y_hora[$dia_num][$hora])) :
                            foreach ($pacientes_por_dia_y_hora[$dia_num][$hora] as $paciente) :
                                // Obtener el servicio del paciente
                                $servicio = $paciente['servicio'];
                                if ($servicio !== 'Terapia Manual - RPG'):
                                    // Verificar si el servicio tiene un color asignado en el array $colores_servicio
                                    $color_fondo = isset($colores_servicio[$servicio]) ? $colores_servicio[$servicio] : '#FFFFFF'; // Color por defecto si no se encuentra el servicio
                        ?>
                        <div class="patient-card" style="background-color: <?php echo $color_fondo; ?>;" onclick="redirigirDiagnostico(<?php echo $paciente['id']; ?>)">
                            <p><strong><?php echo htmlspecialchars($paciente['nombre']); ?></strong></p>
                            <p><?php echo htmlspecialchars($paciente['fecha']); ?></p>
                        </div>
                        <?php
                                    endif;
                            endforeach;
                        else :
                        ?>
                        <!-- Mostrar horas disponibles -->
                        <?php
                        if (isset($disponibilidad[$dia_num]) && in_array($hora, $disponibilidad[$dia_num])) {
                            echo "<div class='available-card'><p>Disponible</p></div>";
                        } else {
                            echo "<p>-</p>";
                        }
                        ?>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                    <td>
                        <?php
                        // Mostrar horarios disponibles
                        $horarios_disponibles = [];
                        foreach ($disponibilidad as $dia => $horas) {
                            if (in_array($hora, $horas) && !isset($pacientes_por_dia_y_hora[date('N', strtotime($dia)) % 7][$hora])) {
                                $horarios_disponibles[] = traducirDia($dia) . ' ' . $hora;
                            }
                        }
                        echo implode(', ', $horarios_disponibles);
                        ?>
                    </td>
                    <td>
                        <?php
                        // Mostrar horarios ocupados
                        $horarios_ocupados = [];
                        foreach ($pacientes_por_dia_y_hora as $dia_num => $horas) {
                            if (isset($horas[$hora])) {
                                $horarios_ocupados[] = $dias_semana[$dia_num] . ' ' . $hora;
                            }
                        }
                        echo implode(', ', $horarios_ocupados);
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($profesional === 'Lucia Foricher'): ?>
    <div class="table-container" style="margin-top: 20px;">
        <hr>
        <h2>Terapia Manual - RPG</h2>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <?php foreach ($dias_semana as $dia) : ?>
                        <th><?php echo $dia; ?></th>
                    <?php endforeach; ?>
                    <th>Horarios Disponibles</th>
                    <th>Horarios Ocupados</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mostrar las horas y los pacientes por día para Terapia Manual - RPG
                foreach ($horasUnicasTerapia as $hora) :
                ?>
                <tr>
                    <td><?php echo $hora; ?></td>
                    <?php
                    foreach ($dias_semana as $dia_num => $dia) :
                    ?>
                    <td>
                        <?php
                        // Verificar si hay pacientes para Terapia Manual - RPG en la hora y día específicos
                        $pacientesTerapiaManual = array_filter($pacientes_por_dia_y_hora[$dia_num][$hora] ?? [], function($paciente) {
                            return $paciente['servicio'] === 'Terapia Manual - RPG';
                        });
                        if (!empty($pacientesTerapiaManual)) :
                            foreach ($pacientesTerapiaManual as $paciente) :
                                // Obtener el servicio del paciente
                                $servicio = $paciente['servicio'];
                                // Verificar si el servicio tiene un color asignado en el array $colores_servicio
                                $color_fondo = isset($colores_servicio[$servicio]) ? $colores_servicio[$servicio] : '#FFFFFF'; // Color por defecto si no se encuentra el servicio
                        ?>
                        <div class="patient-card" style="background-color: <?php echo $color_fondo; ?>;" onclick="redirigirDiagnostico(<?php echo $paciente['id']; ?>)">
                            <p><strong><?php echo htmlspecialchars($paciente['nombre']); ?></strong></p>
                            <p><?php echo htmlspecialchars($paciente['fecha']); ?></p>
                        </div>
                        <?php
                            endforeach;
                        else :
                        ?>
                        <!-- Mostrar horas disponibles -->
                        <?php
                        if (isset($disponibilidadTerapiaManual[$dia]) && in_array($hora, $disponibilidadTerapiaManual[$dia])) {
                            echo "<div class='available-card'><p>Disponible</p></div>";
                        } else {
                            echo "<p>-</p>";
                        }
                        ?>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                    <td>
                        <?php
                        // Mostrar horarios disponibles para Terapia Manual - RPG
                        $horarios_disponibles_terapia = [];
                        foreach ($disponibilidadTerapiaManual as $dia => $horas) {
                            if (in_array($hora, $horas) && !isset($pacientes_por_dia_y_hora[date('N', strtotime($dia)) % 7][$hora])) {
                                $horarios_disponibles_terapia[] = traducirDia($dia) . ' ' . $hora;
                            }
                        }
                        echo implode(', ', $horarios_disponibles_terapia);
                        ?>
                    </td>
                    <td>
                        <?php
                        // Mostrar horarios ocupados para Terapia Manual - RPG
                        $horarios_ocupados_terapia = [];
                        foreach ($pacientes_por_dia_y_hora as $dia_num => $horas) {
                            if (isset($horas[$hora])) {
                                $horarios_ocupados_terapia[] = $dias_semana[$dia_num] . ' ' . $hora;
                            }
                        }
                        echo implode(', ', $horarios_ocupados_terapia);
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            Agendar Paciente
        </div>
        <div class="card-body">
            <form id="agendarPacienteForm">
                <div class="form-group">
                    <label for="servicio">Servicio</label>
                    <select id="servicio" name="servicio" class="form-control" required>
                        <option value="">Seleccione un servicio</option>
                        <option value="Kinesiología">Kinesiología</option>
                        <option value="Terapia Manual - RPG">Terapia Manual - RPG</option>
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
                <div class="form-group">
                    <label for="fecha">Fecha</label>
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
    </div>

    <div class="confirmation-card" id="confirmationCard">
        <h2>Paciente registrado con éxito</h2>
        <button id="agendarOtroPacienteBtn">Agendar otro paciente</button>
        <button id="volverBtn">Volver</button>
    </div>
</div>

<script>
    function redirigirDiagnostico(id) {
        window.location.href = 'diagnostico.php?id=' + id;
    }

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
            'Terapia Manual - RPG': ['Mauro Robert', 'German Fernandez'],
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
