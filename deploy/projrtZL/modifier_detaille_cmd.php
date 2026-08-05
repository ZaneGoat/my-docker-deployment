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

$n_cmd = $_GET["n_cmd"];
$NPrd = $_GET["NPrd"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $new_cmd = $_POST["n_cmd"];
    $new_prd = $_POST["NPrd"];
    $qte = $_POST["qteC"];

    $sql = "UPDATE detaille_cmd
            SET n_cmd='$new_cmd',
                NPrd='$new_prd',
                qteC='$qte'
            WHERE n_cmd='$n_cmd'
            AND NPrd='$NPrd'";

    $conn->query($sql);

    header("Location: commande.php");
    exit();
}

$result = $conn->query("SELECT * FROM detaille_cmd
WHERE n_cmd='$n_cmd'
AND NPrd='$NPrd'");

$row = $result->fetch_assoc();

$commandes = $conn->query("SELECT n_cmd FROM commande");
$produits = $conn->query("SELECT NPrd, NomPrd FROM produit");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Modifier Détail Commande</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Modifier Détail Commande</h2>

<form method="POST">

Commande<br>

<select name="n_cmd">

<?php
while($c=$commandes->fetch_assoc()){
?>

<option value="<?php echo $c['n_cmd'];?>"
<?php if($c['n_cmd']==$row['n_cmd']) echo "selected"; ?>>

<?php echo $c['n_cmd'];?>

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

<input type="number" name="qteC"
value="<?php echo $row['qteC'];?>">

<br><br>

<button type="submit">Modifier</button>

</form>

<br>

<a href="commande.php">
<button type="button">Retour</button>
</a>

<?php include 'fab.php'; ?>
</body>
</html>