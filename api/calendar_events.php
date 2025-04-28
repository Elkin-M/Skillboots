<?php
session_start();
require_once '../conexion/db.php';

// Verificar CSRF
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'CSRF token inválido']);
    exit;
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Validar parámetros
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Obtener los eventos del mes (sesiones de clase y actividades)
try {
    $usuario_id = $_SESSION['user_id'];
    
    // Eventos de las sesiones de clase para los cursos en los que está inscrito el usuario
    $stmt = $conn->prepare("SELECT cs.id, cs.titulo, cs.fecha, cs.hora_inicio, cs.hora_fin, 
                            c.nombre as curso_nombre, c.id as curso_id
                            FROM class_sessions cs
                            JOIN cursos c ON cs.curso_id = c.id
                            JOIN usuarios_cursos uc ON c.id = uc.curso_id
                            WHERE MONTH(cs.fecha) = :month
                            AND YEAR(cs.fecha) = :year
                            AND uc.usuario_id = :user_id
                            ORDER BY cs.fecha, cs.hora_inicio");
    $stmt->execute([
        ':month' => $month,
        ':year' => $year,
        ':user_id' => $usuario_id
    ]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Eventos de actividades con fecha límite para los cursos del usuario
    $stmt = $conn->prepare("SELECT a.id, a.titulo, a.fecha_limite as fecha, 
                            'Entrega: ' || a.titulo as descripcion,
                            m.titulo as modulo_titulo, c.nombre as curso_nombre, c.id as curso_id
                            FROM actividades a
                            JOIN modulos m ON a.unidad_id = m.id
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
    
    // Combinar eventos
    $events = array_merge($sessions, $activities);
    
    // Formatear para la respuesta
    $formattedEvents = [];
    foreach ($events as $event) {
        $tipo = isset($event['hora_inicio']) ? 'session' : 'activity';
        $titulo = $event['titulo'];
        $curso = $event['curso_nombre'];
        
        $formattedEvents[] = [
            'id' => $event['id'],
            'titulo' => "$titulo - $curso",
            'fecha' => $event['fecha'],
            'tipo' => $tipo,
            'descripcion' => isset($event['descripcion']) ? $event['descripcion'] : null,
            'curso_id' => $event['curso_id'],
            'hora_inicio' => isset($event['hora_inicio']) ? $event['hora_inicio'] : null,
            'hora_fin' => isset($event['hora_fin']) ? $event['hora_fin'] : null
        ];
    }
    
    // Devolver eventos
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'events' => $formattedEvents
    ]);
    
} catch (PDOException $e) {
    error_log("Error obteniendo eventos del calendario: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Error obteniendo eventos del calendario']);
}
?>