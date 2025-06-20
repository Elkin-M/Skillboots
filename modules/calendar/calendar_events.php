<?php
session_start();
require_once '../../conexion/db.php';

// Deshabilitar mostrar errores en pantalla para producción
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Forzar header JSON desde el inicio
header('Content-Type: application/json');

try {
    // Verificar CSRF - Usar hash_equals para comparación segura
    $csrf_valid = false;
    $received_token = null;
    $session_token = $_SESSION['csrf_token'] ?? null;
    
    // Obtener token de diferentes fuentes
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $received_token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (isset($_POST['csrf_token'])) {
        $received_token = $_POST['csrf_token'];
    } elseif (isset($_GET['csrf_token'])) {
        $received_token = $_GET['csrf_token'];
    }
    
    // Validar CSRF con hash_equals para evitar timing attacks
    if ($session_token && $received_token) {
        $csrf_valid = hash_equals($session_token, $received_token);
    }
    
    if (!$csrf_valid) {
        http_response_code(403);
        echo json_encode([
            'error' => 'CSRF token inválido', 
            'debug' => 'Token no válido o no presente',
            'success' => false,
            'events' => []
        ]);
        exit;
    }
    
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Usuario no autenticado',
            'success' => false,
            'events' => []
        ]);
        exit;
    }
    
    // Validar parámetros con valores por defecto
    $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    
    // Validar que los parámetros sean válidos
    if ($month < 1 || $month > 12 || $year < 1900 || $year > 2100) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Parámetros de fecha inválidos',
            'success' => false,
            'events' => []
        ]);
        exit;
    }
    
    $usuario_id = $_SESSION['user_id'];
    
    // Eventos de las sesiones de clase para los cursos en los que está inscrito el usuario
    // Corregido: usar start_time en lugar de fecha, hora_inicio, hora_fin
    $stmt = $conn->prepare("SELECT cs.id, cs.title, cs.start_time, cs.status,
                            c.nombre as curso_nombre, c.id as curso_id
                            FROM class_sessions cs
                            JOIN cursos c ON cs.curso_id = c.id
                            JOIN usuarios_cursos uc ON c.id = uc.curso_id
                            WHERE MONTH(cs.start_time) = :month
                            AND YEAR(cs.start_time) = :year
                            AND uc.usuario_id = :user_id
                            ORDER BY cs.start_time");
    
    $stmt->execute([
        ':month' => $month,
        ':year' => $year,
        ':user_id' => $usuario_id
    ]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Eventos de actividades con fecha límite para los cursos del usuario
    $stmt = $conn->prepare("SELECT a.id, a.titulo, a.fecha_limite as fecha, 
                            CONCAT('Entrega: ', a.titulo) as descripcion,
                            m.titulo as modulo_titulo, c.nombre as curso_nombre, c.id as curso_id
                            FROM actividades a
                            JOIN modulos m ON a.modulo_id = m.id
                            JOIN cursos c ON m.curso_id = c.id
                            JOIN usuarios_cursos uc ON c.id = uc.curso_id
                            WHERE MONTH(a.fecha_limite) = :month
                            AND YEAR(a.fecha_limite) = :year
                            AND uc.usuario_id = :user_id
                            AND a.fecha_limite IS NOT NULL
                            ORDER BY a.fecha_limite");
    
    $stmt->execute([
        ':month' => $month,
        ':year' => $year,
        ':user_id' => $usuario_id
    ]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Asegurar que tenemos arrays válidos
    $sessions = $sessions ?: [];
    $activities = $activities ?: [];
    
    // Formatear eventos de sesiones
    $formattedSessions = [];
    foreach ($sessions as $session) {
        $datetime = new DateTime($session['start_time']);
        $formattedSessions[] = [
            'id' => $session['id'],
            'titulo' => $session['title'] . ' - ' . $session['curso_nombre'],
            'fecha' => $datetime->format('Y-m-d'),
            'hora_inicio' => $datetime->format('H:i:s'),
            'hora_fin' => null, // No hay hora fin en la BD, se puede calcular si es necesario
            'tipo' => 'session',
            'status' => $session['status'],
            'descripcion' => 'Sesión de clase: ' . $session['title'],
            'curso_id' => $session['curso_id'],
            'curso_nombre' => $session['curso_nombre']
        ];
    }
    
    // Formatear eventos de actividades
    $formattedActivities = [];
    foreach ($activities as $activity) {
        $formattedActivities[] = [
            'id' => $activity['id'],
            'titulo' => $activity['titulo'] . ' - ' . $activity['curso_nombre'],
            'fecha' => $activity['fecha'],
            'hora_inicio' => null,
            'hora_fin' => null,
            'tipo' => 'activity',
            'status' => null,
            'descripcion' => $activity['descripcion'],
            'curso_id' => $activity['curso_id'],
            'curso_nombre' => $activity['curso_nombre'],
            'modulo_titulo' => $activity['modulo_titulo']
        ];
    }
    
    // Combinar todos los eventos
    $allEvents = array_merge($formattedSessions, $formattedActivities);
    
    // Ordenar por fecha
    usort($allEvents, function($a, $b) {
        $dateA = $a['fecha'];
        $dateB = $b['fecha'];
        
        if ($dateA === $dateB) {
            // Si las fechas son iguales, ordenar por hora (sessions primero)
            if ($a['hora_inicio'] && $b['hora_inicio']) {
                return strcmp($a['hora_inicio'], $b['hora_inicio']);
            }
            return $a['hora_inicio'] ? -1 : 1;
        }
        
        return strcmp($dateA, $dateB);
    });
    
    // Devolver eventos exitosamente
    echo json_encode([
        'success' => true,
        'events' => $allEvents,
        'total' => count($allEvents),
        'debug' => [
            'sessions_count' => count($formattedSessions),
            'activities_count' => count($formattedActivities),
            'user_id' => $usuario_id,
            'month' => $month,
            'year' => $year
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log("Error obteniendo eventos del calendario: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error obteniendo eventos del calendario',
        'success' => false,
        'events' => [],
        'debug' => 'Error de base de datos'
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Error general en calendar_events: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor',
        'success' => false,
        'events' => [],
        'debug' => 'Error general del servidor'
    ], JSON_UNESCAPED_UNICODE);
}
?>