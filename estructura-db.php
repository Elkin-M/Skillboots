<?php
$mysqli = new mysqli("localhost", "root", "", "elkinmb3");
$result = $mysqli->query("SHOW TABLES");

while ($row = $result->fetch_array()) {
    $table = $row[0];
    echo "Estructura de la tabla: $table\n";
    $describe_result = $mysqli->query("DESCRIBE $table");
    
    while ($describe_row = $describe_result->fetch_assoc()) {
        echo "Columna: " . $describe_row['Field'] . ", Tipo: " . $describe_row['Type'] . "\n";
    }
}
?>