<?php

session_start();

if(!isset($_SESSION["username"])){
header("Location: login.php");
exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

$id=$_GET["id"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

$nom=$_POST["nom"];
$prenom=$_POST["prenom"];
$telephone=$_POST["telephone"];
$adresse=$_POST["adresse"];
$email=$_POST["email"];

$conn->query("UPDATE fournisseur SET

nom_frs='$nom',
prnom_frs='$prenom',
tele_frs='$telephone',
adrs_frs='$adresse',
email_frs='$email'

WHERE N_frs=$id");

header("Location:fournisseur.php");
exit();

}

$result=$conn->query("SELECT * FROM fournisseur WHERE N_frs=$id");

$row=$result->fetch_assoc();

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Modifier fournisseur</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<h2>Modifier fournisseur</h2>

<form method="POST">

Nom<br>
<input type="text" name="nom" value="<?php echo $row['nom_frs'];?>"><br><br>

Prénom<br>
<input type="text" name="prenom" value="<?php echo $row['prnom_frs'];?>"><br><br>

Téléphone<br>
<input type="text" name="telephone" value="<?php echo $row['tele_frs'];?>"><br><br>

Adresse<br>
<input type="text" name="adresse" value="<?php echo $row['adrs_frs'];?>"><br><br>

Email<br>
<input type="email" name="email" value="<?php echo $row['email_frs'];?>"><br><br>

<button type="submit">Modifier</button>

</form>

<?php include 'fab.php'; ?>
</body>
</html>