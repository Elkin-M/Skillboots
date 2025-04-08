<?php
session_start();
require_once './conexion/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperar datos del formulario
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $descripcion = $_POST['descripcion'];
    $imagen = $_FILES['imagen']['name'];
    $precio = $_POST['precio'];
    $duracion = $_POST['duracion'];
    $nivel = $_POST['nivel'];
    $estado = $_POST['estado'];
    $modulos = $_POST['modulos'];  // Aquí es un array, debes iterar sobre sus valores
    $instructor_id = $_SESSION['user_id']; // Asegúrate de que el instructor_id se esté pasando correctamente

    // Validar y sanitizar datos
    $nombre = htmlspecialchars($nombre);
    $categoria = htmlspecialchars($categoria);
    $descripcion = htmlspecialchars($descripcion);
    $precio = filter_var($precio, FILTER_VALIDATE_FLOAT);
    $duracion = filter_var($duracion, FILTER_VALIDATE_INT);
    $nivel = htmlspecialchars($nivel);
    $estado = htmlspecialchars($estado);

    // Mover la imagen al directorio de destino
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($imagen);

    if(move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
        echo "El archivo ". basename($_FILES['imagen']['name']). " ha sido subido correctamente.";
    } else {
        echo "Lo sentimos, hubo un error al subir tu archivo.";
    }

    // Preparar la consulta SQL para insertar el curso
    $sql = "INSERT INTO cursos (nombre, categoria, descripcion, imagen, precio, horas_totales, nivel, estado, instructor_id, total_lecciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nombre, $categoria, $descripcion, $target_file, $precio, $duracion, $nivel, $estado, $instructor_id, 0]); // Aquí se inserta 0 como valor predeterminado para total_lecciones

    // Obtener el ID del curso recién insertado
    $curso_id = $conn->lastInsertId();

    // Insertar módulos y lecciones
    foreach ($modulos as $modulo) {
        // Asegúrate de que cada valor que estamos pasando a htmlspecialchars es una cadena de texto
        if (isset($modulo['titulo']) && is_string($modulo['titulo'])) {
            $modulo_titulo = htmlspecialchars($modulo['titulo']);
        } else {
            $modulo_titulo = ''; // Asignar un valor por defecto si no es una cadena válida
        }

        if (isset($modulo['descripcion']) && is_string($modulo['descripcion'])) {
            $modulo_descripcion = htmlspecialchars($modulo['descripcion']);
        } else {
            $modulo_descripcion = ''; // Asignar un valor por defecto si no es una cadena válida
        }

        // Insertar módulo
        $sql = "INSERT INTO modulos (curso_id, titulo, descripcion) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$curso_id, $modulo_titulo, $modulo_descripcion]);

        $modulo_id = $conn->lastInsertId();

        // Insertar lecciones dentro de cada módulo
        if (isset($modulo['contenido']) && is_array($modulo['contenido'])) {
            foreach ($modulo['contenido'] as $leccion) {
                if (isset($leccion['titulo']) && is_string($leccion['titulo'])) {
                    $leccion_titulo = htmlspecialchars($leccion['titulo']);
                } else {
                    $leccion_titulo = ''; // Asignar un valor por defecto si no es una cadena válida
                }

                if (isset($leccion['tipo']) && is_string($leccion['tipo'])) {
                    $leccion_tipo = htmlspecialchars($leccion['tipo']);
                } else {
                    $leccion_tipo = ''; // Asignar un valor por defecto si no es una cadena válida
                }

                if (isset($leccion['valor']) && is_string($leccion['valor'])) {
                    $leccion_valor = htmlspecialchars($leccion['valor']);
                } else {
                    $leccion_valor = ''; // Asignar un valor por defecto si no es una cadena válida
                }

                // Insertar lección
                $sql = "INSERT INTO contenido_modular (modulo_id, titulo, tipo, contenido) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$modulo_id, $leccion_titulo, $leccion_tipo, $leccion_valor]);
            }
        }
    }



    // Redirigir a la página de administración de cursos
    header("Location: crear_curso.php#crearCurso");
    exit;
}
?>
