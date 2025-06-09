<?php
session_start();
require_once '../conexion/db.php'; // Asegúrate de usar la ruta correcta a tu archivo de conexión



// Verificar si se proporcionó un ID de curso válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirigir a la página principal si no hay ID válido
    header('Location: index.php');
    exit;
}

$curso_id = (int)$_GET['id'];

try {
    // Obtener información del curso
// Obtener información del curso
$sql = "SELECT c.*, u.name as instructor_nombre , lastname as instructor_apellido
        FROM cursos c
        LEFT JOIN usuarios u ON c.instructor_id = u.id
        WHERE c.id = :curso_id AND c.estado = 'publicado' AND c.estate = 'activo'";


    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);

    if ($stmt->rowCount() === 0) {
        echo "Curso no encontrado";
        exit;
    }
    
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener los módulos del curso ordenados por el campo orden
    $sql = "SELECT * FROM modulos WHERE curso_id = :curso_id ORDER BY orden ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener valoraciones del curso
$sql = "SELECT rating FROM course_ratings WHERE curso_id = :curso_id";
$stmt = $conn->prepare($sql);
$stmt->execute([':curso_id' => $curso_id]);
$valoraciones = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Calcular promedio de valoraciones
if (!empty($valoraciones)) {
    $promedio = round(array_sum($valoraciones) / count($valoraciones), 1);
} else {
    $promedio = 0.0;
}

    // Obtener cursos relacionados por categoría (excluyendo el curso actual)
    $sql = "SELECT * FROM cursos 
WHERE categoria = :categoria 
AND id != :curso_id 
AND estado = 'publicado' 
AND estate = 'activo' 
LIMIT 6";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':categoria' => $curso['categoria'],
    ':curso_id' => $curso_id
]);

    
$cursos_relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Para cada módulo, obtener recursos, actividades y contenido modular
    foreach ($modulos as &$modulo) {
        // Obtener recursos (unidad_id en la tabla recursos corresponde a modulo_id)
        $sql = "SELECT * FROM recursos WHERE unidad_id = :modulo_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':modulo_id' => $modulo['id']]);
        $modulo['recursos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener actividades
        $sql = "SELECT * FROM actividades WHERE unidad_id = :modulo_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':modulo_id' => $modulo['id']]);
        $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Para cada actividad, obtener sus preguntas
        foreach ($actividades as &$actividad) {
            $sql = "SELECT * FROM preguntas WHERE actividad_id = :actividad_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':actividad_id' => $actividad['id']]);
            $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Para cada pregunta, obtener sus opciones
            foreach ($preguntas as &$pregunta) {
                $sql = "SELECT * FROM opciones WHERE pregunta_id = :pregunta_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':pregunta_id' => $pregunta['id']]);
                $pregunta['opciones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $actividad['preguntas'] = $preguntas;
        }
        
        $modulo['actividades'] = $actividades;
        
        // Obtener contenido modular
        $sql = "SELECT * FROM contenido_modular WHERE modulo_id = :modulo_id ORDER BY orden ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':modulo_id' => $modulo['id']]);
        $modulo['contenido_modular'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Verificar si el usuario está inscrito en el curso
    $usuario_inscrito = false;
    $progreso_curso = 0;
    
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Consultar si el usuario está inscrito
        $sql = "SELECT * FROM usuarios_cursos WHERE curso_id = :curso_id AND usuario_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':curso_id' => $curso_id,
            ':user_id' => $user_id
        ]);
        
        if ($stmt->rowCount() > 0) {
            $usuario_inscrito = true;
                
                // Obtener progreso del curso
                $sql = "SELECT AVG(progreso) as progreso FROM usuarios_cursos WHERE curso_id = :curso_id AND usuario_id = :user_id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':curso_id' => $curso_id,
                    ':user_id' => $user_id
                ]);
                
                $progreso_curso = round($stmt->fetch(PDO::FETCH_ASSOC)['progreso'], 2);
        }
    }
    
    // Actualizar contador de vistas
    $sql = "UPDATE cursos SET vistas = vistas + 1 WHERE id = :curso_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':curso_id' => $curso_id]);
    
} catch (Exception $e) {
    error_log("Error en curso.php: " . $e->getMessage());
    echo "Error al cargar el curso. Por favor, inténtalo de nuevo.";
    exit;
}

// Obtener número de inscritos
$sql = "SELECT COUNT(*) as inscritos FROM usuarios_cursos WHERE curso_id = :curso_id";
$stmt = $conn->prepare($sql);
$stmt->execute([':curso_id' => $curso_id]);
$inscritos = $stmt->fetch(PDO::FETCH_ASSOC)['inscritos'];

