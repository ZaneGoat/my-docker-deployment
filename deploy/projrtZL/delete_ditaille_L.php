<?php
session_start();

if(!isset($_SESSION["username"])){
header("Location:login.php");
exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

$n_livr=$_GET["n_livr"];
$NPrd=$_GET["NPrd"];

$conn->query("DELETE FROM ditaille_L
WHERE n_livr='$n_livr'
AND NPrd='$NPrd'");

header("Location:ditaille_L.php");

exit();

?>