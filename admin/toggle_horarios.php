<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'sante');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$profesional = $_SESSION['profesional'];

// Obtener el estado actual de los horarios del profesional
$sql_habilitado = "SELECT habilitado FROM disponibilidad WHERE profesional = ? LIMIT 1";
$stmt = $conn->prepare($sql_habilitado);
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error . " - SQL: " . $sql_habilitado);
}
$stmt->bind_param('s', $profesional);
$stmt->execute();
$result_habilitado = $stmt->get_result();
$habilitado = $result_habilitado->fetch_assoc()['habilitado'];

$habilitar = !$habilitado;

if ($habilitar) {
    $sql_update = "UPDATE disponibilidad SET habilitado = 1 WHERE profesional = ?";
} else {
    $sql_update = "UPDATE disponibilidad SET habilitado = 0 WHERE profesional = ?";
}

$stmt = $conn->prepare($sql_update);
if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error . " - SQL: " . $sql_update);
}
$stmt->bind_param('s', $profesional);
$stmt->execute();

header('Location: pacientes.php');
exit();
?>