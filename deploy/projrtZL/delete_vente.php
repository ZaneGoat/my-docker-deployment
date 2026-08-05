<?php

session_start();

if(!isset($_SESSION["username"])){
header("Location:login.php");
exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

$id=$_GET["id"];

$conn->query("DELETE FROM vente WHERE n_vnt=$id");

header("Location:vente.php");

exit();

?>