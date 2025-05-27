<?php
// ============================================================================
// ARCHIVO: courses/get_inscritos.php
// ============================================================================
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'profesor') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$instructor_id = $_SESSION['user_id'];
$curso_id = $_GET['curso_id'] ?? null;

if (!$curso_id) {
    echo json_encode(['success' => false, 'message' => 'ID del curso requerido']);
    exit;
}

try {
    // Verificar que el curso pertenezca al instructor
    $verify_query = "SELECT id FROM cursos WHERE id = ? AND instructor_id = ?";
    $verify_stmt = $pdo->prepare($verify_query);
    $verify_stmt->execute([$curso_id, $instructor_id]);
    
    if (!$verify_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Curso no encontrado']);
        exit;
    }
    
    // Obtener estudiantes inscritos
    $query = "
        SELECT 
            u.id as usuario_id,
            u.nombre,
            u.email,
            i.fecha_inscripcion,
            i.estado
        FROM usuarios u
        INNER JOIN inscripciones i ON u.id = i.usuario_id
        WHERE i.curso_id = ? AND i.estado = 'activa'
        ORDER BY i.fecha_inscripcion DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$curso_id]);
    $inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas
    foreach ($inscritos as &$inscrito) {
        $inscrito['fecha_inscripcion'] = date('d/m/Y', strtotime($inscrito['fecha_inscripcion']));
    }
    
    echo json_encode([
        'success' => true,
        'inscritos' => $inscritos
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}