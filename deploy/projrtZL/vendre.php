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

// Ajouter
if($_SERVER["REQUEST_METHOD"]=="POST"){

    $NPrd=$_POST["NPrd"];
    $n_vnt=$_POST["n_vnt"];
    $qteV=$_POST["qteV"];

    $conn->query("INSERT INTO vendre(NPrd,n_vnt,qteV)
    VALUES('$NPrd','$n_vnt','$qteV')");

    header("Location: vendre.php");
    exit();
}

// Recherche
if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){

$r=$_GET["recherche"];

$result=$conn->query("SELECT * FROM vendre
WHERE NPrd LIKE '%$r%'
OR n_vnt LIKE '%$r%'");

}else{

$result=$conn->query("SELECT * FROM vendre");

}

$produits=$conn->query("SELECT NPrd,NomPrd FROM produit");
$ventes=$conn->query("SELECT n_vnt FROM vente");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Vendre</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Ajouter une vente de produit</h2>

<form method="POST">

Produit<br>

<select name="NPrd">

<?php while($p=$produits->fetch_assoc()){ ?>

<option value="<?php echo $p['NPrd']; ?>">

<?php echo $p['NPrd']." - ".$p['NomPrd']; ?>

</option>

<?php } ?>

</select>

<br><br>

Vente<br>

<select name="n_vnt">

<?php while($v=$ventes->fetch_assoc()){ ?>

<option value="<?php echo $v['n_vnt']; ?>">

<?php echo $v['n_vnt']; ?>

</option>

<?php } ?>

</select>

<br><br>

Quantité<br>

<input type="number" name="qteV" required>

<br><br>

<button type="submit">Ajouter</button>

</form>

<hr>

<form method="GET">

<input type="text" name="recherche" placeholder="Recherche">

<button type="submit">Rechercher</button>

<?php
if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){
?>

<a href="vendre.php">
<button type="button">Retour</button>
</a>

<?php } ?>

</form>

<table border="1" cellpadding="10">

<tr>

<th>Produit</th>
<th>Vente</th>
<th>Quantité</th>
<th>Action</th>

</tr>

<?php

while($row=$result->fetch_assoc()){

?>

<tr>

<td><?php echo $row["NPrd"]; ?></td>

<td><?php echo $row["n_vnt"]; ?></td>

<td><?php echo $row["qteV"]; ?></td>

<td>

<a class="btn-modifier"
href="modifier_vendre.php?NPrd=<?php echo $row['NPrd']; ?>&n_vnt=<?php echo $row['n_vnt']; ?>">
Modifier
</a>

<a class="btn-supprimer"
href="delete_vendre.php?NPrd=<?php echo $row['NPrd']; ?>&n_vnt=<?php echo $row['n_vnt']; ?>"
onclick="return confirm('Supprimer ?')">

Supprimer

</a>

</td>

</tr>

<?php } ?>

</table>

<br>

<a href="accueil.php">

<button>Retour à l'accueil</button>

</a>

<?php include 'fab.php'; ?>
</body>
</html>