<?php
session_start([
    'cookie_lifetime' => 0,
]);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_POST['profesional']) && !isset($_GET['profesional'])) {
    echo "Profesional no especificado.";
    exit();
}

$profesional = isset($_POST['profesional']) ? $_POST['profesional'] : $_GET['profesional'];

// En entorno de prueba/desarrollo, usamos un enfoque basado en memoria
// Esta es una simulación para probar la funcionalidad. 
// En producción, esto sería reemplazado por una conexión real a la base de datos

class MockDatabase {
    private $tables = [];
    
    public function __construct() {
        // Inicializar tablas mock
        $this->tables['profesionales_deshabilitados'] = [];
        $this->tables['profesionales_dias_deshabilitados'] = [];
        $this->tables['turnos'] = [];
        
        // Si hay datos en la sesión, restaurarlos
        if (isset($_SESSION['mock_db'])) {
            $this->tables = $_SESSION['mock_db'];
        }
    }
    
    public function prepare($query) {
        return new MockStatement($this, $query);
    }
    
    public function query($query) {
        // Implementación simple solo para CREATE TABLE
        if (stripos($query, 'CREATE TABLE IF NOT EXISTS') !== false) {
            return true; // Pretender que la tabla se creó
        }
        return false;
    }
    
    public function getTable($tableName) {
        return isset($this->tables[$tableName]) ? $this->tables[$tableName] : [];
    }
    
    public function insert($table, $data) {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = [];
        }
        
        // Asignar un ID auto-incrementado
        $id = count($this->tables[$table]) + 1;
        $data['id'] = $id;
        
        $this->tables[$table][] = $data;
        
        // Guardar en sesión
        $_SESSION['mock_db'] = $this->tables;
        
