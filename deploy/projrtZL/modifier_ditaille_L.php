<?php
session_start();

if(!isset($_SESSION["username"])){
header("Location:login.php");
exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

$n_livr=$_GET["n_livr"];
$NPrd=$_GET["NPrd"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

$new_livr=$_POST["n_livr"];
$new_prd=$_POST["NPrd"];
$qte=$_POST["qte_L"];

$conn->query("UPDATE ditaille_L SET

n_livr='$new_livr',
NPrd='$new_prd',
qte_L='$qte'

WHERE n_livr='$n_livr'
AND NPrd='$NPrd'");

header("Location:ditaille_L.php");
exit();

}

$result=$conn->query("SELECT * FROM ditaille_L
WHERE n_livr='$n_livr'
AND NPrd='$NPrd'");

$row=$result->fetch_assoc();

$livraisons=$conn->query("SELECT n_livr FROM livraison");
$produits=$conn->query("SELECT NPrd,NomPrd FROM produit");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Modifier Détail Livraison</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<h2>Modifier Détail Livraison</h2>

<form method="POST">

Livraison<br>

<select name="n_livr">

<?php while($l=$livraisons->fetch_assoc()){ ?>

<option value="<?php echo $l['n_livr'];?>"

<?php if($l['n_livr']==$row['n_livr']) echo "selected"; ?>>

<?php echo $l['n_livr'];?>

</option>

<?php } ?>

</select>

<br><br>

Produit<br>

<select name="NPrd">

<?php while($p=$produits->fetch_assoc()){ ?>

<option value="<?php echo $p['NPrd'];?>"

<?php if($p['NPrd']==$row['NPrd']) echo "selected"; ?>>

<?php echo $p['NPrd']." - ".$p['NomPrd'];?>

</option>

<?php } ?>

</select>

<br><br>

Quantité<br>

<input type="number" name="qte_L"
value="<?php echo $row['qte_L'];?>">

<br><br>

<button type="submit">Modifier</button>

</form>

<?php include 'fab.php'; ?>
</body>
</html>