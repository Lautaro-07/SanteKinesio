<!DOCTYPE html>
<html lang="en">
<head>
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
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/santeLogo.jpg">
    <title>Sante</title>
    <style>
        body {
            background-color: #F6EBD5 !important;
            font-family: Arial, Helvetica, sans-serif;
        }

        .background-convenio {
            background-color: #96B394;
            width: 90%;
            height: auto;
            padding: 20px;
            text-align: center;
            margin: auto;
            position: relative;
            top: 10px;
            border-radius: 16px;
        }

        .background-convenio h3 {
            color: #fff;
            font-size: 30px;
            padding: 0;
            margin: 0;
            letter-spacing: 3px;
        }

        .convenio_title p {
            color: #628889;
            font-size: 17px;
            padding: 0;
            margin: auto;
            text-align: center;
            position: relative;
            top: 20px;
            width: 70%;
        }

        .convenios {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            width: 90%;
            margin: auto;
            position: relative;
            top: 50px;
        }

        .imagenes_convenio {
            width: 150px;
            height: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .imagenes_convenio img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            margin: 6px;
            position: relative;
            top: 10px;
        }

        .descripcion_convenios {
            display: flex;
            flex-wrap: wrap;
            width: 50%;
            height: auto;
            align-items: center;
            flex-direction: column;
            justify-content: center;
            margin-top: 20px;
            text-align: center;
            position: relative;
            right: 120px;
        }

        .descripcion_convenios div {
            color: #628889;
            background-color: #d9dad9;
            padding: 10px;
            width: 100%;
            border-radius: 16px;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .convenios {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .imagenes_convenio {
                width: 100%;
                display: flex;
                flex-direction: row;
                justify-content: center;
                gap: 10px;
            }

            .imagenes_convenio img {
                width: 100px;
                height: 100px;
            }

            .descripcion_convenios {
                width: 90%;
                position: relative;
                right: 0px;
            }
        }
    </style>
</head>
<body>
    <section class="convenio_title">
        <div class="background-convenio">
            <h3>CONVENIOS ACTIVOS</h3>
        </div>
        <p>En Santé, buscamos generar alianzas para poder ofrecer nuestros servicios con el propósito de mejorar el rendimiento y prevenir lesiones, garantizando una atención integral y personalizada.</p>
    </section>
    <section class="convenios">
        <div class="imagenes_convenio">
            <img style="width: 150px; height: 120px;" src="../img/palihue.jpg" alt="">
            <img src="../img/osecac.jpg" alt="">
            <img src="../img/cooperativa.png" alt="">
        </div>
        <div class="descripcion_convenios">
            <div>
                <ul>
                    <li>Atención kinésica con obra social</li>
                    <li>Atención nutricional</li>
                    <li>Charlas preventivas</li>
                    <li>Evaluación de factores de riesgo para las jugadoras</li>
                </ul>
            </div>
            <div>
                <ul>
                    <li>Atención kinésica y nutricional solo presentando la orden médica y autorización</li>
                </ul>
            </div>
            <div>
                <ul>
                    <li>10% de descuento en terapias particulares: Terapia Manual, nutrición, drenaje linfático, taller de movimiento saludable</li>
                    <li>Atención kinésica con obra social</li>
                </ul>
            </div>
        </div>
    </section>
</body>
</html>