        return true;
    }
    
    public function delete($table, $condition) {
        if (!isset($this->tables[$table])) return false;
        
        $newData = [];
        foreach ($this->tables[$table] as $row) {
            $keep = true;
            foreach ($condition as $key => $value) {
                if (isset($row[$key]) && $row[$key] === $value) {
                    $keep = false;
                    break;
                }
            }
            if ($keep) $newData[] = $row;
        }
        
        $this->tables[$table] = $newData;
        $_SESSION['mock_db'] = $this->tables;
        
        return true;
    }
    
    public function update($table, $data, $condition) {
        if (!isset($this->tables[$table])) return false;
        
        foreach ($this->tables[$table] as &$row) {
            $match = true;
            foreach ($condition as $key => $value) {
                if (!isset($row[$key]) || $row[$key] !== $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                foreach ($data as $key => $value) {
                    $row[$key] = $value;
                }
            }
        }
        
        $_SESSION['mock_db'] = $this->tables;
        
        return true;
    }
    
    public function select($table, $condition = []) {
        if (!isset($this->tables[$table])) return [];
        
        $result = [];
        foreach ($this->tables[$table] as $row) {
            $match = true;
            foreach ($condition as $key => $value) {
                // Manejo especial para LIKE
                if (is_array($value) && isset($value['operator']) && $value['operator'] === 'LIKE') {
                    $pattern = str_replace('%', '.*', $value['value']);
                    if (!preg_match('/^' . $pattern . '$/', $row[$key])) {
                        $match = false;
                        break;
                    }
                } 
                // Igualdad simple
                else if (!isset($row[$key]) || $row[$key] !== $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) $result[] = $row;
        }
        
        return $result;
    }
}

class MockStatement {
    private $db;
    private $query;
    private $params = [];
    private $lastResult = null;
    
    public function __construct($db, $query) {
        $this->db = $db;
        $this->query = $query;
    }
    
    public function bind_param($types, ...$params) {
        $this->params = $params;
        return true;
    }
    
    public function execute() {
        // Procesamiento básico para inserts, updates, y deletes
        $query = strtolower($this->query);
        
        // INSERT
        if (strpos($query, 'insert into') !== false) {
            preg_match('/insert into\s+(\w+)/', $query, $matches);
            $table = $matches[1];
            
            // Extraer nombres de columnas
            preg_match('/\(([^)]+)\)\s+values\s+\(([^)]+)\)/i', $query, $matches);
            
            if (count($matches) >= 3) {
                $columns = array_map('trim', explode(',', $matches[1]));
                
                $data = [];
                for ($i = 0; $i < count($columns); $i++) {
                    $data[$columns[$i]] = $this->params[$i];
                }
                
                $this->db->insert($table, $data);
                return true;
            }
        }
        // DELETE
        else if (strpos($query, 'delete from') !== false) {
            preg_match('/delete from\s+(\w+)\s+where\s+(.*)/i', $query, $matches);
            
            if (count($matches) >= 3) {
                $table = $matches[1];
                
                // Extraer condición WHERE
                $whereClause = $matches[2];
                preg_match('/(\w+)\s*=\s*\?/i', $whereClause, $condMatches);
                
                if (count($condMatches) >= 2) {
                    $condition = [$condMatches[1] => $this->params[0]];
                    $this->db->delete($table, $condition);
                    return true;
                }
            }
        }
        // UPDATE
        else if (strpos($query, 'update') !== false) {
            preg_match('/update\s+(\w+)\s+set\s+(.*)\s+where\s+(.*)/i', $query, $matches);
            
            if (count($matches) >= 4) {
                $table = $matches[1];
                $setClause = $matches[2];
                $whereClause = $matches[3];
                
                $data = [];
                preg_match_all('/(\w+)\s*=\s*\?/i', $setClause, $setMatches);
                
                if (count($setMatches[1]) > 0) {
                    for ($i = 0; $i < count($setMatches[1]); $i++) {
                        $data[$setMatches[1][$i]] = $this->params[$i];
                    }
                    
                    // Extraer condición WHERE
                    preg_match('/(\w+)\s*=\s*\?/i', $whereClause, $condMatches);
                    
                    if (count($condMatches) >= 2) {
                        $condition = [$condMatches[1] => $this->params[count($setMatches[1])]];
                        $this->db->update($table, $data, $condition);
                        return true;
                    }
                }
            }
        }
        // SELECT
        else if (strpos($query, 'select') !== false) {
            preg_match('/select\s+.*\s+from\s+(\w+)(?:\s+where\s+(.*))?/i', $query, $matches);
            
            if (count($matches) >= 2) {
                $table = $matches[1];
                $whereClause = isset($matches[2]) ? $matches[2] : '';
                
                $condition = [];
                if (!empty($whereClause)) {
                    preg_match_all('/(\w+)\s*=\s*\?/i', $whereClause, $condMatches);
                    
                    if (count($condMatches[1]) > 0) {
                        for ($i = 0; $i < count($condMatches[1]); $i++) {
                            $condition[$condMatches[1][$i]] = $this->params[$i];
                        }
                    }
                    
                    // Manejar LIKE
                    preg_match_all('/(\w+)\s+LIKE\s+\?/i', $whereClause, $likeMatches);
                    
                    if (count($likeMatches[1]) > 0) {
                        $offset = count($condMatches[1]);
                        for ($i = 0; $i < count($likeMatches[1]); $i++) {
                            $condition[$likeMatches[1][$i]] = [
                                'operator' => 'LIKE',
                                'value' => $this->params[$offset + $i]
                            ];
                        }
                    }
                }
                
                $this->lastResult = $this->db->select($table, $condition);
                return true;
            }
        }
        
        return false;
    }
    
    public function get_result() {
        return new MockResult($this->lastResult);
    }
}

class MockResult {
    private $data;
    private $position = 0;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function fetch_all($resultType = null) {
        return $this->data;
    }
    
    public function fetch_assoc() {
        if ($this->position >= count($this->data)) {
            return null;
        }
        
        return $this->data[$this->position++];
    }
    
    public function __get($name) {
        if ($name === 'num_rows') {
            return count($this->data);
        }
        
        return null;
    }
}

// Iniciar nuestra base de datos mock
$conn = new MockDatabase();

// Horarios disponibles por profesional en Sante
if (!isset($_SESSION['disponibilidadProfesionales'])) {
    $_SESSION['disponibilidadProfesionales'] = [
        'Lucia Foricher' => [
            'Monday' => ['08:00', '09:00', '10:00', '11:00'],
            'Wednesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Friday' => ['08:00', '09:00', '10:00', '11:00'],
            'Monday (Terapia Manual - RPG)' => ['16:00', '17:00', '18:00', '19:00'],
            'Wednesday (Terapia Manual - RPG)' => ['16:00', '17:00', '18:00', '19:00'],
            'Tuesday (Terapia Manual - RPG)' => ['11:00', '12:00', '13:00', '14:00', '15:00'],
            'Thursday (Terapia Manual - RPG)' => ['11:00', '12:00', '13:00', '14:00', '15:00'],
            'Friday (Terapia Manual - RPG)' => ['12:00', '13:00', '14:00', '15:00']
        ],
        'Mauro Robert' => [
            'Monday' => ['13:00', '14:00', '15:00', '16:00'],
            'Tuesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Wednesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Thursday' => ['13:00', '14:00', '15:00', '16:00'],
            'Friday' => ['13:00', '14:00', '15:00', '16:00']
        ],
        'German Fernandez' => [
            'Monday' => ['17:30', '18:30', '19:30'],
            'Tuesday' => ['17:30', '18:30', '19:30'],
            'Wednesday' => ['17:30', '18:30', '19:30'],
            'Thursday' => ['17:30', '18:30', '19:30'],
            'Friday' => ['17:30', '18:30', '19:30']
        ],
        'Gastón Olgiati' => [
            'Monday' => ['13:00', '14:00', '15:00', '16:00'],
            'Wednesday' => ['13:00', '14:00', '15:00', '16:00'],
            'Friday' => ['13:00', '14:00', '15:00', '16:00']
        ],
        'Hernan Lopez' => [
            'Tuesday' => ['08:00', '09:00', '10:00', '11:00', '12:00'],
            'Thursday' => ['08:00', '09:00', '10:00', '11:00', '12:00'],
        ],
        'Alejandro Perez' => [
            'Monday' => ['08:00', '09:00', '10:00', '11:00'],
            'Wednesday' => ['08:00', '09:00', '10:00', '11:00'],
            'Friday' => ['08:00', '09:00', '10:00', '11:00']
        ],
        'Melina Thome' => [
            'Monday' => ['17:00', '18:00', '19:00'],
            'Wednesday' => ['17:00', '18:00', '19:00'],
            'Friday' => ['17:00', '18:00', '19:00']
        ],
        'Maria Paz' => [
            'Wednesday' => ['17:00', '18:00', '19:00'],
            'Saturday' => ['12:00']
        ],
        'Miriam Rossello' => [
            'Tuesday' => [
                '08:00', '08:15', '08:30', '08:45',
                '09:00', '09:15', '09:30', '09:45',
                '10:00', '10:15', '10:30', '10:45',
                '11:00', '11:15', '11:30', '11:45'
            ],
            'Thursday' => [
                '08:00', '08:15', '08:30', '08:45',
                '09:00', '09:15', '09:30', '09:45',
                '10:00', '10:15', '10:30', '10:45',
                '11:00', '11:15', '11:30', '11:45'
            ]
        ],
        'Florencia Goñi' => [
            'Monday' => ['17:00', '18:00'],
            'Tuesday' => ['17:00', '18:00'],
            'Thursday' => ['17:00']
        ],
        'Constanza Marinello' => [
            'Monday' => ['15:00'],
            'Tuesday' => ['16:00', '17:00'],
            'Thursday' => ['13:00', '14:00', '15:00'],
            'Friday' => ['15:00', '16:00']
        ],
        'Mariana Ilari' => [
            'Monday' => ['08:30', '09:30', '10:30', '11:30'],
            'Wednesday' => ['10:30', '11:30'],
            'Thursday' => ['08:30', '09:30', '10:30', '11:30'],
            'Friday' => ['17:00', '18:00']
        ],
        'Leila Heguilein' => [
            'Tuesday' => ['17:00', '18:00', '19:00', '20:00'],
            'Wednesday' => ['17:00', '18:00', '19:00', '20:00'],
        ]
    ];
}
$disponibilidadProfesionales = $_SESSION['disponibilidadProfesionales'];

// Verificar o crear tablas para profesionales deshabilitados
$conn->query("CREATE TABLE IF NOT EXISTS profesionales_deshabilitados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profesional VARCHAR(255) NOT NULL,
    deshabilitado TINYINT(1) DEFAULT 1,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(profesional)
)");

// Verificar o crear una tabla mejorada de profesionales deshabilitados 
// que ahora guarda días específicos
$conn->query("CREATE TABLE IF NOT EXISTS profesionales_dias_deshabilitados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profesional VARCHAR(255) NOT NULL,
    dia_semana VARCHAR(20) NOT NULL,
    deshabilitado TINYINT(1) DEFAULT 1,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY prof_dia (profesional, dia_semana)
)");

// Array con los días disponibles para seleccionar
$dias_semana_es = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];

