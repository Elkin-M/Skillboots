<?php
header('Content-Type: application/json');
require_once '../../conexion/db.php';

// Obtener el parámetro de búsqueda
$nombre_curso = isset($_GET['nombre_curso']) ? $_GET['nombre_curso'] : '';

try {
    $sql = "SELECT c.id, c.nombre, c.descripcion, c.categoria, c.precio, c.duracion, c.nivel, 
                c.imagen, u.name as instructor_nombre 
         FROM cursos c 
         LEFT JOIN usuarios u ON c.instructor_id = u.id 
         WHERE c.nombre LIKE :nombre";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':nombre' => '%'.$nombre_curso.'%']);
    
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'cursos' => $cursos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al buscar cursos: ' . $e->getMessage()
    ]);
}
?>
