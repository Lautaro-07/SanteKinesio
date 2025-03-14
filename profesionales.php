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
    'Kinesiología Dermatofuncional' => ['Florencia', 'Constanza'],
    'Nutrición' => ['Maria Paz'],
    'Terapia Manual - RPG' => ['Lucia Foricher', 'Mariana'],
    'Traumatología' => ['Miriam'],
    'Drenaje Linfático' => ['Lucia Foricher', 'Florencia', 'Constanza'],
    'Kinesiología' => ['Lucia Foricher', 'Mauro Robert','Melina Thome', 'Gastón Olgiati', 'German Fernandez', 'Alejandro Perez'],
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

$precios_servicios = [
    'Drenaje Linfático' => 12000,
    'Kinesiología' => 11000,
    'Kinesiología Dermatofuncional' => 13000,
    'Nutrición' => 10000,
    'Terapia Manual - RPG' => 12500,
    'Traumatología' => 14000,
];

$precio_servicio = $precios_servicios[$servicio] ?? 'No disponible';

// Asociar imágenes a cada profesional
// $imagenes_profesionales = [
//     'Florencia' => 'img/florencia.jpg',
//     'Constanza' => 'img/constanza.jpg',
//     'Maria Paz' => 'img/maria.jpg',
//     'Lucia' => 'img/lucia.jpg',
//     'Mariana' => 'img/mariana.jpg',
//     'Miriam' => 'img/miriam.jpg',
//     'Mauro' => 'img/mauro.jpg',
//     'German Fernandez' => 'img/german.jpg',
//     'Gastón Olgiati' => 'img/gastonO.jpg',
//     'Melina Thome' => 'img/melina.jpg',
//     'Hernán López' => 'img/hernan.jpg',
//     'Alejandro Perez' => 'img/alejandro.jpg',
// ];
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
        background: url('img/profesionalesIMG.jpg') no-repeat center center/cover;
        position: relative;
        padding: 50px 20px;
        height: 72vh;
        
    }

    .profesional_container::before{
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.5);
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
        background-color: transparent; /* Quita el fondo oscuro */
        max-width: 600px; /* Ajusta el ancho total */
    }

    .btn_seleccionar{
        border: none;
        background-color: rgb(196, 196, 196);
        font-size: 22px;
        font-weight: 600;
        color: rgb(125, 129, 170);
        border-radius: 20px;
        width: 200px;
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
        top: 15%;
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

    @media (max-width: 876px) {
        
        .profesional_container{
        background: url('img/profesionalesIMG.jpg') no-repeat center center/cover;
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
        background: url('img/profesionalesIMG.jpg') no-repeat center center/cover;
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
            <h4>Valor de la sesion particular: $<?php echo htmlspecialchars($precio_servicio); ?></h4>
        </div>
        <div class="contenedor_profesionales">
            <?php
            if (empty($profesionales)) {
                echo $no_profesionales_msg;
                } else {
                foreach ($profesionales as $profesional) {
                    echo "
                 <div class='tarjeta_profesional'>
                    <form method='POST'>
                        <input type='hidden' name='profesional' value='$profesional'>
                        <button type='submit' class='btn_seleccionar'><p class='nombre_profesional'>Lic. $profesional</p></button>
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

    
    <!-- <div class="contenedor_profesionales">
        <?php
        // if (empty($profesionales)) {
        //     echo $no_profesionales_msg;
        // } else {
        //     foreach ($profesionales as $profesional) {
        //         echo "
        //         <div class='tarjeta_profesional'>
        //             <p class='nombre_profesional'>Lic. $profesional</p>
        //             <form method='POST'>
        //                 <input type='hidden' name='profesional' value='$profesional'>
        //                 <button type='submit' class='btn_seleccionar'>Seleccionar</button>
        //             </form>
        //         </div>";
        //     }
        // }
        ?>
    </div>
    </div> -->
    
</body>
</html>