// Verificar días deshabilitados para este profesional
$dias_deshabilitados = [];
$query = $conn->prepare("SELECT dia_semana FROM profesionales_dias_deshabilitados WHERE profesional = ? AND deshabilitado = 1");
$query->bind_param("s", $profesional);
$query->execute();
$result = $query->get_result();
while ($row = $result->fetch_assoc()) {
    $dias_deshabilitados[] = $row['dia_semana'];
}

// Verificar si el profesional está completamente deshabilitado (para compatibilidad)
$query = $conn->prepare("SELECT deshabilitado FROM profesionales_deshabilitados WHERE profesional = ?");
$query->bind_param("s", $profesional);
$query->execute();
$result = $query->get_result();
$profesional_deshabilitado = false;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $profesional_deshabilitado = (bool)$row['deshabilitado'];
}

// FUNCIÓN ACTUALIZADA: deshabilitarDiaEspecifico
function deshabilitarDiaEspecifico($conn, $profesional, $dia_semana) {
    // Insertar o actualizar el estado del día específico en la base de datos
    $stmt = $conn->prepare("INSERT INTO profesionales_dias_deshabilitados (profesional, dia_semana, deshabilitado) 
                          VALUES (?, ?, 1) 
                          ON DUPLICATE KEY UPDATE deshabilitado = 1");
    $stmt->bind_param("ss", $profesional, $dia_semana);
    $stmt->execute();
}

// FUNCIÓN ACTUALIZADA: habilitarTodosLosDias
function habilitarTodosLosDias($conn, $profesional) {
    // Eliminar todos los días deshabilitados para este profesional
    $stmt = $conn->prepare("DELETE FROM profesionales_dias_deshabilitados WHERE profesional = ?");
    $stmt->bind_param("s", $profesional);
    $stmt->execute();
    
    // También limpiar la tabla antigua para compatibilidad
    $stmt = $conn->prepare("DELETE FROM profesionales_deshabilitados WHERE profesional = ?");
    $stmt->bind_param("s", $profesional);
    $stmt->execute();
}

// Modal para seleccionar días a deshabilitar
$modal_deshabilitar = false;

// Manejar las solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mostrar_modal_deshabilitar'])) {
        $modal_deshabilitar = true;
    } elseif (isset($_POST['deshabilitar_dia']) && isset($_POST['dia_seleccionado'])) {
        $dia_seleccionado = $_POST['dia_seleccionado'];
        deshabilitarDiaEspecifico($conn, $profesional, $dia_seleccionado);
        echo "<script>alert('Día " . $dias_semana_es[$dia_seleccionado] . " deshabilitado.'); window.location.href='profesional_pacientes.php?profesional=" . urlencode($profesional) . "';</script>";
    } elseif (isset($_POST['habilitar'])) {
        habilitarTodosLosDias($conn, $profesional);
        echo "<script>alert('Todos los horarios habilitados.'); window.location.href='profesional_pacientes.php?profesional=" . urlencode($profesional) . "';</script>";
    } elseif (isset($_POST['asistencia_id'])) {
        $asistio = isset($_POST['asistio']) ? $_POST['asistio'] : 0;
        $asistencia_id = $_POST['asistencia_id'];
        $sql_update = "UPDATE turnos SET asistio = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param('ii', $asistio, $asistencia_id);
        if ($stmt->execute()) {
            echo "<script>alert('Estado de asistencia actualizado correctamente.'); window.location.href = 'profesional_pacientes.php?profesional=" . urlencode($profesional) . (isset($_GET['view']) ? '&view=' . $_GET['view'] : '') . "';</script>";
        } else {
            echo "Error al actualizar el estado de asistencia: " . $conn->error;
        }
    }
}

// Establecer vista (día, semana, mes)
$view = isset($_GET['view']) ? $_GET['view'] : 'day'; // Por defecto, ver el día actual

// Establecer fecha de referencia
$fecha_actual = date('Y-m-d');
$dia_actual = date('j');
$mes_actual = date('m');
$anio_actual = date('Y');
$dia_semana_actual = date('N'); // 1 (lunes) a 7 (domingo)

// Sobrescribir con valores GET si existen
$mes = isset($_GET['mes']) ? $_GET['mes'] : $mes_actual;
$anio = isset($_GET['anio']) ? $_GET['anio'] : $anio_actual;
$dia = isset($_GET['dia']) ? $_GET['dia'] : $dia_actual;

// Calcular fechas para la vista seleccionada
switch ($view) {
    case 'day':
        // Para vista de día, usamos la fecha actual o la especificada
        $fecha_ini = "$anio-$mes-$dia";
        $fecha_fin = "$anio-$mes-$dia";
        break;
    case 'week':
        // Para vista de semana, calculamos el inicio y fin de la semana actual
        $fecha_referencia = "$anio-$mes-$dia";
        $num_dia_semana = date('w', strtotime($fecha_referencia));
        if ($num_dia_semana == 0) $num_dia_semana = 7; // Ajustar domingo a 7
        $inicio_semana = date('Y-m-d', strtotime("-" . ($num_dia_semana - 1) . " days", strtotime($fecha_referencia)));
        $fin_semana = date('Y-m-d', strtotime("+6 days", strtotime($inicio_semana)));
        $fecha_ini = $inicio_semana;
        $fecha_fin = $fin_semana;
        break;
    case 'month':
        // Para vista de mes, usamos todo el mes
        $fecha_ini = "$anio-$mes-01";
        $fecha_fin = date('Y-m-t', strtotime($fecha_ini));
        break;
    default:
        $fecha_ini = "$anio-$mes-$dia";
        $fecha_fin = "$anio-$mes-$dia";
}

// Variables para búsqueda
$busqueda_nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$busqueda_telefono = isset($_GET['telefono']) ? $_GET['telefono'] : '';

