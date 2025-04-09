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
            'Monday (Terapia Manual - RPG)' => ['16:00', '17:00', '18:00', '19:00'],
            'Wednesday (Terapia Manual - RPG)' => ['16:00', '17:00', '18:00', '19:00'],
            'Tuesday (Terapia Manual - RPG)' => ['11:00', '12:00', '13:00', '14:00', '15:00'],
            'Thursday (Terapia Manual - RPG)' => ['11:00', '12:00', '13:00', '14:00', '15:00'],
            'Friday (Terapia Manual - RPG)' => ['12:00', '13:00', '14:00', '15:00']
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
        'Hernan Lopez' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00', '12:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00', '12:00'],
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
        'Mariana Ilari  ' => [
            'Monday' => ['08:30', '09:30', '10:30', '11:30'],
            'Wednesday' => ['10:30', '11:30'],
            'Thursday' => ['08:30', '09:30', '10:30', '11:30'],
            'Friday' => ['17:00', '18:00']
        ],
        'Leila Heguilein' => [
            'Tuesday' => ['17:00', '18:00', '19:00', '20:00'],
            'Wednesday' => ['17:00', '18:00', '19:00', '20:00'],
        ]
    ];
}

$disponibilidadProfesionales = $_SESSION['disponibilidadProfesionales'];

// FUNCIÓN ACTUALIZADA: deshabilitarHorarios
function deshabilitarHorarios(&$disponibilidad, $profesional) {
    // Simplemente marcar todos los horarios como no disponibles sin restricciones
    // para asegurar que se deshabiliten absolutamente todos
    foreach ($disponibilidad[$profesional] as $dia => &$horas) {
        foreach ($horas as &$hora) {
            $hora = "No disponible";
        }
    }
}

