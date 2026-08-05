<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ELectro_vente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h2>ELectro_vente</h2>
        </div>
        <div class="card-body">
            <h4>Bienvenue : <?php echo htmlspecialchars($_SESSION["username"]); ?> 👋</h4>
            <hr>
            <div class="menu">
                <a href="client.php">Clients</a>
                <a href="produits.php">Produits</a>
                <a href="commande.php">Commandes</a>
                <a href="facture.php">Factures</a>
                <a href="fournisseur.php">Fournisseurs</a>
                <a href="livraison.php">Livraisons</a>
                <a href="vente.php">Ventes & Vendre</a>
            </div>
            <div class="center">
                <a class="logout" href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>
</div>

<?php include 'fab.php'; ?>
</body>
</html>