// Construir la consulta SQL con los filtros de búsqueda
$sql = "SELECT * FROM turnos WHERE profesional = ?";
$params = [$profesional];
$param_types = 's';

if (!empty($busqueda_nombre)) {
    $sql .= " AND nombre LIKE ?";
    $params[] = '%' . $busqueda_nombre . '%';
    $param_types .= 's';
}

if (!empty($busqueda_telefono)) {
    $sql .= " AND telefono LIKE ?";
    $params[] = '%' . $busqueda_telefono . '%';
    $param_types .= 's';
}

// Filtrar para mostrar solo fechas actuales o futuras
$sql .= " AND fecha >= CURDATE()";

// Añadir fechas para el rango seleccionado
$sql .= " AND fecha BETWEEN ? AND ?";
$params[] = $fecha_ini;
$params[] = $fecha_fin;
$param_types .= 'ss';

$sql .= " ORDER BY fecha, hora";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$pacientes = $stmt->get_result();

// Verificar si la consulta fue exitosa
if ($pacientes === false) {
    echo "Error al obtener los pacientes: " . $conn->error;
    exit();
}

// Verificar los resultados obtenidos
$pacientes_array = $pacientes->fetch_all(MYSQLI_ASSOC);

// Agrupar pacientes por fecha
$pacientes_por_dia = [];
foreach ($pacientes_array as $paciente) {
    $fecha = $paciente['fecha'];
    if (!isset($pacientes_por_dia[$fecha])) {
        $pacientes_por_dia[$fecha] = [];
    }
    $pacientes_por_dia[$fecha][] = $paciente;
}

// Horarios predeterminados para Terapia Manual RPG para Lucia Foricher
$horarios_terapia_manual = ['09:00', '10:00', '11:00', '15:00', '16:00', '17:00', '18:00'];

// Mapear días de la semana
$dias_semana = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];

// Mapeo de nombres de días en inglés a números
$dias_ingles_a_numero = [
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6,
    'Sunday' => 7
];

// Mapeo de números a nombres de días en inglés
$numeros_a_dias_ingles = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'
];

// Colores por servicio
$colores_servicio = [
    'Kinesiología' => '#E2C6C2',
    'Terapia Manual - RPG' => '#A6DA9C',
    'Drenaje Linfático' => '#BBFFFF',
    'Nutrición' => '#EE976A',
    'Traumatología' => '#A9B0F4',
    'Psicología' => '#f8c8dc',
];

// Función para traducir días de la semana al español
function traducirDia($diaIngles) {
    $traducciones = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    return isset($traducciones[$diaIngles]) ? $traducciones[$diaIngles] : $diaIngles;
}

// Función para traducir días con Terapia Manual - RPG
function traducirDiaTerapiaManual($diaIngles) {
    // Verificar si la cadena contiene el patrón "(Terapia Manual - RPG)"
    if (strpos($diaIngles, '(Terapia Manual - RPG)') !== false) {
        // Extrae el nombre del día sin la parte de Terapia Manual
        $diaSolo = trim(substr($diaIngles, 0, strpos($diaIngles, '(')));
        $traducido = traducirDia($diaSolo);
        return $traducido . ' (Terapia Manual - RPG)';
    }
    return $diaIngles;
}

// Funciones de navegación de fechas
function obtenerMesAnterior($mes, $anio) {
    if ($mes == 1) {
        return [12, $anio - 1];
    }
    return [$mes - 1, $anio];
}

function obtenerMesSiguiente($mes, $anio) {
    if ($mes == 12) {
        return [1, $anio + 1];
    }
    return [$mes + 1, $anio];
}

function obtenerDiaAnterior($dia, $mes, $anio) {
    $fecha = mktime(0, 0, 0, $mes, $dia, $anio);
    $fecha_anterior = strtotime('-1 day', $fecha);
    return [
        'dia' => date('j', $fecha_anterior),
        'mes' => date('n', $fecha_anterior),
        'anio' => date('Y', $fecha_anterior)
    ];
}

function obtenerDiaSiguiente($dia, $mes, $anio) {
    $fecha = mktime(0, 0, 0, $mes, $dia, $anio);
    $fecha_siguiente = strtotime('+1 day', $fecha);
    return [
        'dia' => date('j', $fecha_siguiente),
        'mes' => date('n', $fecha_siguiente),
        'anio' => date('Y', $fecha_siguiente)
    ];
}

function obtenerSemanaAnterior($dia, $mes, $anio) {
    $fecha = mktime(0, 0, 0, $mes, $dia, $anio);
    $fecha_anterior = strtotime('-7 days', $fecha);
    return [
        'dia' => date('j', $fecha_anterior),
        'mes' => date('n', $fecha_anterior),
        'anio' => date('Y', $fecha_anterior)
    ];
}

function obtenerSemanaSiguiente($dia, $mes, $anio) {
    $fecha = mktime(0, 0, 0, $mes, $dia, $anio);
    $fecha_siguiente = strtotime('+7 days', $fecha);
    return [
        'dia' => date('j', $fecha_siguiente),
        'mes' => date('n', $fecha_siguiente),
        'anio' => date('Y', $fecha_siguiente)
    ];
}

// Calcular fechas para la navegación
$dia_ant = obtenerDiaAnterior($dia, $mes, $anio);
$dia_sig = obtenerDiaSiguiente($dia, $mes, $anio);
$semana_ant = obtenerSemanaAnterior($dia, $mes, $anio);
$semana_sig = obtenerSemanaSiguiente($dia, $mes, $anio);
list($mes_anterior, $anio_anterior) = obtenerMesAnterior($mes, $anio);
list($mes_siguiente, $anio_siguiente) = obtenerMesSiguiente($mes, $anio);

// Obtener nombre del mes actual en español
setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252');
$nombre_mes = date('F', mktime(0, 0, 0, $mes, 1, $anio));
$nombre_mes = str_replace(
    ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    $nombre_mes
);

// Función para obtener el nombre del día en español
function obtenerNombreDia($fecha) {
    $dias = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    return $dias[date('l', strtotime($fecha))];
}

