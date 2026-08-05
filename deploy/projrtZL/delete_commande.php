<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "my_project");

if ($conn->connect_error) {
    die("Erreur de connexion");
}

$id = $_GET["id"];

$sql = "DELETE FROM commande WHERE n_cmd=$id";

if ($conn->query($sql)) {
    header("Location: commande.php");
    exit();
} else {
    echo "Erreur : " . $conn->error;
}

$conn->close();
?>