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

$NPrd = $_GET["NPrd"];
$n_vnt = $_GET["n_vnt"];

$sql = "DELETE FROM vendre
        WHERE NPrd='$NPrd'
        AND n_vnt='$n_vnt'";

$conn->query($sql);

header("Location: vente.php");
exit();
?>