<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

$id = $_GET["id"];

if($_SERVER["REQUEST_METHOD"]=="POST"){

$date=$_POST["date_livrs"];
$nom=$_POST["nom_frs"];
$id_frs=$_POST["N_frs"];

$conn->query("UPDATE livraison SET

date_livrs='$date',
nom_frs='$nom',
N_frs='$id_frs'

WHERE n_livr=$id");

header("Location: livraison.php");
exit();

}

$result=$conn->query("SELECT * FROM livraison WHERE n_livr=$id");

$row=$result->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Modifier Livraison</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Modifier Livraison</h2>

<form method="POST">

Date livraison<br>
<input type="date" name="date_livrs" value="<?php echo $row['date_livrs']; ?>" required><br><br>

Nom fournisseur<br>
<input type="text" name="nom_frs" value="<?php echo $row['nom_frs']; ?>" required><br><br>

N° Fournisseur<br>
<input type="number" name="N_frs" value="<?php echo $row['N_frs']; ?>" required><br><br>

<button type="submit">Modifier</button>

</form>

<br>

<a href="livraison.php">
<button type="button">Retour</button>
</a>

<?php include 'fab.php'; ?>
</body>
</html>