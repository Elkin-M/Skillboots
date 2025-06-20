<?php
// debug_contenido.php - Visualizador limpio de contenido (modificado)
session_start();
require_once '../conexion/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de contenido requerido');
}

$contenido_id = (int)$_GET['id'];

try {
    // Obtener información del contenido
    $sql = "SELECT cm.*, m.titulo as modulo_titulo, m.curso_id, c.nombre as curso_nombre
            FROM contenido_modular cm
            JOIN modulos m ON cm.modulo_id = m.id
            JOIN cursos c ON m.curso_id = c.id
            WHERE cm.id = :contenido_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':contenido_id' => $contenido_id]);
    
    if ($stmt->rowCount() === 0) {
        die('Contenido no encontrado');
    }
    
    $contenido = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// Función mejorada para verificar archivos con múltiples rutas
function encontrarArchivo($ruta_original) {
    // Lista de rutas posibles a probar
    $rutas_posibles = [
        $ruta_original,                                    // Ruta original
        '../' . $ruta_original,                           // Un nivel arriba
        './' . $ruta_original,                            // Directorio actual
        '../../' . $ruta_original,                        // Dos niveles arriba
        '../uploads/' . basename($ruta_original),         // Carpeta uploads
        '../assets/' . basename($ruta_original),          // Carpeta assets
        '../media/' . basename($ruta_original),           // Carpeta media
        '../videos/' . basename($ruta_original),          // Carpeta videos
        'uploads/' . basename($ruta_original),            // uploads local
        'assets/' . basename($ruta_original),             // assets local
        'media/' . basename($ruta_original),              // media local
        'videos/' . basename($ruta_original)              // videos local
    ];
    
    foreach ($rutas_posibles as $ruta) {
        if (file_exists($ruta) && is_file($ruta) && is_readable($ruta)) {
            return $ruta;
        }
    }
    
    return false;
}

// Verificar si es YouTube
$es_youtube = (strpos($contenido['contenido'], 'youtube.com') !== false || 
               strpos($contenido['contenido'], 'youtu.be') !== false);

// Si es YouTube, extraer ID del video
$video_id = '';
if ($es_youtube) {
    if (strpos($contenido['contenido'], 'youtu.be/') !== false) {
        $video_id = substr($contenido['contenido'], strpos($contenido['contenido'], 'youtu.be/') + 9);
        $video_id = explode('?', $video_id)[0];
        $video_id = explode('&', $video_id)[0];
    } elseif (strpos($contenido['contenido'], 'youtube.com/watch?v=') !== false) {
        parse_str(parse_url($contenido['contenido'], PHP_URL_QUERY), $params);
        $video_id = isset($params['v']) ? $params['v'] : '';
    } elseif (strpos($contenido['contenido'], 'youtube.com/embed/') !== false) {
        $video_id = substr($contenido['contenido'], strpos($contenido['contenido'], 'youtube.com/embed/') + 20);
        $video_id = explode('?', $video_id)[0];
    }
}

// Para archivos locales, buscar el archivo
$archivo_encontrado = false;
$ruta_archivo = '';
if (!$es_youtube && $contenido['tipo'] !== 'texto') {
    $archivo_encontrado = encontrarArchivo($contenido['contenido']);
    if ($archivo_encontrado) {
        $ruta_archivo = $archivo_encontrado;
    }
}

