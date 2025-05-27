<?php
session_start();
require_once '../../conexion/db.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'profesor') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$instructor_id = $_SESSION['user_id'];

try {
    // Consulta para obtener tareas pendientes de revisión
    $query = "
        SELECT 
            et.id,
            t.titulo,
            c.nombre as curso_nombre,
            u.name as estudiante_nombre,
            et.fecha_entrega,
            et.archivo
        FROM entregas_tareas et
        INNER JOIN tareas t ON et.tarea_id = t.id
        INNER JOIN cursos c ON t.curso_id = c.id
        INNER JOIN usuarios u ON et.estudiante_id = u.id
        WHERE c.instructor_id = ? 
        AND et.estado = 'entregada'
        AND (et.calificacion IS NULL OR et.calificacion = 0)
        ORDER BY et.fecha_entrega ASC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$instructor_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas
    foreach ($tasks as &$task) {
        $task['fecha_entrega'] = date('d/m/Y H:i', strtotime($task['fecha_entrega']));
    }
    
    echo json_encode([
        'success' => true,
        'tasks' => $tasks
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}