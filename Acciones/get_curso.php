<?php
// Archivo: get_curso.php
// Descripción: Devuelve la información de un curso específico por su ID

// Configuración de cabeceras para respuesta JSON
header('Content-Type: application/json');

// Prevenir errores de CORS si es necesario
// header('Access-Control-Allow-Origin: *');

// Incluir archivo de conexión a la base de datos
require_once '../conexion/db.php';

// Verificar que se recibió el ID del curso
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de curso no proporcionado'
    ]);
    exit;
}

// Obtener y sanitizar el ID del curso
$cursoId = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->getConnection();
    
    // Preparar la consulta SQL para obtener información del curso
    $query = "SELECT c.id, c.titulo, c.descripcion, c.fecha_creacion, c.estado, c.imagen,
              (SELECT COUNT(*) FROM usuarios_cursos uc WHERE uc.curso_id = c.id) as inscritos_actuales
              FROM cursos c 
              WHERE c.id = :id";
    
    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $cursoId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Verificar si se encontró el curso
    if ($stmt->rowCount() > 0) {
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Determinar un valor predeterminado para el total de plazas
        // Puedes ajustar este valor según tus necesidades
        $plazas_totales = 30; // Valor predeterminado como ejemplo
        
        // Formatear los datos para la respuesta
        $response = [
            'success' => true,
            'curso' => [
                'id' => $curso['id'],
                'titulo' => $curso['titulo'],
                'descripcion' => $curso['descripcion'],
                'fecha_inicio' => $curso['fecha_creacion'],
                'plazas_totales' => $plazas_totales,
                'inscritos' => [
                    'total' => $plazas_totales,
                    'completadas' => $curso['inscritos_actuales']
                ],
                'estado' => $curso['estado'],
                'imagen' => $curso['imagen']
            ]
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Curso no encontrado'
        ];
    }
    
    // Devolver la respuesta
    echo json_encode($response);
    
} catch (PDOException $e) {
    // Manejar errores de la base de datos
    error_log('Error de base de datos: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener información del curso: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Manejar otros errores
    error_log('Error general: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
?>