// Función para formatear fecha en español
function formatoFechaEspanol($fecha) {
    $meses = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    ];
    
    $partes = explode('-', $fecha);
    if (count($partes) !== 3) return $fecha;
    
    $dia = ltrim($partes[2], '0');
    $mes = $meses[$partes[1]];
    $anio = $partes[0];
    
    return "$dia de $mes de $anio";
}

// Recopila información sobre horarios ocupados
$horarios_ocupados = [];

// Llena la matriz con horarios ocupados desde los datos de pacientes
foreach ($pacientes_array as $paciente) {
    $fecha = $paciente['fecha'];
    $hora = $paciente['hora'];
    $dia_semana = date('N', strtotime($fecha)); // 1 (lunes) a 7 (domingo)
    
    if (!isset($horarios_ocupados[$dia_semana])) {
        $horarios_ocupados[$dia_semana] = [];
    }
    
    if (!in_array($hora, $horarios_ocupados[$dia_semana])) {
        $horarios_ocupados[$dia_semana][] = $hora;
    }
}

// Función para generar URLs de agendamiento
function generarUrlAgendamiento($profesional, $fecha, $servicio = '') {
    $url = "agendar_administrador.php?profesional=" . urlencode($profesional);
    
    if (!empty($fecha)) {
        $url .= "&fecha=" . urlencode($fecha);
    }
    
    if (!empty($servicio)) {
        $url .= "&servicio=" . urlencode($servicio);
    }
    
    return $url;
}

// Generar fechas para mostrar en el calendario
$calendario_fechas = [];
$fecha_inicio_rango = new DateTime($fecha_ini);
$fecha_fin_rango = new DateTime($fecha_fin);
$intervalo = new DateInterval('P1D');
$periodo = new DatePeriod($fecha_inicio_rango, $intervalo, $fecha_fin_rango->modify('+1 day'));

