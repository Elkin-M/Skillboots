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

// Validar datos
if (!$curso_id || !$modulo_id || !$contenido_id) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Registrar progreso
    $sql = "INSERT INTO progreso_contenido 
           (usuario_id, curso_id, modulo_id, contenido_id, completado, fecha_acceso) 
           VALUES (?, ?, ?, ?, 1, NOW())
           ON DUPLICATE KEY UPDATE completado = 1, fecha_acceso = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $curso_id, $modulo_id, $contenido_id]);
    
    // Actualizar progreso en usuarios_cursos
    $sql = "SELECT COUNT(*) as total FROM contenido_modular cm
           JOIN modulos m ON cm.modulo_id = m.id
           WHERE m.curso_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$curso_id]);
    $total = $stmt->fetch(PDO::FETCH_COLUMN);
    
    $sql = "SELECT COUNT(*) as completados FROM progreso_contenido
           WHERE usuario_id = ? AND curso_id = ? AND completado = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $curso_id]);
    $completados = $stmt->fetch(PDO::FETCH_COLUMN);
    
    $progreso = ($total > 0) ? ($completados / $total) * 100 : 0;
    
    $sql = "INSERT INTO usuarios_cursos (usuario_id, curso_id, progreso, lecciones_completadas, ultimo_acceso)
           VALUES (?, ?, ?, ?, NOW())
           ON DUPLICATE KEY UPDATE progreso = ?, lecciones_completadas = ?, ultimo_acceso = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $curso_id, $progreso, $completados, $progreso, $completados]);
    
    echo json_encode(['success' => true, 'message' => 'Progreso actualizado']);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Error al actualizar progreso']);
}
?>