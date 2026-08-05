<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "");
if (!$conn->connect_error) {
    $conn->query("CREATE DATABASE IF NOT EXISTS my_project");
    $conn->select_db("my_project");
}

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Ajouter un client
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $telephone = $_POST["telephone"];
    $adresse = $_POST["adresse"];

    $sql = "INSERT INTO client (nom_clt, prenom, tele_clt, adrs_clt)
            VALUES ('$nom','$prenom','$telephone','$adresse')";

    if ($conn->query($sql)) {
        header("Location: client.php");
        exit();
    } else {
        echo "Erreur : " . $conn->error;
    }
}

// Recherche
if (isset($_GET['recherche']) && $_GET['recherche'] != "") {
    $recherche = $_GET['recherche'];

    $result = $conn->query("SELECT * FROM client
        WHERE nom_clt LIKE '%$recherche%'
        OR prenom LIKE '%$recherche%'
        OR tele_clt LIKE '%$recherche%'");
} else {
    $result = $conn->query("SELECT * FROM client");
}

if ($result === false) {
    die("<div style='background:rgba(255,0,0,0.1); border:1px solid red; padding:20px; border-radius:10px; text-align:center; margin:20px;'><h3 style='color:#ff4d4d;'>SQL Error: " . htmlspecialchars($conn->error) . "</h3><p style='color:white;'>This usually means the database tables don't exist yet. Make sure you ran the SQL script in phpMyAdmin!</p></div>");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des clients</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-layout">
    <div class="sidebar-forms">
        <form method="POST" action="client.php">
            <h3>Ajouter un client</h3>
            Nom :<br>
            <input type="text" name="nom" required><br><br>

            Prénom :<br>
            <input type="text" name="prenom" required><br><br>

            Téléphone :<br>
            <input type="text" name="telephone" required><br><br>

            Adresse :<br>
            <input type="text" name="adresse" required><br><br>

            <button type="submit">Ajouter</button>
        </form>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin: 0; text-align: left;">Recherche & Liste des clients</h2>
            <button onclick="window.print()" style="background: var(--theme-color); color: #000; margin: 0; padding: 10px 18px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">🖨️ Imprimer la liste</button>
        </div>

        <form method="GET" action="client.php" style="max-width: 100%; margin-bottom: 25px; display: flex; gap: 10px; align-items: center; background: none; border: none; box-shadow: none; padding: 0;">
            <input type="text" name="recherche" placeholder="Rechercher un client" style="flex: 1; margin: 0; max-width: 100%;">
            <button type="submit" style="margin: 0; white-space: nowrap; padding: 12px 20px;">Rechercher</button>
            <?php if (isset($_GET['recherche']) && $_GET['recherche'] != "") { ?>
                <a href="client.php" class="btn" style="background: var(--theme-dark); color: white; margin: 0; white-space: nowrap; line-height: 24px; padding: 12px 20px;">Réinitialiser</a>
            <?php } ?>
        </form>

        <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Téléphone</th>
            <th>Adresse</th>
            <th>Action</th>
        </tr>

        <?php
        while($row = $result->fetch_assoc()){
            echo "<tr>
                <td>".$row['n_clt']."</td>
                <td>".$row['nom_clt']."</td>
                <td>".$row['prenom']."</td>
                <td>".$row['tele_clt']."</td>
                <td>".$row['adrs_clt']."</td>
                <td>
                   <div class='actions'>
        <a class='btn-modifier' href='modifier.php?id=".$row['n_clt']."'>Modifier</a>

        <a class='btn-supprimer' href='delete.php?id=".$row['n_clt']."' onclick=\"return confirm('Voulez-vous supprimer ce client ?')\">Supprimer</a>
        </div>
                </td>
            </tr>";
        }
        ?>
        </table>
    </div>
</div>

<br><br>
<div class="center">
    <a href="accueil.php">
        <button type="button"> retour à l'accueil</button>
    </a>
</div>
<?php include 'fab.php'; ?>
</body>
</html>