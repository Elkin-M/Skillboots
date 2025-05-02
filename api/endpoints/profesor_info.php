<?php
header('Content-Type: application/json');
require_once('../../conexion/db.php');
session_start();

// Obtener parámetro de búsqueda
$nombre_profesor = isset($_GET['nombre_profesor']) ? $_GET['nombre_profesor'] : '';

try {
    $sql = "SELECT u.id, CONCAT(u.name, ' ', u.lastname) as nombre,
               COUNT(c.id) as total_cursos
        FROM usuarios u
        LEFT JOIN cursos c ON u.id = c.instructor_id
        WHERE u.rol = 'profesor'
        AND (u.name LIKE :nombre1 OR u.lastname LIKE :nombre2)
        GROUP BY u.id";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':nombre1' => '%'.$nombre_profesor.'%',
    ':nombre2' => '%'.$nombre_profesor.'%'
]);

    
    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profesor) {
        // Obtener los cursos impartidos por el profesor
        $sql = "SELECT c.nombre, c.descripcion, c.nivel, c.duracion
                FROM cursos c
                WHERE c.instructor_id = :instructor_id
                AND c.estado = 'publicado'";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':instructor_id' => $profesor['id']]);

        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $profesor['cursos'] = $cursos;

        echo json_encode([
            'success' => true,
            'data' => $profesor
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el profesor'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener información del profesor: ' . $e->getMessage()
    ]);
}
?>