// Obtener número de vistas
$sql = "SELECT vistas FROM cursos WHERE id = :curso_id";
$stmt = $conn->prepare($sql);
$stmt->execute([':curso_id' => $curso_id]);
$vistas = $stmt->fetch(PDO::FETCH_ASSOC)['vistas'];




// Construir las rutas base para enlaces
$base_path = dirname($_SERVER['PHP_SELF']);
if (substr($base_path, -1) !== '/') {
    $base_path .= '/';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/skillboots/includes/head.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($curso['nombre']); ?> - SkillBoots</title>
    
    <!-- CSS Styles -->
    <link href="../assets/css/dashboard-profesor.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8fafc;
        }

        /* Header del curso mejorado */
        .curso-hero {
            background:url(https://www.revista-ballesol.com/wp-content/uploads/2024/05/autism-day-with-colorful-portrait.webp);
            color: white !important;
            padding: 4rem 0;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .curso-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
        }

        .curso-hero-content {
            position: relative;
            z-index: 2;
        }

        .curso-title {
            color: white;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .curso-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .curso-badges {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .badge-custom {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .instructor-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .instructor-avatar {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Sidebar mejorado */
        .curso-sidebar {
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .price-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            border: none;
        }

        .price-header {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .price-amount {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .price-period {
            opacity: 0.8;
            font-size: 1rem;
        }

        .progress-course {
            padding: 1.5rem;
            background: #f8f9fa;
        }

        .progress-custom {
            height: 12px;
            background-color: #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-bar-custom {
            background: linear-gradient(90deg, var(--success-color), #20c997);
            height: 100%;
            border-radius: 6px;
            transition: width 0.3s ease;
            position: relative;
        }

        .btn-enroll {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-enroll:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
        }

        .course-stats {
            padding: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .stat-item:last-child {
            margin-bottom: 0;
        }

        .stat-icon {
            width: 20px;
            text-align: center;
            color: var(--primary-color);
        }

        /* Estilos del contenido del curso */
        .course-description-section,
        .course-curriculum {
            margin-bottom: 4rem;
        }

        .content-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 2rem;
            border-bottom: 1px solid #e9ecef;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .section-subtitle {
            color: #6c757d;
            margin-bottom: 0;
            font-size: 1rem;
        }

        .card-body-custom {
            padding: 2rem;
        }

        /* Grid de características */
        .course-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-item {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .feature-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .feature-content h5 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .feature-content p {
            color: #6c757d;
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        /* Acordeón personalizado */
        .custom-accordion {
            border: none;
        }

        .accordion-item-custom {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            overflow: hidden;
            transition: var(--transition);
        }

        .accordion-item-custom:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .accordion-header-custom {
            margin-bottom: 0;
        }

        .accordion-button-custom {
            background: white;
            border: none;
            padding: 1.5rem;
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition);
            cursor: pointer;
        }

        .accordion-button-custom:not(.collapsed) {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .accordion-button-custom:focus {
            box-shadow: none;
            outline: none;
        }

        .module-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .module-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .module-info {
            flex: 1;
        }

        .module-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--dark-color);
        }

        .module-meta {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .lesson-count {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            align-items: center;
        }

        .accordion-icon {
            transition: transform 0.3s ease;
        }

        .accordion-button-custom:not(.collapsed) .accordion-icon {
            transform: rotate(180deg);
        }

        .accordion-body-custom {
            padding: 0;
        }

        .module-description {
            padding: 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .module-description p {
            margin-bottom: 0;
            color: #6c757d;
        }

        /* Lista de lecciones */
        .lesson-list {
            padding: 0;
        }

        .lesson-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f1f1;
            transition: var(--transition);
        }

        .lesson-item:hover {
            background: #f8f9fa;
        }

        .lesson-item:last-child {
            border-bottom: none;
        }

        .lesson-item.locked {
            opacity: 0.6;
        }

        .lesson-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .lesson-content {
            flex: 1;
        }

        .lesson-title {
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--dark-color);
        }

        .lesson-meta {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .lesson-type {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .badge-danger-custom {
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }

        .badge-warning-custom {
            background: #ffc107;
            color: #212529;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }

        .lesson-action {
            flex-shrink: 0;
        }

        .lock-icon {
            color: #6c757d;
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .curso-title {
                font-size: 2rem;
            }
            
            .curso-badges {
                gap: 0.5rem;
            }
            
            .instructor-card {
                flex-direction: column;
                text-align: center;
            }
            
            .price-amount {
                font-size: 2rem;
            }

            .course-features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .feature-item {
                padding: 1rem;
            }

            .card-header-custom {
                padding: 1.5rem;
            }

            .card-body-custom {
                padding: 1.5rem;
            }

            .module-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .lesson-item {
                padding: 0.75rem 1rem;
            }

            .lesson-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
        }
         /* Sección de Valoraciones */
         .reviews-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 3rem;
            overflow: hidden;
        }

        .reviews-header {
            background: linear-gradient(135deg, rgba(255, 146, 29, 0.85) 0%, rgba(151, 52, 251, 0.85) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .reviews-header h2 {
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .rating-overview {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .rating-score {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .rating-stars {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .rating-count {
            opacity: 0.9;
            font-size: 1rem;
        }

        /* Formulario de valoración */
        .rating-form {
            background: #f8f9fa;
            padding: 2rem;
            border-bottom: 1px solid #e9ecef;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 2rem;
            color: #ddd;
            transition: var(--transition);
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: var(--warning-color);
            transform: scale(1.1);
        }

        .rating-form .btn {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .rating-form .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);
        }

        /* Lista de comentarios */
        .reviews-list {
            padding: 2rem;
        }

        .review-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .review-item:hover {
            box-shadow: var(--box-shadow);
            transform: translateY(-2px);
        }

        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }

        .reviewer-avatar.placeholder {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .reviewer-info h6 {
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .review-date {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .review-rating {
            margin-left: auto;
            font-size: 1.25rem;
        }

        .review-content {
            line-height: 1.6;
            color: #495057;
        }

        .no-reviews {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-reviews i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Cursos Relacionados */
        .related-courses-section {
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            color: #2c3e50;
        }

        .course-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
            border: none;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .course-image {
            height: 200px;
            object-fit: fill;
            width: 100%;
        }

        .course-image-placeholder {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .course-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .course-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2c3e50;
            line-height: 1.4;
        }

        .course-badges {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .course-badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .course-price {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            margin-top: auto;
        }

        .course-price.free {
            color: var(--success-color);
        }

        .course-price.paid {
            color: var(--primary-color);
        }

        .course-footer {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .btn-course {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-course:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.3);
        }

        .no-related-courses {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .no-related-courses i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .rating-overview {
                text-align: center;
            }

            .rating-score {
                font-size: 2.5rem;
            }

            .star-rating {
                justify-content: center;
            }

            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .review-rating {
                margin-left: 0;
            }

            .section-title {
                font-size: 2rem;
            }

            .course-content {
                padding: 1rem;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .review-item {
            animation: fadeInUp 0.6s ease forwards;
        }

        .course-card {
            animation: fadeInUp 0.6s ease forwards;
        }
        @media (min-width: 992px) {
  .curso-relacionado-unico {
    flex: 0 0 33.3% !important;
    max-width: 205% !important;
  }
}

/* OPCIÓN 1: Gradiente sobre imagen (Recomendada) */
.curso-hero {
    padding: 80px 0;
    position: relative;
    overflow: hidden;
    min-height: 500px;
}

.curso-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.2);
    z-index: 1;
}

.content-overlay {
    position: relative;
    z-index: 2;
    color: white;
}

.curso-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.curso-subtitle {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    line-height: 1.6;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

/* Course Meta Information */
.course-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
    margin-bottom: 2rem;
}

.badge-custom {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}

.badge-custom:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* Instructor Section */
.instructor-highlight-center {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.instructor-avatar {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.instructor-info {
    text-align: left;
}

.instructor-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.instructor-rating {
    font-size: 0.9rem;
    opacity: 0.9;
}

/* Content Cards */
.content-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 2rem;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header-custom {
    background: linear-gradient(135deg, #f8f9ff 0%, #e3e8ff 100%);
    padding: 2rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.section-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
    display: flex;
    align-items: center;
}

.section-title i {
    color: #667eea;
}

.card-body-custom {
    padding: 2rem;
}

/* Features Grid */
.course-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8f9ff;
    border-radius: 12px;
    border-left: 4px solid #667eea;
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.feature-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.feature-content h5 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.feature-content p {
    color: #4a5568;
    margin: 0;
    line-height: 1.5;
}

/* Price Card Sidebar */
.curso-sidebar {
    position: sticky;
    top: 2rem;
}

.price-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.price-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 1.5rem;
}

.price-amount {
    font-size: 3rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.price-period {
    font-size: 1rem;
    opacity: 0.9;
}

/* Progress Container */
.progress-course {
    padding: 1.5rem;
    background: #f8f9ff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.progress-custom {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar-custom {
    height: 100%;
    background: linear-gradient(90deg, #48bb78, #38a169);
    border-radius: 4px;
    transition: width 0.5s ease;
}

/* Enrollment Button */
.btn-enroll {
    font-size: 1.1rem;
    font-weight: 600;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: none;
}

.btn-enroll:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-primary.btn-enroll {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.btn-success.btn-enroll {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

/* Course Stats */
.course-stats {
    padding: 1.5rem;
    background: #f8f9ff;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    color: #4a5568;
    font-size: 0.9rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-icon {
    color: #667eea;
    width: 18px;
    text-align: center;
}

/* Responsive Design */
@media (max-width: 992px) {
    .curso-hero {
        padding: 60px 0;
    }
    
    .curso-title {
        font-size: 2.5rem;
    }
    
    .curso-subtitle {
        font-size: 1.1rem;
    }
    
    .instructor-highlight-center {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .instructor-info {
        text-align: center;
    }
    
    .course-features-grid {
        grid-template-columns: 1fr;
    }
    
    .curso-sidebar {
        position: static;
        margin-top: 2rem;
    }
}

@media (max-width: 768px) {
    .curso-hero {
        padding: 40px 0;
    }
    
    .curso-title {
        font-size: 2rem;
    }
    
    .curso-subtitle {
        font-size: 1rem;
    }
    
    .course-meta {
        justify-content: center;
        gap: 0.5rem;
    }
    
    .badge-custom {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    
    .card-header-custom,
    .card-body-custom {
        padding: 1.5rem;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    .feature-item {
        padding: 1rem;
    }
    
    .price-amount {
        font-size: 2.5rem;
    }
}

@media (max-width: 576px) {
    .curso-title {
        font-size: 1.75rem;
    }
    
    .instructor-highlight-center {
        gap: 0.75rem;
    }
    
    .instructor-avatar {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .course-features-grid {
        gap: 1rem;
    }
    
    .feature-item {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }
    
    .feature-icon {
        margin: 0 auto;
    }
}

    </style>
</head>

<body>
    <!-- Navbar PHP se mantiene igual -->
    <?php 
    require_once '../auth/auth.php';
    $isLoggedIn = Auth::isAuthenticated();
    $userRole = $isLoggedIn ? Auth::getUserRole() : 'visitante';
    $userName = $isLoggedIn && isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

    $pageData = [
        'isLoggedIn' => $isLoggedIn,
        'userRole' => $userRole,
        'userName' => $userName
    ];

    if ($isLoggedIn && $userRole === 'estudiante') {
        include '../includes/navbar-estu.php';
    } elseif ($pageData['userRole'] === 'profesor'){
        include '../includes/navbar-pro.php';
    } else {
        include '../includes/navbar.php';
    }
    ?>

    <!-- Hero Section del Curso - Solo contenido principal -->
<!-- Hero Section del Curso - Con imagen de fondo -->
<section class="curso-hero" style="background: 
    linear-gradient(135deg, rgba(255, 146, 29, 0.85) 0%, rgba(151, 52, 251, 0.85) 100%),
    url('<?php echo htmlspecialchars($curso["imagen"] ?? "ruta/por/defecto.jpg"); ?>') center/cover no-repeat;">
    <div class="container">
        <div class="row justify-content-center">
            <!-- Contenido Principal Centrado -->
            <div class="col-lg-10 col-xl-8">
                <div class="content-overlay text-center">
                    <!-- Título y descripción -->
                    <h1 class="curso-title"><?php echo htmlspecialchars($curso['nombre']); ?></h1>
                    <p class="curso-subtitle"><?php echo htmlspecialchars($curso['descripcion']); ?></p>

                    <!-- Meta información del curso -->
                    <div class="course-meta justify-content-center">
                        <span class="badge-custom">
                            <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($curso['categoria']); ?>
                        </span>
                        <span class="badge-custom">
                            <i class="fas fa-signal me-2"></i><?php echo htmlspecialchars($curso['nivel']); ?>
                        </span>
                        <span class="badge-custom">
                            <i class="fas fa-book me-2"></i><?php echo $curso['total_lecciones']; ?> lecciones
                        </span>
                        <span class="badge-custom">
                            <i class="fas fa-clock me-2"></i><?php echo $curso['horas_totales']; ?> horas
                        </span>
                    </div>

                    <!-- Información del instructor centrada -->
                    <div class="instructor-highlight-center mt-4">
                        <div class="instructor-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="instructor-info">
                            <div class="instructor-name">Instructor: <?php echo htmlspecialchars($curso['instructor_nombre'] . ' ' . $curso['instructor_apellido']); ?></div>
                            <div class="instructor-rating">
                                <i class="fas fa-star text-warning me-1"></i>Instructor experto
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Contenido del Curso -->
    <div class="container">
        <!-- Sección de Descripción Detallada con Sidebar de Precio -->
        <section class="course-description-section mb-5">
            <div class="row">
                <!-- Contenido Principal -->
                <div class="col-lg-8">
                    <div class="content-card">
                        <div class="card-header-custom">
                            <h2 class="section-title">
                                <i class="fas fa-info-circle me-3"></i>
                                Acerca de este curso
                            </h2>
                        </div>
                        <div class="card-body-custom">
                            <div class="course-features-grid">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-laptop-code"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h5>Proyectos Prácticos</h5>
                                        <p>Aplica lo aprendido con ejercicios reales del mundo laboral</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h5>Certificado Oficial</h5>
                                        <p>Obtén un certificado verificable al completar el curso</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h5>Comunidad Activa</h5>
                                        <p>Conecta con otros estudiantes y el instructor</p>
                                    </div>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="feature-content">
                                        <h5>A tu Ritmo</h5>
                                        <p>Estudia cuando quieras, donde quieras</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar de Precio movido aquí -->
                <div class="col-lg-4">
                    <div class="curso-sidebar sticky-top" style="top: 2rem;">
                        <div class="card price-card">
                            <div class="price-header text-center">
                                <?php if ($curso['precio'] > 0): ?>
                                    <div class="price-amount"><?php echo number_format($curso['precio'], 2); ?>€</div>
                                    <div class="price-period">Acceso completo</div>
                                <?php else: ?>
                                    <div class="price-amount text-success">Gratis</div>
                                    <div class="price-period">Acceso completo</div>
                                <?php endif; ?>
                            </div>

                            <?php if ($usuario_inscrito): ?>
                                <div class="progress-course">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-medium">Tu progreso</span>
                                        <span class="text-success fw-bold"><?php echo $progreso_curso; ?>%</span>
                                    </div>
                                    <div class="progress-custom">
                                        <div class="progress-bar-custom" style="width: <?php echo $progreso_curso; ?>%;"></div>
                                    </div>
                                </div>
                                <div class="p-3">
                                    <a href="<?php echo $base_path; ?>iniciar-curso.php?id=<?php echo $curso_id; ?>" 
                                       class="btn btn-success btn-enroll w-100">
                                        <i class="fas fa-play me-2"></i>Continuar curso
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="p-3">
                                    <?php if ($curso['precio'] > 0): ?>
                                        <a href="<?php echo $base_path; ?>inscribirse.php?id=<?php echo $curso_id; ?>" 
                                           class="btn btn-primary btn-enroll w-100">
                                            <i class="fas fa-credit-card me-2"></i>Inscribirse
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo $base_path; ?>inscribirse.php?id=<?php echo $curso_id; ?>" 
                                           class="btn btn-success btn-enroll w-100">
                                            <i class="fas fa-gift me-2"></i>Inscribirse gratis
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="course-stats">
                                <div class="stat-item">
                                    <i class="fas fa-users stat-icon"></i>
                                    <span><?php echo isset($inscritos) ? $inscritos : 0; ?> estudiantes inscritos</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-eye stat-icon"></i>
                                    <span><?php echo isset($vistas) ? $vistas : 0; ?> visualizaciones</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-calendar-alt stat-icon"></i>
                                    <span>Actualizado: <?php echo date('d/m/Y', strtotime($curso['created_at'])); ?></span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-certificate stat-icon"></i>
                                    <span>Certificado incluido</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-infinity stat-icon"></i>
                                    <span>Acceso de por vida</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Temario del Curso -->
        <section class="course-curriculum mb-5">
            <div class="content-card">
                <div class="card-header-custom">
                    <h2 class="section-title">
                        <i class="fas fa-list-alt me-3"></i>
                        Contenido del curso
                    </h2>
                    <p class="section-subtitle">
                        <?php echo count($modulos); ?> módulos • <?php echo $curso['total_lecciones']; ?> lecciones • <?php echo $curso['horas_totales']; ?> horas de contenido
                    </p>
                </div>
                
                <div class="card-body-custom">
                    <div class="accordion custom-accordion" id="acordeonModulos">
                        <?php foreach ($modulos as $index => $modulo): ?>
                        <div class="accordion-item-custom">
                            <div class="accordion-header-custom" id="heading<?php echo $modulo['id']; ?>">
                                <button class="accordion-button-custom <?php echo $index > 0 ? 'collapsed' : ''; ?>" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse<?php echo $modulo['id']; ?>" 
                                        aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                        aria-controls="collapse<?php echo $modulo['id']; ?>">
                                    <div class="module-header">
                                        <div class="module-number"><?php echo $modulo['orden']; ?></div>
                                        <div class="module-info">
                                            <h4 class="module-title"><?php echo htmlspecialchars($modulo['titulo']); ?></h4>
                                            <div class="module-meta">
                                                <span class="lesson-count">
                                                    <i class="fas fa-play-circle me-1"></i>
                                                    <?php echo count($modulo['contenido_modular']) + count($modulo['actividades']); ?> elementos
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-icon">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </button>
                            </div>
                            
                            <div id="collapse<?php echo $modulo['id']; ?>" 
                                 class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                 aria-labelledby="heading<?php echo $modulo['id']; ?>" 
                                 data-bs-parent="#acordeonModulos">
                                <div class="accordion-body-custom">
                                    <?php if (!empty($modulo['descripcion'])): ?>
                                        <div class="module-description">
                                            <p><?php echo nl2br(htmlspecialchars($modulo['descripcion'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="lesson-list">
                                        <!-- Contenido modular -->
                                        <?php foreach ($modulo['contenido_modular'] as $contenido): ?>
                                        <div class="lesson-item <?php echo !$usuario_inscrito ? 'locked' : ''; ?>">
                                            <div class="lesson-icon">
                                                <?php 
                                                $icono = 'fa-file-alt';
                                                $color = 'text-primary';
                                                if ($contenido['tipo'] == 'video') { $icono = 'fa-video'; $color = 'text-danger'; }
                                                elseif ($contenido['tipo'] == 'audio') { $icono = 'fa-headphones'; $color = 'text-info'; }
                                                elseif ($contenido['tipo'] == 'imagen') { $icono = 'fa-image'; $color = 'text-warning'; }
                                                ?>
                                                <i class="fas <?php echo $icono; ?> <?php echo $color; ?>"></i>
                                            </div>
                                            <div class="lesson-content">
                                                <h6 class="lesson-title"><?php echo htmlspecialchars($contenido['titulo']); ?></h6>
                                                <div class="lesson-meta">
                                                    <span class="lesson-type"><?php echo ucfirst($contenido['tipo']); ?></span>
                                                </div>
                                            </div>
                                            <div class="lesson-action">
                                                <?php if ($usuario_inscrito): ?>
                                                    <a href="<?php echo $base_path; ?>contenido.php?id=<?php echo $contenido['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-play me-1"></i>Ver
                                                    </a>
                                                <?php else: ?>
                                                    <span class="lock-icon">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        
                                        <!-- Recursos -->
                                        <?php foreach ($modulo['recursos'] as $recurso): ?>
                                        <div class="lesson-item <?php echo !$usuario_inscrito ? 'locked' : ''; ?>">
                                            <div class="lesson-icon">
                                                <i class="fas fa-file-download text-success"></i>
                                            </div>
                                            <div class="lesson-content">
                                                <h6 class="lesson-title"><?php echo htmlspecialchars($recurso['titulo']); ?></h6>
                                                <div class="lesson-meta">
                                                    <span class="lesson-type">Recurso descargable</span>
                                                    <?php if ($recurso['obligatorio']): ?>
                                                        <span class="badge badge-danger-custom">Obligatorio</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="lesson-action">
                                                <?php if ($usuario_inscrito): ?>
                                                    <a href="<?php echo $base_path; ?>recurso.php?id=<?php echo $recurso['id']; ?>" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-download me-1"></i>Descargar
                                                    </a>
                                                <?php else: ?>
                                                    <span class="lock-icon">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        
                                        <!-- Actividades -->
                                        <?php foreach ($modulo['actividades'] as $actividad): ?>
                                        <div class="lesson-item <?php echo !$usuario_inscrito ? 'locked' : ''; ?>">
                                            <div class="lesson-icon">
                                                <?php 
                                                $icono = 'fa-tasks';
                                                $color = 'text-warning';
                                                if ($actividad['tipo'] == 'quiz') { $icono = 'fa-question-circle'; $color = 'text-info'; }
                                                elseif ($actividad['tipo'] == 'assignment') { $icono = 'fa-clipboard-list'; $color = 'text-secondary'; }
                                                ?>
                                                <i class="fas <?php echo $icono; ?> <?php echo $color; ?>"></i>
                                            </div>
                                            <div class="lesson-content">
                                                <h6 class="lesson-title"><?php echo htmlspecialchars($actividad['titulo']); ?></h6>
                                                <div class="lesson-meta">
                                                    <span class="lesson-type"><?php echo ucfirst($actividad['tipo']); ?></span>
                                                    <?php if ($actividad['obligatorio']): ?>
                                                        <span class="badge badge-danger-custom">Obligatorio</span>
                                                    <?php endif; ?>
                                                    <?php if ($actividad['puntuacion'] > 0): ?>
                                                        <span class="badge badge-warning-custom"><?php echo $actividad['puntuacion']; ?> puntos</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="lesson-action">
                                                <?php if ($usuario_inscrito): ?>
                                                    <a href="<?php echo $base_path; ?>actividad.php?id=<?php echo $actividad['id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-pencil-alt me-1"></i>Realizar
                                                    </a>
                                                <?php else: ?>
                                                    <span class="lock-icon">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
                
        <!-- Sección de Valoraciones y Comentarios -->
        <section class="reviews-section">
            <div class="reviews-header">
                <h2><i class="fas fa-star me-3"></i>Valoraciones y Comentarios</h2>
                <div class="rating-overview">
                    <div class="rating-score"><?php echo number_format($promedio, 1); ?></div>
                    <div class="rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= round($promedio) ? 'text-warning' : 'text-light'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-count">(<?php echo count($valoraciones); ?> valoraciones)</div>
                </div>
            </div>

            <?php if ($usuario_inscrito): ?>
            <div class="rating-form">
                <h4 class="text-center mb-4">Comparte tu experiencia</h4>
                <form action="<?php echo $base_path; ?>valorar-curso.php" method="post">
                    <input type="hidden" name="curso_id" value="<?php echo $curso_id; ?>">
                    
                    <div class="text-center mb-4">
                        <label class="form-label fw-bold">Tu valoración:</label>
                        <div class="star-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?php echo $i; ?>" name="puntuacion" value="<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="comentario" class="form-label fw-bold">Tu comentario:</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="4" 
                                  placeholder="Comparte tu experiencia con este curso..." required></textarea>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Valoración
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="reviews-list">
                <?php if (count($valoraciones) > 0): ?>
                    <?php foreach ($valoraciones as $valoracion): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <?php if (!empty($valoracion['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($valoracion['avatar']); ?>" 
                                     class="reviewer-avatar" alt="Avatar de <?php echo htmlspecialchars($valoracion['nombre']); ?>">
                            <?php else: ?>
                                <div class="reviewer-avatar placeholder">
                                    <?php echo strtoupper(substr($valoracion['nombre'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="reviewer-info">
                                <h6><?php echo htmlspecialchars($valoracion['nombre'] . ' ' . ($valoracion['apellido'] ?? '')); ?></h6>
                                <div class="review-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    <?php echo date('d/m/Y', strtotime($valoracion['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $valoracion['puntuacion'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="review-content">
                            <?php echo nl2br(htmlspecialchars($valoracion['comentario'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="fas fa-comments"></i>
                        <h5>Aún no hay valoraciones</h5>
                        <p>¡Sé el primero en compartir tu experiencia con este curso!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sección de Cursos Relacionados -->
        <section class="related-courses-section">
            <h2 class="section-title">
                <i class="fas fa-graduation-cap me-3"></i>Cursos Relacionados
            </h2>
            
            <div class="row">
                <?php if (count($cursos_relacionados) > 0): ?>
                    <?php foreach ($cursos_relacionados as $curso_rel): ?>
                        <div class="col-lg-3 col-md-6 mb-4 curso-relacionado-unico">
                        <div class="card course-card">
                            <?php if (!empty($curso_rel['imagen'])): ?>
                                <img src="<?php echo htmlspecialchars($curso_rel['imagen']); ?>" 
                                     class="course-image" alt="<?php echo htmlspecialchars($curso_rel['nombre']); ?>">
                            <?php else: ?>
                                <div class="course-image-placeholder">
                                    <i class="fas fa-book-open"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="course-content">
                                <h5 class="course-title"><?php echo htmlspecialchars($curso_rel['nombre']); ?></h5>
                                
                                <div class="course-badges">
                                    <span class="badge bg-secondary course-badge">
                                        <i class="fas fa-signal me-1"></i><?php echo htmlspecialchars($curso_rel['nivel']); ?>
                                    </span>
                                    <span class="badge bg-info course-badge">
                                        <i class="fas fa-book me-1"></i><?php echo $curso_rel['total_lecciones']; ?> lecciones
                                    </span>
                                </div>
                                
                                <div class="course-price <?php echo $curso_rel['precio'] > 0 ? 'paid' : 'free'; ?>">
                                    <?php if ($curso_rel['precio'] > 0): ?>
                                        <i class="fas fa-euro-sign me-1"></i><?php echo number_format($curso_rel['precio'], 2); ?>
                                    <?php else: ?>
                                        <i class="fas fa-gift me-1"></i>Gratis
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="course-footer">
                                <a href="<?php echo $base_path; ?>curso.php?id=<?php echo $curso_rel['id']; ?>" 
                                   class="btn-course">
                                    <i class="fas fa-eye me-2"></i>Ver Curso
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="no-related-courses">
                            <i class="fas fa-search"></i>
                            <h4>No hay cursos relacionados</h4>
                            <p class="text-muted">Por el momento no hay otros cursos disponibles en esta categoría.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    
    
    <!-- Footer Start -->
 <div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-lg-7 col-md-12">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Get In Touch</h5>
                        <p><i class="fa fa-map-marker-alt mr-2"></i>123 Street, New York, USA</p>
                        <p><i class="fa fa-phone-alt mr-2"></i>+012 345 67890</p>
                        <p><i class="fa fa-envelope mr-2"></i>info@example.com</p>
                        <div class="d-flex justify-content-start mt-4">
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-outline-light btn-square mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-outline-light btn-square" href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Our Courses</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Web Design</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Apps Design</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Marketing</a>
                            <a class="text-white mb-2" href="#"><i class="fa fa-angle-right mr-2"></i>Research</a>
                            <a class="text-white" href="#"><i class="fa fa-angle-right mr-2"></i>SEO</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Newsletter</h5>
                <p>Rebum labore lorem dolores kasd est, et ipsum amet et at kasd, ipsum sea tempor magna tempor. Accu kasd sed ea duo ipsum. Dolor duo eirmod sea justo no lorem est diam</p>
                <div class="w-100">
                    <div class="input-group">
                        <input type="text" class="form-control border-light" style="padding: 30px;" placeholder="Your Email Address">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-4">Sign Up</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-white border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .1) !important;">
        <div class="row">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
                <p class="m-0 text-white">&copy; <a href="#">Domain Name</a>. All Rights Reserved. Designed by <a href="https://htmlcodex.com">HTML Codex</a>
                </p>
            </div>
            <div class="col-lg-6 text-center text-md-right">
                <ul class="nav d-inline-flex">
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Privacy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Terms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">FAQs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Help</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-angle-double-up"></i></a>

    
    <!-- JavaScript necesario -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para el sistema de valoración por estrellas
        document.addEventListener('DOMContentLoaded', function() {
            const starInputs = document.querySelectorAll('.star-rating input');
            const starLabels = document.querySelectorAll('.star-rating label');
            
            starInputs.forEach((input, index) => {
                input.addEventListener('change', function() {
                    const rating = parseInt(this.value);
                    
                    starLabels.forEach((label, labelIndex) => {
                        const labelRating = parseInt(starInputs[labelIndex].value);
                        
                        if (labelRating <= rating) {
                            label.style.color = '#ffc107';
                            label.style.transform = 'scale(1.1)';
                        } else {
                            label.style.color = '#ddd';
                            label.style.transform = 'scale(1)';
                        }
                    });
                });
                
                // Efecto hover
                input.addEventListener('mouseover', function() {
                    const rating = parseInt(this.value);
                    
                    starLabels.forEach((label, labelIndex) => {
                        const labelRating = parseInt(starInputs[labelIndex].value);
                        
                        if (labelRating <= rating) {
                            label.style.color = '#ffc107';
                        }
                    });
                });
            });
            
            // Restaurar estado original al salir del hover
            document.querySelector('.star-rating').addEventListener('mouseleave', function() {
                const checkedInput = document.querySelector('.star-rating input:checked');
                
                if (checkedInput) {
                    const rating = parseInt(checkedInput.value);
                    
                    starLabels.forEach((label, labelIndex) => {
                        const labelRating = parseInt(starInputs[labelIndex].value);
                        
                        if (labelRating <= rating) {
                            label.style.color = '#ffc107';
                        } else {
                            label.style.color = '#ddd';
                        }
                    });
                } else {
                    starLabels.forEach(label => {
                        label.style.color = '#ddd';
                    });
                }
            });

            // Animación de entrada para las tarjetas
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observar elementos para animación
            document.querySelectorAll('.review-item, .course-card').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>