foreach ($periodo as $fecha) {
    $calendario_fechas[] = $fecha->format('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes de <?php echo htmlspecialchars($profesional); ?> - Santé</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&family=Noto+Sans&family=Poppins:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/989f8affb2.js" crossorigin="anonymous"></script>
    <link rel="icon" href="../img/santeLogo.jpg">
    <style>
        body {
            background-color: #F6EBD5;
            font-family: 'Poppins', sans-serif;
        }
        
        .nav_container {
            background-color: #F6EBD5;
            color: white;
        }
        
        .logo {
            height: 50px;
        }
        
        h1 {
            color: #96B394;
            margin-bottom: 20px;
        }
        
        /* Selector de vistas */
        .view-selector {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .view-button {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ced4da;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .view-button:hover {
            background-color: #e9ecef;
        }
        
        .view-button.active {
            background-color: #96B394;
            color: white;
            border-color: #96B394;
        }
        
        /* Navegación de fechas */
        .date-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background-color: #fff;
            padding: 10px 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .date-button {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .date-button:hover {
            background-color: #7d9a7d;
            color: white;
        }
        
        .current-date {
            font-weight: bold;
            color: #333;
        }
        
        /* Buscar y botones de control */
        .search-form {
            background-color: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .schedule-control {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 10px;
        }
        
        .btn-primary, .add-patient-btn {
            background-color: #96B394;
            border-color: #96B394;
        }
        
        .btn-primary:hover, .add-patient-btn:hover {
            background-color: #7d9a7d;
            border-color: #7d9a7d;
        }
        
        .add-patient-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        /* Contenedor de pacientes */
        .day-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .day-header {
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .day-title {
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .time-slot {
            background-color: #f8f9fa;
            padding: 8px 15px;
            color: #555;
            font-weight: 500;
            margin-top: 10px;
            border-radius: 5px;
        }
        
        /* Tarjetas de pacientes */
        .patient-list {
            padding: 10px 15px;
        }
        
        .patient-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.2s;
        }
        
        .patient-card:last-child {
            border-bottom: none;
        }
        
        .patient-card:hover {
            background-color: #f9f9f9;
        }
        
        .patient-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .service-indicator {
            width: 6px;
            height: 30px;
            border-radius: 3px;
            flex-shrink: 0;
        }
        
        .patient-name {
            margin: 0;
            font-weight: 500;
            font-size: 1rem;
        }
        
        .patient-hour {
            color: #666;
            font-size: 0.875rem;
            margin: 0;
        }
        
        .attendance-form {
            display: flex;
            align-items: center;
        }
        
        .attendance-checkbox {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .attendance-label {
            margin: 0;
            cursor: pointer;
            font-size: 0.875rem;
            color: #666;
        }
        
        /* Mensaje sin pacientes */
        .no-patients {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .date-navigation {
                flex-direction: column;
                gap: 10px;
            }
            
            .nav-buttons {
                display: flex;
                width: 100%;
                justify-content: space-between;
            }
            
            .search-form .row .col-12 {
                margin-bottom: 10px;
            }
        }
        
        /* Estilos para modificar horarios */
        .btn-modificar-horarios {
            background-color: #96B394;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-modificar-horarios:hover {
            background-color: #7d9a7d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: white;
        }
        
        .btn-modificar-horarios i {
            margin-right: 5px;
        }
        
        /* Colores de servicios */
        .colores_servicios {
            text-align: center;
            margin: auto;
            width: 90%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        /* Estilos para la columna de horarios disponibles */
        .horarios-disponibles {
            border-right: 2px solid #ddd;
            background-color: #f8f9fa;
            padding: 0;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .horarios-disponibles h5 {
            background-color: #96B394;
            color: white;
            padding: 10px;
            margin: 0;
            text-align: center;
        }
        
        .horarios-disponibles ul {
            list-style: none;
            padding: 10px;
            margin: 0;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .horarios-disponibles li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        .horarios-disponibles li:last-child {
            border-bottom: none;
        }
        
        .terapia-manual {
            color: #009688; /* Color distintivo para Terapia Manual */
            font-weight: bold;
        }
        
        /* Estilos para el número de sesión */
        .session-number {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            margin-left: 5px;
        }
        
        .session-first {
            background-color: #FFD700; /* Amarillo para la primera sesión */
            color: #333;
        }
        
        .session-tenth {
            background-color: #8B0000; /* Bordo para la décima sesión */
            color: #fff;
        }

        /* Estilos para el botón de agendar en cada día */
        .btn-agendar-dia {
            background-color: #A6DA9C;
            color: #333;
            border: none;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-left: 10px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .btn-agendar-dia:hover {
            background-color: #8BC34A;
            color: #333;
            transform: translateY(-1px);
        }
        
        .empty-day-message {
            color: #666;
            padding: 15px;
            text-align: center;
            font-style: italic;
        }
        
        /* Estilos para Modal */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1040;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-deshabilitar {
            background-color: white;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1050;
        }
        
        .modal-header {
            background-color: #96B394;
            color: white;
            padding: 15px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #e9ecef;
        }
        
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <h1 class="text-center">Pacientes de <?php echo $profesional; ?></h1>
        
        <!-- Selector de vista (día, semana, mes) -->
        <div class="view-selector">
            <a href="?profesional=<?php echo urlencode($profesional); ?>&view=day" class="view-button <?php echo $view === 'day' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-day"></i> Día
            </a>
            <a href="?profesional=<?php echo urlencode($profesional); ?>&view=week" class="view-button <?php echo $view === 'week' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-week"></i> Semana
            </a>
            <a href="?profesional=<?php echo urlencode($profesional); ?>&view=month" class="view-button <?php echo $view === 'month' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Mes
            </a>
        </div>
        
        <!-- Navegación de fechas -->
        <div class="date-navigation">
            <?php if ($view === 'day'): ?>
                <a href="?profesional=<?php echo urlencode($profesional); ?>&view=day&dia=<?php echo $dia_ant['dia']; ?>&mes=<?php echo $dia_ant['mes']; ?>&anio=<?php echo $dia_ant['anio']; ?>" class="date-button">
                    <i class="fas fa-chevron-left"></i> Día anterior
                </a>
                <span class="current-date">
                    <?php echo formatoFechaEspanol($fecha_ini); ?>
                </span>
                <a href="?profesional=<?php echo urlencode($profesional); ?>&view=day&dia=<?php echo $dia_sig['dia']; ?>&mes=<?php echo $dia_sig['mes']; ?>&anio=<?php echo $dia_sig['anio']; ?>" class="date-button">
                    Día siguiente <i class="fas fa-chevron-right"></i>
                </a>
            <?php elseif ($view === 'week'): ?>
                <a href="?profesional=<?php echo urlencode($profesional); ?>&view=week&dia=<?php echo $semana_ant['dia']; ?>&mes=<?php echo $semana_ant['mes']; ?>&anio=<?php echo $semana_ant['anio']; ?>" class="date-button">
                    <i class="fas fa-chevron-left"></i> Semana anterior
                </a>
                <span class="current-date">
                    Semana: <?php echo date('d/m/Y', strtotime($fecha_ini)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)); ?>
                </span>
                <a href="?profesional=<?php echo urlencode($profesional); ?>&view=week&dia=<?php echo $semana_sig['dia']; ?>&mes=<?php echo $semana_sig['mes']; ?>&anio=<?php echo $semana_sig['anio']; ?>" class="date-button">
                    Semana siguiente <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <a href="?profesional=<?php echo urlencode($profesional); ?>&view=month&mes=<?php echo $mes_anterior; ?>&anio=<?php echo $anio_anterior; ?>" class="date-button">
                    <i class="fas fa-chevron-left"></i> Mes anterior
                </a>
                <span class="current-date">
                    <?php echo $nombre_mes . ' ' . $anio; ?>
                </span>
                <a href="?profesional=<?php echo urlencode($profesional); ?>&view=month&mes=<?php echo $mes_siguiente; ?>&anio=<?php echo $anio_siguiente; ?>" class="date-button">
                    Mes siguiente <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Formulario de búsqueda -->
        <div class="search-form">
            <form method="GET" class="mb-0">
                <input type="hidden" name="profesional" value="<?php echo htmlspecialchars($profesional); ?>">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                <?php if ($view === 'day'): ?>
                    <input type="hidden" name="dia" value="<?php echo $dia; ?>">
                <?php endif; ?>
                <input type="hidden" name="mes" value="<?php echo $mes; ?>">
                <input type="hidden" name="anio" value="<?php echo $anio; ?>">
                
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <input type="text" name="nombre" class="form-control" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($busqueda_nombre); ?>">
                    </div>
                    <div class="col-12 col-md-5">
                        <input type="text" name="telefono" class="form-control" placeholder="Buscar por teléfono" value="<?php echo htmlspecialchars($busqueda_telefono); ?>">
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">Buscar</button>
                            <a href="?profesional=<?php echo urlencode($profesional); ?>&view=<?php echo $view; ?>" class="btn btn-secondary flex-grow-1">Limpiar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Botón para agendar paciente -->
        <div class="d-flex justify-content-center mb-3">
            <a href="agendar_administrador.php" class="add-patient-btn">
                <i class="fas fa-user-plus"></i> Agendar nuevo paciente
            </a>
        </div>
        
        <!-- Botón para modificar horarios -->
        <div class="d-flex justify-content-center mb-3">
            <a href="modificar_horarios.php?profesional=<?php echo urlencode($profesional); ?>" 
                class="btn-modificar-horarios">
                <i class="fa fa-clock-o" aria-hidden="true"></i> Modificar Horarios
            </a>
        </div>
        
        <!-- Control de habilitación de horarios -->
        <div class="schedule-control">
            <form method="POST" action="" class="d-inline-block">
                <button type="submit" name="mostrar_modal_deshabilitar" class="btn btn-danger px-4 py-2">
                    <i class="fas fa-ban me-2"></i>Deshabilitar día específico
                </button>
            </form>
            
            <form method="POST" action="" class="d-inline-block">
                <button type="submit" name="habilitar" class="btn btn-success px-4 py-2">
                    <i class="fas fa-check-circle me-2"></i>Habilitar todos los días
                </button>
            </form>
        </div>
        
        <!-- Leyenda de colores de servicios -->
        <div class="colores_servicios d-flex flex-wrap justify-content-center">
            <?php foreach ($colores_servicio as $servicio => $color): ?>
                <div class="me-3 mb-2">
                    <span style="display: inline-block; width: 20px; height: 20px; background-color: <?php echo $color; ?>; margin-right: 5px;"></span>
                    <?php echo $servicio; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Columna para horarios disponibles -->
        <div class="horarios-disponibles mb-4">
            <h5>Horarios Disponibles</h5>
            <ul>
                <?php 
                // Obtener el día actual en número (1-7)
                $hoy_numero = date('N');
                
                try {
                    // Mostrar los horarios de servicios normales DISPONIBLES (no ocupados)
                    if (isset($disponibilidadProfesionales[$profesional])) {
                        foreach ($disponibilidadProfesionales[$profesional] as $dia => $horas) {
                            // Si el día contiene "Terapia Manual - RPG", procesarlo después
                            if (strpos($dia, 'Terapia Manual - RPG') !== false) {
                                continue;
                            }
                            
                            // Convertir nombre del día en inglés a número (1-7)
                            $dia_numero = isset($dias_ingles_a_numero[$dia]) ? $dias_ingles_a_numero[$dia] : null;
                            
                            // Si no es un día de la semana estándar, continuar
                            if ($dia_numero === null) continue;
                            
                            // Quitar comprobación de días pasados para mostrar todos los horarios
                            // if ($dia_numero < $hoy_numero) continue;
                            
                            // Si el día está deshabilitado, no lo mostramos
                            if (in_array($dia, $dias_deshabilitados)) continue;
                            
                            // Traducir el día al español
                            $dia_espanol = traducirDia($dia);
                            
                            // Filtrar horas ocupadas
                            $horas_disponibles = [];
                            foreach ($horas as $hora) {
                                // Verificar si la hora está ocupada para este día
                                if (!isset($horarios_ocupados[$dia_numero]) || !in_array($hora, $horarios_ocupados[$dia_numero])) {
                                    $horas_disponibles[] = $hora;
                                }
                            }
                            ?>
                            <li>
                                <strong><?php echo $dia_espanol; ?></strong>:
                                <?php if (empty($horas_disponibles)): ?>
                                    <span class="text-muted">No hay horarios disponibles</span>
                                <?php else: ?>
                                    <span><?php echo implode(', ', $horas_disponibles); ?></span>
                                <?php endif; ?>
                            </li>
                            <?php
                        }
                        
                        // Procesar horarios de Terapia Manual - RPG
                        if ($profesional === 'Lucia Foricher' || $profesional === 'Mariana Ilari') {
                            foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $dia_base) {
                                $dia_terapia = $dia_base . ' (Terapia Manual - RPG)';
                                
                                // Convertir nombre del día en inglés a número (1-7)
                                $dia_numero = isset($dias_ingles_a_numero[$dia_base]) ? $dias_ingles_a_numero[$dia_base] : null;
                                
                                // Quitar comprobación de días pasados para mostrar todos los horarios
                                // if ($dia_numero < $hoy_numero) continue;
                                
                                // Si el día está deshabilitado, no lo mostramos
                                if (in_array($dia_base, $dias_deshabilitados)) continue;
                                
                                $horas = [];
                                
                                // Verificar si este día tiene horarios de terapia manual definidos
                                if (isset($disponibilidadProfesionales[$profesional][$dia_terapia])) {
                                    $horas = $disponibilidadProfesionales[$profesional][$dia_terapia];
                                } 
                                
                                // Si no hay horas definidas para este día, continuar
                                if (empty($horas)) continue;
                                
                                // Filtrar horas ocupadas
                                $horas_disponibles = [];
                                foreach ($horas as $hora) {
                                    // Verificar si la hora está ocupada para este día
                                    if (!isset($horarios_ocupados[$dia_numero]) || !in_array($hora, $horarios_ocupados[$dia_numero])) {
                                        $horas_disponibles[] = $hora;
                                    }
                                }
                                
                                // Traducir el día al español
                                $dia_espanol = traducirDia($dia_base);
                                ?>
                                <li>
                                    <strong class="terapia-manual"><?php echo $dia_espanol; ?> (Terapia Manual - RPG)</strong>:
                                    <?php if (empty($horas_disponibles)): ?>
                                        <span class="text-muted">No hay horarios disponibles</span>
                                    <?php else: ?>
                                        <span><?php echo implode(', ', $horas_disponibles); ?></span>
                                    <?php endif; ?>
                                </li>
                                <?php
                            }
                        }
                    }
                } catch (Exception $e) {
                    echo "<li class='text-danger'>Error al procesar horarios: " . $e->getMessage() . "</li>";
                }
                ?>
            </ul>
        </div>
        
        <!-- Listado de pacientes -->
        <?php 
        // Mostrar contenedores para todas las fechas en el rango
        foreach ($calendario_fechas as $fecha_calendario):
            $diaSemana = obtenerNombreDia($fecha_calendario);
            $fecha_formateada = date('d/m/Y', strtotime($fecha_calendario));
            $dia_numero = date('N', strtotime($fecha_calendario));
            $dia_nombre_ingles = $numeros_a_dias_ingles[$dia_numero];
            
            // Verificar si hay pacientes para esta fecha
            $hay_pacientes = isset($pacientes_por_dia[$fecha_calendario]);
            
            // Verificar si el profesional trabaja este día de la semana
            $profesional_trabaja_este_dia = false;
            $tiene_terapia_manual = false;
            
            if (isset($disponibilidadProfesionales[$profesional][$dia_nombre_ingles])) {
                $profesional_trabaja_este_dia = true;
            }
            
            // Verificar si tiene horarios de terapia manual
            $dia_terapia = $dia_nombre_ingles . ' (Terapia Manual - RPG)';
            if (($profesional === 'Lucia Foricher' || $profesional === 'Mariana Ilari') && 
                isset($disponibilidadProfesionales[$profesional][$dia_terapia])) {
                $tiene_terapia_manual = true;
                $profesional_trabaja_este_dia = true;
            }
            
            // El día está deshabilitado?
            $dia_deshabilitado = in_array($dia_nombre_ingles, $dias_deshabilitados);
        ?>
            <div class="day-container">
                <div class="day-header">
                    <h3 class="day-title">
                        <?php echo $diaSemana; ?>
                        <?php if ($profesional_trabaja_este_dia && !$dia_deshabilitado): ?>
                            <a href="<?php echo generarUrlAgendamiento($profesional, $fecha_calendario); ?>" class="btn-agendar-dia">
                                <i class="fas fa-plus-circle"></i> Agendar
                            </a>
                            <?php if ($tiene_terapia_manual): ?>
                                <a href="<?php echo generarUrlAgendamiento($profesional, $fecha_calendario, 'Terapia Manual - RPG'); ?>" class="btn-agendar-dia" style="background-color: #A6DA9C;">
                                    <i class="fas fa-plus-circle"></i> Terapia Manual
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </h3>
                    <span><?php echo $fecha_formateada; ?></span>
                </div>
                
                <div class="patient-list">
                    <?php if ($hay_pacientes): 
                        $pacientes_fecha = $pacientes_por_dia[$fecha_calendario];
                        
                        // Ordenar pacientes por hora
                        usort($pacientes_fecha, function($a, $b) {
                            return strtotime($a['hora']) - strtotime($b['hora']);
                        });
                        
                        // Agrupar pacientes por hora para esta fecha
                        $pacientes_por_hora = [];
                        foreach ($pacientes_fecha as $paciente) {
                            $hora = $paciente['hora'];
                            if (!isset($pacientes_por_hora[$hora])) {
                                $pacientes_por_hora[$hora] = [];
                            }
                            $pacientes_por_hora[$hora][] = $paciente;
                        }
                        
                        foreach ($pacientes_por_hora as $hora => $pacientes_hora): ?>
                            <div class="time-slot">
                                <i class="far fa-clock me-2"></i><?php echo date('H:i', strtotime($hora)); ?>
                            </div>
                            
                            <?php foreach ($pacientes_hora as $paciente): 
                                // Determinar la clase CSS para el número de sesión
                                $session_class = '';
                                $session_number = isset($paciente['numero_sesion']) ? (int)$paciente['numero_sesion'] : 0;
                                
                                if ($session_number === 1) {
                                    $session_class = 'session-first';
                                } elseif ($session_number === 10) {
                                    $session_class = 'session-tenth';
                                }
                            ?>
                                <div class="patient-card" onclick="window.location.href='diagnostico.php?id=<?php echo $paciente['id']; ?>'" style="cursor: pointer;">
                                    <div class="patient-info">
                                        <div class="service-indicator" style="background-color: <?php echo isset($colores_servicio[$paciente['servicio']]) ? $colores_servicio[$paciente['servicio']] : '#ddd'; ?>"></div>
                                        <div>
                                            <h5 class="patient-name">
                                                <?php echo htmlspecialchars($paciente['nombre']); ?>
                                                <?php if (isset($paciente['numero_sesion']) && !empty($paciente['numero_sesion'])): ?>
                                                    <span class="session-number <?php echo $session_class; ?>">
                                                        <?php echo $paciente['numero_sesion']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </h5>
                                            <p class="patient-hour"><?php echo $paciente['servicio']; ?></p>
                                        </div>
                                    </div>
                                    <form method="POST" class="attendance-form">
                                        <input type="hidden" name="asistencia_id" value="<?php echo $paciente['id']; ?>">
                                        <input type="hidden" name="profesional" value="<?php echo htmlspecialchars($profesional); ?>">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div>
                                                <input type="radio" name="asistio" value="1" class="attendance-checkbox" id="asistio_si_<?php echo $paciente['id']; ?>" 
                                                    <?php echo isset($paciente['asistio']) && $paciente['asistio'] == 1 ? 'checked' : ''; ?> 
                                                    onchange="this.form.submit()">
                                                <label for="asistio_si_<?php echo $paciente['id']; ?>" style="font-size: 0.8rem; margin-left: 3px;">
                                                    Asistió
                                                </label>
                                            </div>
                                            <div>
                                                <input type="radio" name="asistio" value="0" class="attendance-checkbox" id="asistio_no_<?php echo $paciente['id']; ?>" 
                                                    <?php echo isset($paciente['asistio']) && $paciente['asistio'] == 0 ? 'checked' : ''; ?> 
                                                    onchange="this.form.submit()">
                                                <label for="asistio_no_<?php echo $paciente['id']; ?>" style="font-size: 0.8rem; margin-left: 3px;">
                                                    No asistió
                                                </label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
    <?php if ($profesional_trabaja_este_dia && !$dia_deshabilitado): ?>
        <div class="empty-day-message">
            <p>No hay pacientes programados para esta fecha.</p>
        </div>
    <?php else: ?>
        <div class="empty-day-message" style="display: none;">
            <?php if ($dia_deshabilitado): ?>
                <p>Este día está deshabilitado para el profesional.</p>
            <?php else: ?>
                <p>El profesional no atiende en este día de la semana.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Modal para seleccionar días a deshabilitar -->
    <?php if ($modal_deshabilitar): ?>
    <div class="modal-backdrop">
        <div class="modal-deshabilitar">
            <div class="modal-header">
                <h5>Seleccionar día a deshabilitar</h5>
                <button type="button" class="close-btn" onclick="window.location.href='profesional_pacientes.php?profesional=<?php echo urlencode($profesional); ?>'">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="profesional" value="<?php echo htmlspecialchars($profesional); ?>">
                    <div class="mb-3">
                        <label for="dia_seleccionado" class="form-label">Seleccione el día que desea deshabilitar:</label>
                        <select class="form-select" id="dia_seleccionado" name="dia_seleccionado" required>
                            <?php foreach ($dias_semana_es as $dia_en => $dia_es): ?>
                                <?php if (isset($disponibilidadProfesionales[$profesional][$dia_en]) || 
                                          isset($disponibilidadProfesionales[$profesional][$dia_en . ' (Terapia Manual - RPG)'])): ?>
                                    <option value="<?php echo $dia_en; ?>" <?php echo in_array($dia_en, $dias_deshabilitados) ? 'disabled' : ''; ?>>
                                        <?php echo $dia_es; ?> <?php echo in_array($dia_en, $dias_deshabilitados) ? '(Ya deshabilitado)' : ''; ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='profesional_pacientes.php?profesional=<?php echo urlencode($profesional); ?>'">Cancelar</button>
                        <button type="submit" name="deshabilitar_dia" class="btn btn-danger">Deshabilitar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Actualizar etiqueta de asistencia cuando se cambia el checkbox
        document.querySelectorAll('.attendance-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.nextElementSibling;
                label.textContent = this.checked ? 'Asistió' : 'No asistió';
            });
        });

        // Añadir efecto hover a las tarjetas de pacientes
        document.querySelectorAll('.patient-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f9f9f9';
            });
            card.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    </script>
</body>
</html>
