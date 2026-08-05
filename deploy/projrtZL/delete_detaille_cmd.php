<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

$n_cmd = $_GET["n_cmd"];
$NPrd = $_GET["NPrd"];

$sql = "DELETE FROM detaille_cmd
        WHERE n_cmd='$n_cmd'
        AND NPrd='$NPrd'";

$conn->query($sql);

header("Location: commande.php");
exit();
?>