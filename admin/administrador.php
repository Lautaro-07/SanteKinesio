<?php
session_start([
    'cookie_lifetime' => 0, // La sesión se cierra cuando se cierra el navegador
]);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Obtener lista de servicios
$conn = new mysqli('localhost', 'root', '', 'sante');
$servicios_result = $conn->query("SELECT servicio, precio FROM precio_servicios");
$servicios = [];
while ($row = $servicios_result->fetch_assoc()) {
    $servicios[] = $row;
}
$conn->close();

// Inicializar la consulta para obtener pacientes
$conn = new mysqli('localhost', 'root', '', 'sante'); // Definir la conexión a la base de datos
$sql = "SELECT * FROM turnos WHERE 1=1";

// Filtrar por la semana actual
$inicio_semana = (new DateTime())->modify('this week')->format('Y-m-d');
$fin_semana = (new DateTime())->modify('this week +6 days')->format('Y-m-d');
$sql .= " AND fecha BETWEEN ? AND ?";
$params = [$inicio_semana, $fin_semana];
$param_types = 'ss';

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

// Obtener lista de profesionales
$profesionales_result = $conn->query("SELECT DISTINCT profesional FROM turnos");
$profesionales = [];
while ($row = $profesionales_result->fetch_assoc()) {
    $profesionales[] = $row['profesional'];
}

// Mapear días de la semana
$dias_semana = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday'
];

$dias_semana_espanol = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes'
];

// Agrupar pacientes por día de la semana
$pacientes_por_dia = [];
$pacientes_por_hora = [];
while ($row = $pacientes->fetch_assoc()) {
    $dia_semana = date('N', strtotime($row['fecha'])); // 1 (para lunes) a 7 (para domingo)
    $hora = date('H:i', strtotime($row['hora']));
    if (!isset($pacientes_por_dia[$dia_semana])) {
        $pacientes_por_dia[$dia_semana] = [];
    }
    if (!isset($pacientes_por_hora[$dia_semana])) {
        $pacientes_por_hora[$dia_semana] = [];
    }
    $pacientes_por_dia[$dia_semana][] = $row;
    if (!isset($pacientes_por_hora[$dia_semana][$hora])) {
        $pacientes_por_hora[$dia_semana][$hora] = [];
    }
    $pacientes_por_hora[$dia_semana][$hora][] = $row;
}

// Obtener disponibilidad de horarios y filtrar los horarios ocupados por los pacientes
$disponibilidadProfesionales = [
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

// Filtrar los horarios ocupados por los pacientes y aplicar la lógica para kinesiología
$horarios_ocupados = [];
foreach ($disponibilidadProfesionales as $profesional => &$horariosDia) {
    foreach ($horariosDia as $dia => &$horarios) {
        foreach ($horarios as $key => $hora) {
            $dia_num = array_search($dia, array_keys($dias_semana)) + 1;
            if (isset($pacientes_por_hora[$dia_num][$hora])) {
                $horarios_ocupados[$dia][$hora] = true;
                unset($horarios[$key]);
            }
        }
    }
}

$colores_servicio = [
    'Kinesiología' => '#E2C6C2',
    'Terapia manual' => '#A6DA9C',
    'Drenaje Linfático' => '#BBFFFF',
    'Nutrición' => '#EE976A',
    'Traumatología' => '#A9B0F4'
];
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
    <title>Sante - Administrador</title>
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

        .profesionales_container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            position: relative;
            top: 20px;
        }

        .profesional {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }

        .imgProfesional img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .profesionalTexto span {
            display: block;
            font-weight: bold;
            color: #96B394;
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
            width: 130px;
            border-radius: 10px;
            background-color: #f4f4f4; /* Color de fondo por defecto */
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
    <script>
        function mostrarPrecioServicio() {
            var select = document.getElementById("servicio");
            var option = select.options[select.selectedIndex];
            var precio = option.getAttribute("data-precio");
            document.getElementById("precio_actual").value = precio;
        }

        function fetchPacientes(profesional) {
            // Mostrar todos los horarios y pacientes del profesional seleccionado
            var rows = document.querySelectorAll('.profesional');
            rows.forEach(function(row) {
                row.style.display = 'none';
            });
            var selectedProf = document.querySelector('.profesional[data-profesional="' + profesional + '"]');
            selectedProf.style.display = 'block';

            // Actualizar el encabezado de horarios disponibles
            var header = document.getElementById('horarios-header');
            header.innerText = "Horarios de " + profesional;
        }
    </script>
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
                    <a class="btn_horarios nav_link nav-link" style="color: #fff;" href="agendar_paciente.php">Agendar Paciente</a>
                </li>
                <li class="nav-item">
                    <button class="btn_horarios" onclick="document.getElementById('modificar-precio').style.display='block'">Modificar Precio</button>                    
                </li>
            </ul>
        </div>
    </div>
</nav>
</header>

<div class="content" style="color: #000 !important;">
    <h1 style="font-weight: 600; letter-spacing: 10px;">Bienvenido</h1>
    <hr>

    <div class="profesionales_container">
        <?php foreach ($profesionales as $prof): ?>
            <div class="profesional" data-profesional="<?php echo $prof; ?>" onclick="fetchPacientes('<?php echo $prof; ?>')">
                <div class="imgProfesional">
                    <img src="../img/<?php echo strtolower(str_replace(' ', '', $prof)); ?>.jpg" alt="<?php echo $prof; ?>">
                </div>
                <div class="profesionalTexto">
                    <span><?php echo $prof; ?></span>
                    <p><?php echo $prof; ?></p>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Pacientes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($dias_semana as $dia_num => $dia) {
                                if (isset($pacientes_por_hora[$dia_num])) {
                                    foreach ($pacientes_por_hora[$dia_num] as $hora => $pacientes) {
                                        echo "<tr>";
                                        echo "<td>$hora</td>";
                                        echo "<td>";
                                        foreach ($pacientes as $paciente) {
                                            if ($paciente['profesional'] == $prof) {
                                                echo "<div class=\"patient-card\" style=\"background-color: {$colores_servicio[$paciente['servicio']]};\">";
                                                echo "<p><strong>{$paciente['nombre']}</strong></p>";
                                                echo "<form method=\"POST\" action=\"administrador.php?id={$paciente['id']}\">";
                                                echo "<input type=\"hidden\" name=\"asistencia_id\" value=\"{$paciente['id']}\">";
                                                echo "<input type=\"checkbox\" name=\"asistio\" ".($paciente['asistio'] ? 'checked' : '')." onchange=\"this.form.submit()\">";
                                                echo "</form>";
                                                echo "</div>";
                                            }
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="modificar-precio" style="display:none" class="button-container">
        <form method="POST" action="administrador.php">
            <label for="servicio">Seleccione el servicio:</label>
            <select name="servicio" id="servicio" required onchange="mostrarPrecioServicio()">
                <?php foreach ($servicios as $servicio): ?>
                    <option value="<?php echo $servicio['servicio']; ?>" data-precio="<?php echo $servicio['precio']; ?>">
                        <?php echo $servicio['servicio']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="precio_actual">Precio actual:</label>
            <input type="text" id="precio_actual" readonly>
            <label for="nuevo_precio">Nuevo precio:</label>
            <input type="number" name="nuevo_precio" id="nuevo_precio" required>
            <button type="submit" name="modificar_precio">Modificar Precio</button>
        </form>
    </div>
</div>

</body>
</html>
