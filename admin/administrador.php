<?php
session_start([
    'cookie_lifetime' => 0, // La sesión se cierra cuando se cierra el navegador
]);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'sante');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener la lista de profesionales
$sql_profesionales = "SELECT DISTINCT profesional FROM turnos";
$result_profesionales = $conn->query($sql_profesionales);

// Variables para búsqueda
$busqueda_nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$busqueda_obra_social = isset($_GET['obra_social']) ? $_GET['obra_social'] : '';
$profesional_seleccionado = isset($_GET['profesional']) ? $_GET['profesional'] : '';
$dia_semana = isset($_GET['dia_semana']) ? $_GET['dia_semana'] : ''; // Nueva variable para el día de la semana

// Inicializar la consulta para obtener pacientes
$sql = "SELECT * FROM turnos WHERE 1=1";
$conditions = []; // Array para almacenar las condiciones de búsqueda
$params = [];
$param_types = '';

// Agregar filtros de búsqueda por nombre, obra social, día de la semana y profesional
if ($busqueda_nombre != '') {
    $conditions[] = "nombre LIKE ?";
    $params[] = "%$busqueda_nombre%";
    $param_types .= 's';
}

if ($busqueda_obra_social != '') {
    $conditions[] = "obra_social LIKE ?";
    $params[] = "%$busqueda_obra_social%";
    $param_types .= 's';
}

if ($dia_semana != '') {
    $conditions[] = "DAYOFWEEK(fecha) = ?";
    $params[] = $dia_semana;
    $param_types .= 'i';
}

if ($profesional_seleccionado != '') {
    $conditions[] = "profesional = ?";
    $params[] = $profesional_seleccionado;
    $param_types .= 's';
}

// Si hay condiciones, agregarlas a la consulta
if (count($conditions) > 0) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($sql);
if (!empty($param_types)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$pacientes = $stmt->get_result();

// Verificar si la consulta fue exitosa
if ($pacientes === false) {
    echo "Error al obtener los pacientes: " . $conn->error;
    exit();
}

// Eliminar paciente si se recibe el ID
if (isset($_GET['eliminar_id'])) {
    $eliminar_id = $_GET['eliminar_id'];
    $sql_delete = "DELETE FROM turnos WHERE id = ?";

    if ($stmt = $conn->prepare($sql_delete)) {
        $stmt->bind_param('i', $eliminar_id);
        if ($stmt->execute()) {
            echo "<script>alert('Paciente eliminado correctamente.'); window.location.href = 'administrador.php';</script>";
        } else {
            echo "Error al eliminar el paciente.";
        }
    } else {
        echo "Error al preparar la consulta de eliminación.";
    }
    exit();
}

// Actualizar paciente si se recibe el ID, nueva obra social, nueva fecha, nueva hora y nuevo número de sesión
if (isset($_POST['editar_id'])) {
    $editar_id = $_POST['editar_id'];
    $nueva_obra_social = $_POST['nueva_obra_social'];
    $nueva_fecha = $_POST['nueva_fecha'];
    $nueva_hora = $_POST['nueva_hora'];
    $nuevo_numero_sesion = $_POST['nuevo_numero_sesion'];
    $sql_update = "UPDATE turnos SET obra_social = ?, fecha = ?, hora = ?, numero_sesion = ? WHERE id = ?";

    if ($stmt = $conn->prepare($sql_update)) {
        $stmt->bind_param('sssii', $nueva_obra_social, $nueva_fecha, $nueva_hora, $nuevo_numero_sesion, $editar_id);
        if ($stmt->execute()) {
            echo "<script>alert('Datos actualizados correctamente.'); window.location.href = 'administrador.php';</script>";
        } else {
            echo "Error al actualizar los datos.";
        }
    } else {
        echo "Error al preparar la consulta de actualización.";
    }
    exit();
}

if (isset($_POST['nota_id']) && isset($_POST['nuevo_comentario'])) {
    $nota_id = $_POST['nota_id'];
    $nuevo_comentario = $_POST['nuevo_comentario'];

    $sql_update = "UPDATE turnos SET comentarios = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql_update)) {
        $stmt->bind_param('si', $nuevo_comentario, $nota_id);
        if ($stmt->execute()) {
            echo "<script>alert('Comentario actualizado correctamente.'); window.location.href = 'administrador.php';</script>";
        } else {
            echo "Error al actualizar el comentario.";
        }
    } else {
        echo "Error al preparar la consulta.";
    }
    exit();
}

