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

    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $telephone = $_POST["telephone"];
    $adresse = $_POST["adresse"];

    $sql = "UPDATE client SET
            nom_clt='$nom',
            prenom='$prenom',
            tele_clt='$telephone',
            adrs_clt='$adresse'
            WHERE n_clt=$id";

    if ($conn->query($sql)) {
        header("Location: client.php");
        exit();
    } else {
        echo "Erreur : " . $conn->error;
    }
}

$result = $conn->query("SELECT * FROM client WHERE n_clt=$id");
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier un client</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Modifier le client</h2>

<form method="POST">

Nom :<br>
<input type="text" name="nom" value="<?php echo $row['nom_clt']; ?>" required><br><br>

Prénom :<br>
<input type="text" name="prenom" value="<?php echo $row['prenom']; ?>" required><br><br>

Téléphone :<br>
<input type="text" name="telephone" value="<?php echo $row['tele_clt']; ?>" required><br><br>

Adresse :<br>
<input type="text" name="adresse" value="<?php echo $row['adrs_clt']; ?>" required><br><br>

<button type="submit">Modifier</button>

</form>

<br>

<a href="client.php">
    <button type="button">Retour</button>
</a>

<?php include 'fab.php'; ?>
</body>
</html>