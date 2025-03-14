<?php
$profesionales = [
    'lucia' => [
        'nombre' => 'Lucia Foricher Castellon',
        'imagen' => '../img/lucia.jpg',
        'descripciones' => [
            'Licenciada en kinesiología con amplia experiencia en terapias físicas y rehabilitación.',
            'TMR-TECNICAS MATEMATICAS REFELEJADAS. formacion completa.',
            'Método busquet, formacion completa.',
            'Metodos globales de correccion postural.'
        ]
    ],
    'alejandro' => [
        'nombre' => 'Alejandro Perez Etchever',
        'imagen' => '../img/alejandro.jpg',
        'descripciones' => [
            'Especialista en kinesiología deportiva y rehabilitación de lesiones.'
        ]
    ],
    'constanza' => [
        'nombre' => 'Constanza Marinello',
        'imagen' => '../img/constanza.jpg',
        'descripciones' => [
            'Licenciada en kinesiología con enfoque en tratamientos posturales y de columna.'
        ]
    ],
    'florencia' => [
        'nombre' => 'Florencia Goñi',
        'imagen' => '../img/florencia.jpg',
        'descripciones' => [
            'Especialista en kinesiología pediátrica y desarrollo motor infantil.'
        ]
    ],
    'gaston' => [
        'nombre' => 'Gastón Olgiati',
        'imagen' => '../img/GastonO.jpg',
        'descripciones' => [
            'Licenciado en kinesiología con experiencia en rehabilitación neurológica.'
        ]
    ],
    'maria' => [
        'nombre' => 'Maria Paz Ruilopez',
        'imagen' => '../img/maria.jpg',
        'descripciones' => [
            'Licenciada en nutrición, especialista en dietas personalizadas y control de peso.'
        ]
    ],
    'melina' => [
        'nombre' => 'Melina Thome',
        'imagen' => '../img/melina.jpg',
        'descripciones' => [
            'Kinesióloga con experiencia en rehabilitación deportiva y terapias manuales.'
        ]
    ],
    'miriam' => [
        'nombre' => 'Dra. Miriam Rossello',
        'imagen' => '../img/miriam.jpg',
        'descripciones' => [
            'Especialista en miembro superior con amplia experiencia en cirugía y rehabilitación.'
        ]
    ]
];

$nombre = $_GET['nombre'];
$profesional = $profesionales[$nombre];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/page.css">
    <link rel="stylesheet" href="bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/santeLogo.jpg">
    <title>Detalle Profesional</title>
    <style>
        body {
            background-color: #F6EBD5;
            font-family: 'Poppins', sans-serif;
        }

        .profesionalContainer{
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            width: 90%;
            margin: auto;
            align-content: center;
            position: relative;
            top: 120px;
        }

        .detalle-container {
            max-width: 600px;
            margin: auto;
            background-color: #d9dad9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .detalle-container h2 {
            color: #96B394;
            position: relative;
            bottom: 10px;
            font-size: 23px;
        }
        .detalle-container p {
            color: #628889;
        }

        .imgProfesional{
            width: 250px;
            height: 350px;
        }

        .imgProfesional img{
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        ul li{
            font-size: 18px;
            margin: 5px;
        }

        @media (max-width: 1007px) {
            .imgProfesional{
            width: 250px;
            height: 350px;
        }

        .imgProfesional img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: relative;
            bottom: 50px;
            border-radius: 16px;
        }
        }
    </style>
</head>
<body>
    <section class="profesionalContainer">
    <div class="imgProfesional">
        <img src="<?php echo $profesional['imagen']; ?>" alt="<?php echo $profesional['nombre']; ?>">
    </div>
    <div class="detalle-container">
        <h2><?php echo $profesional['nombre']; ?></h2>
        <ul>
            <?php foreach ($profesional['descripciones'] as $descripcion) : ?>
                <li><?php echo $descripcion; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    </section>
</body>
</html>