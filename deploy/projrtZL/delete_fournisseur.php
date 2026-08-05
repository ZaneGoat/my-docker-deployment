<?php

session_start();

if(!isset($_SESSION["username"])){
header("Location:login.php");
exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

$id=$_GET["id"];

$conn->query("DELETE FROM fournisseur WHERE N_frs=$id");

header("Location:fournisseur.php");

exit();

?>