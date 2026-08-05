<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

$n_fct = $_GET["n_fct"];
$NPrd = $_GET["NPrd"];

$sql = "DELETE FROM detaille_factur
        WHERE n_fct='$n_fct'
        AND NPrd='$NPrd'";

$conn->query($sql);

header("Location: detaille_factur.php");
exit();
?>