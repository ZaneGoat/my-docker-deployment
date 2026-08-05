<$php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "my_project");
$n_cmd= $_GET['n_cmd'];
if ($conn->connect_error) {
    die("Erreur de connexion");
}

// Ajouter
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $n_cmd = $_POST["n_cmd"];
    $NPrd = $_POST["NPrd"];
    $qteC = $_POST["qteC"];

    $sql = "INSERT INTO detaille_cmd(n_cmd, NPrd, qteC)
            VALUES('$n_cmd','$NPrd','$qteC')";

    $conn->query($sql);

    header("Location: detaille_cmd.php");
    exit();
}

// Recherche
if (isset($_GET["recherche"]) && $_GET["recherche"] != "") {

    $r = $_GET["recherche"];

    $result = $conn->query("SELECT * FROM detaille_cmd
                            WHERE n_cmd LIKE '%$r%'
                            OR NPrd LIKE '%$r%'");

} else {

    $result = $conn->query("SELECT * FROM detaille_cmd");
}

// Listes déroulantes
$commandes = $conn->query("SELECT n_cmd FROM commande");
$produits = $conn->query("SELECT NPrd, NomPrd FROM produit");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Détail Commande</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<h2>Ajouter un détail de commande</h2>

<form method="POST">

Commande :<br>

<select name="n_cmd" required>

<?php
while($c = $commandes->fetch_assoc()){
?>

<option value="<?php echo $c['n_cmd']; ?>">
<?php echo $c['n_cmd']; ?>
</option>

<?php
}
?>

</select>

<br><br>

Produit :<br>

<select name="NPrd" required>

<?php
while($p = $produits->fetch_assoc()){
?>

<option value="<?php echo $p['NPrd']; ?>">
<?php echo $p['NPrd']." - ".$p['NomPrd']; ?>
</option>

<?php
}
?>

</select>

<br><br>

Quantité :<br>

<input type="number" name="qteC" required>

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

<a href="detaille_cmd.php">
<button type="button">Retour</button>
</a>

<?php
}
?>

</form>

<h2>Liste des détails des commandes</h2>

<table border="1" cellpadding="10">

<tr>

<th>Commande</th>
<th>Produit</th>
<th>Quantité</th>
<th>Action</th>

</tr>

<?php

while($row = $result->fetch_assoc()){

?>

<tr>

<td><?php echo $row["n_cmd"]; ?></td>

<td><?php echo $row["NPrd"]; ?></td>

<td><?php echo $row["qteC"]; ?></td>

<td>

<a class="btn"
href="modifier_detaille_cmd.php?n_cmd=<?php echo $row['n_cmd']; ?>&NPrd=<?php echo $row['NPrd']; ?>">
Modifier
</a>

<a class="btn delete"
href="delete_detaille_cmd.php?n_cmd=<?php echo $row['n_cmd']; ?>&NPrd=<?php echo $row['NPrd']; ?>"
onclick="return confirm('Supprimer ce détail ?');">
Supprimer
</a>

</td>

</tr>

<?php

}

?>

</table>

<br>

<a href="accueil.php">
<button type="button">Retour à l'accueil</button>
</a>

<?php include 'fab.php'; ?>
</body>
</html>