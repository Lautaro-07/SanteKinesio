<?php
session_start([
    'cookie_lifetime' => 0, // La sesión se cierra cuando se cierra el navegador
]);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'sante');
$servicios_result = $conn->query("SELECT servicio, precio FROM precio_servicios");
$servicios = [];
while ($row = $servicios_result->fetch_assoc()) {
    $servicios[] = $row;
}
$conn->close();

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

$conn = new mysqli('localhost', 'root', '', 'sante');
$sql = "SELECT * FROM turnos WHERE fecha BETWEEN ? AND ? ORDER BY fecha";
$params = [$inicio_semana, $fin_semana];
$param_types = 'ss';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$pacientes = $stmt->get_result();

if ($pacientes === false) {
    echo "Error al obtener los pacientes: " . $conn->error;
    exit();
}

$profesionales_result = $conn->query("SELECT DISTINCT profesional FROM turnos");
$profesionales = [];
while ($row = $profesionales_result->fetch_assoc()) {
    $profesionales[] = $row['profesional'];
}

$pacientes_por_profesional = [];
while ($row = $pacientes->fetch_assoc()) {
    $profesional = $row['profesional'];
    if (!isset($pacientes_por_profesional[$profesional])) {
        $pacientes_por_profesional[$profesional] = [];
    }
    $pacientes_por_profesional[$profesional][] = $row;
}

$colores_servicio = [
    'Kinesiología' => '#E2C6C2',
    'Terapia Manual - RPG' => '#A6DA9C',
    'Drenaje Linfático' => '#BBFFFF',
    'Nutrición' => '#EE976A',
    'Traumatología' => '#A9B0F4'
];

$profesionales_info = [
    'Lucia Foricher' => 'lucia.jpg',
    'Hernan Lopez' => 'hernan.jpg',
    'Alejandro Perez Etcheber' => 'alejandro.jpg',
    'Melina Thome' => 'melina.jpg',
    'Dr. Mauro Robert' => 'mauro.jpg',
    'Gastón Olgiati' => 'gastonO.jpg',
    'Maria Paz' => 'maria.jpg',
    'Dr. German Fernandez' => 'german.jpg',
    'Dra. Mariana Ilari' => 'mariana.jpg',
    'Constanza Marinello' => 'constanza.jpg',
    'Florencia Goñi' => 'florencia.jpg',
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
            background-color:rgb(241, 235, 235);
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
            display: inline-block;
            width: 90px;
            font-size: 12px;
            background-color: #f4f4f4; /* Color de fondo por defecto */
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
                <ul class="ul_container navbar-nav ms-auto"> <!-- ms-auto empuja los elementos a la derecha -->
                    <li class="nav-item">
                        <a class="btn_horarios nav_link nav-link" style="color: #fff;" href="agendar_paciente.php">Agendar Paciente</a>
                    </li>
                    <li class="nav-item">
                        <button class="btn_horarios" onclick="document.getElementById('modificar-precio').style.display='block'">Modificar Precio</button>                    
                    </li>
                    <li class="nav-item">
                        <form method="GET" action="administrador.php">
                            <input type="hidden" name="semana" value="anterior">
                            <button type="submit">Semana Anterior</button>
                        </form>
                    </li>
                    <li class="nav-item">
                        <form method="GET" action="administrador.php">
                            <input type="hidden" name="semana" value="actual">
                            <button type="submit">Semana Actual</button>
                        </form>
                    </li>
                    <li class="nav-item">
                        <form method="GET" action="administrador.php">
                            <input type="hidden" name="semana" value="siguiente">
                            <button type="submit">Semana Siguiente</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<div class="content" style="color: #000 !important;">
    <h1 style="font-weight: 600; letter-spacing: 10px;">Bienvenido</h1>
    <hr>

    <section class="profesionales_container">
        <?php
        foreach ($profesionales_info as $nombre => $imagen) {
        ?>
        <div class="profesional">
            <div class="imgProfesional">
                <img src="../img/<?php echo $imagen; ?>" alt="<?php echo $nombre; ?>">
            </div>
            <div class="profesionalTexto">
                <span>Lic. en kinesiología</span>
                <p><?php echo strtoupper($nombre); ?></p>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Pacientes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php
                                if (isset($pacientes_por_profesional[$nombre])) {
                                    foreach ($pacientes_por_profesional[$nombre] as $paciente) {
                                        echo "<div class=\"patient-card\" style=\"background-color: {$colores_servicio[$paciente['servicio']]};\">";
                                        echo "<p><strong>{$paciente['nombre']}</strong></p>";
                                        echo "<p>{$paciente['fecha']} {$paciente['hora']}</p>";
                                        echo "</div>";
                                    }
                                } else {
                                    echo "<p>No hay pacientes registrados para este profesional.</p>";
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>
    </section>

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
