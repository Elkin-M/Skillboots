<?php
session_start();
require_once '../conexion/db.php';

/**
 * Script para eliminar cursos. Puede funcionar en dos modos:
 * 1. Modo manual: Un instructor elimina uno de sus cursos (requiere sesión)
 * 2. Modo automático: Elimina permanentemente cursos marcados como eliminados hace 30+ días
 */

// Determinar el modo de funcionamiento
$modo_automatico = isset($_GET['modo']) && $_GET['modo'] === 'automatico';

// En modo automático no se requiere autenticación (se ejecutaría mediante CRON)
if (!$modo_automatico) {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autorizado']);
        exit;
    }
    
    // Obtener el ID del curso
    $curso_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($curso_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de curso no válido']);
        exit;
    }
    
    try {
        // Verificar que el curso pertenezca al instructor
        $stmt = $conn->prepare("SELECT id FROM cursos WHERE id = :curso_id AND instructor_id = :instructor_id");
        $stmt->bindParam(':curso_id', $curso_id, PDO::PARAM_INT);
        $stmt->bindParam(':instructor_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este curso']);
            exit;
        }
        
        // Marcar el curso como eliminado en lugar de eliminarlo directamente
        $stmt = $conn->prepare("UPDATE cursos SET estado = 'eliminado', fecha_eliminacion = NOW() WHERE id = :curso_id");
        $stmt->bindParam(':curso_id', $curso_id, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Curso marcado para eliminación. Se eliminará permanentemente en 30 días.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    // Modo automático - Eliminación permanente de cursos marcados hace más de 30 días
    try {
        // Formato de respuesta según el contexto (CLI o Web)
        $is_cli = php_sapi_name() === 'cli';
        $log = [];
        
        // Obtener cursos marcados como eliminados hace más de 30 días
        $stmt = $conn->prepare(
            "SELECT id FROM cursos 
             WHERE estado = 'eliminado' 
             AND fecha_eliminacion < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $stmt->execute();
        $cursos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $eliminados = 0;
        $errores = 0;
        
        // Para cada curso, eliminar sus registros relacionados y luego el curso
        foreach ($cursos as $curso_id) {
            // Comenzar una transacción para garantizar integridad
            $conn->beginTransaction();
            
            try {
                // Eliminar registros relacionados
                $conn->prepare("DELETE FROM course_ratings WHERE curso_id = ?")->execute([$curso_id]);
                $conn->prepare("DELETE FROM curso_lecciones WHERE curso_id = ?")->execute([$curso_id]);
                $conn->prepare("DELETE FROM curso_inscripciones WHERE curso_id = ?")->execute([$curso_id]);
                // Agregar más tablas relacionadas según sea necesario
                
                // Finalmente eliminar el curso
                $conn->prepare("DELETE FROM cursos WHERE id = ?")->execute([$curso_id]);
                
                // Confirmar transacción
                $conn->commit();
                $eliminados++;
                $log[] = "Curso ID $curso_id eliminado correctamente";
            } catch (Exception $e) {
                // Revertir en caso de error
                $conn->rollBack();
                $errores++;
                $log[] = "Error al eliminar curso ID $curso_id: " . $e->getMessage();
                error_log("Error al eliminar curso ID $curso_id: " . $e->getMessage());
            }
        }
        
        $mensaje = "Proceso completado. Se eliminaron permanentemente $eliminados cursos. Errores: $errores.";
        
        // Devolver respuesta según el contexto
        if ($is_cli) {
            echo $mensaje . "\n";
            if (!empty($log)) {
                echo "Detalles:\n" . implode("\n", $log) . "\n";
            }
        } else {
            echo json_encode([
                'success' => true,
                'message' => $mensaje,
                'eliminados' => $eliminados,
                'errores' => $errores,
                'log' => $log
            ]);
        }
        
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
        if ($is_cli) {
            echo $mensaje . "\n";
        } else {
            echo json_encode(['success' => false, 'message' => $mensaje]);
        }
    }
}
?>