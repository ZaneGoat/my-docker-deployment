<?php
session_start();

if(!isset($_SESSION["username"])){
header("Location:login.php");
exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

$id=$_GET["id"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

$date=$_POST["date_vnt"];
$montant=$_POST["mt_T_vnt"];
$client=$_POST["n_clt"];

$conn->query("UPDATE vente SET

date_vnt='$date',
mt_T_vnt='$montant',
n_clt='$client'

WHERE n_vnt=$id");

header("Location:vente.php");
exit();

}

$result=$conn->query("SELECT * FROM vente WHERE n_vnt=$id");

$row=$result->fetch_assoc();

$clients=$conn->query("SELECT n_clt, nom_clt FROM client");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Modifier Vente</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Modifier Vente</h2>

<form method="POST">

Date<br>
<input type="date" name="date_vnt" value="<?php echo $row['date_vnt'];?>"><br><br>

Montant<br>
<input type="number" step="0.01" name="mt_T_vnt" value="<?php echo $row['mt_T_vnt'];?>"><br><br>

Client<br>

<select name="n_clt">

<?php

while($c=$clients->fetch_assoc()){

?>

<option value="<?php echo $c['n_clt'];?>"

<?php if($c['n_clt']==$row['n_clt']) echo "selected"; ?>>

<?php echo $c['n_clt']." - ".$c['nom_clt']; ?>

</option>

<?php } ?>

</select>

<br><br>

<button type="submit">Modifier</button>
<a href="vente.php">
<button>Retour s</button>
</a>
</form>

<?php include 'fab.php'; ?>
</body>
</html>