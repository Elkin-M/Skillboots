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

// Validar datos requeridos
$titulo = $_POST['titulo'] ?? '';
$curso_id = $_POST['curso_id'] ?? '';
$modulo_id = $_POST['modulo_id'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$contenido = $_POST['contenido'] ?? '';
$obligatorio = isset($_POST['obligatorio']) ? 1 : 0;

if (empty($titulo) || empty($curso_id) || empty($modulo_id) || empty($tipo)) {
    echo json_encode(['success' => false, 'message' => 'Datos requeridos faltantes']);
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
    
    // Verificar que el módulo exista y pertenezca al curso
    $verify_module_query = "SELECT id FROM modulos WHERE id = ? AND curso_id = ?";
    $verify_module_stmt = $pdo->prepare($verify_module_query);
    $verify_module_stmt->execute([$modulo_id, $curso_id]);
    
    if (!$verify_module_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Módulo no encontrado o no válido']);
        exit;
    }
    
    $archivo_url = null;
    
    // Procesar archivo si no es tipo texto
    if ($tipo !== 'texto') {
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
            exit;
        }
        
        $archivo = $_FILES['archivo'];
        
        // Validar tamaño (50MB máximo)
        if ($archivo['size'] > 50 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande (máximo 50MB)']);
            exit;
        }
        
        // Validar tipo de archivo
        $tipos_permitidos = [
            'pdf' => ['application/pdf'],
            'video' => ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv'],
            'imagen' => ['image/jpeg', 'image/png', 'image/gif', 'image/jpg']
        ];
        
        if (isset($tipos_permitidos[$tipo]) && !in_array($archivo['type'], $tipos_permitidos[$tipo])) {
            echo json_encode(['success' => false, 'message' => 'Tipo de archivo no válido']);
            exit;
        }
        
        // Crear directorio si no existe
        $upload_dir = "../uploads/materiales/curso_{$curso_id}/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_archivo = uniqid('material_') . '.' . $extension;
        $ruta_completa = $upload_dir . $nombre_archivo;
        
        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
            exit;
        }
        
        $archivo_url = "uploads/materiales/curso_{$curso_id}/" . $nombre_archivo;
    }
    
    // Insertar material en la base de datos
    $query = "
        INSERT INTO materiales (curso_id, modulo_id, titulo, tipo, archivo_url, contenido, obligatorio, instructor_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $curso_id,
        $modulo_id,
        $titulo,
        $tipo,
        $archivo_url,
        $contenido,
        $obligatorio,
        $instructor_id
    ]);
    
    if ($result) {
        $material_id = $pdo->lastInsertId();
        
        // Opcional: Notificar a estudiantes
        notificarEstudiantesMaterial($pdo, $curso_id, $material_id, $titulo);
        
        echo json_encode([
            'success' => true,
            'message' => 'Material subido exitosamente',
            'material_id' => $material_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el material']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Función para notificar a estudiantes (opcional)
function notificarEstudiantesMaterial($pdo, $curso_id, $material_id, $titulo) {
    try {
        // Obtener estudiantes inscritos
        $query = "
            SELECT u.id, u.email, u.nombre
            FROM usuarios u
            INNER JOIN inscripciones i ON u.id = i.usuario_id
            WHERE i.curso_id = ? AND i.estado = 'activa'
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$curso_id]);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Crear notificaciones
        foreach ($estudiantes as $estudiante) {
            $notif_query = "
                INSERT INTO notifications (usuario_id, tipo, titulo, mensaje, created_at)
                VALUES (?, 'nuevo_material', ?, ?, NOW())
            ";
            
            $notif_stmt = $pdo->prepare($notif_query);
            $notif_stmt->execute([
                $estudiante['id'],
                'Nuevo material disponible',
                "Se ha subido nuevo material: {$titulo}"
            ]);
        }
        
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Error al notificar estudiantes: " . $e->getMessage());
    }
}