// Función para obtener el tipo MIME correcto
function obtenerTipoMime($archivo) {
    $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    
    $tipos_mime = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'avi' => 'video/avi',
        'mov' => 'video/quicktime',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ];
    
    return isset($tipos_mime[$extension]) ? $tipos_mime[$extension] : '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($contenido['titulo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .content-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }
        .video-container {
            position: relative;
            width: 100%;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .video-responsive {
            width: 100%;
            height: auto;
            min-height: 300px;
        }
        .iframe-responsive {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 */
        }
        .iframe-responsive iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .fallback-buttons {
            text-align: center;
            margin-top: 1rem;
        }
        .content-text {
            line-height: 1.6;
            font-size: 1.1rem;
        }
        .media-error {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
        }
        .audio-container {
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .image-container {
            text-align: center;
        }
        .loading-placeholder {
            background: #f8f9fa;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="content-container">
        <h1 class="mb-4"><?php echo htmlspecialchars($contenido['titulo']); ?></h1>
        
        <div class="content-area">
            <?php if ($contenido['tipo'] == 'texto'): ?>
                <!-- Contenido de texto -->
                <div class="content-text">
                    <?php echo nl2br(htmlspecialchars($contenido['contenido'])); ?>
                </div>
                
            <?php elseif ($contenido['tipo'] == 'video'): ?>
                <?php if ($es_youtube && $video_id): ?>
                    <!-- Video de YouTube -->
                    <div class="video-container">
                        <div class="iframe-responsive">
                            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id);?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    allowfullscreen
                                    loading="lazy">
                            </iframe>
                        </div>
                    </div>
                    
                    <!-- Botón de respaldo para YouTube -->
                    <div class="fallback-buttons">
                        <a href="<?php echo htmlspecialchars($contenido['contenido']); ?>" 
                           target="_blank" 
                           class="btn btn-outline-primary">
                            🎥 Ver en YouTube
                        </a>
                    </div>
                    
                <?php elseif ($archivo_encontrado): ?>
                    <!-- Video local encontrado -->
                    <div class="video-container">
                        <video class="video-responsive" 
                               controls 
                               preload="metadata"
                               <?php 
                               $mime_type = obtenerTipoMime($archivo_encontrado);
                               if ($mime_type): ?>
                                   type="<?php echo $mime_type; ?>"
                               <?php endif; ?>>
                            
                            <!-- Múltiples fuentes para mejor compatibilidad -->
                            <source src="<?php echo htmlspecialchars($ruta_archivo); ?>" 
                                    <?php if ($mime_type): ?>type="<?php echo $mime_type; ?>"<?php endif; ?>>
                                    
                            <!-- Fuentes alternativas -->
                            <?php if (strpos($ruta_archivo, '.mp4') !== false): ?>
                                <source src="<?php echo htmlspecialchars($ruta_archivo); ?>" type="video/mp4">
                            <?php endif; ?>
                            
                            <p class="text-center mt-3">
                                Tu navegador no soporta video HTML5. 
                                <a href="<?php echo htmlspecialchars($ruta_archivo); ?>" target="_blank">
                                    Descargar video
                                </a>
                            </p>
                        </video>
                    </div>
                    
                <?php else: ?>
                    <!-- Video no encontrado -->
                    <div class="media-error">
                        <h5>🎬 Video no disponible</h5>
                        <p class="mb-3">No se pudo encontrar el archivo de video en el servidor.</p>
                        <p class="text-muted small mb-3">Archivo buscado: <code><?php echo htmlspecialchars($contenido['contenido']); ?></code></p>
                        
                        <div class="fallback-buttons">
                            <!-- Intentar acceso directo -->
                            <a href="<?php echo htmlspecialchars($contenido['contenido']); ?>" 
                               target="_blank" 
                               class="btn btn-primary me-2">
                                🎥 Intentar reproducir
                            </a>
                            
                            <!-- Si parece ser una URL -->
                            <?php if (filter_var($contenido['contenido'], FILTER_VALIDATE_URL)): ?>
                            <a href="<?php echo htmlspecialchars($contenido['contenido']); ?>" 
                               target="_blank" 
                               class="btn btn-outline-primary">
                                🔗 Abrir enlace
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($contenido['tipo'] == 'audio'): ?>
                <?php if ($archivo_encontrado): ?>
                    <!-- Audio local encontrado -->
                    <div class="audio-container">
                        <h5 class="mb-3">🎵 Audio</h5>
                        <audio controls 
                               preload="metadata" 
                               class="w-100" 
                               style="max-width: 500px;"
                               <?php 
                               $mime_type = obtenerTipoMime($archivo_encontrado);
                               if ($mime_type): ?>
                                   type="<?php echo $mime_type; ?>"
                               <?php endif; ?>>
                            
                            <source src="<?php echo htmlspecialchars($ruta_archivo); ?>" 
                                    <?php if ($mime_type): ?>type="<?php echo $mime_type; ?>"<?php endif; ?>>
                                    
                            <p class="text-center mt-3">
                                Tu navegador no soporta audio HTML5. 
                                <a href="<?php echo htmlspecialchars($ruta_archivo); ?>" target="_blank">
                                    Descargar audio
                                </a>
                            </p>
                        </audio>
                    </div>
                <?php else: ?>
                    <!-- Audio no encontrado -->
                    <div class="media-error">
                        <h5>🎵 Audio no disponible</h5>
                        <p class="mb-3">No se pudo encontrar el archivo de audio en el servidor.</p>
                        <a href="<?php echo htmlspecialchars($contenido['contenido']); ?>" 
                           target="_blank" 
                           class="btn btn-primary">
                            🎵 Intentar reproducir
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($contenido['tipo'] == 'imagen'): ?>
                <?php if ($archivo_encontrado): ?>
                    <!-- Imagen local encontrada -->
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($ruta_archivo); ?>" 
                             class="img-fluid rounded shadow" 
                             alt="<?php echo htmlspecialchars($contenido['titulo']); ?>"
                             loading="lazy"
                             onerror="this.parentElement.innerHTML='<div class=\'media-error\'><h5>🖼️ Error al cargar imagen</h5><p>No se pudo mostrar la imagen.</p><a href=\'<?php echo htmlspecialchars($ruta_archivo); ?>\' target=\'_blank\' class=\'btn btn-primary\'>Ver imagen</a></div>'">
                    </div>
                <?php else: ?>
                    <!-- Imagen no encontrada -->
                    <div class="media-error">
                        <h5>🖼️ Imagen no disponible</h5>
                        <p class="mb-3">No se pudo encontrar el archivo de imagen en el servidor.</p>
                        <a href="<?php echo htmlspecialchars($contenido['contenido']); ?>" 
                           target="_blank" 
                           class="btn btn-primary">
                            🖼️ Ver imagen
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
    <!-- Tipo de contenido desconocido -->
    <div class="alert alert-info">
        <h5>📄 Contenido</h5>
        <div class="embed-responsive embed-responsive-16by9">
        <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    allowfullscreen
                                    loading="lazy">
                            </iframe>
        </div>
    </div>
<?php endif; ?>

        </div>
        
        <!-- Información del módulo y curso -->
        <div class="mt-4 pt-3 border-top text-center">
            <small class="text-muted">
                <strong>Módulo:</strong> <?php echo htmlspecialchars($contenido['modulo_titulo']); ?> | 
                <strong>Curso:</strong> <?php echo htmlspecialchars($contenido['curso_nombre']); ?>
            </small>
        </div>
    </div>

    <script>
    // Mejorar compatibilidad de video
    document.addEventListener('DOMContentLoaded', function() {
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('error', function(e) {
                console.error('Error de video:', e);
                const container = this.closest('.video-container');
                if (container) {
                    container.innerHTML = `
                        <div class="media-error">
                            <h5>🎬 Error al cargar video</h5>
                            <p>No se pudo reproducir el video. Intenta con los enlaces alternativos.</p>
                            <a href="${this.src}" target="_blank" class="btn btn-primary">🎥 Descargar video</a>
                        </div>
                    `;
                }
            });
            
            // Intentar cargar el video
            video.load();
        });
        
        const audios = document.querySelectorAll('audio');
        audios.forEach(audio => {
            audio.addEventListener('error', function(e) {
                console.error('Error de audio:', e);
                const container = this.closest('.audio-container');
                if (container) {
                    container.innerHTML = `
                        <div class="media-error">
                            <h5>🎵 Error al cargar audio</h5>
                            <p>No se pudo reproducir el audio.</p>
                            <a href="${this.src}" target="_blank" class="btn btn-primary">🎵 Descargar audio</a>
                        </div>
                    `;
                }
            });
        });
    });
    </script>
</body>
</html>