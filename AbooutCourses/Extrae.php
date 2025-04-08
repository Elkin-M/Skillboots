<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo "<script>const cursos = [];</script>"; // Array vacío si no hay usuario
    exit;
}

$id_usuario = $_SESSION['user_id']; // Obtener el ID del usuario de la sesión

$conexion = new mysqli("localhost", "root", "", "elkinmb3");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta con JOIN para obtener solo los cursos del usuario
$sql = "SELECT c.*, uc.progreso, uc.lecciones_completadas, uc.ultimo_acceso 
        FROM cursos c
        INNER JOIN usuarios_cursos uc ON c.id = uc.curso_id
        WHERE uc.usuario_id = ?";

// Preparar la consulta (usando prepared statements para seguridad)
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

echo "<script>\n";
echo "const cursos = [\n";

$primero = true;
while ($fila = $resultado->fetch_assoc()) {
    if (!$primero) echo ",\n";
    
    // Obtener progreso directamente de la tabla de relación
    $progreso = $fila["progreso"];
    
    // Determinar color del progreso
    $colorProgreso = "secondary";
    if ($progreso > 75) $colorProgreso = "success";
    else if ($progreso > 45) $colorProgreso = "primary";
    else if ($progreso > 25) $colorProgreso = "warning";
    else if ($progreso > 0) $colorProgreso = "danger";
    
    // Determinar estado
    $estado = "No iniciado";
    if ($progreso == 100) $estado = "Finalizado";
    else if ($progreso > 0) $estado = "En progreso";
    
    // Determinar estadoClase
    $estadoClase = $progreso > 0 ? "success" : "muted";
    
    // Obtener lecciones completadas directamente de la tabla
    $lecciones_completadas = $fila["lecciones_completadas"];
    
    // Calcular horas restantes basado en el progreso
    $horas_restantes = $fila["horas_totales"] - (($progreso / 100) * $fila["horas_totales"]);
    
    // Formatear el último acceso desde la base de datos
    $ultimo_acceso = "Nunca";
    if (!empty($fila["ultimo_acceso"])) {
        // Convertir formato de fecha a algo más amigable
        $fecha_acceso = new DateTime($fila["ultimo_acceso"]);
        $hoy = new DateTime();
        $diff = $fecha_acceso->diff($hoy);
        
        if ($diff->days == 0) {
            $ultimo_acceso = "Hoy";
        } else if ($diff->days == 1) {
            $ultimo_acceso = "Ayer";
        } else if ($diff->days < 7) {
            $ultimo_acceso = "Hace " . $diff->days . " días";
        } else if ($diff->days < 30) {
            $ultimo_acceso = "Hace " . floor($diff->days / 7) . " semanas";
        } else {
            $ultimo_acceso = $fecha_acceso->format('d/m/Y');
        }
    }
    
    // Usar imagen de la base de datos o generar placeholder
    $imagen = $fila["imagen"] ? $fila["imagen"] : "https://via.placeholder.com/400x225?text=" . urlencode($fila["nombre"]);
    
    echo "    {\n";
    echo "        id: " . $fila["id"] . ",\n";
    echo "        titulo: '" . addslashes($fila["nombre"]) . "',\n";
    echo "        descripcion: '" . addslashes($fila["descripcion"]) . "',\n";
    echo "        imagen: '" . addslashes($imagen) . "',\n";
    echo "        progreso: " . $progreso . ",\n";
    echo "        colorProgreso: '" . $colorProgreso . "',\n";
    echo "        ultimoAcceso: '" . $ultimo_acceso . "',\n";
    echo "        lecciones: {\n";
    echo "            completadas: " . $lecciones_completadas . ",\n";
    echo "            total: " . $fila["total_lecciones"] . "\n";
    echo "        },\n";
    echo "        horasRestantes: " . $horas_restantes . ",\n";
    echo "        estado: '" . $estado . "',\n";
    echo "        estadoClase: '" . $estadoClase . "'\n";
    echo "    }";
    
    $primero = false;
}

echo "\n];\n";
echo "</script>\n";

$stmt->close();
$conexion->close();
?>