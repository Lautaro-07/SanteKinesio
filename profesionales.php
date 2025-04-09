<?php
session_start();

// Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sante";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$servicio = $_SESSION['servicio'] ?? '';
$profesionales_por_servicio = [
    'Nutrición' => ['Maria Paz'],
    'Terapia Manual - RPG' => ['Lucia Foricher', 'Mariana Ilari'],
    'Traumatología' => ['Miriam Rossello'],
    'Drenaje Linfático' => ['Florencia Goñi', 'Constanza Marinello'],
    'Kinesiología' => ['Lucia Foricher', 'Alejandro Perez', 'Mauro Robert', 'Gastón Olgiati', 'German Fernandez','Melina Thome', 'Hernan Lopez'],
    'Psicología' => ['Leila Heguilein'],
];

$profesionales = $profesionales_por_servicio[$servicio] ?? [];

if (empty($profesionales)) {
    $no_profesionales_msg = "
    <div class='falta_profesionalesContainer'>
        <p class='falta_profesionales'>No hay profesionales disponibles para el servicio de $servicio actualmente.</p>
        <hr>
        <span class='falta_profesionales'>Contacta con nuestra sede</span>
        <a href='https://wa.me/5492915347980' class='whatsapp-button' target='_blank'>
            <i style='color: #fff;' class='fa-solid fa-phone'></i>
        </a>
    </div>
    ";
} else {
    $no_profesionales_msg = "";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['profesional'] = $_POST['profesional'];
    header('Location: fecha.php');
    exit();
}

// Obtener el precio del servicio desde la base de datos
$precio_servicio = 'No disponible';
if ($servicio !== '') {
    $stmt = $conn->prepare("SELECT precio FROM precio_servicios WHERE servicio = ?");
    if ($stmt) {
        $stmt->bind_param("s", $servicio);
        $stmt->execute();
        $stmt->bind_result($precio);
        if ($stmt->fetch()) {
            $precio_servicio = $precio;
        }
        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
}

// Lista de servicios disponibles y sus respectivas imágenes de fondo
$servicios_imagenes = [
    'Kinesiología Dermatofuncional' => 'img/kinesiologiadermatofuncional.jpg',
    'Nutrición' => 'img/nutricion.jpg',
    'Terapia Manual - RPG' => 'img/drenajeLinfatico.jpg',
    'Traumatología' => 'img/drenajeLinfatico.jpg',
    'Drenaje Linfático' => 'img/drenajeLinfatico.jpg',
    'Kinesiología' => 'img/profesionalesIMG.jpg',
    'Psicología' => 'img/nutricion.jpg',	
];
// Asignar la imagen de fondo según el servicio seleccionado
$imagen_fondo = isset($servicios_imagenes[$servicio]) ? $servicios_imagenes[$servicio] : 'img/default.jpg';

// Descripciones de los servicios
$descripciones_servicios = [
    'Kinesiología' => 'En Santé trabajamos bajo la modalidad de rehabilitación kinésica funcional. Es una sesión grupal de hasta 4 pacientes por hora - Trabajamos con obras sociales, consultanos por las diferenciales',
    'Nutrición' => 'La consulta tiene una duración de 1 hora, donde se hace la evaluación general de los hábitos y estado general. Nuestra nutricionista se encuentra formada en alimentación basada en plantas y alimentación integral',
    'Drenaje Linfático' => 'Es una tecnica manual suave de masaje no invasiva, realizada por kinesiologos, que ayuda a mejorar el flujo linfatico',
    'Terapia Manual - RPG' => 'La terapia manual consta de una sesión individual en camilla con el profesional donde se realiza una evaluación global con el fin de encontrar la causa del dolor. Tambien puede utilizarse para como herramienta preventiva, buscando y trabajando sorbe los factores de riesgo de cada persona.',
    'Kinesiología Dermatofuncional' => 'Mejora la salud y estética de la piel con tratamientos especializados.',
    'Traumatología' => 'Diagnóstico y tratamiento de lesiones musculoesqueléticas.',
    'Psicología' => 'La psicología es una disciplina que estudia los procesos mentales y el comportamiento humano.',
];

$descripcion_servicio = $descripciones_servicios[$servicio] ?? 'Descripción no disponible.';

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesionales</title>
    <link rel="stylesheet" href="css/estilo.css">
    <script src="bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="icon" href="img/santeLogo.jpg">
    <style>

        .footer_container{
            display: flex; 
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 75px; 
            margin-top: 10px; 
            color: #000000;
        }

      .titulo_servicio {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin: 20px 0;
        color: #333;
        z-index: 10 !important;
        padding: 10px;
        background-color: #fff;
        border: 2px solid  #9DBC98;
        border-radius: 20px;
        width: 90%;
        margin: auto;
        position: relative;
        bottom: 20px;
    }

    .profesional_container{
        background: url('<?php echo htmlspecialchars($imagen_fondo); ?>') no-repeat center center/cover;
        position: relative;
        padding: 50px 20px;
        height: auto;
        min-height: 80vh;
    }

    .profesional_container::before{
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.3);
        z-index: 0;
    }

    .contenedor_profesionales{
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        width: 100%;
        margin: auto;
        z-index: 20 !important;
        position: relative;
        background-color: transparent;
        max-width: 600px;
        top: 40px;
    }

    .btn_seleccionar{
        border: none;
        background-color: rgb(196, 196, 196);
        font-size: 22px;
        font-weight: 600;
        color: rgb(125, 129, 170);
        border-radius: 20px;
        width: 270px;
        height: auto;
        padding: 0px;
        margin: 10px;
        text-align: center;
        z-index: 30 !important;
        cursor: pointer;
        position: relative;
        right: 0px;
    }

    .btn_seleccionar:hover{
        transform: scale(1.05);
        transition: .5s;
    }

    .precio_servicio{
        margin: auto;
        position: absolute;
        top: 12.5%;
        left: 6%;
    }
    
    .precio_servicio h4{
        border: 2px solid #9DBC98;
        background-color: #fff;
        width: 140px;
        height: 40px;
        padding: 10px;
        text-align: center;
        border-radius: 10px;
    }

    .descripcion_servicio {
        text-align: center;
        font-size: 15px;
        font-weight: 600;
        word-spacing: 2px;
        padding: 11px;
        background-color: #fff;
        border-radius: 100px;
        color:rgb(51, 50, 50);
        border: 2px solid #9DBC98;
        max-width: 600px;
        margin: auto;
        z-index: 10 !important;
        position: relative;
        top: 15px;
    }

    .footer_container{
        position: relative;
        top: 40px;
    }

    @media (max-width: 876px) {
        
        .profesional_container{
        background: url('<?php echo htmlspecialchars($imagen_fondo); ?>') no-repeat center center/cover;
        position: relative;
        padding: 50px 20px;
        height: 85vh;
        }

        .precio_servicio{
            margin: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            top: 0;
            left: 0;
        }

    }

    @media (max-width: 495px) {
        
        .profesional_container{
        background: url('<?php echo htmlspecialchars($imagen_fondo); ?>') no-repeat center center/cover;
        position: relative;
        padding: 50px 20px;
        height: auto;
        }

    }

    </style>
