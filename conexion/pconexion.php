<?php
include 'db.php';

try {
    // Si llegas aquí, la conexión fue exitosa
    echo "Conexión exitosa a la base de datos.";
} catch (PDOException $e) {
    echo "Error en la conexión: " . $e->getMessage();
}
?>
