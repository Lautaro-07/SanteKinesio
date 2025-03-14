<?php
session_start([
    'cookie_lifetime' => 0, // La sesión se cierra cuando se cierra el navegador
]);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Verificar si se ha proporcionado un ID de paciente
if (!isset($_GET['id'])) {
    echo "ID de paciente no proporcionado.";
    exit();
}

$id = $_GET['id'];

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'sante');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos del paciente
$sql = "SELECT * FROM turnos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Paciente no encontrado.";
    exit();
}

$paciente = $result->fetch_assoc();

// Manejar el envío del formulario de diagnóstico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_diagnostico'])) {
    $diagnostico = $_POST['diagnostico'];

    $sql_update = "UPDATE turnos SET diagnostico = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param('si', $diagnostico, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Diagnóstico guardado correctamente.'); window.location.href = 'diagnostico.php?id=$id';</script>";
    } else {
        echo "Error al guardar el diagnóstico: " . $conn->error;
    }
}

// Manejar la carga de las imágenes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_imagenes'])) {
    if (isset($_FILES['imagenes_diagnostico']) && count($_FILES['imagenes_diagnostico']['error']) > 0) {
        for ($i = 0; $i < count($_FILES['imagenes_diagnostico']['name']); $i++) {
            if ($_FILES['imagenes_diagnostico']['error'][$i] === UPLOAD_ERR_OK) {
                $imagen_diagnostico = file_get_contents($_FILES['imagenes_diagnostico']['tmp_name'][$i]);

                $sql_insert = "INSERT INTO imagenes_diagnostico (turno_id, imagen) VALUES (?, ?)";
                $stmt = $conn->prepare($sql_insert);
                $stmt->bind_param('ib', $id, $imagen_diagnostico);
                $stmt->send_long_data(1, $imagen_diagnostico);

                if (!$stmt->execute()) {
                    echo "Error al guardar la imagen: " . $conn->error;
                }
            }
        }
        echo "<script>alert('Imágenes guardadas correctamente.'); window.location.href = 'diagnostico.php?id=$id';</script>";
    } else {
        echo "Error al cargar las imágenes.";
    }
}

// Manejar la eliminación de la imagen
if (isset($_POST['eliminar_imagen'])) {
    $imagen_id = $_POST['imagen_id'];
    $sql_delete = "DELETE FROM imagenes_diagnostico WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param('i', $imagen_id);

    if ($stmt->execute()) {
        echo "<script>alert('Imagen eliminada correctamente.'); window.location.href = 'diagnostico.php?id=$id';</script>";
    } else {
        echo "Error al eliminar la imagen: " . $conn->error;
    }
}

// Obtener las imágenes del diagnóstico
$sql_imagenes = "SELECT * FROM imagenes_diagnostico WHERE turno_id = ?";
$stmt_imagenes = $conn->prepare($sql_imagenes);
$stmt_imagenes->bind_param('i', $id);
$stmt_imagenes->execute();
$imagenes_result = $stmt_imagenes->get_result();
$imagenes = $imagenes_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="../bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <link rel="icon" href="../img/santeLogo.jpg">
    <title>Diagnóstico del Paciente</title>
    <style>
        .nav_container {
    background-color: #F6EBD5 !important;
    margin: 0;
    padding: 0 !important;
}

.logo_container {
    width: 70px;
    height: 80px;
    position: relative;
    left: 20px;
}