// Actualizar asistencia de paciente
if (isset($_POST['asistencia_id'])) {
    $asistencia_id = $_POST['asistencia_id'];
    $asistio = isset($_POST['asistio']) ? 1 : 0;

    $sql_asistencia = "UPDATE turnos SET asistio = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql_asistencia)) {
        $stmt->bind_param('ii', $asistio, $asistencia_id);
        if ($stmt->execute()) {
            echo "<script>alert('Asistencia actualizada correctamente.'); window.location.href = 'administrador.php';</script>";
        } else {
            echo "Error al actualizar la asistencia.";
        }
    } else {
        echo "Error al preparar la consulta de asistencia.";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="../bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">
    <link rel="icon" href="../img/santeLogo.jpg">
    <title>Kaizen - Administración de Pacientes</title>
    <script>
        function notaFormulario(id, comentarioActual) {
            document.getElementById('nota_id').value = id; // ID del comentario
            document.getElementById('nuevo_comentario').value = comentarioActual; // Carga el comentario actual
            document.getElementById('formularioNotas').style.display = 'block'; // Muestra el formulario
        }

        function cerrarNotaFormulario() {
            document.getElementById('formularioNotas').style.display = 'none'; // Oculta el formulario
        }
    </script>
    <style>
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

        /* Header */

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

        /* Table Styles */
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
            color: #fff;
        }

        td {
            background-color: #f9f9f9;
        }

        .highlight {
            background-color: yellow;
        }

        /* Estilo para el botón de eliminar */
        .delete-btn {
            background-color: #e63946;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            position: relative;
            bottom: 5px;
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
            top: 5px;
        }

        .delete-btn:hover {
            color: #fff !important;
            text-decoration: none;
            background-color: #b71c1c;
        }

        /* Estilo para el botón de editar */
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
            margin-top: 2px;
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

        /* Responsive Styles */
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

        .asistencia_contianer {
            width: 0;
            font-size: 20px;
            background-color: #333;
            text-align: center;
            position: relative;
            right: 35px;
        }
    </style>
