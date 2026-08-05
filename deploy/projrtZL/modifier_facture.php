<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "my_project");

if ($conn->connect_error) {
    die("Erreur de connexion");
}

$id = $_GET["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $produit = $_POST["produit"];
    $date = $_POST["date_fct"];
    $tva = $_POST["tva"];
    $ht = $_POST["total_HT"];
    $ttc = $_POST["TTC"];
    $N_frs = $_POST["N_frs"];

    $sql = "UPDATE facture SET
            produit='$produit',
            date_fct='$date',
            tva='$tva',
            total_HT='$ht',
            TTC='$ttc',
            N_frs='$N_frs'
            WHERE n_fct='$id'";

    $conn->query($sql);

    header("Location: facture.php");
    exit();
}

$result = $conn->query("SELECT * FROM facture WHERE n_fct='$id'");
$row = $result->fetch_assoc();

$produits = $conn->query("SELECT NomPrd, PrixVnt FROM produit");
$fournisseurs = $conn->query("SELECT N_frs, nom_frs FROM fournisseur");
?>

<!DOCTYPE html>
<html lang="fr">
<head>

<meta charset="UTF-8">

<title>Modifier Facture</title>

<link rel="stylesheet" href="style.css">

<script>

function calculTotal(){

var produit=document.getElementById("produit");

var prix=parseFloat(
produit.options[produit.selectedIndex].dataset.prix
);

var qte=parseFloat(document.getElementById("qte").value)||1;

var ht=prix*qte;

document.getElementById("total_HT").value=ht.toFixed(2);

var tva=parseFloat(document.getElementById("tva").value)||0;

var ttc=ht+(ht*tva/100);

document.getElementById("TTC").value=ttc.toFixed(2);

}

</script>

</head>

<body>

<h2>Modifier Facture</h2>

<form method="POST">

Produit :<br>

<select name="produit" id="produit" onchange="calculTotal()">

<?php
while($p=$produits->fetch_assoc()){
?>

<option
value="<?php echo $p['NomPrd'];?>"
data-prix="<?php echo $p['PrixVnt'];?>"

<?php
if($row['produit']==$p['NomPrd']) echo "selected";
?>

>

<?php echo $p['NomPrd'];?>

</option>

<?php } ?>

</select>

<br><br>

Date :<br>

<input type="date"
name="date_fct"
value="<?php echo $row['date_fct'];?>"
required>

<br><br>

Quantité :<br>

<input type="number"
id="qte"
value="1"
min="1"
onkeyup="calculTotal()"
onchange="calculTotal()">

<br><br>

TVA :<br>

<select name="tva"
id="tva"
onchange="calculTotal()">

<option value="20" <?php if($row['tva']==20) echo "selected"; ?>>20%</option>

<option value="14" <?php if($row['tva']==14) echo "selected"; ?>>14%</option>

<option value="7" <?php if($row['tva']==7) echo "selected"; ?>>7%</option>

</select>

<br><br>

Total HT :<br>

<input type="number"
step="0.01"
name="total_HT"
id="total_HT"
value="<?php echo $row['total_HT'];?>"
readonly>

<br><br>

TTC :<br>

<input type="number"
step="0.01"
name="TTC"
id="TTC"
value="<?php echo $row['TTC'];?>"
readonly>

<br><br>

Fournisseur :<br>

<select name="N_frs">

<?php
while($f=$fournisseurs->fetch_assoc()){
?>

<option
value="<?php echo $f['N_frs'];?>"

<?php
if($row['N_frs']==$f['N_frs']) echo "selected";
?>

>

<?php echo $f['nom_frs'];?>

</option>

<?php } ?>

</select>

<br><br>

<button type="submit">Modifier</button>

<a href="facture.php">
<button type="button">Retour</button>
</a>

</form>

<script>
calculTotal();
</script>

<?php include 'fab.php'; ?>
</body>
</html>