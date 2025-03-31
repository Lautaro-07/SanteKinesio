<?php
session_start([
    'cookie_lifetime' => 0, // La sesión se cierra cuando se cierra el navegador
]);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_POST['profesional'])) {
    echo "Profesional no especificado.";
    exit();
}

$profesional = $_POST['profesional'];

$conn = new mysqli('localhost', 'root', '', 'sante');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT * FROM turnos WHERE profesional = ? ORDER BY fecha, hora";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $profesional);
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

$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    0 => 'Domingo'
];

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

        .patient-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
            margin: 5px;
            text-align: center;
            display: inline-block;
            width: 90px;
            font-size: 12px;
            background-color: #f4f4f4; /* Color de fondo por defecto */
            cursor: pointer; /* Añadido para indicar que es clickeable */
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
                            <button type="submit">Volver</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<div class="content" style="color: #000 !important;">
    <h1>Pacientes de <?php echo htmlspecialchars($profesional); ?></h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <?php foreach ($dias_semana as $dia) : ?>
                        <th><?php echo $dia; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Obtener todas las horas únicas incluyendo las de disponibilidad
                $horasUnicas = [];
                if (isset($disponibilidad[$profesional])) {
                    foreach ($disponibilidad[$profesional] as $dia => $horas) {
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
                    foreach ($dias_semana as $dia_num => $dia) :
                    ?>
                    <td>
                        <?php
                        if (isset($pacientes_por_dia_y_hora[$dia_num][$hora])) :
                            foreach ($pacientes_por_dia_y_hora[$dia_num][$hora] as $paciente) :
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
                        if (isset($disponibilidad[$dia][$hora])) {
                            echo "<p>Disponible</p>";
                        } else {
                            echo "<p>-</p>";
                        }
                        ?>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function redirigirDiagnostico(id) {
        window.location.href = 'diagnostico.php?id=' + id;
    }
</script>
</body>
</html>