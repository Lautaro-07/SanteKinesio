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

// Variables para búsqueda
$busqueda_nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$busqueda_profesional = isset($_GET['busqueda_profesional']) ? $_GET['busqueda_profesional'] : '';

// Inicializar la consulta para obtener pacientes
$conn = new mysqli('localhost', 'root', '', 'sante'); // Definir la conexión a la base de datos
$sql = "SELECT * FROM turnos WHERE 1=1";
$params = [];
$param_types = '';

// Agregar filtros de búsqueda por nombre y profesional
if ($busqueda_nombre != '') {
    $sql .= " AND nombre LIKE ?";
    $params[] = "%$busqueda_nombre%";
    $param_types .= 's';
}

if ($busqueda_profesional != '') {
    $sql .= " AND profesional = ?";
    $params[] = $busqueda_profesional;
    $param_types .= 's';
}

// Filtrar por el mes actual
$sql .= " AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";

$sql .= " ORDER BY fecha";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
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

// Filtrar los horarios ocupados por los pacientes y aplicar la lógica para kinesiología
$horarios_ocupados = [];
if ($busqueda_profesional != '' && isset($disponibilidadProfesionales[$busqueda_profesional])) {
    foreach ($disponibilidadProfesionales[$busqueda_profesional] as $dia => &$horarios) {
        foreach ($horarios as $key => $hora) {
            $dia_num = array_search($dia, array_keys($dias_semana)) + 1;
            if (isset($pacientes_por_hora[$dia_num][$hora])) {
                $horarios_ocupados[$dia][$hora] = true;
                unset($horarios[$key]);
            }
        }
    }
};

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
            vertical-align: top; /* Asegura que el contenido se muestre en la parte superior */
        }

        th {
            background-color: #F6EBD5;
            color:rgb(31, 31, 31);
        }

        .patient-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin: 5px auto;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-block;
            min-width: 110px;
        }
        .patient-card.yellow {
            background-color: yellow;
        }

        .patient-card:hover {
            background-color: #e0e0e0;
        }

        .highlight {
            background-color: yellow;
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

        .navbar-collapse {
            justify-content: flex-end; /* Asegura que los enlaces se alineen a la derecha */
        }

        .navbar-nav {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 15px;
        }

        .btn_horarios {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 8px;
            cursor: pointer;
            border-radius: 10px !important;
        }

        .btn_horarios:hover {
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

            .btn_horarios {
                position: relative; 
                background-color: #F6EBD5;
                width: 200px;
                padding: 8px;
                border-radius: 10px !important;
                color: #fff !important;
                text-align: center;
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
    <script>
        function mostrarPrecioServicio() {
            var select = document.getElementById("servicio");
            var option = select.options[select.selectedIndex];
            var precio = option.getAttribute("data-precio");
            document.getElementById("precio_actual").value = precio;
        }

        function fetchPacientes(profesional) {
            var rows = document.querySelectorAll('.patient-card');
            rows.forEach(function(row) {
                if (profesional === "" || row.getAttribute('data-profesional') === profesional) {
                    row.style.display = 'inline-block';
                } else {
                    row.style.display = 'none';
                }
            });

            var horarios = document.querySelectorAll('.horarios-disponibles div, .horarios-ocupados div');
            horarios.forEach(function(div) {
                if (profesional === "" || div.getAttribute('data-profesional') === profesional) {
                    div.style.display = 'block';
                } else {
                    div.style.display = 'none';
                }
            });
            
            // Actualizar el encabezado de horarios disponibles
            var header = document.getElementById('horarios-header');
            if (profesional === "") {
                header.innerText = "Horarios Disponibles";
            } else {
                header.innerText = "Horarios de " + profesional;
            }
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
    
    <form method="GET" action="administrador.php">
        <input type="text" id="nombre" name="nombre" placeholder="Buscar por nombre" value="<?php echo $busqueda_nombre; ?>">
        <select id="busqueda_profesional" name="busqueda_profesional" onchange="fetchPacientes(this.value)">
            <option value="">-- Todos los Profesionales --</option>
            <?php foreach ($profesionales as $prof): ?>
                <option value="<?php echo $prof; ?>" <?php if ($busqueda_profesional == $prof) echo 'selected'; ?>>
                    <?php echo $prof; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn_horarios" type="submit">Buscar</button>
    </form>

    <div class="table-container" id="pacientes-table">
        <table>
            <thead>
                <tr>
                    <th id="horarios-header">Horarios Disponibles</th>
                    <th>Horarios Ocupados</th>
                    <th>Lunes</th>
                    <th>Martes</th>
                    <th>Miércoles</th>
                    <th>Jueves</th>
                    <th>Viernes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="horarios-disponibles">
                        <?php if (isset($disponibilidadProfesionales[$busqueda_profesional])): ?>
                            <?php foreach ($disponibilidadProfesionales[$busqueda_profesional] as $dia => $horarios): ?>
                                <div data-profesional="<?php echo $busqueda_profesional; ?>">
                                    <strong><?php echo $dias_semana_espanol[$dia]; ?></strong>
                                    <ul>
                                        <?php foreach ($horarios as $horario): ?>
                                            <?php
                                            $dia_num = array_search($dia, array_keys($dias_semana)) + 1;
                                            $hora_ocupada = false;

                                            // Verificar si el horario está ocupado
                                            if (isset($pacientes_por_hora[$dia_num][$horario])) {
                                                $hora_ocupada = true;
                                            }
                                            ?>
                                            <?php if (!$hora_ocupada): ?>
                                                <li><?php echo $horario; ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Seleccione un profesional para ver los horarios disponibles.</p>
                        <?php endif; ?>
                    </td>
                    <td class="horarios-ocupados">
                        <?php if (isset($pacientes_por_hora)): ?>
                            <?php foreach ($pacientes_por_hora as $dia_num => $horas): ?>
                                <div data-profesional="<?php echo $busqueda_profesional; ?>">
                                    <strong><?php echo $dias_semana_espanol[$dias_semana[$dia_num]]; ?></strong>
                                    <ul>
                                        <?php foreach ($horas as $hora => $pacientes): ?>
                                            <li><?php echo $hora; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay horarios ocupados.</p>
                        <?php endif; ?>
                    </td>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <td>
                            <?php if (isset($pacientes_por_dia[$i])): ?>
                                <?php foreach ($pacientes_por_dia[$i] as $paciente): ?>
                                    <div class="patient-card" style="background-color: <?php echo $colores_servicio[$paciente['servicio']] ?? '#FFFFFF'; ?>;" onclick="location.href='diagnostico.php?id=<?php echo $paciente['id']; ?>'">
                                        <p><strong><?php echo htmlspecialchars($paciente['nombre']); ?></strong></p>
                                        <p><?php echo htmlspecialchars($paciente['fecha']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No hay pacientes programados.</p>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            </tbody>
        </table>
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
