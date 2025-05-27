<?php
// ============================================================================
// ARCHIVO: courses/remove_student.php
// ============================================================================
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'profesor') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$instructor_id = $_SESSION['user_id'];
$usuario_id = $_POST['usuario_id'] ?? null;
$curso_id = $_POST['curso_id'] ?? null;

if (!$usuario_id || !$curso_id) {
    echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
    exit;
}

try {
    // Verificar que el curso pertenezca al instructor
    $verify_query = "SELECT id, nombre FROM cursos WHERE id = ? AND instructor_id = ?";
    $verify_stmt = $pdo->prepare($verify_query);
    $verify_stmt->execute([$curso_id, $instructor_id]);
    $curso = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$curso) {
        echo json_encode(['success' => false, 'message' => 'Curso no encontrado']);
        exit;
    }
    
    // Eliminar inscripción
    $delete_query = "UPDATE inscripciones SET estado = 'eliminada' WHERE usuario_id = ? AND curso_id = ?";
    $delete_stmt = $pdo->prepare($delete_query);
    $result = $delete_stmt->execute([$usuario_id, $curso_id]);
    
    if ($result) {
        // Notificar al estudiante
        $notif_query = "
            INSERT INTO notifications (usuario_id, tipo, titulo, mensaje, created_at)
            VALUES (?, 'eliminado_curso', ?, ?, NOW())
        ";
        
        $notif_stmt = $pdo->prepare($notif_query);
        $notif_stmt->execute([
            $usuario_id,
            'Eliminado del curso',
            "Has sido eliminado del curso: {$curso['nombre']}"
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Estudiante eliminado del curso exitosamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el estudiante']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}

