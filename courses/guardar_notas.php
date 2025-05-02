<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../auth/auth.php';
require_once '../conexion/db.php';

// Verificar autenticación
if (!Auth::isAuthenticated()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$curso_id = filter_input(INPUT_POST, 'curso_id', FILTER_VALIDATE_INT);
$modulo_id = filter_input(INPUT_POST, 'modulo_id', FILTER_VALIDATE_INT);
$contenido_id = filter_input(INPUT_POST, 'contenido_id', FILTER_VALIDATE_INT);
$notas = filter_input(INPUT_POST, 'notas', FILTER_SANITIZE_STRING);
$auto_save = filter_input(INPUT_POST, 'auto_save', FILTER_VALIDATE_BOOLEAN);

// Validar datos
if (!$curso_id || !$modulo_id || !$contenido_id) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Verificar si ya existen notas
    $sql = "SELECT id FROM notas_estudiante 
           WHERE usuario_id = ? AND curso_id = ? AND contenido_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $curso_id, $contenido_id]);
    $existente = $stmt->fetch(PDO::FETCH_COLUMN);
    
    if ($existente) {
        // Actualizar notas existentes
        $sql = "UPDATE notas_estudiante 
               SET notas = ?, fecha_actualizacion = NOW() 
               WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$notas, $existente]);
    } else {
        // Crear nuevas notas
        $sql = "INSERT INTO notas_estudiante 
               (usuario_id, curso_id, contenido_id, notas) 
               VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, $curso_id, $contenido_id, $notas]);
    }
    
    // Responder según el tipo de guardado
    if ($auto_save) {
        echo json_encode(['success' => true, 'message' => 'Notas guardadas automáticamente']);
    } else {
        header('Location: ver-cursos.php?id=' . $curso_id . '&modulo=' . $modulo_id . '&contenido=' . $contenido_id . '&mensaje=notas_guardadas');
    }
} catch (PDOException $e) {
    if ($auto_save) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => 'Error al guardar notas']);
    } else {
        header('Location: ver-cursos.php?id=' . $curso_id . '&modulo=' . $modulo_id . '&contenido=' . $contenido_id . '&error=error_guardar_notas');
    }
}
?>