</head>

<body>
    <header>
        <nav class="nav_container">
            <div class="logo_container">
                <img src="img/santeLogo.jpg" alt="Logo" class="logo">
            </div>
        </nav>
    </header>
    <section class="profesional_container">
        <h1 class='titulo_servicio'><?php echo htmlspecialchars($servicio); ?></h1>
        <div class="precio_servicio">
            <h4>Valor de la sesión particular: $<?php echo htmlspecialchars($precio_servicio); ?></h4>
        </div>
        <div class="descripcion_servicio">
            <p><?php echo htmlspecialchars($descripcion_servicio); ?></p>
        </div>
        <div class="contenedor_profesionales">
            <?php
            if (empty($profesionales)) {
                echo $no_profesionales_msg;
            } else {
                foreach ($profesionales as $profesional) {
                    // Obtener un horario de disponibilidad del profesional
                    $stmt = $conn->prepare("SELECT hora_inicio, hora_fin FROM disponibilidad WHERE nombre_profesional = ? LIMIT 1");
                    if ($stmt) {
                        $stmt->bind_param("s", $profesional);
                        $stmt->execute();
                        $stmt->bind_result($hora_inicio, $hora_fin);
                        $stmt->fetch();
                        $stmt->close();
                    } else {
                        $hora_inicio = $hora_fin = 'No disponible';
                    }

                    echo "
                        <div class='tarjeta_profesional'>
                            <form method='POST'>
                                <input type='hidden' name='profesional' value='$profesional'>
                                <button type='submit' class='btn_seleccionar'>
                                    <p class='nombre_profesional'>Lic. $profesional</p>
                                    <p class='horarios_profesional'>$hora_inicio - $hora_fin</p>
                                </button>
                                <hr>
                            </form>
                        </div>";
                }
            }
            ?>
        </div>
        <footer class="footer_container">
        <div class="iconos_contianer">
            <a href="https://www.instagram.com/santecentrodesalud/"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://wa.me/5492915204351"><i class="fa-solid fa-phone"></i></a>
            <a href=""><i class="fa-solid fa-envelope"></i></a>
        </div>
        </footer>
    </section>
</body>
</html>
