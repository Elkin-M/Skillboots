<?php
include 'db.php';

$curso_id = $_GET['id'];  // Recibir ID del curso

// Obtener curso
$sql_curso = "SELECT * FROM cursos WHERE id = $curso_id";
$curso = $conn->query($sql_curso)->fetch_assoc();

echo "<h1>{$curso['nombre']}</h1>";
echo "<p>{$curso['descripcion']}</p>";

// Obtener módulos del curso
$sql_modulos = "SELECT * FROM modulos WHERE curso_id = $curso_id ORDER BY orden ASC";
$modulos = $conn->query($sql_modulos);

while ($modulo = $modulos->fetch_assoc()) {
    echo "<h2>{$modulo['titulo']}</h2>";
    echo "<p>{$modulo['descripcion']}</p>";

    // Obtener contenido del módulo
    $sql_contenido = "SELECT * FROM contenido_modular WHERE modulo_id = {$modulo['id']} ORDER BY orden ASC";
    $contenido = $conn->query($sql_contenido);

    while ($item = $contenido->fetch_assoc()) {
        if ($item['tipo'] == 'texto') {
            echo "<p>{$item['contenido']}</p>";
        } elseif ($item['tipo'] == 'video') {
            echo "<iframe width='560' height='315' src='{$item['contenido']}' frameborder='0' allowfullscreen></iframe>";
        } elseif ($item['tipo'] == 'imagen') {
            echo "<img src='{$item['contenido']}' alt='Imagen del curso' style='max-width:100%;'>";
        } elseif ($item['tipo'] == 'pdf') {
            echo "<a href='{$item['contenido']}' target='_blank'>📄 Descargar PDF</a>";
        } elseif ($item['tipo'] == 'quiz') {
            echo "<p><strong>Pregunta:</strong> {$item['contenido']}</p>";
        }
    }
}
?>
