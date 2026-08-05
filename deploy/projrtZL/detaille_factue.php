<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

if ($conn->connect_error) {
    die("Erreur de connexion");
}

// Ajouter
if($_SERVER["REQUEST_METHOD"]=="POST"){

    $n_fct=$_POST["n_fct"];
    $NPrd=$_POST["NPrd"];
    $qteF=$_POST["qteF"];

    $sql="INSERT INTO detaille_factur(n_fct,NPrd,qteF)
          VALUES('$n_fct','$NPrd','$qteF')";

    $conn->query($sql);

    header("Location: detaille_factur.php");
    exit();
}

// Recherche
if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){

    $r=$_GET["recherche"];

    $result=$conn->query("SELECT * FROM detaille_factur
    WHERE n_fct LIKE '%$r%'
    OR NPrd LIKE '%$r%'");

}else{

    $result=$conn->query("SELECT * FROM detaille_factur");
}

// Select
$factures=$conn->query("SELECT n_fct FROM facture");
$produits=$conn->query("SELECT NPrd,NomPrd FROM produit");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Détail Facture</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<h2>Ajouter un détail de facture</h2>

<form method="POST">

Facture<br>

<select name="n_fct" required>

<?php
while($f=$factures->fetch_assoc()){
?>

<option value="<?php echo $f['n_fct']; ?>">
<?php echo $f['n_fct']; ?>
</option>

<?php } ?>

</select>

<br><br>

Produit<br>

<select name="NPrd" required>

<?php
while($p=$produits->fetch_assoc()){
?>

<option value="<?php echo $p['NPrd']; ?>">
<?php echo $p['NPrd']." - ".$p['NomPrd']; ?>
</option>

<?php } ?>

</select>

<br><br>

Quantité<br>

<input type="number" name="qteF" required>

<br><br>

<button type="submit">Ajouter</button>

</form>

<hr>

<form method="GET">

<input type="text" name="recherche" placeholder="Rechercher">

<button type="submit">Rechercher</button>

<?php
if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){
?>

<a href="detaille_factur.php">
<button type="button">Retour</button>
</a>

<?php } ?>

</form>

<h2>Liste des détails des factures</h2>

<table border="1" cellpadding="10">

<tr>

<th>Facture</th>
<th>Produit</th>
<th>Quantité</th>
<th>Action</th>

</tr>

<?php

while($row=$result->fetch_assoc()){

?>

<tr>

<td><?php echo $row["n_fct"]; ?></td>

<td><?php echo $row["NPrd"]; ?></td>

<td><?php echo $row["qteF"]; ?></td>

<td>

<a class="btn"
href="modifier_detaille_factur.php?n_fct=<?php echo $row['n_fct']; ?>&NPrd=<?php echo $row['NPrd']; ?>">
Modifier
</a>

<a class="btn delete"
href="delete_detaille_factur.php?n_fct=<?php echo $row['n_fct']; ?>&NPrd=<?php echo $row['NPrd']; ?>"
onclick="return confirm('Supprimer ce détail ?')">
Supprimer
</a>

</td>

</tr>

<?php } ?>

</table>

<br>

<a href="accueil.php">
<button type="button">Retour à l'accueil</button>
</a>

<?php include 'fab.php'; ?>
</body>
</html>