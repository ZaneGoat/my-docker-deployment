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

$NPrd = $_GET["NPrd"];
$n_vnt = $_GET["n_vnt"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $new_prd = $_POST["NPrd"];
    $new_vnt = $_POST["n_vnt"];
    $qte = $_POST["qteV"];

    $sql = "UPDATE vendre
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

$NPrd = $_GET["NPrd"];
$n_vnt = $_GET["n_vnt"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $new_prd = $_POST["NPrd"];
    $new_vnt = $_POST["n_vnt"];
    $qte = $_POST["qteV"];

    $sql = "UPDATE vendre
            SET NPrd='$new_prd',
                n_vnt='$new_vnt',
                qteV='$qte'
            WHERE NPrd='$NPrd'
            AND n_vnt='$n_vnt'";

    $conn->query($sql);

    header("Location: vente.php");
    exit();
}

$result = $conn->query("SELECT * FROM vendre
WHERE NPrd='$NPrd'
AND n_vnt='$n_vnt'");

$row = $result->fetch_assoc();

$produits = $conn->query("SELECT NPrd, NomPrd FROM produit");
$ventes = $conn->query("SELECT n_vnt FROM vente");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Vente Produit</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Modifier Vente Produit</h2>

<form method="POST">

Produit<br>

<select name="NPrd">

<?php
while($p=$produits->fetch_assoc()){
?>

<option value="<?php echo $p['NPrd']; ?>"
<?php if($p['NPrd']==$row['NPrd']) echo "selected"; ?>>

<?php echo $p['NPrd']." - ".$p['NomPrd']; ?>

</option>

<?php } ?>

</select>

<br><br>

Vente<br>

<select name="n_vnt">

<?php
while($v=$ventes->fetch_assoc()){
?>

<option value="<?php echo $v['n_vnt']; ?>"
<?php if($v['n_vnt']==$row['n_vnt']) echo "selected"; ?>>

<?php echo $v['n_vnt']; ?>

</option>

<?php } ?>

</select>

<br><br>

Quantité<br>

<input type="number" name="qteV"
value="<?php echo $row['qteV']; ?>" required>

<br><br>

<button type="submit">Modifier</button>

</form>

<br>

<a href="vente.php">
<button type="button">Retour</button>
</a>

<?php include 'fab.php'; ?>
</body>
</html>