.logo {
    width: 80px;
    height: 100%;
}

        .img-thumbnail {
            max-width: 150px;
            cursor: pointer;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }
        .modal-content {
            margin: 15% auto;
            display: block;
            width: 80%;
            max-width: 700px;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 25px;
            color: white;
            font-size: 35px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333;
            line-height: 1.6;
            box-sizing: border-box;
        }

        h1, h2 {
            color: #96B394;
            text-align: center;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #96B394;
        }

        button {
            cursor: pointer;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .content {
            padding: 20px;
            margin: auto;
            width: 90%;
            max-width: 1200px;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        form input, form select {
            flex: 1;
            min-width: 150px;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            background-color: #96B394;
            color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #96B394;
            color: #000;
        }

        td {
            background-color: #f9f9f9;
        }

        .delete-btn {
            background-color: #e63946;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 5px;
        }

        .btn_diagnostico {
            background-color: rgb(21, 85, 168);
            color: #fff;
            border: none;
            padding: 8px 11px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            position: relative;
            margin-top: 2px;
        }

        .delete-btn:hover {
            color: #fff !important;
            text-decoration: none;
            background-color: #b71c1c;
        }

        .edit-btn {
            background-color: #96B394;
            color: #fff;
            border: none;
            padding: 5px 2px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            width: 75px;
            transition: background-color 0.3s ease;
            margin-top: 6px;
        }

        .edit-btn:hover {
            background-color: rgb(94, 117, 92);
        }

        .nota-btn {
            background-color: rgb(47, 175, 122);
            color: #fff;
            border: none;
            padding: 5px 2px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            width: 75px;
            transition: background-color 0.3s ease;
            margin-top: 2px;
        }

        .nota-btn:hover {
            background-color: rgb(80, 133, 76);
        }

        #formularioEdicion {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -20%);
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            max-width: 400px;
            width: 90%;
        }

        #formularioEdicion form input {
            width: calc(100% - 20px);
            margin-bottom: 10px;
        }

        #formularioEdicion button {
            width: 48%;
            margin: 5px 1%;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                text-align: center;
            }

            form {
                flex-direction: column;
            }

            form input, form select, form button {
                width: 100%;
            }

            table {
                font-size: 14px;
                overflow-x: auto;
                display: block;
                max-width: 100%;
                white-space: nowrap;
            }

            table th, table td {
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav_container navbar navbar-dark">
            <div class="container-fluid">
                <div class="logo_container">
                    <img class="logo" src="../img/santeLogo.jpg" alt="Logo">
                </div>
                <div class="" id="navbarNavAltMarkup">
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav_link nav-link" href="../index.php">Agendar Paciente</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <div class="color"></div>

    <div class="content">
        <h1>Diagnóstico del Paciente</h1>
        <hr>
        <table class="table">
            <tr>
                <th>Nombre</th>
                <td><?php echo $paciente['nombre']; ?></td>
            </tr>
            <tr>
                <th>Teléfono</th>
                <td><?php echo $paciente['telefono']; ?></td>
            </tr>
            <tr>
                <th>Correo</th>
                <td><?php echo $paciente['gmail']; ?></td>
            </tr>
            <tr>
                <th>Obra Social</th>
                <td><?php echo $paciente['obra_social']; ?></td>
            </tr>
            <tr>
                <th>Servicio</th>
                <td><?php echo $paciente['servicio']; ?></td>
            </tr>
            <tr>
                <th>Profesional</th>
                <td><?php echo $paciente['profesional']; ?></td>
            </tr>
            <tr>
                <th>Fecha</th>
                <td><?php echo $paciente['fecha']; ?></td>
            </tr>
            <tr>
                <th>Hora</th>
                <td><?php echo $paciente['hora']; ?></td>
            </tr>
            <tr>
                <th>Número de Sesión</th>
                <td><?php echo $paciente['numero_sesion']; ?></td>
            </tr>
            <tr>
                <th>Comentarios</th>
                <td><?php echo $paciente['comentarios']; ?></td>
            </tr>
            <tr>
                <th>Asistencia</th>
                <td><?php echo $paciente['asistio'] ? 'El paciente asistió a su sesión' : 'El paciente no asistió a su sesión'; ?></td>
            </tr>
            <?php if (count($imagenes) > 0): ?>
            <tr>
                <th>Imágenes Diagnóstico</th>
                <td>
                    <?php foreach ($imagenes as $imagen): ?>
                        <div style="display:inline-block; position:relative;">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($imagen['imagen']); ?>" class="img-thumbnail" onclick="mostrarImagen(this)">
                            <form method="POST" action="diagnostico.php?id=<?php echo $id; ?>" style="display:inline;">
                                <input type="hidden" name="imagen_id" value="<?php echo $imagen['id']; ?>">
                                <button type="submit" name="eliminar_imagen" class="btn btn-danger btn-sm" style="position:absolute; top:0; right:0;">X</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>

        <h2>Diagnóstico</h2>
        <form method="POST" action="diagnostico.php?id=<?php echo $id; ?>">
            <textarea name="diagnostico" rows="10" style="width:100%;"><?php echo $paciente['diagnostico']; ?></textarea>
            <br><br>
            <button type="submit" name="guardar_diagnostico">Guardar Diagnóstico</button>
        </form>

        <h2>Imágenes Diagnóstico</h2>
        <form method="POST" action="diagnostico.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
            <label for="imagenes_diagnostico">Agregar Imágenes:</label>
            <input type="file" name="imagenes_diagnostico[]" id="imagenes_diagnostico" accept="image/*" multiple>
            <br><br>
            <button type="submit" name="guardar_imagenes">Guardar Imágenes</button>
        </form>
    </div>

    <div id="modal" class="modal">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <img class="modal-content" id="imgModal">
    </div>

    <script>
        function mostrarImagen(img) {
            var modal = document.getElementById('modal');
            var modalImg = document.getElementById('imgModal');
            modal.style.display = "block";
            modalImg.src = img.src;
        }

        function cerrarModal() {
            var modal = document.getElementById('modal');
            modal.style.display = "none";
        }
    </script>
</body>
</html>