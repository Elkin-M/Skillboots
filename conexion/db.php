<?php
$config = include('config.php');

try {
    $conn = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['user'],
        $config['password']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error en la conexión: " . $e->getMessage());
    die("Error en la conexión. Intente más tarde.");
}
if (!$conn) {
    die("Error en la conexión. Intente más tarde.");
}else{
    // echo "Conexión exitosa";
}
?>
