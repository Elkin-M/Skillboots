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
        echo json_encode(['success' => false, 'message' => 'Curso no encontrado o no autorizado']);
        exit;
    }
    
    // Consulta para obtener módulos del curso
    $query = "
        SELECT 
            id,
            titulo,
            descripcion,
            orden
        FROM modulos 
        WHERE curso_id = ?
        ORDER BY orden ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$curso_id]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'modules' => $modules
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}