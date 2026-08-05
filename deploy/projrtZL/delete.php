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

$sql = "DELETE FROM client WHERE n_clt=$id";

if ($conn->query($sql)) {
    header("Location: client.php");
    exit();
} else {
    echo "Erreur : " . $conn->error;
}

$conn->close();
?>