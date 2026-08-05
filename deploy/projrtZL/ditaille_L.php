<?php
session_start();

if(!isset($_SESSION["username"])){
    header("Location: login.php");
    exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $n_livr=$_POST["n_livr"];
    $NPrd=$_POST["NPrd"];
    $qte=$_POST["qte_L"];

    $conn->query("INSERT INTO ditaille_L(n_livr,NPrd,qte_L)
    VALUES('$n_livr','$NPrd','$qte')");

    header("Location: ditaille_L.php");
    exit();
}

if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){

$r=$_GET["recherche"];

$result=$conn->query("SELECT * FROM ditaille_L
WHERE n_livr LIKE '%$r%'
OR NPrd LIKE '%$r%'");

}else{

$result=$conn->query("SELECT * FROM ditaille_L");

}

$livraisons=$conn->query("SELECT n_livr FROM livraison");
$produits=$conn->query("SELECT NPrd,NomPrd FROM produit");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Détail Livraison</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<h2>Ajouter un détail de livraison</h2>

<form method="POST">

Livraison<br>

<select name="n_livr">

<?php while($l=$livraisons->fetch_assoc()){ ?>

<option value="<?php echo $l['n_livr']; ?>">

<?php echo $l['n_livr']; ?>

</option>

<?php } ?>

</select>

<br><br>

Produit<br>

<select name="NPrd">

<?php while($p=$produits->fetch_assoc()){ ?>

<option value="<?php echo $p['NPrd']; ?>">

<?php echo $p['NPrd']." - ".$p['NomPrd']; ?>

</option>

<?php } ?>

</select>

<br><br>

Quantité<br>

<input type="number" name="qte_L" required>

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

<a href="ditaille_L.php">
<button type="button">Retour</button>
</a>

<?php } ?>

</form>

<table border="1" cellpadding="10">

<tr>

<th>Livraison</th>
<th>Produit</th>
<th>Quantité</th>
<th>Action</th>

</tr>

<?php

while($row=$result->fetch_assoc()){

?>

<tr>

<td><?php echo $row["n_livr"]; ?></td>

<td><?php echo $row["NPrd"]; ?></td>

<td><?php echo $row["qte_L"]; ?></td>

<td>

<a class="btn"
href="modifier_ditaille_L.php?n_livr=<?php echo $row['n_livr']; ?>&NPrd=<?php echo $row['NPrd']; ?>">

Modifier

</a>

<a class="btn delete"
href="delete_ditaille_L.php?n_livr=<?php echo $row['n_livr']; ?>&NPrd=<?php echo $row['NPrd']; ?>"
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