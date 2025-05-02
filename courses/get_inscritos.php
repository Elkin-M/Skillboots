<?php
session_start();
include "../conexion/db.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autorizado']);
    exit;
}

// Obtener y validar el ID del curso
$curso_id = isset($_GET['curso_id']) ? filter_var($_GET['curso_id'], FILTER_VALIDATE_INT) : 0;

if (!$curso_id) {
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
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver los inscritos de este curso']);
        exit;
    }
    
    // Obtener inscritos
    $stmt = $conn->prepare("
        SELECT 
            u.name AS nombre,
            u.email,
            i.ultimo_acceso AS fecha_inscripcion
        FROM 
            usuarios_cursos i 
        INNER JOIN 
            usuarios u ON i.usuario_id = u.id
        WHERE 
            i.curso_id = :curso_id
        ORDER BY 
            i.ultimo_acceso DESC
    ");
    $stmt->bindParam(':curso_id', $curso_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar si hay inscritos y formatear la fecha
    if (empty($inscritos)) {
        echo json_encode(['success' => true, 'inscritos' => []]);
        exit;
    }

    foreach ($inscritos as &$inscrito) {
        if (isset($inscrito['fecha_inscripcion'])) {
            $fecha = new DateTime($inscrito['fecha_inscripcion']);
            $inscrito['fecha_inscripcion'] = $fecha->format('d/m/Y H:i');
        }
    }
    
    // Enviar respuesta
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'inscritos' => $inscritos]);
    
} catch (PDOException $e) {
    error_log('Error en get_inscritos.php: ' . $e->getMessage());
    
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
}
?>
