<!DOCTYPE html>
<html lang="es">
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/santeLogo.jpg">
    <title>Sante</title>
    <style>
        body {
            background-color: #F6EBD5;
            font-family: 'Poppins', sans-serif;
        }
        .background-convenio{
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

        .background-convenio h3{
            color: #fff;
            font-size: 30px;
            padding: 0;
            margin: 0;
            letter-spacing: 3px;
        }

        .convenio_title p{
            color:#628889;
            font-size: 17px;
            padding: 0;
            margin: auto;
            text-align: center;
            position: relative;
            top: 20px;
            width: 70%;
        }

        .profesionales_container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

        button{
            border: none;
            background-color: transparent;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>
<body>
    <section class="convenio_title">
        <div class="background-convenio">
            <h3>¿QUIÉNES SOMOS?</h3>
        </div>
        <p>Te presentamos nuestro equipo de profesionales</p>
    </section>
    <section class="profesionales_container">
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Lucia Foricher'">
                <div class="imgProfesional">
                    <img src="../img/lucia.jpg" alt="Lucia Foricher Castellon">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>LUCIA FORICHER CASTELLON</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Hernan Lopez'">
                <div class="imgProfesional">
                    <img src="../img/hernan.jpg" alt="Hernan Lopez">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>HERNAN LOPEZ</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Alejandro Perez Etcheber'">
                <div class="imgProfesional">
                    <img src="../img/alejandro.jpg" alt="Alejandro Perez Etcheber">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>ALEJANDRO PEREZ ETCHEBER</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Melina Thome'">
                <div class="imgProfesional">
                    <img src="../img/melina.jpg" alt="Melina Thome">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>MELINA THOME</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Dr. Mauro Robert'">
                <div class="imgProfesional">
                    <img src="../img/mauro.jpg" alt="Dr. Mauro Robert">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>DR. MAURO ROBERT</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Gastón Olgiati'">
                <div class="imgProfesional">
                    <img src="../img/gastonO.jpg" alt="Gastón Olgiati">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>GASTÓN OLGIATI</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Maria Paz'">
                <div class="imgProfesional">
                    <img src="../img/maria.jpg" alt="Maria Paz">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. En Nutrición</span>
                    <p>Maria Paz</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Dr. German Fernandez'">
                <div class="imgProfesional">
                    <img src="../img/german.jpg" alt="Dr. German Fernandez">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>DR. GERMAN FERNANDEZ</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Mariana Ilari'">
                <div class="imgProfesional">
                    <img src="../img/mariana.jpg" alt="Mariana Ilari">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>MARIANA ILARI</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Leila Heguilein'">
                <div class="imgProfesional">
                    <img src="../img/Leila.jpg" alt="Leila Heguilein">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en Psicología</span>
                    <p>Leila Heguilein</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Constanza Marinello'">
                <div class="imgProfesional">
                    <img src="../img/constanza.jpg" alt="Constanza Marinello">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>CONSTANZA MARINELLO</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Florencia Goñi'">
                <div class="imgProfesional">
                    <img src="../img/florencia.jpg" alt="Florencia Goñi">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en kinesiología</span>
                    <p>FLORENCIA GOÑI</p>
                </div>
            </button>
        </div>
        <div class="profesional">
            <button onclick="location.href='detalle.php?nombre=Miriam Rossello'">
                <div class="imgProfesional">
                    <img src="../img/miriam.jpg" alt="Miriam Rossello">
                </div>
                <div class="profesionalTexto">
                    <span>Lic. en Nutrición</span>
                    <p>Miriam Rossello</p>
                </div>
            </button>
        </div>
    </section>
</body>
</html>