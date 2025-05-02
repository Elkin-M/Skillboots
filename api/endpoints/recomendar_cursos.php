<?php
header('Content-Type: application/json');
require_once '../../conexion/db.php';
session_start();

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    responderJSON(['error' => 'No autorizado', 'code' => 401]);
    exit;
}

try {
    // Obtener categorías de interés basadas en cursos tomados
    $sql = "SELECT c.categoria
            FROM usuarios_cursos uc
            JOIN cursos c ON uc.curso_id = c.id
            WHERE uc.usuario_id = :usuario_id
            GROUP BY c.categoria";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Si no hay categorías, recomendar cursos populares
    if (empty($categorias)) {
        $sql = "SELECT c.id, c.nombre, c.descripcion, c.categoria, c.nivel, c.duracion, c.precio,
                       CONCAT(u.name, ' ', u.lastname) as instructor
                FROM cursos c
                JOIN usuarios u ON c.instructor_id = u.id
                WHERE c.estado = 'publicado'
                ORDER BY c.vistas DESC
                LIMIT 5";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $recomendaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'tipo' => 'populares',
                'cursos' => $recomendaciones
            ]
        ]);
    } else {
        // Obtener cursos de categorías similares no tomados por el usuario
        $placeholders = implode(',', array_fill(0, count($categorias), '?'));

        $sql = "SELECT c.id, c.nombre, c.descripcion, c.categoria, c.nivel, c.duracion, c.precio,
                       CONCAT(u.name, ' ', u.lastname) as instructor
                FROM cursos c
                JOIN usuarios u ON c.instructor_id = u.id
                LEFT JOIN usuarios_cursos uc ON c.id = uc.curso_id AND uc.usuario_id = ?
                WHERE c.categoria IN ($placeholders)
                AND c.estado = 'publicado'
                AND uc.id IS NULL
                ORDER BY c.vistas DESC
                LIMIT 5";

        $stmt = $conn->prepare($sql);
        $params = array_merge([$usuario_id], $categorias);
        $stmt->execute($params);

        $recomendaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'tipo' => 'personalizadas',
                'cursos' => $recomendaciones
            ]
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener recomendaciones de cursos: ' . $e->getMessage()
    ]);
}
?>