// FUNCIÓN ACTUALIZADA: habilitarHorarios
function habilitarHorarios(&$disponibilidad, $profesional) {
    // Usar originalDisponibilidad de la sesión si existe (añadida por modificar_horarios.php)
    if (isset($_SESSION['originalDisponibilidad']) && isset($_SESSION['originalDisponibilidad'][$profesional])) {
        $disponibilidad[$profesional] = $_SESSION['originalDisponibilidad'][$profesional];
        return;
    }
    
    // Si no hay originalDisponibilidad en la sesión, usar los valores predeterminados
    $originalDisponibilidad = [
        'Lucia Foricher' => [
            'Monday' => ['08:00', '09:00', '10:00', '11:00'],
            'Wednesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Friday' => ['08:00', '09:00', '10:00', '11:00'],
            'Monday (Terapia Manual - RPG)' => ['16:00', '17:00', '18:00', '19:00'],
            'Wednesday (Terapia Manual - RPG)' => ['16:00', '17:00', '18:00', '19:00'],
            'Tuesday (Terapia Manual - RPG)' => ['11:00', '12:00', '13:00', '14:00', '15:00'],
            'Thursday (Terapia Manual - RPG)' => ['11:00', '12:00', '13:00', '14:00', '15:00'],
            'Friday (Terapia Manual - RPG)' => ['12:00', '13:00', '14:00', '15:00']
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
        'Hernan Lopez' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00', '12:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00', '12:00']
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
        'Mariana Ilari' => [
            'Monday' => ['08:30', '09:30', '10:30', '11:30'],
            'Wednesday' => ['10:30', '11:30'],
            'Thursday' => ['08:30', '09:30', '10:30', '11:30'],
            'Friday' => ['17:00', '18:00']
        ],
        'Leila Heguilein' => [
            'Tuesday' => ['17:00', '18:00', '19:00', '20:00'],
            'Wednesday' => ['17:00', '18:00', '19:00', '20:00'],
        ]
    ];

    // Si existe el profesional en la configuración predeterminada, usarlo
    if (isset($originalDisponibilidad[$profesional])) {
        $disponibilidad[$profesional] = $originalDisponibilidad[$profesional];
    }
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

$fecha_ini = "$anio-$mes-01";
$fecha_fin = date('Y-m-t', strtotime($fecha_ini));

$sql .= " AND fecha BETWEEN ? AND ?";
$params[] = $fecha_ini;
$params[] = $fecha_fin;
$param_types .= 'ss';

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
    'Terapia Manual - RPG' => '#A6DA9C',
    'Drenaje Linfático' => '#BBFFFF',
    'Nutrición' => '#EE976A',
    'Traumatología' => '#A9B0F4',
    'PsicologÍa' => '#f8c8dc',
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

// Obtener pacientes de Terapia Manual - RPG con Lucia Foricher
$pacientesTerapiaManualRPG = [];
if ($profesional === 'Lucia Foricher') {
    $sql_terapia = "SELECT * FROM turnos WHERE profesional = ? AND servicio = 'Terapia Manual - RPG' AND fecha BETWEEN ? AND ? ORDER BY fecha";
    $stmt_terapia = $conn->prepare($sql_terapia);
    $stmt_terapia->bind_param('sss', $profesional, $fecha_ini, $fecha_fin);
    $stmt_terapia->execute();
    $pacientesTerapiaManualRPG = $stmt_terapia->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para contar pacientes en un día y hora específicos
function contarPacientesPorDiaHora($pacientes, $dia_semana, $hora) {
    $contador = 0;
    foreach ($pacientes as $paciente) {
        $fecha = new DateTime($paciente['fecha']);
        $dia_paciente = $fecha->format('N') % 7; // 0 (domingo) a 6 (sábado)
        $hora_paciente = date('H:i', strtotime($paciente['hora']));
        
        if ($dia_paciente == $dia_semana && $hora_paciente == $hora) {
            $contador++;
        }
    }
    return $contador;
}

// Función para obtener el array de días de la semana
function obtenerDiasSemana() {
    return [
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo'
    ];
}

// Obtener nombre del mes actual
setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252');
$nombre_mes = date('F', mktime(0, 0, 0, $mes, 1, $anio));
$nombre_mes = str_replace(
    ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    $nombre_mes
);

// Funciones para manejar fechas y formato en español
function obtenerNombreDia($fecha) {
    $dias = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    return $dias[date('l', strtotime($fecha))];
}

function formatoFechaEspanol($fecha) {
    $meses = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    ];
    
    $partes = explode('-', $fecha);
    if (count($partes) !== 3) return $fecha;
    
    $dia = ltrim($partes[2], '0');
    $mes = $meses[$partes[1]];
    $anio = $partes[0];
    
    return "$dia de $mes de $anio";
}

// Consultas para estadísticas
// Total de pacientes del mes
$sql_total_mes = "SELECT COUNT(*) as total FROM turnos WHERE profesional = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?";
$stmt_total_mes = $conn->prepare($sql_total_mes);
$stmt_total_mes->bind_param('sii', $profesional, $mes, $anio);
$stmt_total_mes->execute();
$result_total_mes = $stmt_total_mes->get_result();
$row_total_mes = $result_total_mes->fetch_assoc();
$total_pacientes_mes = $row_total_mes['total'];

// Total de pacientes que asistieron
$sql_asistieron = "SELECT COUNT(*) as total FROM turnos WHERE profesional = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ? AND asistio = 1";
$stmt_asistieron = $conn->prepare($sql_asistieron);
$stmt_asistieron->bind_param('sii', $profesional, $mes, $anio);
$stmt_asistieron->execute();
$result_asistieron = $stmt_asistieron->get_result();
$row_asistieron = $result_asistieron->fetch_assoc();
$total_asistieron = $row_asistieron['total'];

// Total de pacientes que no asistieron
$sql_no_asistieron = "SELECT COUNT(*) as total FROM turnos WHERE profesional = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ? AND asistio = 0 AND fecha < CURDATE()";
$stmt_no_asistieron = $conn->prepare($sql_no_asistieron);
$stmt_no_asistieron->bind_param('sii', $profesional, $mes, $anio);
$stmt_no_asistieron->execute();
$result_no_asistieron = $stmt_no_asistieron->get_result();
$row_no_asistieron = $result_no_asistieron->fetch_assoc();
$total_no_asistieron = $row_no_asistieron['total'];

// Calcular porcentaje de asistencia
$porcentaje_asistencia = ($total_pacientes_mes > 0) ? round(($total_asistieron / $total_pacientes_mes) * 100) : 0;

// Pacientes por día de la semana
$sql_por_dia = "SELECT DAYOFWEEK(fecha) as dia, COUNT(*) as total FROM turnos 
                WHERE profesional = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ? 
                GROUP BY DAYOFWEEK(fecha) 
                ORDER BY DAYOFWEEK(fecha)";
$stmt_por_dia = $conn->prepare($sql_por_dia);
$stmt_por_dia->bind_param('sii', $profesional, $mes, $anio);
$stmt_por_dia->execute();
$result_por_dia = $stmt_por_dia->get_result();
$pacientes_por_dia = [];
while ($row = $result_por_dia->fetch_assoc()) {
    $dia_index = ($row['dia'] - 1) % 7; // Ajustar para que 0 sea domingo
    $pacientes_por_dia[$dia_index] = $row['total'];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes - <?php echo $profesional; ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="../bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="icon" href="../img/santeLogo.jpg">
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

        .content-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin: 20px;
        }

        .calendar {
            flex: 1;
            min-width: 300px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin: 10px;
        }

        .day-column {
            width: 100%;
            margin-bottom: 20px;
        }

        .day-title {
            background-color: #96B394;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }

        .patient-card {
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .patient-card.asistio {
            background-color: #d1e7dd;
            border-left: 4px solid #198754;
        }

        .patient-card.no-asistio {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }

        .patient-card.terapia-manual {
            background-color: #A6DA9C;
        }

        .status-indicator {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 5px;
        }

        .time-box {
            padding: 3px 6px;
            background-color: #e2e3e5;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 5px;
        }

        .patient-info {
            margin-top: 5px;
        }

        .patient-name {
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .patient-contact {
            font-size: 0.9em;
            color: #666;
        }

        .patient-service {
            font-size: 0.85em;
            padding: 3px 6px;
            border-radius: 3px;
            display: inline-block;
            margin-top: 5px;
        }

        .stats {
            flex: 1;
            min-width: 300px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin: 10px;
        }

        .stat-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }

        .stat-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            text-align: center;
            width: 45%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .stat-title {
            font-size: 0.9em;
            color: #666;
        }

        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #96B394;
            margin: 10px 0;
        }

        .attendance-chart {
            width: 100%;
            height: 200px;
            margin-top: 20px;
        }

        .month-navigation {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }

        .month-btn {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .month-btn:hover {
            background-color: #7a9e78;
        }

        .current-month {
            font-size: 1.2em;
            font-weight: bold;
            color: #96B394;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .form-field {
            flex: 1;
            min-width: 200px;
        }

        .form-field input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
        }

        .form-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-btn {
            background-color: #96B394;
            color: white;
        }

        .reset-btn {
            background-color: #6c757d;
            color: white;
        }

        .schedule-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .full-width-btn {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            width: 100%;
            max-width: 250px;
            text-align: center;
        }

        .disable-btn {
            background-color: #dc3545;
        }

        .enable-btn {
            background-color: #198754;
        }

        .full-width-btn:hover {
            opacity: 0.9;
        }

        .attendance-form {
            margin-top: 10px;
        }

        .attendance-checkbox {
            margin-right: 5px;
        }

        .attendance-label {
            font-size: 0.9em;
            cursor: pointer;
        }

        .attendance-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            cursor: pointer;
            margin-top: 5px;
        }

        .day-container {
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .stat-card {
                width: 100%;
            }
            
            .form-field {
                min-width: 100%;
            }
            
            .form-buttons {
                width: 100%;
                justify-content: space-between;
            }
            
            .schedule-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .full-width-btn {
                max-width: 100%;
            }
        }

        .empty-day-message {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        /* Estilos adicionales para los horarios disponibles */
        .available-hours {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .available-hours h3 {
            color: #96B394;
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .day-hours {
            margin-bottom: 25px;
        }

        .day-hours-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #555;
        }

        .hours-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .hour-item {
            background-color: #e8f4ea;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9em;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .hour-item.not-available {
            background-color: #f8d7da;
            color: #721c24;
        }

        .terapia-tag {
            background-color: #A6DA9C;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-left: 5px;
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
                            <a class="nav_link_inicio nav-link" style="color: #fff;" href="../index.php">Inicio</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mt-4">
        <h1 class="mb-4">Pacientes de <?php echo $profesional; ?></h1>
        
        <div class="month-navigation">
            <a href="?mes=<?php echo $mes_anterior; ?>&anio=<?php echo $anio_anterior; ?>" class="month-btn">
                <i class="fas fa-chevron-left"></i> Mes anterior
            </a>
            <div class="current-month"><?php echo $nombre_mes . ' ' . $anio; ?></div>
            <a href="?mes=<?php echo $mes_siguiente; ?>&anio=<?php echo $anio_siguiente; ?>" class="month-btn">
                Mes siguiente <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        
        <div class="search-form">
            <form method="GET" action="" class="d-flex flex-wrap w-100">
                <input type="hidden" name="mes" value="<?php echo $mes; ?>">
                <input type="hidden" name="anio" value="<?php echo $anio; ?>">
                
                <div class="form-field me-2">
                    <input type="text" name="nombre" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($busqueda_nombre); ?>" class="form-control">
                </div>
                
                <div class="form-field me-2">
                    <input type="text" name="telefono" placeholder="Buscar por teléfono" value="<?php echo htmlspecialchars($busqueda_telefono); ?>" class="form-control">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="form-btn search-btn">Buscar</button>
                    <a href="?mes=<?php echo $mes; ?>&anio=<?php echo $anio; ?>" class="form-btn reset-btn text-decoration-none text-white">Limpiar</a>
                </div>
            </form>
        </div>
        
        <div class="schedule-buttons">
            <form method="POST" action="">
                <button type="submit" name="deshabilitar" class="full-width-btn disable-btn">
                    <i class="fas fa-ban"></i> Deshabilitar Horarios
                </button>
            </form>
            
            <form method="POST" action="">
                <button type="submit" name="habilitar" class="full-width-btn enable-btn">
                    <i class="fas fa-check-circle"></i> Habilitar Horarios
                </button>
            </form>
        </div>
        
        <div class="content-wrapper">
            <div class="calendar">
                <h2 class="mb-4">Calendario de Pacientes</h2>
                
                <?php
                // Obtener todos los días del mes actual
                $num_dias = date('t', strtotime("$anio-$mes-01"));
                $dias_agrupados = [];
                
                // Agrupar por día del mes
                for ($i = 1; $i <= $num_dias; $i++) {
                    $fecha_actual = sprintf("%04d-%02d-%02d", $anio, $mes, $i);
                    $timestamp = strtotime($fecha_actual);
                    $dia_semana = date('w', $timestamp); // 0 (domingo) a 6 (sábado)
                    $nombre_dia = $dias_semana[$dia_semana];
                    $fecha_formateada = date('d/m/Y', $timestamp);
                    
                    // Buscar pacientes para este día
                    $pacientes_dia = [];
                    foreach ($pacientes_array as $paciente) {
                        if ($paciente['fecha'] == $fecha_actual) {
                            $pacientes_dia[] = $paciente;
                        }
                    }
                    
                    // Solo mostrar días con pacientes
                    if (!empty($pacientes_dia)) {
                        $dias_agrupados[] = [
                            'fecha' => $fecha_actual,
                            'nombre_dia' => $nombre_dia,
                            'fecha_formateada' => $fecha_formateada,
                            'pacientes' => $pacientes_dia
                        ];
                    }
                }
                
                // Verificar si hay resultados
                if (empty($dias_agrupados)) {
                    echo '<div class="alert alert-info">No se encontraron pacientes para el mes seleccionado.</div>';
                } else {
                    // Mostrar pacientes agrupados por día
                    foreach ($dias_agrupados as $dia) {
                        ?>
                        <div class="day-container">
                            <div class="day-title">
                                <?php echo $dia['nombre_dia'] . ' - ' . $dia['fecha_formateada']; ?>
                            </div>
                            
                            <?php
                            // Ordenar pacientes por hora
                            usort($dia['pacientes'], function($a, $b) {
                                return strtotime($a['hora']) - strtotime($b['hora']);
                            });
                            
                            foreach ($dia['pacientes'] as $paciente) {
                                $clase_asistencia = '';
                                if ($paciente['fecha'] < date('Y-m-d')) {
                                    $clase_asistencia = $paciente['asistio'] ? 'asistio' : 'no-asistio';
                                }
                                
                                $clase_servicio = '';
                                if ($paciente['servicio'] == 'Terapia Manual - RPG') {
                                    $clase_servicio = 'terapia-manual';
                                }
                                ?>
                                
                                <div class="patient-card <?php echo $clase_asistencia . ' ' . $clase_servicio; ?>" onclick="window.location.href='diagnostico.php?id=<?php echo $paciente['id']; ?>'" style="cursor: pointer;">
                                    <div class="time-box">
                                        <?php echo date('H:i', strtotime($paciente['hora'])); ?>
                                    </div>
                                    
                                    <div class="patient-info">
                                        <div class="patient-name">
                                            <?php echo htmlspecialchars($paciente['nombre']); ?>
                                            <?php if ($paciente['fecha'] < date('Y-m-d')): ?>
                                                <span class="status-indicator <?php echo $paciente['asistio'] ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $paciente['asistio'] ? 'Asistió' : 'No asistió'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="patient-contact">
                                            <?php echo htmlspecialchars($paciente['telefono']); ?>
                                        </div>
                                        
                                        <div class="patient-service" style="background-color: <?php echo isset($colores_servicio[$paciente['servicio']]) ? $colores_servicio[$paciente['servicio']] : '#e9ecef'; ?>">
                                            <?php echo htmlspecialchars($paciente['servicio']); ?>
                                        </div>
                                        
                                        <?php if ($paciente['diagnostico']): ?>
                                            <div class="patient-info">
                                                <strong>Diagnóstico:</strong> <?php echo htmlspecialchars($paciente['diagnostico']); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($paciente['fecha'] < date('Y-m-d')): ?>
                                            <form method="POST" action="" class="attendance-form" onclick="event.stopPropagation();">
                                                <input type="hidden" name="asistencia_id" value="<?php echo $paciente['id']; ?>">
                                                <div class="form-check">
                                                    <input class="form-check-input attendance-checkbox" type="checkbox" name="asistio" id="asistio_<?php echo $paciente['id']; ?>" <?php echo $paciente['asistio'] ? 'checked' : ''; ?> onclick="event.stopPropagation();">
                                                    <label class="form-check-label attendance-label" for="asistio_<?php echo $paciente['id']; ?>" onclick="event.stopPropagation();">
                                                        Registrar asistencia
                                                    </label>
                                                    <button type="submit" class="attendance-btn" onclick="event.stopPropagation();">Guardar</button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                            <?php } ?>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            
            <div class="stats">
                <h2 class="mb-4">Estadísticas</h2>
                <div class="stat-container">
                    <div class="stat-card">
                        <div class="stat-title">Total de Pacientes</div>
                        <div class="stat-value"><?php echo $total_pacientes_mes; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">Asistieron</div>
                        <div class="stat-value"><?php echo $total_asistieron; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">No Asistieron</div>
                        <div class="stat-value"><?php echo $total_no_asistieron; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-title">% de Asistencia</div>
                        <div class="stat-value"><?php echo $porcentaje_asistencia; ?>%</div>
                    </div>
                </div>
                
                <div class="available-hours">
                    <h3>Horarios Disponibles</h3>
                    
                    <?php
                    $dias_ingles = [
                        'Monday' => 'Lunes',
                        'Tuesday' => 'Martes',
                        'Wednesday' => 'Miércoles',
                        'Thursday' => 'Jueves',
                        'Friday' => 'Viernes',
                        'Saturday' => 'Sábado',
                        'Sunday' => 'Domingo'
                    ];
                    
                    $dias_semana_orden = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    
                    foreach ($dias_semana_orden as $dia) {
                        $tiene_horarios = false;
                        $tiene_horarios_terapia = false;
                        
                        // Verificar si hay horarios regulares para este día
                        if (isset($disponibilidadProfesionales[$profesional][$dia]) && !empty($disponibilidadProfesionales[$profesional][$dia])) {
                            $tiene_horarios = true;
                        }
                        
                        // Verificar si hay horarios de terapia para este día
                        $dia_terapia = $dia . ' (Terapia Manual - RPG)';
                        if (isset($disponibilidadProfesionales[$profesional][$dia_terapia]) && !empty($disponibilidadProfesionales[$profesional][$dia_terapia])) {
                            $tiene_horarios_terapia = true;
                        }
                        
                        if ($tiene_horarios || $tiene_horarios_terapia) {
                            echo '<div class="day-hours">';
                            echo '<div class="day-hours-title">' . $dias_ingles[$dia] . '</div>';
                            
                            if ($tiene_horarios) {
                                echo '<div class="hours-list">';
                                foreach ($disponibilidadProfesionales[$profesional][$dia] as $hora) {
                                    $clase = ($hora === 'No disponible') ? 'hour-item not-available' : 'hour-item';
                                    echo '<span class="' . $clase . '">' . $hora . '</span>';
                                }
                                echo '</div>';
                            }
                            
                            if ($tiene_horarios_terapia) {
                                echo '<div class="day-hours-title mt-3">' . $dias_ingles[$dia] . ' <span class="terapia-tag">Terapia Manual - RPG</span></div>';
                                echo '<div class="hours-list">';
                                foreach ($disponibilidadProfesionales[$profesional][$dia_terapia] as $hora) {
                                    $clase = ($hora === 'No disponible') ? 'hour-item not-available' : 'hour-item';
                                    echo '<span class="' . $clase . '">' . $hora . '</span>';
                                }
                                echo '</div>';
                            }
                            
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>&copy; 2023 Sante - Todos los derechos reservados</p>
    </footer>
</body>
</html>