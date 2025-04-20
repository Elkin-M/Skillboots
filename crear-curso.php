<?php
session_start();
require_once 'conexion/db.php'; // Asegúrate de que este archivo se encargue de la conexión PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciamos una transacción para asegurar integridad
        $conn->beginTransaction();

        // Recuperar datos del curso
        $nombre = $_POST['nombre'];
        $categoria = $_POST['categoria'];
        $descripcion = $_POST['descripcion'];
        $precio = isset($_POST['precio']) ? $_POST['precio'] : 0;
        $duracion = isset($_POST['duracion']) ? $_POST['duracion'] : 0;
        $nivel = isset($_POST['nivel']) ? $_POST['nivel'] : 'Principiante';
        $estado = isset($_POST['estado']) ? $_POST['estado'] : 'borrador';
        $instructor_id = isset($_POST['instructor_id']) ? $_POST['instructor_id'] : 1;

        // Valores para campos adicionales en la tabla cursos
        $total_lecciones = 0; // Se puede actualizar después
        $horas_totales = isset($_POST['duracion']) ? $_POST['duracion'] : 0;
        $estate = 'activo'; // Por defecto activo

        // Manejar la subida de la imagen del curso
        $imagen = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';

            // Crear el directorio si no existe
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $imagen = $uploadDir . basename($_FILES['imagen']['name']);
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen)) {
                // La imagen se subió correctamente
            } else {
                throw new Exception("Error al subir la imagen.");
            }
        }

        // Insertar el curso en la base de datos
        $sql = "INSERT INTO cursos (nombre, categoria, descripcion, imagen, precio, duracion, nivel, estado,
                total_lecciones, horas_totales, instructor_id, estate, fecha_creacion)
                VALUES (:nombre, :categoria, :descripcion, :imagen, :precio, :duracion, :nivel, :estado,
                :total_lecciones, :horas_totales, :instructor_id, :estate, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':categoria' => $categoria,
            ':descripcion' => $descripcion,
            ':imagen' => $imagen,
            ':precio' => $precio,
            ':duracion' => $duracion,
            ':nivel' => $nivel,
            ':estado' => $estado,
            ':total_lecciones' => $total_lecciones,
            ':horas_totales' => $horas_totales,
            ':instructor_id' => $instructor_id,
            ':estate' => $estate
        ]);

        $curso_id = $conn->lastInsertId(); // Obtener el ID del curso recién insertado

        // Contadores para actualizar total_lecciones
        $contador_lecciones = 0;

        // Insertar módulos y actividades
        if (isset($_POST['unidades'])) {
            foreach ($_POST['unidades'] as $index => $unidad) {
                // Validación básica
                if (empty($unidad['titulo'])) {
                    continue; // Saltar este módulo si no tiene título
                }

                $titulo = $unidad['titulo'];
                $descripcion = isset($unidad['descripcion']) ? $unidad['descripcion'] : '';
                $orden = $index + 1; // Usar el índice como orden

                // Insertar en la tabla modulos (antes "unidades")
                $sql = "INSERT INTO modulos (curso_id, titulo, descripcion, orden)
                        VALUES (:curso_id, :titulo, :descripcion, :orden)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':curso_id' => $curso_id,
                    ':titulo' => $titulo,
                    ':descripcion' => $descripcion,
                    ':orden' => $orden
                ]);

                $modulo_id = $conn->lastInsertId(); // ID del módulo recién insertado

                // Insertar recursos
                if (isset($unidad['recursos']) && is_array($unidad['recursos'])) {
                    foreach ($unidad['recursos'] as $recurso) {
                        // Skip si no hay título
                        if (empty($recurso['titulo'])) {
                            continue;
                        }

                        $titulo_recurso = $recurso['titulo'];
                        $tipo_recurso = isset($recurso['tipo']) ? $recurso['tipo'] : 'texto';
                        $contenido_recurso = isset($recurso['contenido']) ? $recurso['contenido'] : '';
                        $obligatorio_recurso = isset($recurso['obligatorio']) ? 1 : 0;
                        $url_recurso = isset($recurso['url']) ? $recurso['url'] : '';

                        // En la tabla recursos, el campo se llama unidad_id pero guardamos el modulo_id
                        $sql = "INSERT INTO recursos (unidad_id, titulo, tipo, contenido, obligatorio, url)
                                VALUES (:modulo_id, :titulo, :tipo, :contenido, :obligatorio, :url)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':modulo_id' => $modulo_id,
                            ':titulo' => $titulo_recurso,
                            ':tipo' => $tipo_recurso,
                            ':contenido' => $contenido_recurso,
                            ':obligatorio' => $obligatorio_recurso,
                            ':url' => $url_recurso
                        ]);

                        $contador_lecciones++;
                    }
                }

                // Insertar actividades
                if (isset($unidad['actividades']) && is_array($unidad['actividades'])) {
                    foreach ($unidad['actividades'] as $actividad) {
                        // Skip si no hay título
                        if (empty($actividad['titulo'])) {
                            continue;
                        }

                        $titulo_actividad = $actividad['titulo'];
                        $tipo_actividad = isset($actividad['tipo']) ? $actividad['tipo'] : 'quiz';
                        $contenido_actividad = isset($actividad['contenido']) ? $actividad['contenido'] : '';
                        $puntuacion = isset($actividad['puntuacion']) ? $actividad['puntuacion'] : 0;
                        $fecha_limite = isset($actividad['fecha_limite']) && !empty($actividad['fecha_limite']) ?
                                        $actividad['fecha_limite'] : null;
                        $tiempo = isset($actividad['tiempo']) ? $actividad['tiempo'] : 0;
                        $obligatorio_actividad = isset($actividad['obligatorio']) ? 1 : 0;

                        // En la tabla actividades, el campo se llama unidad_id pero guardamos el modulo_id
                        $sql = "INSERT INTO actividades (unidad_id, titulo, tipo, contenido, puntuacion, fecha_limite, tiempo, obligatorio)
                                VALUES (:modulo_id, :titulo, :tipo, :contenido, :puntuacion, :fecha_limite, :tiempo, :obligatorio)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':modulo_id' => $modulo_id,
                            ':titulo' => $titulo_actividad,
                            ':tipo' => $tipo_actividad,
                            ':contenido' => $contenido_actividad,
                            ':puntuacion' => $puntuacion,
                            ':fecha_limite' => $fecha_limite,
                            ':tiempo' => $tiempo,
                            ':obligatorio' => $obligatorio_actividad
                        ]);

                        $actividad_id = $conn->lastInsertId(); // ID de la actividad recién insertada
                        $contador_lecciones++;

                        // Insertar preguntas y opciones
                        if (isset($actividad['preguntas']) && is_array($actividad['preguntas'])) {
                            foreach ($actividad['preguntas'] as $pregunta) {
                                if (empty($pregunta['texto'])) {
                                    continue;
                                }

                                $texto_pregunta = $pregunta['texto'];
                                $tipo_pregunta = isset($pregunta['tipo']) ? $pregunta['tipo'] : 'opcion_multiple';
                                $respuesta_correcta = isset($pregunta['respuesta_correcta']) ? $pregunta['respuesta_correcta'] : '';

                                $sql = "INSERT INTO preguntas (actividad_id, texto, tipo, respuesta_correcta)
                                        VALUES (:actividad_id, :texto, :tipo, :respuesta_correcta)";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([
                                    ':actividad_id' => $actividad_id,
                                    ':texto' => $texto_pregunta,
                                    ':tipo' => $tipo_pregunta,
                                    ':respuesta_correcta' => $respuesta_correcta
                                ]);

                                $pregunta_id = $conn->lastInsertId();

                                // Insertar opciones
                                if (isset($pregunta['opciones']) && is_array($pregunta['opciones'])) {
                                    foreach ($pregunta['opciones'] as $opcion) {
                                        if (empty($opcion['texto'])) {
                                            continue;
                                        }

                                        $texto_opcion = $opcion['texto'];
                                        $es_correcta = isset($opcion['es_correcta']) && $opcion['es_correcta'] ? 1 : 0;

                                        $sql = "INSERT INTO opciones (pregunta_id, texto, es_correcta)
                                                VALUES (:pregunta_id, :texto, :es_correcta)";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->execute([
                                            ':pregunta_id' => $pregunta_id,
                                            ':texto' => $texto_opcion,
                                            ':es_correcta' => $es_correcta
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                // Insertar contenido modular (si existe en el formulario)
                if (isset($unidad['contenido_modular']) && is_array($unidad['contenido_modular'])) {
                    foreach ($unidad['contenido_modular'] as $contenido_index => $contenido) {
                        if (empty($contenido['titulo'])) {
                            continue;
                        }

                        $titulo_contenido = $contenido['titulo'];
                        $tipo_contenido = isset($contenido['tipo']) ? $contenido['tipo'] : 'texto';
                        $contenido_texto = isset($contenido['contenido']) ? $contenido['contenido'] : '';
                        $orden_contenido = $contenido_index + 1;

                        $sql = "INSERT INTO contenido_modular (modulo_id, tipo, contenido, orden, titulo)
                                VALUES (:modulo_id, :tipo, :contenido, :orden, :titulo)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':modulo_id' => $modulo_id,
                            ':tipo' => $tipo_contenido,
                            ':contenido' => $contenido_texto,
                            ':orden' => $orden_contenido,
                            ':titulo' => $titulo_contenido
                        ]);

                        $contador_lecciones++;
                    }
                }
            }
        }

        // Actualizar el contador de lecciones en el curso
        $sql = "UPDATE cursos SET total_lecciones = :total_lecciones WHERE id = :curso_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':total_lecciones' => $contador_lecciones,
            ':curso_id' => $curso_id
        ]);

        // Commit de la transacción
        $conn->commit();

        // Crear notificación para el instructor
        $sql = "INSERT INTO notifications (user_id, role, message, link, icon, read, created_at)
                VALUES (:user_id, 'instructor', :message, :link, 'check-circle', 0, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $instructor_id,
            ':message' => "Tu curso '$nombre' ha sido creado exitosamente.",
            ':link' => "curso.php?id=$curso_id"
        ]);

        // Redirigir o mostrar un mensaje de éxito
        header("Location: admin.php?success=true&curso_id=$curso_id");
        exit;

    } catch (Exception $e) {
        // Si hay algún error, revertir todo
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        // Registrar el error y mostrar un mensaje genérico
        error_log("Error en crear-curso.php: " . $e->getMessage());
        header("Location: admin.php?error=true&message=" . urlencode("Error al guardar el curso. Por favor, inténtalo de nuevo."));
        exit;
    }
} else {
    // Manejar el caso en que no se envíe un POST
    echo "Método no permitido.";
    exit;
}
?>
