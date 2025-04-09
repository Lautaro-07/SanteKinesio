<?php
session_start([
    'cookie_lifetime' => 0,
]);

// Verificar que el usuario esté logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

// Obtener el profesional desde la sesión o parámetro
$profesional = isset($_GET['profesional']) ? $_GET['profesional'] : $_SESSION['profesional'];

// Si no hay profesional especificado, redirigir
if (empty($profesional)) {
    echo "<script>alert('No se ha especificado un profesional.'); window.location.href='administrador.php';</script>";
    exit();
}

// Obtener los horarios disponibles actuales
$disponibilidadProfesionales = isset($_SESSION['disponibilidadProfesionales']) ? $_SESSION['disponibilidadProfesionales'] : [];
$disponibilidad = isset($disponibilidadProfesionales[$profesional]) ? $disponibilidadProfesionales[$profesional] : [];

// Nombres de los días en español
$dias_semana = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inicializar los nuevos horarios
    $nuevos_horarios = [];
    
    // Procesar cada día de la semana
    foreach ($dias_semana as $dia_ingles => $dia_espanol) {
        // Verificar si hay horarios para este día
        if (isset($_POST['horarios'][$dia_ingles]) && !empty($_POST['horarios'][$dia_ingles])) {
            $nuevos_horarios[$dia_ingles] = $_POST['horarios'][$dia_ingles];
        }
        
        // Procesar Terapia Manual - RPG (solo para días con este servicio)
        $dia_terapia = $dia_ingles . ' (Terapia Manual - RPG)';
        if (isset($_POST['horarios'][$dia_terapia]) && !empty($_POST['horarios'][$dia_terapia])) {
            $nuevos_horarios[$dia_terapia] = $_POST['horarios'][$dia_terapia];
        }
    }
    
    // Actualizar la disponibilidad en la sesión
    $_SESSION['disponibilidadProfesionales'][$profesional] = $nuevos_horarios;
    
    // Guardar los horarios originales actualizados para este profesional
    // Esto se utilizará para la función habilitarHorarios en pacientes.php
    if (!isset($_SESSION['originalDisponibilidad'])) {
        $_SESSION['originalDisponibilidad'] = [];
    }
    $_SESSION['originalDisponibilidad'][$profesional] = $nuevos_horarios;
    
    // Mensaje de éxito
    $mensaje = "Horarios actualizados correctamente.";
    $tipo_mensaje = "success";
    
    // Redirigir a administrador.php
    header("Location: administrador.php");
    exit();
}

