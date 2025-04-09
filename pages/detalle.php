<?php
$profesionales = [
    'Hernan Lopez' => [
        'nombre' => 'Hernan Lopez',
        'imagen' => '../img/hernan.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Área de abordaje: traumatología y neurorehabilitación en adultos mayores.'
        ]
    ],
    'Alejandro Perez Etcheber' => [
        'nombre' => 'Alejandro Perez Etcheber',
        'imagen' => '../img/alejandro.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Curso de abordaje integral del adulto mayor',
            'Curso de kinefilaxia, flexibilidad y movilidad.'
        ]
    ],
    'Melina Thome' => [
        'nombre' => 'Melina Thome',
        'imagen' => '../img/melina.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Área de abordaje: rehabilitación traumatológica de adultos y adultos mayores.'
        ]
    ],
    'Dr. Mauro Robert' => [
        'nombre' => 'Dr. Mauro Robert',
        'imagen' => '../img/mauro.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Rehabilitación traumatológica deportiva',
            'Método Busquet en formación',
            'MEP sports',
            'Tapping neuromuscular',
            'Punción seca'
        ]
    ],
    'Gastón Olgiati' => [
        'nombre' => 'Gastón Olgiati',
        'imagen' => '../img/gastonO.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Método Busquet en formación',
            'Tapping neuromuscular',
            'MEP Sports',
            'Vendaje deportivo',
            'Reprogramación propioceptiva'
        ]
    ],
    'Dr. German Fernandez' => [
        'nombre' => 'Dr. German Fernandez',
        'imagen' => '../img/german.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Orientación: rehabilitación traumatológica y deportiva, atención de adultos mayores',
            'Cursos:',
            'Abordaje integral del adulto mayor - AAK',
            'Gimnasia postural',
            'Reprogramación propioceptiva Busquet',
            'Actualización en tendinopatías'
        ]
    ],
    'Mariana Ilari' => [
        'nombre' => 'Mariana Ilari',
        'imagen' => '../img/mariana.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Método Busquet, reprogramación propioceptiva',
            'Rehabilitación traumatológica y postural',
            'Concepto Mulligan',
            'TMR'
        ]
    ],
    'Leila Heguilein' => [
        'nombre' => 'Leila Heguilein',
        'imagen' => '../img/leila.jpg',
        'descripciones' => [
            'Licenciada en psicología',
            'Formacion en profesorado en psicología ',
            'Actualmente trabajando en el dispositivo de hospital de día y en el dispositivo de adicciones de la clínica privada bahiense'
        ]
    ],
    'Lucia Foricher' => [
        'nombre' => 'Lucia Foricher Castellon',
        'imagen' => '../img/lucia.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP (2018)',
            'Formada en:',
            'TMR-Técnicas Metaméricas Reflejas, formación completa',
            'Método Busquet, formación completa y reprogramación propioceptiva',
            'Métodos globales de corrección postural',
            'Mulligan Concept',
            'Punción seca',
            'Drenaje linfático manual, método Leduc'
        ]
    ],
    'Constanza Marinello' => [
        'nombre' => 'Constanza Marinello',
        'imagen' => '../img/constanza.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Posgrado en kinesiología dermatofuncional y estética',
            'Curso superior de Flebología y Linfología, Método Leduc'
        ]
    ],
    'Miriam Rossello' => [
        'nombre' => 'Miriam Rossello',
        'imagen' => '../img/Miriam.jpg',
        'descripciones' => [
            'Lic. en Nutrición. F.H. Barcelo. 2020',
            'Curso de Plant Besed Diet y Deporte. Sociedad Argentina de Medicina de Estilo de Vida. 2021',
            'Posgrado "Nutrición Basada en Plantas. Salud, ética y soberanía alimentaria". UNR. 2022',
            'Diplomatura en Nutrición Digesto-Absortiva. AADYND. 2023',
            'Curso de actualización de Posgrado Nutrición Vegetariana y Vegana. UNLP. 2023',
            'Curso de Nutrición Funcional Integrativa. Nutrinfo. 2024'
        ]
    ],
    'Maria Paz' => [
        'nombre' => 'Maria Paz',
        'imagen' => '../img/maria.jpg',
        'descripciones' => [
            'Lic. en Nutrición. F.H. Barcelo. 2020',
            'Curso de Plant Besed Diet y Deporte. Sociedad Argentina de Medicina de Estilo de Vida. 2021',
            'Posgrado "Nutrición Basada en Plantas. Salud, ética y soberanía alimentaria". UNR. 2022',
            'Diplomatura en Nutrición Digesto-Absortiva. AADYND. 2023',
            'Curso de actualización de Posgrado Nutrición Vegetariana y Vegana. UNLP. 2023',
            'Curso de Nutrición Funcional Integrativa. Nutrinfo. 2024'
        ]
    ],
    'Florencia Goñi' => [
        'nombre' => 'Florencia Goñi',
        'imagen' => '../img/florencia.jpg',
        'descripciones' => [
            'Lic. en kinesiología y fisiatría, UCALP',
            'Posgrado en kinesiología dermatofuncional y estética',
            'Curso superior de Flebología y Linfología, Método Leduc'
        ]
    ]
];

$nombre = urldecode($_GET['nombre'] ?? '');

if (array_key_exists($nombre, $profesionales)) {
    $profesional = $profesionales[$nombre];
} else {
    $profesional = null;
}
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
        <?php if ($profesional): ?>
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
        <?php else: ?>
            <div class="detalle-container">
                <h2>Profesional no encontrado</h2>
                <p>El profesional que estás buscando no existe o no está disponible.</p>
            </div>
        <?php endif; ?>
    </section>
</body>
</html>