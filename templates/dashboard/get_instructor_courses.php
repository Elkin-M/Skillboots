<?php
session_start();
require_once '../../conexion/db.php';

header('Content-Type: application/json');

// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Depuración: Verificar que $_SESSION esté disponible
if (!isset($_SESSION)) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
    exit;
}

// Depuración: Verificar usuario y rol
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'profesor') {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado: sesión inválida o rol incorrecto',
        'debug' => $_SESSION // 👈 Esto mostrará la sesión completa (solo en desarrollo)
    ]);
    exit;
}

$instructor_id = $_SESSION['user_id'];

try {
    // Consulta para obtener cursos activos del instructor
    $query = "
        SELECT 
            id,
            nombre,
            descripcion,
            estado
        FROM cursos 
        WHERE instructor_id = ? 
        AND estado IN ('publicado', 'en_progreso')
        ORDER BY nombre ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$instructor_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Depuración: Validar contenido
    echo json_encode([
        'success' => true,
        'courses' => $courses,
        'debug' => [
            'instructor_id' => $instructor_id,
            'total' => count($courses)
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos',
        'error' => $e->getMessage()
    ]);
}
