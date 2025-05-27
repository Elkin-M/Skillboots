<?php
// ============================================================================
// ARCHIVO: tasks/review_task.php
// ============================================================================
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'profesor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];
$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    header('Location: ../dashboard/index.php');
    exit;
}

try {
    // Obtener detalles de la tarea entregada
    $query = "
        SELECT 
            et.id,
            et.tarea_id,
            et.estudiante_id,
            et.archivo,
            et.comentarios as comentarios_estudiante,
            et.fecha_entrega,
            et.calificacion,
            et.comentarios_profesor,
            t.titulo as tarea_titulo,
            t.descripcion as tarea_descripcion,
            t.fecha_limite,
            c.nombre as curso_nombre,
            u.nombre as estudiante_nombre,
            u.email as estudiante_email
        FROM entregas_tareas et
        INNER JOIN tareas t ON et.tarea_id = t.id
        INNER JOIN cursos c ON t.curso_id = c.id
        INNER JOIN usuarios u ON et.estudiante_id = u.id
        WHERE et.id = ? AND c.instructor_id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$task_id, $instructor_id]);
    $task_detail = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task_detail) {
        header('Location: ../dashboard/index.php');
        exit;
    }
    
} catch (PDOException $e) {
    header('Location: ../dashboard/index.php');
    exit;
}

// Procesar calificación si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calificacion = $_POST['calificacion'] ?? null;
    $comentarios = $_POST['comentarios_profesor'] ?? '';
    
    if ($calificacion !== null && is_numeric($calificacion) && $calificacion >= 0 && $calificacion <= 100) {
        try {
            $update_query = "
                UPDATE entregas_tareas 
                SET calificacion = ?, comentarios_profesor = ?, fecha_calificacion = NOW(), estado = 'calificada'
                WHERE id = ?
            ";
            
            $update_stmt = $pdo->prepare($update_query);
            $result = $update_stmt->execute([$calificacion, $comentarios, $task_id]);
            
            if ($result) {
                // Notificar al estudiante
                $notif_query = "
                    INSERT INTO notifications (usuario_id, tipo, titulo, mensaje, created_at)
                    VALUES (?, 'tarea_calificada', ?, ?, NOW())
                ";
                
                $notif_stmt = $pdo->prepare($notif_query);
                $notif_stmt->execute([
                    $task_detail['estudiante_id'],
                    'Tarea calificada',
                    "Tu tarea '{$task_detail['tarea_titulo']}' ha sido calificada con {$calificacion}/100"
                ]);
                
                $success_message = "Tarea calificada exitosamente";
                $task_detail['calificacion'] = $calificacion;
                $task_detail['comentarios_profesor'] = $comentarios;
            }
        } catch (PDOException $e) {
            $error_message = "Error al guardar la calificación";
        }
    } else {
        $error_message = "La calificación debe ser un número entre 0 y 100";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Tarea - <?= htmlspecialchars($task_detail['tarea_titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-tasks mr-2"></i>Revisar Tarea</h4>
                        <a href="../dashboard/index.php" class="btn btn-secondary btn-sm float-end">
                            <i class="fas fa-arrow-left mr-1"></i>Volver al Dashboard
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle mr-2"></i><?= $success_message ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle mr-2"></i><?= $error_message ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Información de la Tarea</h5>
                                <table class="table">
                                    <tr>
                                        <th>Título:</th>
                                        <td><?= htmlspecialchars($task_detail['tarea_titulo']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Curso:</th>
                                        <td><?= htmlspecialchars($task_detail['curso_nombre']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Estudiante:</th>
                                        <td><?= htmlspecialchars($task_detail['estudiante_nombre']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de entrega:</th>
                                        <td><?= date('d/m/Y H:i', strtotime($task_detail['fecha_entrega'])) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha límite:</th>
                                        <td><?= date('d/m/Y H:i', strtotime($task_detail['fecha_limite'])) ?></td>
                                    </tr>
                                </table>
                                
                                <?php if ($task_detail['archivo']): ?>
                                    <h6>Archivo entregado:</h6>
                                    <a href="../<?= htmlspecialchars($task_detail['archivo']) ?>" 
                                       class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-download mr-1"></i>Descargar archivo
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($task_detail['comentarios_estudiante']): ?>
                                    <h6 class="mt-3">Comentarios del estudiante:</h6>
                                    <div class="alert alert-light">
                                        <?= nl2br(htmlspecialchars($task_detail['comentarios_estudiante'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4">
                                <h5>Calificar Tarea</h5>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="calificacion" class="form-label">Calificación (0-100):</label>
                                        <input type="number" class="form-control" id="calificacion" 
                                               name="calificacion" min="0" max="100" 
                                               value="<?= $task_detail['calificacion'] ?? '' ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="comentarios_profesor" class="form-label">Comentarios:</label>
                                        <textarea class="form-control" id="comentarios_profesor" 
                                                  name="comentarios_profesor" rows="4"><?= htmlspecialchars($task_detail['comentarios_profesor'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i>
                                        <?= $task_detail['calificacion'] ? 'Actualizar Calificación' : 'Calificar Tarea' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
