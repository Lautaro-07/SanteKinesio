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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio = $_POST['servicio'];
    $_SESSION['servicio'] = $servicio;
    header('Location: profesionales.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sante - Horario</title>
    <link rel="stylesheet" href="css/estilo.css">
    <script src="bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="icon" href="img/santeLogo.jpg">
    <style>
        /* Estilo para la sección con imagen de fondo */
    
        .footer_container{
        display: flex; 
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 75px;  
        margin-top: 40px;
        }

    
    .servicio_title {
    font-size: 30px;
    font-weight: bold;
    margin: 20px auto;
    color: #fff;
    background-color: #9DBC98;
    width: 200px;
    height: 70px;
    text-align: center;
    padding: 15px;
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;  /* Agregado para que z-index funcione */
    z-index: 10;  /* Asegurar que esté sobre otras capas */
    }

.servicios_contenedor {
    background: url('img/imgSedeSante.jpg') no-repeat center center/cover;
    position: relative;
    padding: 50px 20px;
    height: auto;
}

.servicios_contenedor::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.5);
    z-index: 0;
}

.btn{
    z-index: 100 !important; 
    background-color: aliceblue;
    color:rgb(126, 122, 122);
    border: 5px solid #9DBC98;
    border-radius: 20px;
    width: 200px;
    height: 70px;
    padding: 9px;
    font-size: 20px;
    font-weight: 600;
}

.div_botones{
    display: flex;
    flex-direction: row;
    justify-content: space-around;
    width: 60%;
    flex-wrap: wrap;
    margin: auto;
    position: relative;
    top:20px;
}

.form_botones{
    margin: 10px;
}



.btn:hover{
    color: rgb(126, 122, 122) !important;
    transition: .5s;
    transform: scale(1.05);
}

/* Responsive */
@media (max-width: 768px) {
    .div_botones{
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    width: 60%;
    flex-wrap: wrap;
    margin: auto;
    position: relative;
    top: 10px;
    }

    .card {
        width: 90%;
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
    <section class="servicios_contenedor">
        <h2 class="servicio_title">SERVICIOS</h2>
        <div class="container">
            <?php
            $servicios = [
                'Drenaje Linfático',
                'Kinesiología',
                'Kinesiología Dermatofuncional',
                'Nutrición',
                'Terapia Manual - RPG',
                'Traumatología'
            ];

            echo '<div class="div_botones">';
            foreach ($servicios as $servicio) {
                echo "
                <form method='POST' class='form_botones'>
                    <input type='hidden' name='servicio' value='{$servicio}'>
                    <button type='submit' class='btn'>{$servicio}</button>
                </form>";
            }
            echo '</div>';
            ?>
        </div>
        <div>
        <footer class="footer_container">
            <div class="iconos_contianer">
                <a href="https://www.instagram.com/santecentrodesalud/"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://wa.me/5492915204351"><i class="fa-solid fa-phone"></i></a>
                <a href=""><i class="fa-solid fa-envelope"></i></a>
            </div>
        </footer>
        </div>
    </section>
</body>
</html>