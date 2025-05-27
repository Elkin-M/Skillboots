<?php
session_start();
require_once '../../conexion/db.php';
header('Content-Type: application/json');

$debug = []; // Para acumular mensajes de depuración

// Verificar sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'profesor') {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado',
        'debug' => ['Razón' => 'Sesión no iniciada o rol no es profesor']
    ]);
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Capturar datos del formulario
$titulo = $_POST['titulo'] ?? '';
$curso_id = $_POST['curso_id'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$zoom_link = $_POST['zoom_link'] ?? '';

$debug[] = "Datos recibidos: titulo=$titulo, curso_id=$curso_id, descripcion=$descripcion, start_time=$start_time, zoom_link=$zoom_link, instructor_id=$instructor_id";

// Validar campos requeridos
if (empty($titulo) || empty($curso_id) || empty($start_time)) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos requeridos faltantes',
        'debug' => $debug
    ]);
    exit;
}

try {
    // Verificar propiedad del curso
    $verify_query = "SELECT id FROM cursos WHERE id = ? AND instructor_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->execute([$curso_id, $instructor_id]);

    if (!$verify_stmt->fetch()) {
        $debug[] = "Curso con ID $curso_id no pertenece al instructor $instructor_id.";
        echo json_encode([
            'success' => false,
            'message' => 'Curso no encontrado o no autorizado',
            'debug' => $debug
        ]);
        exit;
    }

    // Validar fecha futura
    if (strtotime($start_time) <= time()) {
        $debug[] = "Fecha inválida: $start_time está en el pasado o presente.";
        echo json_encode([
            'success' => false,
            'message' => 'La fecha y hora deben ser futuras',
            'debug' => $debug
        ]);
        exit;
    }

    // Insertar sesión
    $query = "
        INSERT INTO class_sessions (curso_id, title, description, start_time, zoom_link, instructor_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'programada', NOW())
    ";

    $stmt = $conn->prepare($query);
    $result = $stmt->execute([
        $curso_id,
        $titulo,
        $descripcion,
        $start_time,
        $zoom_link,
        $instructor_id
    ]);

    if ($result) {
        $session_id = $conn->lastInsertId();
        $debug[] = "Sesión insertada con ID: $session_id";

        // Notificar estudiantes por grupo y curso
        notificarEstudiantesPorGruposYCurso($conn, $curso_id, $session_id, $titulo, $debug);

        echo json_encode([
            'success' => true,
            'message' => 'Sesión programada exitosamente',
            'session_id' => $session_id,
            'debug' => $debug
        ]);
    } else {
        $debug[] = "Falló la ejecución del INSERT";
        echo json_encode([
            'success' => false,
            'message' => 'Error al programar la sesión',
            'debug' => $debug
        ]);
    }

} catch (PDOException $e) {
    $debug[] = 'Excepción SQL: ' . $e->getMessage();
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos',
        'debug' => $debug
    ]);
}

// Función para notificar estudiantes por grupos activos o inscritos directamente al curso
function notificarEstudiantesPorGruposYCurso($pdo, $curso_id, $session_id, $titulo, &$debug) {
    try {
        $debug[] = "Iniciando notificación a estudiantes por grupos y curso para curso $curso_id";

        // Consulta corregida con parámetros distintos para evitar error SQLSTATE[HY093]
        $query = "
            SELECT DISTINCT u.id, u.email, u.name
            FROM usuarios u
            WHERE u.id IN (
                -- Estudiantes por grupo activo
                SELECT eg.estudiante_id
                FROM estudiantes_grupo eg
                INNER JOIN grupos g ON eg.grupo_id = g.id
                WHERE g.curso_id = :curso_id1 AND eg.estado = 'activo'

                UNION

                -- Estudiantes inscritos directamente en el curso
                SELECT uc.usuario_id
                FROM usuarios_cursos uc
                WHERE uc.curso_id = :curso_id2
            )
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':curso_id1' => $curso_id,
            ':curso_id2' => $curso_id
        ]);

        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $debug[] = count($estudiantes) . " estudiantes encontrados para notificar";

        foreach ($estudiantes as $estudiante) {
            // Insertar notificación
            $notif_query = "
                INSERT INTO notifications (user_id, role, message, link, icon, `read`, created_at)
                VALUES (:user_id, :role, :message, :link, :icon, :read, NOW())
            ";

            $notif_stmt = $pdo->prepare($notif_query);
            $notif_stmt->execute([
                ':user_id' => $estudiante['id'],
                ':role' => 'estudiante',
                ':message' => "Nueva sesión programada: {$titulo}",
                ':link' => "ver_sesion.php?id={$session_id}",
                ':icon' => 'calendar',
                ':read' => 0
            ]);

            $debug[] = "Notificación enviada a usuario ID: " . $estudiante['id'];
        }

    } catch (Exception $e) {
        $debug[] = "Error notificando estudiantes: " . $e->getMessage();
    }
}
