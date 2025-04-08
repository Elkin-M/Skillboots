<?php
include './conexion/db.php'; 

$busqueda = isset($_GET['q']) ? $_GET['q'] : '';

echo "<h2>Resultados para: <strong>" . htmlspecialchars($busqueda) . "</strong></h2>";

try {
    $sql = "SELECT * FROM cursos WHERE nombre LIKE :busqueda";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['busqueda' => "%$busqueda%"]);

    if ($stmt->rowCount() > 0) {
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<p>" . htmlspecialchars($fila['nombre']) . " - " . htmlspecialchars($fila['descripcion']) . "</p>";
        }
    } else {
        echo "<p>No se encontraron resultados.</p>";
    }
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
}
?>
