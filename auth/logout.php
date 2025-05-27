<?php
include './db.php';
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("DELETE FROM usuarios_online WHERE usuario_id = :usuario_id");
    $stmt->bindParam(':usuario_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
}


session_start();
session_destroy();
header('Location: ../index.php');
?>