// URL de retorno por defecto
$pagina_retorno = 'administrador.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Horarios - <?php echo htmlspecialchars($profesional); ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../bootstrap-5.1.3-dist/css/bootstrap.css">
    <script src="../bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="icon" href="../img/santeLogo.jpg">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
            box-sizing: border-box;
        }

        h1, h2 {
            color: #96B394;
            text-align: center;
        }

        .content {
            padding: 20px;
            margin: auto;
            width: 90%;
            max-width: 1000px;
        }

        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .card-header {
            background-color: #96B394;
            color: white;
            padding: 15px;
        }

        .dia-horarios {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .dia-titulo {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .horas-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .hora-item {
            display: flex;
            align-items: center;
            background-color: #f2f2f2;
            padding: 5px 10px;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .btn-agregar-hora {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-eliminar-hora {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-cancelar {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-guardar {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .nueva-hora-input {
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .terapia-manual {
            background-color: #A6DA9C;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-left: 5px;
        }

        .form-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .content {
                width: 95%;
            }
            
            .form-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-buttons button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav_container navbar navbar-dark navbar-expand-lg">
            <div class="container-fluid">
                <div class="logo_container">
                    <img class="logo" src="../img/santeLogo.jpg" alt="Logo">
                </div>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" 
                    aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <ul class="ul_container navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="btn_horarios nav_link nav-link" style="color: #fff;" href="administrador.php">Volver</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="content mt-4">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Modificar Horarios</h2>
                <p class="text-white mb-0">Profesional: <?php echo htmlspecialchars($profesional); ?></p>
            </div>
            <div class="card-body p-4">
                <form id="formHorarios" method="POST" action="">
                    <input type="hidden" name="pagina_retorno" value="administrador.php">
                    
                    <div class="horarios-container">
                        <?php foreach ($dias_semana as $dia_ingles => $dia_espanol): ?>
                            <?php if ($dia_ingles != 'Saturday' && $dia_ingles != 'Sunday'): ?>
                                <!-- Horarios regulares -->
                                <div class="dia-horarios">
                                    <div class="dia-titulo"><?php echo $dia_espanol; ?></div>
                                    <div class="horas-container" id="horas-<?php echo $dia_ingles; ?>">
                                        <?php if (isset($disponibilidad[$dia_ingles])): ?>
                                            <?php foreach ($disponibilidad[$dia_ingles] as $hora): ?>
                                                <div class="hora-item">
                                                    <input type="text" class="nueva-hora-input" name="horarios[<?php echo $dia_ingles; ?>][]" value="<?php echo htmlspecialchars($hora); ?>" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" title="Formato de hora: HH:MM">
                                                    <button type="button" class="btn-eliminar-hora">&times;</button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn-agregar-hora" data-dia="<?php echo $dia_ingles; ?>">Agregar Hora</button>
                                </div>
                                
                                <!-- Horarios para Terapia Manual - RPG (solo si existen) -->
                                <?php $dia_terapia = $dia_ingles . ' (Terapia Manual - RPG)'; ?>
                                <?php if (isset($disponibilidad[$dia_terapia])): ?>
                                    <div class="dia-horarios">
                                        <div class="dia-titulo">
                                            <?php echo $dia_espanol; ?> 
                                            <span class="terapia-manual">Terapia Manual - RPG</span>
                                        </div>
                                        <div class="horas-container" id="horas-<?php echo str_replace(' ', '-', $dia_terapia); ?>">
                                            <?php foreach ($disponibilidad[$dia_terapia] as $hora): ?>
                                                <div class="hora-item">
                                                    <input type="text" class="nueva-hora-input" name="horarios[<?php echo $dia_terapia; ?>][]" value="<?php echo htmlspecialchars($hora); ?>" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" title="Formato de hora: HH:MM">
                                                    <button type="button" class="btn-eliminar-hora">&times;</button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn-agregar-hora" data-dia="<?php echo $dia_terapia; ?>">Agregar Hora</button>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-buttons">
                        <a href="administrador.php" class="btn btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn btn-guardar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar nueva hora
            document.querySelectorAll('.btn-agregar-hora').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const dia = this.getAttribute('data-dia');
                    const containerId = 'horas-' + dia.replace(/ /g, '-');
                    const container = document.getElementById(containerId);
                    
                    // Crear nuevo elemento de hora
                    const horaItem = document.createElement('div');
                    horaItem.className = 'hora-item';
                    horaItem.innerHTML = `
                        <input type="text" class="nueva-hora-input" name="horarios[${dia}][]" value="" placeholder="HH:MM" pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]" title="Formato de hora: HH:MM" required>
                        <button type="button" class="btn-eliminar-hora">&times;</button>
                    `;
                    
                    // Agregar el elemento al contenedor
                    container.appendChild(horaItem);
                    
                    // Agregar evento para eliminar
                    const btnEliminar = horaItem.querySelector('.btn-eliminar-hora');
                    btnEliminar.addEventListener('click', function() {
                        horaItem.remove();
                    });
                    
                    // Enfocar el nuevo input
                    horaItem.querySelector('input').focus();
                });
            });
            
            // Eliminar hora
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('btn-eliminar-hora')) {
                    e.target.closest('.hora-item').remove();
                }
            });
            
            // Validar formato de hora
            document.getElementById('formHorarios').addEventListener('submit', function(e) {
                const inputs = document.querySelectorAll('.nueva-hora-input');
                let isValid = true;
                
                inputs.forEach(function(input) {
                    // Validar formato HH:MM
                    const pattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
                    if (input.value && !pattern.test(input.value)) {
                        input.setCustomValidity('Formato incorrecto. Use HH:MM (ejemplo: 09:30)');
                        isValid = false;
                    } else {
                        input.setCustomValidity('');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Por favor, verifique el formato de las horas ingresadas (HH:MM).');
                }
            });
        });
    </script>
</body>
</html>