<?php

session_start();

if(!isset($_SESSION["username"])){
header("Location: login.php");
exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

$id=$_GET["id"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

$catg=$_POST["catg"];
$nom=$_POST["NomPrd"];
$qte=$_POST["QteStck"];
$achat=$_POST["PrixAch"];
$vente=$_POST["PrixVnt"];

$conn->query("UPDATE produit SET

catg='$catg',
NomPrd='$nom',
QteStck='$qte',
PrixAch='$achat',
PrixVnt='$vente'

WHERE NPrd=$id");

header("Location: produits.php");
exit();

}

$result=$conn->query("SELECT * FROM produit WHERE NPrd=$id");

$row=$result->fetch_assoc();

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Modifier Produit</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<h2>Modifier Produit</h2>

<form method="POST">

Catégorie<br>
<input type="text" name="catg" value="<?php echo $row['catg'];?>"><br><br>

Nom Produit<br>
<input type="text" name="NomPrd" value="<?php echo $row['NomPrd'];?>"><br><br>

Quantité<br>
<input type="number" name="QteStck" value="<?php echo $row['QteStck'];?>"><br><br>

Prix Achat<br>
<input type="number" step="0.01" name="PrixAch" value="<?php echo $row['PrixAch'];?>"><br><br>

Prix Vente<br>
<input type="number" step="0.01" name="PrixVnt" value="<?php echo $row['PrixVnt'];?>"><br><br>

<button type="submit">Modifier</button>

</form>

<?php include 'fab.php'; ?>
</body>
</html>