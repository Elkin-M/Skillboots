<?php
require_once 'config.php';

// Obtener la ruta del endpoint
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// Identificar el endpoint
$endpoint = end($segments);

// Redirigir a los archivos de endpoints correspondientes
switch ($endpoint) {
    case 'curso-info':
        require 'endpoints/curso_info.php';
        break;

    case 'mis-cursos':
        require 'endpoints/mis_cursos.php';
        break;

    case 'actividades-pendientes':
        require 'endpoints/actividades_pendientes.php';
        break;

    case 'actividad-detalle':
        require 'endpoints/actividad_detalle.php';
        break;

    case 'progreso-general':
        require 'endpoints/progreso_general.php';
        break;

    case 'crear-ticket-soporte':
        require 'endpoints/crear_ticket_soporte.php';
        break;

    case 'solicitar-soporte-humano':
        require 'endpoints/solicitar_soporte_humano.php';
        break;

    case 'profesor-info':
        require 'endpoints/profesor_info.php';
        break;

    case 'recomendar-cursos':
        require 'endpoints/recomendar_cursos.php';
        break;

    case 'actividades-completadas':
        require 'endpoints/actividades_completadas.php';
        break;

    case 'certificados-disponibles':
        require 'endpoints/certificados_disponibles.php';
        break;

    case 'calificaciones-curso':
        require 'endpoints/calificaciones_curso.php';
        break;

    case 'sesiones-clase':
        require 'endpoints/sesiones_clase.php';
        break;

    case 'comentarios':
        require 'endpoints/comentarios.php';
        break;

    case 'contenido-modular':
        require 'endpoints/contenido_modular.php';
        break;

    case 'contenido-visto':
        require 'endpoints/contenido_visto.php';
        break;

    case 'calificaciones-curso':
        require 'endpoints/calificaciones_curso.php';
        break;

    case 'notas-estudiante':
        require 'endpoints/notas_estudiante.php';
        break;

    case 'notificaciones':
        require 'endpoints/notificaciones.php';
        break;

    case 'opciones-preguntas':
        require 'endpoints/opciones_preguntas.php';
        break;

    case 'preguntas-actividades':
        require 'endpoints/preguntas_actividades.php';
        break;

    case 'progreso-contenido':
        require 'endpoints/progreso_contenido.php';
        break;

    case 'quizzes':
        require 'endpoints/quizzes.php';
        break;

    case 'recursos':
        require 'endpoints/recursos.php';
        break;

    default:
        responderJSON([
            'error' => 'Endpoint no encontrado',
            'endpoints_disponibles' => [
                'curso-info',
                'mis-cursos',
                'actividades-pendientes',
                'actividad-detalle',
                'progreso-general',
                'crear-ticket-soporte',
                'solicitar-soporte-humano',
                'profesor-info',
                'recomendar-cursos',
                'actividades-completadas',
                'certificados-disponibles',
                'calificaciones-curso',
                'sesiones-clase',
                'comentarios',
                'contenido-modular',
                'contenido-visto',
                'calificaciones-curso',
                'notas-estudiante',
                'notificaciones',
                'opciones-preguntas',
                'preguntas-actividades',
                'progreso-contenido',
                'quizzes',
                'recursos'
            ]
        ]);
}
?>
