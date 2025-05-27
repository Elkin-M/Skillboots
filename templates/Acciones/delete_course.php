<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'profesor') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$instructor_id = $_SESSION['user_id'];
$curso_id = $_GET['id'] ?? null;

if (!$curso_id || !is_numeric($curso_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de curso no válido']);
    exit;
}

try {
    // Verificar que el curso pertenezca al instructor y no esté ya eliminado
    $verify_query = "SELECT id, nombre FROM cursos WHERE id = ? AND instructor_id = ? AND deleted_at IS NULL";
    $verify_stmt = $pdo->prepare($verify_query);
    $verify_stmt->execute([$curso_id, $instructor_id]);
    $curso = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$curso) {
        echo json_encode(['success' => false, 'message' => 'Curso no encontrado o ya eliminado']);
        exit;
    }
    
    // Verificar si hay estudiantes inscritos
    $count_query = "SELECT COUNT(*) as total FROM inscripciones WHERE curso_id = ? AND estado = 'activa'";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute([$curso_id]);
    $estudiantes_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($estudiantes_count > 0) {
        // Si hay estudiantes, marcar como eliminado (soft delete) con fecha de eliminación en 30 días
        $delete_date = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $soft_delete_query = "
            UPDATE cursos 
            SET deleted_at = ?, estado = 'eliminado' 
            WHERE id = ?
        ";
        
        $soft_delete_stmt = $pdo->prepare($soft_delete_query);
        $result = $soft_delete_stmt->execute([$delete_date, $curso_id]);
        
        if ($result) {
            // Notificar a estudiantes inscritos
            notificarEstudiantesEliminacion($pdo, $curso_id, $curso['nombre']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Curso marcado para eliminación. Se eliminará permanentemente en 30 días.',
                'soft_delete' => true
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al marcar el curso para eliminación']);
        }
    } else {
        // Si no hay estudiantes, eliminar inmediatamente
        $pdo->beginTransaction();
        
        try {
            // Eliminar materiales asociados
            $delete_materials = "DELETE FROM materiales WHERE curso_id = ?";
            $pdo->prepare($delete_materials)->execute([$curso_id]);
            
            // Eliminar sesiones asociadas
            $delete_sessions = "DELETE FROM sesiones WHERE curso_id = ?";
            $pdo->prepare($delete_sessions)->execute([$curso_id]);
            
            // Eliminar tareas y entregas asociadas
            $delete_entregas = "DELETE et FROM entregas_tareas et INNER JOIN tareas t ON et.tarea_id = t.id WHERE t.curso_id = ?";
            $pdo->prepare($delete_entregas)->execute([$curso_id]);
            
            $delete_tareas = "DELETE FROM tareas WHERE curso_id = ?";
            $pdo->prepare($delete_tareas)->execute([$curso_id]);
            
            // Eliminar módulos
            $delete_modules = "DELETE FROM modulos WHERE curso_id = ?";
            $pdo->prepare($delete_modules)->execute([$curso_id]);
            
            // Eliminar el curso
            $delete_course = "DELETE FROM cursos WHERE id = ?";
            $pdo->prepare($delete_course)->execute([$curso_id]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Curso eliminado exitosamente',
                'hard_delete' => true
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Función para notificar a estudiantes sobre eliminación del curso
function notificarEstudiantesEliminacion($pdo, $curso_id, $curso_nombre) {
    try {
        // Obtener estudiantes inscritos
        $query = "
            SELECT u.id, u.email, u.nombre
            FROM usuarios u
            INNER JOIN inscripciones i ON u.id = i.usuario_id
            WHERE i.curso_id = ? AND i.estado = 'activa'
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$curso_id]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Crear notificaciones
        foreach ($estudiantes as $estudiante) {
            $notif_query = "
                INSERT INTO notifications (usuario_id, tipo, titulo, mensaje, created_at)
                VALUES (?, 'curso_eliminado', ?, ?, NOW())
            ";
            
            $notif_stmt = $pdo->prepare($notif_query);
            $notif_stmt->execute([
                $estudiante['id'],
                'Curso será eliminado',
                "El curso '{$curso_nombre}' será eliminado en 30 días. Descarga todo el material que necesites."
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error al notificar estudiantes sobre eliminación: " . $e->getMessage());
    }
}