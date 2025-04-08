<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar datos del curso
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    $estado = mysqli_real_escape_string($conn, $_POST['estado']);

    // Calcular total de lecciones
    $total_lecciones = 0;
    if (isset($_POST['modulos']) && is_array($_POST['modulos'])) {
        foreach ($_POST['modulos'] as $modulo) {
            if (isset($modulo['contenido']) && is_array($modulo['contenido'])) {
                $total_lecciones += count($modulo['contenido']);
            }
        }
    }

    // Obtener horas totales del formulario
    $horas_totales = isset($_POST['duracion']) ? floatval($_POST['duracion']) : 0;

    // Obtener instructor_id (si lo tienes disponible en sesión o como campo oculto)
    $instructor_id = isset($_POST['instructor_id']) ? intval($_POST['instructor_id']) : 1; // valor por defecto o desde sesión

    // Fecha actual
    $fecha_creacion = date('Y-m-d H:i:s');

    // Manejo de imagen
    $imagen = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "uploads/courses/";
        $filename = time() . '_' . basename($_FILES["imagen"]["name"]);
        $target_file = $target_dir . $filename;

        // Crear directorio si no existe
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Mover archivo
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            $imagen = $target_file;
        }
    }

    // Insertar curso con la imagen incluida
    $sql_curso = "INSERT INTO cursos (nombre, descripcion, categoria, estado, fecha_creacion, 
                                     total_lecciones, horas_totales, imagen, instructor_id) 
                 VALUES ('$nombre', '$descripcion', '$categoria', '$estado', '$fecha_creacion', 
                         $total_lecciones, $horas_totales, '$imagen', $instructor_id)";

    if ($conn->query($sql_curso) === TRUE) {
        $curso_id = $conn->insert_id; // Obtener el ID del curso recién creado

        // Recorrer módulos enviados desde el formulario
        if (isset($_POST['modulos']) && is_array($_POST['modulos'])) {
            foreach ($_POST['modulos'] as $indice => $modulo) {
                $titulo_modulo = mysqli_real_escape_string($conn, $modulo['titulo']);
                $descripcion_modulo = mysqli_real_escape_string($conn, $modulo['descripcion']);

                // Insertar módulo
                $sql_modulo = "INSERT INTO modulos (curso_id, titulo, descripcion, orden) 
                              VALUES ($curso_id, '$titulo_modulo', '$descripcion_modulo', $indice)";

                if ($conn->query($sql_modulo) === TRUE) {
                    $modulo_id = $conn->insert_id; // ID del módulo creado

                    // Insertar contenido dentro del módulo
                    if (isset($modulo['contenido']) && is_array($modulo['contenido'])) {
                        foreach ($modulo['contenido'] as $orden => $contenido) {
                            $titulo_leccion = mysqli_real_escape_string($conn, $contenido['titulo']);
                            $tipo = mysqli_real_escape_string($conn, $contenido['tipo']); 
                            $valor = mysqli_real_escape_string($conn, $contenido['valor']);

                            $sql_contenido = "INSERT INTO contenido_modular (modulo_id, titulo, tipo, contenido, orden) 
                                            VALUES ($modulo_id, '$titulo_leccion', '$tipo', '$valor', $orden)";

                            if (!$conn->query($sql_contenido)) {
                                echo "Error al guardar lección: " . $conn->error;
                                exit;
                            }
                        }
                    }
                } else {
                    echo "Error al guardar módulo: " . $conn->error;
                    exit;
                }
            }
        }

        // Redireccionar a la página de cursos con mensaje de éxito
        header("Location: index.php?page=cursos&success=1");
        exit;
    } else {
        echo "Error al crear curso: " . $conn->error;
    }
} else {
    // Si alguien intenta acceder directamente sin enviar el formulario
    header("Location: index.php");
    exit;
}
?>