</head>
<body>

    <!-- Navegador lateral -->
    <header>
        <nav class="nav_container navbar-expand-lg navbar navbar-dark">
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

    <!-- Contenido principal -->
    <div class="content">
        <h1 style="font-weight: 600; letter-spacing: 10px;">Bienvenido</h1>
        <hr>
        <!-- Formulario para seleccionar profesional, nombre, obra social y día de la semana -->
        <form method="GET" action="administrador.php">
            <select name="profesional" id="profesional">
                <option value="">Seleccionar Profesional</option>
                <?php while ($row_profesional = $result_profesionales->fetch_assoc()): ?>
                    <option value="<?php echo $row_profesional['profesional']; ?>" <?php echo $profesional_seleccionado == $row_profesional['profesional'] ? 'selected' : ''; ?>><?php echo $row_profesional['profesional']; ?></option>
                <?php endwhile; ?>
            </select>
            <input type="text" name="nombre" value="<?php echo $busqueda_nombre; ?>" placeholder="Buscar por Nombre">
            <input type="text" name="obra_social" value="<?php echo $busqueda_obra_social; ?>" placeholder="Buscar por Obra Social">

            <!-- Nueva opción para seleccionar el día de la semana -->
            <select name="dia_semana" id="dia_semana">
                <option value="">Seleccionar Día</option>
                <option value="1" <?php echo isset($_GET['dia_semana']) && $_GET['dia_semana'] == '1' ? 'selected' : ''; ?>>Domingo</option>
                <option value="2" <?php echo isset($_GET['dia_semana']) && $_GET['dia_semana'] == '2' ? 'selected' : ''; ?>>Lunes</option>
                <option value="3" <?php echo isset($_GET['dia_semana']) && $_GET['dia_semana'] == '3' ? 'selected' : ''; ?>>Martes</option>
                <option value="4" <?php echo isset($_GET['dia_semana']) && $_GET['dia_semana'] == '4' ? 'selected' : ''; ?>>Miércoles</option>
                <option value="5" <?php echo isset($_GET['dia_semana']) && $_GET['dia_semana'] == '5' ? 'selected' : ''; ?>>Jueves</option>
                <option value="6" <?php echo isset($_GET['dia_semana']) && $_GET['dia_semana'] == '6' ? 'selected' : ''; ?>>Viernes</option>
                <option value="7" <?php echo isset($_GET['dia_semana']) && $_GET['dia_semana'] == '7' ? 'selected' : ''; ?>>Sábado</option>
            </select>

            <button type="submit">Buscar</button>
        </form>

        <!-- Mostrar resultados -->
        <?php if (isset($pacientes) && $pacientes->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Obra Social</th>
                        <th>Servicio</th>
                        <th>Profesional</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Número de Sesión</th>
                        <th>Notas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pacientes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['nombre']; ?></td>
                            <td><?php echo $row['telefono']; ?></td>
                            <td><?php echo $row['gmail']; ?></td>
                            <td><?php echo $row['obra_social']; ?></td>
                            <td><?php echo $row['servicio']; ?></td>
                            <td><?php echo $row['profesional']; ?></td>
                            <td><?php echo $row['fecha']; ?></td>
                            <td><?php echo $row['hora']; ?></td>
                            <td class="<?php echo $row['numero_sesion'] == 1 ? 'highlight' : ''; ?>"><?php echo $row['numero_sesion']; ?></td>
                            <td><?php echo $row['comentarios']; ?></td>
                            <td>
                                <form method="POST" class="asistencia_contianer" action="administrador.php">
                                    <input type="hidden" name="asistencia_id" value="<?php echo $row['id']; ?>">
                                    <input type="checkbox" name="asistio" <?php echo $row['asistio'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                </form>
                                <a href="?eliminar_id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('¿Estás seguro de que quieres eliminar este paciente?');">Eliminar</a>
                                <br>
                                <button class="edit-btn" onclick="abrirFormulario(<?php echo $row['id']; ?>, '<?php echo $row['obra_social']; ?>', '<?php echo $row['fecha']; ?>', '<?php echo $row['hora']; ?>', '<?php echo $row['numero_sesion']; ?>')">Editar</button>
                                <br>
                                <button class="nota-btn" onclick="notaFormulario('<?php echo $row['id']; ?>', '<?php echo addslashes(str_replace(array("\r\n", "\n", "\r"), '', $row['comentarios'])); ?>')">Editar nota</button>
                                <br>
                                <a href="diagnostico.php?id=<?php echo $row['id']; ?>" class="btn_diagnostico ">Paciente</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron pacientes con los criterios de búsqueda.</p>
        <?php endif; ?>
    </div>

    <div id="formularioNotas" style="display:none; position:fixed; top:20%; left:50%; transform:translate(-50%, -20%); background-color:white; padding:20px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
        <h2>Crear Comentario</h2>
        <form method="POST" action="administrador.php">
            <input type="hidden" name="nota_id" id="nota_id">
            <label for="nuevo_comentario">Comentario:</label>
            <textarea name="nuevo_comentario" id="nuevo_comentario" rows="4" style="width:100%; resize: none !important;"></textarea>
            <br><br>
            <button type="submit">Guardar</button>
            <button type="button" onclick="cerrarNotaFormulario()">Cancelar</button>
        </form>
    </div>

    <!-- Formulario modal para editar datos -->
    <div id="formularioEdicion" style="display:none; position:fixed; top:20%; left:50%; transform:translate(-50%, -20%); background-color:white; padding:20px; border:1px solid #ddd; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
        <h2>Editar Paciente</h2>
        <form method="POST" action="administrador.php">
            <input type="hidden" name="editar_id" id="editar_id">
            <label for="nueva_obra_social">Nueva Obra Social:</label>
            <input type="text" name="nueva_obra_social" id="nueva_obra_social">
            <br>
            <label for="nueva_fecha">Nueva Fecha:</label>
            <input type="date" name="nueva_fecha" id="nueva_fecha">
            <br>
            <label for="nueva_hora">Nueva Hora:</label>
            <input type="time" name="nueva_hora" id="nueva_hora">
            <br>
            <label for="nuevo_numero_sesion">Nuevo Número de Sesión:</label>
            <input type="number" name="nuevo_numero_sesion" id="nuevo_numero_sesion">
            <br><br>
            <button type="submit">Guardar</button>
            <button type="button" onclick="cerrarFormulario()">Cancelar</button>
        </form>
    </div>

    <script>
        function abrirFormulario(id, obraSocial, fecha, hora, numeroSesion) {
            document.getElementById('editar_id').value = id;
            document.getElementById('nueva_obra_social').value = obraSocial;
            document.getElementById('nueva_fecha').value = fecha;
            document.getElementById('nueva_hora').value = hora;
            document.getElementById('nuevo_numero_sesion').value = numeroSesion;
            document.getElementById('formularioEdicion').style.display = 'block';
        }

        function cerrarFormulario() {
            document.getElementById('formularioEdicion').style.display = 'none';
        }
    </script>

</body>

</html>

<?php $conn->close(); ?>