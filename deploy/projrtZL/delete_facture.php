<?php
session_start();

if(!isset($_SESSION["username"])){
    header("Location: login.php");
    exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

$id=$_GET["id"];

$conn->query("DELETE FROM facture WHERE n_fct=$id");

header("Location: facture.php");
exit();
?>