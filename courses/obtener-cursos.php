<?php
include './conexion/db.php';  // Asegúrate de que este archivo crea una conexión PDO llamada $conn

header('Content-Type: application/json');

try {
    // Obtener el ID del usuario
    session_start();
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;

    // Consulta SQL con prepared statements
    $sql = "SELECT 
                c.id, 
                c.nombre AS titulo, 
                c.descripcion, 
                c.imagen,
                uc.progreso, 
                uc.ultimo_acceso,
                uc.lecciones_completadas,
                c.total_lecciones,
                c.horas_totales * (1 - uc.progreso/100) AS horas_restantes,
                CASE 
                    WHEN uc.progreso = 0 THEN 'No iniciado' 
                    WHEN uc.progreso = 100 THEN 'Finalizado' 
                    ELSE 'En progreso' 
                END AS estado
            FROM 
                cursos c
            LEFT JOIN 
                usuarios_cursos uc ON c.id = uc.curso_id AND uc.usuario_id = :usuario_id
            WHERE 
                uc.usuario_id = :usuario_id
            ORDER BY 
                uc.ultimo_acceso DESC
            LIMIT 6";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();

    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar resultados
    $response = [];
    foreach ($cursos as $row) {
        // Determinar el color del progreso (misma lógica que antes)
        if ($row["progreso"] === 0) {
            $color = "secondary";
        } elseif ($row["progreso"] === 100) {
            $color = "success";
        } elseif ($row["progreso"] < 30) {
            $color = "danger";
        } elseif ($row["progreso"] < 60) {
            $color = "warning";
        } else {
            $color = "primary";
        }

        // Formatear último acceso (misma lógica que antes)
        $ultimoAcceso = new DateTime($row["ultimo_acceso"]);
        $hoy = new DateTime();
        $diferencia = $ultimoAcceso->diff($hoy);
        
        if ($diferencia->days == 0) {
            $textoUltimoAcceso = "Hoy";
        } elseif ($diferencia->days == 1) {
            $textoUltimoAcceso = "Ayer";
        } elseif ($diferencia->days < 7) {
            $textoUltimoAcceso = "Hace " . $diferencia->days . " días";
        } else {
            $textoUltimoAcceso = "Hace " . floor($diferencia->days / 7) . " semanas";
        }

        // Construir respuesta
        $response[] = [
            "id" => $row["id"],
            "titulo" => $row["titulo"],
            "descripcion" => $row["descripcion"],
            "imagen" => $row["imagen"],
            "progreso" => $row["progreso"],
            "colorProgreso" => $color,
            "ultimoAcceso" => $textoUltimoAcceso,
            "lecciones" => [
                "completadas" => $row["lecciones_completadas"],
                "total" => $row["total_lecciones"]
            ],
            "horasRestantes" => $row["horas_restantes"],
            "estado" => $row["estado"],
            "estadoClase" => ($row["progreso"] === 0) ? "muted" : "success"
        ];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    // Manejo de errores
    http_response_code(500);
    echo json_encode([
        "error" => "Error en la base de datos",
        "message" => $e->getMessage()
    ]);
}
?>