<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

$n_fct = $_GET["n_fct"];
$NPrd = $_GET["NPrd"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $new_fct = $_POST["n_fct"];
    $new_prd = $_POST["NPrd"];
    $qte = $_POST["qteF"];

    $sql = "UPDATE detaille_factur
            SET n_fct='$new_fct',
                NPrd='$new_prd',
                qteF='$qte'
            WHERE n_fct='$n_fct'
            AND NPrd='$NPrd'";

    $conn->query($sql);

    header("Location:factur.php");
    exit();
}

$result = $conn->query("SELECT * FROM detaille_factur
WHERE n_fct='$n_fct'
AND NPrd='$NPrd'");

$row = $result->fetch_assoc();

$factures = $conn->query("SELECT n_fct FROM facture");
$produits = $conn->query("SELECT NPrd,NomPrd FROM produit");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Modifier Détail Facture</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<h2>Modifier Détail Facture</h2>

<form method="POST">

Facture<br>

<select name="n_fct">

<?php
while($f=$factures->fetch_assoc()){
?>

<option value="<?php echo $f['n_fct'];?>"
<?php if($f['n_fct']==$row['n_fct']) echo "selected"; ?>>

<?php echo $f['n_fct'];?>

</option>

<?php } ?>

</select>

<br><br>

Produit<br>

<select name="NPrd">

<?php
while($p=$produits->fetch_assoc()){
?>

<option value="<?php echo $p['NPrd'];?>"
<?php if($p['NPrd']==$row['NPrd']) echo "selected"; ?>>

<?php echo $p['NPrd']." - ".$p['NomPrd'];?>

</option>

<?php } ?>

</select>

<br><br>

Quantité<br>

<input type="number" name="qteF"
value="<?php echo $row['qteF'];?>">

<br><br>

<button type="submit">Modifier</button>

</form>

<br>

<a href="factur.php">
<button type="button">Retour</button>
</a>

<?php include 'fab.php'; ?>
</body>
</html>