<?php
session_start();
require_once '../../conexion/db.php';

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Deshabilitar errores en pantalla para producción
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido'
        ]);
        exit;
    }

    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no autenticado'
        ]);
        exit;
    }

    // Verificar CSRF token
    $csrf_valid = false;
    $received_token = null;
    $session_token = $_SESSION['csrf_token'] ?? null;
    
    // Obtener token de diferentes fuentes
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $received_token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (isset($_POST['csrf_token'])) {
        $received_token = $_POST['csrf_token'];
    }
    
    // Validar CSRF con hash_equals para evitar timing attacks
    if ($session_token && $received_token) {
        $csrf_valid = hash_equals($session_token, $received_token);
    }
    
    if (!$csrf_valid) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'CSRF token inválido'
        ]);
        exit;
    }

    // Obtener y validar parámetros
    $content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : null;
    $user_id = $_SESSION['user_id'];

    if ($content_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de contenido inválido'
        ]);
        exit;
    }

    // Verificar que el contenido existe y obtener información del curso
    $stmt = $conn->prepare("SELECT cm.id, cm.modulo_id, m.curso_id, c.nombre as curso_nombre
                           FROM contenido_modular cm
                           JOIN modulos m ON cm.modulo_id = m.id
                           JOIN cursos c ON m.curso_id = c.id
                           WHERE cm.id = ?");
    $stmt->execute([$content_id]);
    $content = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$content) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Contenido no encontrado'
        ]);
        exit;
    }

    // Usar el course_id del contenido si no se proporcionó
    if (!$course_id) {
        $course_id = $content['curso_id'];
    }

    // Verificar que el usuario esté inscrito en el curso
    $stmt = $conn->prepare("SELECT id FROM usuarios_cursos WHERE usuario_id = ? AND curso_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $enrollment = $stmt->fetch();

    if (!$enrollment) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'No tienes acceso a este curso'
        ]);
        exit;
    }

    // Marcar contenido como visto en contenido_visto
    $stmt = $conn->prepare("INSERT INTO contenido_visto (contenido_id, usuario_id, fecha_visto) 
                           VALUES (?, ?, NOW()) 
                           ON DUPLICATE KEY UPDATE fecha_visto = NOW()");
    $stmt->execute([$content_id, $user_id]);

    // Actualizar progreso en progreso_contenido
    $stmt = $conn->prepare("INSERT INTO progreso_contenido (usuario_id, curso_id, modulo_id, contenido_id, completado, fecha_acceso) 
                           VALUES (?, ?, ?, ?, 1, NOW()) 
                           ON DUPLICATE KEY UPDATE completado = 1, fecha_acceso = NOW()");
    $stmt->execute([$user_id, $course_id, $content['modulo_id'], $content_id]);

    // Calcular progreso actualizado del curso
    $stmt = $conn->prepare("SELECT 
                               COUNT(DISTINCT cm.id) as total_contenido,
                               COUNT(DISTINCT pc.contenido_id) as contenido_completado
                           FROM contenido_modular cm
                           JOIN modulos m ON cm.modulo_id = m.id
                           LEFT JOIN progreso_contenido pc ON cm.id = pc.contenido_id 
                               AND pc.usuario_id = ? AND pc.completado = 1
                           WHERE m.curso_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $progress_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_content = $progress_data['total_contenido'] ?? 0;
    $completed_content = $progress_data['contenido_completado'] ?? 0;
    $progress_percentage = $total_content > 0 ? round(($completed_content / $total_content) * 100, 2) : 0;

    // Actualizar progreso en usuarios_cursos
    $stmt = $conn->prepare("UPDATE usuarios_cursos 
                           SET progreso = ?, 
                               lecciones_completadas = ?, 
                               ultimo_acceso = NOW(),
                               completado = CASE WHEN ? >= 100 THEN 1 ELSE 0 END
                           WHERE usuario_id = ? AND curso_id = ?");
    $stmt->execute([$progress_percentage, $completed_content, $progress_percentage, $user_id, $course_id]);

    // Verificar si el curso está completado
    $course_completed = $progress_percentage >= 100;

    // Si el curso está completado, crear notificación
    if ($course_completed) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, role, message, link, icon, created_at) 
                               VALUES (?, 'estudiante', ?, ?, 'graduation-cap', NOW())
                               ON DUPLICATE KEY UPDATE created_at = NOW()");
        $message = "¡Felicidades! Has completado el curso: " . $content['curso_nombre'];
        $link = "/curso-completado.php?id=" . $course_id;
        $stmt->execute([$user_id, $message, $link]);
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Contenido marcado como visto',
        'data' => [
            'content_id' => $content_id,
            'course_id' => $course_id,
            'progress' => $progress_percentage,
            'completed_lessons' => $completed_content,
            'total_lessons' => $total_content,
            'course_completed' => $course_completed
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Error en mark_content_viewed.php (PDO): " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos'
    ]);
} catch (Exception $e) {
    error_log("Error en mark_content_viewed.php (General): " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor'
    ]);
}
?>