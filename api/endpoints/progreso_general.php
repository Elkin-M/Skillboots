<?php
header('Content-Type: application/json');
require_once('../../conexion/db.php');
session_start();


$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['error' => 'No autorizado', 'code' => 401]);
    exit;
}

try {
    // Obtener datos generales de progreso
    $sql = "SELECT AVG(uc.progreso) as promedio,
                   COUNT(CASE WHEN uc.completado = 1 THEN 1 END) as cursos_completados,
                   COUNT(uc.id) as total_cursos
            FROM usuarios_cursos uc
            WHERE uc.usuario_id = :usuario_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $general = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener curso más avanzado
    $sql = "SELECT c.nombre, uc.progreso
            FROM usuarios_cursos uc
            JOIN cursos c ON uc.curso_id = c.id
            WHERE uc.usuario_id = :usuario_id
            ORDER BY uc.progreso DESC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $curso_avanzado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener calificación promedio
    $sql = "SELECT AVG(ac.calificacion) as promedio_calificacion
            FROM actividades_completadas ac
            WHERE ac.usuario_id = :usuario_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':usuario_id' => $usuario_id]);

    $calificacion = $stmt->fetch(PDO::FETCH_ASSOC);

    // Contar certificados (se asume que un curso completado genera un certificado)
    $certificados = $general['cursos_completados'];

    echo json_encode([
        'success' => true,
        'data' => [
            'promedio' => round($general['promedio'], 2),
            'cursos_completados' => $general['cursos_completados'],
            'total_cursos' => $general['total_cursos'],
            'certificados' => $certificados,
            'calificacion_promedio' => round($calificacion['promedio_calificacion'], 2),
            'curso_mas_avanzado' => $curso_avanzado['nombre'] ?? 'No hay cursos',
            'porcentaje_mas_alto' => $curso_avanzado['progreso'] ?? 0
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener progreso general: ' . $e->getMessage()
    ]);
}
?>
