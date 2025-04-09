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

// Función para traducir días con Terapia Manual - RPG
function traducirDiaTerapiaManual($diaIngles) {
    // Extrae el nombre del día sin la parte de Terapia Manual
    $diaSolo = substr($diaIngles, 0, strpos($diaIngles, ' ('));
    $traducido = traducirDia($diaSolo);
    return $traducido . ' (Terapia Manual - RPG)';
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

// Mapeo de nombres de días en inglés a números
$dias_ingles_a_numero = [
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6,
    'Sunday' => 0
];

// Colores por servicio
$colores_servicio = [
    'Kinesiología' => '#E2C6C2',
    'Terapia Manual - RPG' => '#A6DA9C',
    'Drenaje Linfático' => '#BBFFFF',
    'Nutrición' => '#EE976A',
    'Traumatología' => '#A9B0F4',
    'Psicología' => '#f8c8dc',
];

// Conjunto para llevar el registro de horarios ocupados
$horariosOcupadosSet = [];

// Marcar horarios ocupados basados en pacientes existentes
foreach ($pacientes_por_dia_y_hora as $dia_num => $horas) {
    foreach ($horas as $hora => $pacientes) {
        // Usamos día_num y hora como clave única
        $horariosOcupadosSet[$dia_num . '_' . $hora] = true;
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

// Agregar horas de Terapia Manual para Lucia Foricher
if ($profesional === 'Lucia Foricher') {
    foreach ($disponibilidadProfesionales['Lucia Foricher'] as $dia => $horas) {
        if (strpos($dia, 'Terapia Manual - RPG') !== false) {
            foreach ($horas as $hora) {
                if (!in_array($hora, $horasUnicas)) {
                    $horasUnicas[] = $hora;
                }
            }
        }
    }
}

// Agregar horas de los pacientes existentes
foreach ($pacientes_por_dia_y_hora as $dia_num => $horas) {
    foreach ($horas as $hora => $pacientes) {
        if (!in_array($hora, $horasUnicas)) {
            $horasUnicas[] = $hora;
        }
    }
}

// Ordenar las horas
sort($horasUnicas);

$conn->close();
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

        .colores_servicios{
            text-align: center;
            margin: auto;
            width: 90%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 50px;
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
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #7d9a7d;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        .pagination a.active {
            background-color: #96B394;
            color: white;
            border: 1px solid #96B394;
        }

        .terapia-manual {
            color: #009688; /* Color distintivo para Terapia Manual */
            font-weight: bold;
        }

        /* Estilos para la columna de horarios disponibles */
        .horarios-disponibles {
            border-right: 2px solid #ddd;
            background-color: #f8f9fa;
            padding: 0;
        }

        .horarios-disponibles h5 {
            background-color: #96B394;
            color: white;
            padding: 10px;
            margin: 0;
            text-align: center;
        }

        .horarios-disponibles ul {
            list-style: none;
            padding: 10px;
            margin: 0;
            max-height: 500px;
            overflow-y: auto;
        }

        .horarios-disponibles li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .horarios-disponibles li:last-child {
            border-bottom: none;
        }

        .modal-header {
            background-color: #96B394;
            color: white;
        }
        
        /* Estilo para el botón de modificar horarios */
        .btn-modificar-horarios {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-modificar-horarios:hover {
            background-color: #7d9a7d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: white;
        }
        
        .btn-modificar-horarios i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
<header>
    <nav class="nav_container navbar navbar-dark navbar-expand-lg">
        <div class=" container-fluid">
            <div class="logo_container">
                <img class="logo" src="../img/santeLogo.jpg" alt="Logo">
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <ul class="ul_container navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="btn_horarios nav_link nav-link" style="color: #fff;" href="agendar_paciente.php">Agendar
                            Paciente</a>
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

    <div class="content">
        <h1>Pacientes de <?php echo htmlspecialchars($profesional); ?></h1>
        <div class="d-flex justify-content-center">
            <div class="pagination">
                <a href="?profesional=<?php echo urlencode($profesional); ?>&semana=anterior" <?php echo ($semana == 'anterior') ? 'class="active"' : ''; ?>>Semana Anterior</a>
                <a href="?profesional=<?php echo urlencode($profesional); ?>&semana=actual" <?php echo ($semana == 'actual' || !isset($_GET['semana'])) ? 'class="active"' : ''; ?>>Semana Actual</a>
                <a href="?profesional=<?php echo urlencode($profesional); ?>&semana=siguiente" <?php echo ($semana == 'siguiente') ? 'class="active"' : ''; ?>>Semana Siguiente</a>
            </div>
        </div>
        
        <!-- Botón para modificar horarios -->
        <div class="mt-3 d-flex justify-content-center">
            <a href="modificar_horarios.php?profesional=<?php echo urlencode($profesional); ?>" 
               class="btn-modificar-horarios">
                <i class="fa fa-clock-o" aria-hidden="true"></i> Modificar Horarios
            </a>
        </div>

        <div class="" style="margin: auto;">
            <div class="colores_servicios d-flex flex-wrap">
                <?php foreach ($colores_servicio as $servicio => $color): ?>
                    <div class="me-3 mb-2">
                        <span style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo $color; ?>; margin-right: 5px;"></span>
                        <?php echo $servicio; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Columna para horarios disponibles -->
            <div class="col-md-3 horarios-disponibles">
                <h5>Horarios Disponibles</h5>
                <ul>
                    <?php 
                    // Obtener el día actual en número (1-7)
                    $hoy_numero = date('N');
                    
                    // Crear matriz para almacenar horarios ocupados por día de la semana
                    $horarios_ocupados = [];
                    
                    // Llenar la matriz con horarios ocupados desde los datos de pacientes
                    foreach ($pacientes_por_dia_y_hora as $dia_num => $horas_ocupadas) {
                        if (!isset($horarios_ocupados[$dia_num])) {
                            $horarios_ocupados[$dia_num] = [];
                        }
                        foreach ($horas_ocupadas as $hora => $pacientes) {
                            $horarios_ocupados[$dia_num][] = $hora;
                        }
                    }
                    
                    // Mostrar los horarios de servicios normales DISPONIBLES (no ocupados)
                    foreach ($disponibilidad as $dia => $horas):
                        // Omitir días que contienen "Terapia Manual - RPG"
                        if (strpos($dia, 'Terapia Manual - RPG') !== false) continue;
                        
                        // Convertir nombre del día en inglés a número (1-7)
                        $dia_numero = $dias_ingles_a_numero[$dia];
                        
                        // Traducir el día al español
                        $dia_espanol = traducirDia($dia);
                        
                        // Filtrar horas ocupadas
                        $horas_disponibles = [];
                        foreach ($horas as $hora) {
                            // Verificar si la hora está ocupada para este día
                            if (!isset($horarios_ocupados[$dia_numero]) || !in_array($hora, $horarios_ocupados[$dia_numero])) {
                                $horas_disponibles[] = $hora;
                            }
                        }
                        
                        // Si el día ya ha pasado en la semana actual, mostrar mensaje especial
                        $es_semana_actual = $semana === 'actual' || !isset($_GET['semana']);
                        $dia_pasado = $es_semana_actual && $dia_numero < $hoy_numero;
                    ?>
                        <li>
                            <strong><?php echo $dia_espanol; ?></strong>:
                            <?php if ($dia_pasado): ?>
                                <span class="text-muted">Día pasado</span>
                            <?php elseif (empty($horas_disponibles)): ?>
                                <span class="text-muted">No hay horarios disponibles</span>
                            <?php else: ?>
                                <span><?php echo implode(', ', $horas_disponibles); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php 
                    // Mostrar los horarios de Terapia Manual - RPG específicamente
                    foreach ($disponibilidad as $dia => $horas):
                        if (strpos($dia, 'Terapia Manual - RPG') === false) continue;
                        
                        // Obtener el día base sin la parte de Terapia Manual
                        $dia_base = substr($dia, 0, strpos($dia, ' ('));
                        
                        // Convertir nombre del día en inglés a número (1-7)
                        $dia_numero = $dias_ingles_a_numero[$dia_base];
                        
                        // Traducir el día especial
                        $dia_espanol = traducirDiaTerapiaManual($dia);
                        
                        // Filtrar horas ocupadas para Terapia Manual
                        $horas_disponibles = [];
                        foreach ($horas as $hora) {
                            // Verificar si la hora está ocupada para este día
                            if (!isset($horarios_ocupados[$dia_numero]) || !in_array($hora, $horarios_ocupados[$dia_numero])) {
                                $horas_disponibles[] = $hora;
                            }
                        }
                        
                        // Si el día ya ha pasado en la semana actual, mostrar mensaje especial
                        $es_semana_actual = $semana === 'actual' || !isset($_GET['semana']);
                        $dia_pasado = $es_semana_actual && $dia_numero < $hoy_numero;
                    ?>
                        <li>
                            <strong class="terapia-manual"><?php echo $dia_espanol; ?></strong>:
                            <?php if ($dia_pasado): ?>
                                <span class="text-muted">Día pasado</span>
                            <?php elseif (empty($horas_disponibles)): ?>
                                <span class="text-muted">No hay horarios disponibles</span>
                            <?php else: ?>
                                <span><?php echo implode(', ', $horas_disponibles); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Columna para la tabla de pacientes -->
            <div class="col-md-9">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <?php for ($dia = 1; $dia <= 6; $dia++): ?>
                                    <?php 
                                    $fecha_dia = new DateTime($inicio_semana);
                                    $fecha_dia->modify('+' . ($dia - 1) . ' days');
                                    $fecha_formateada = $fecha_dia->format('d/m');
                                    ?>
                                    <th>
                                        <?php echo $dias_semana[$dia]; ?><br>
                                        <small><?php echo $fecha_formateada; ?></small>
                                    </th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($horasUnicas as $hora): ?>
                                <tr>
                                    <td><?php echo $hora; ?></td>
                                    <?php for ($dia = 1; $dia <= 6; $dia++): ?>
                                        <td>
                                            <?php if (isset($pacientes_por_dia_y_hora[$dia][$hora])): ?>
                                                <?php foreach ($pacientes_por_dia_y_hora[$dia][$hora] as $paciente): ?>
                                                    <a href="diagnostico.php?id=<?php echo $paciente['id']; ?>" class="text-decoration-none">
                                                        <div class="patient-card" style="background-color: <?php echo isset($colores_servicio[$paciente['servicio']]) ? $colores_servicio[$paciente['servicio']] : '#f4f4f4'; ?>;">
                                                            <?php echo htmlspecialchars($paciente['nombre']); ?>
                                                        </div>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <?php 
                                                // Obtener la fecha para este día y hora
                                                $fecha_celda = new DateTime($inicio_semana);
                                                $fecha_celda->modify('+' . ($dia - 1) . ' days');
                                                $fecha_str = $fecha_celda->format('Y-m-d');
                                                
                                                // Convertir número de día a nombre en inglés para verificar disponibilidad
                                                $dias_numero_a_ingles = [
                                                    1 => 'Monday',
                                                    2 => 'Tuesday',
                                                    3 => 'Wednesday',
                                                    4 => 'Thursday',
                                                    5 => 'Friday',
                                                    6 => 'Saturday',
                                                    0 => 'Sunday'
                                                ];
                                                
                                                $dia_ingles = $dias_numero_a_ingles[$dia];
                                                
                                                // Verificar si hay disponibilidad normal para este día y hora
                                                $disponible_normal = isset($disponibilidad[$dia_ingles]) && in_array($hora, $disponibilidad[$dia_ingles]);
                                                
                                                // Verificar si hay disponibilidad de Terapia Manual para este día y hora
                                                $disponible_terapia = false;
                                                $dia_terapia_manual = $dia_ingles . ' (Terapia Manual - RPG)';
                                                
                                                if (isset($disponibilidadProfesionales[$profesional][$dia_terapia_manual]) && 
                                                    in_array($hora, $disponibilidadProfesionales[$profesional][$dia_terapia_manual])) {
                                                    $disponible_terapia = true;
                                                }
                                                
                                                // Mostrar la celda según la disponibilidad
                                                if ($disponible_normal || $disponible_terapia): 
                                                ?>
                                                    <div class="">
                                                        -
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Bootstrap JS y otros scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cualquier script adicional que necesites
    </script>
</body>
</html>