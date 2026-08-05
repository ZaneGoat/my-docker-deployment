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

$is_single = isset($_GET["id"]);

if ($is_single) {
    $id = $conn->real_escape_string($_GET["id"]);
    
    // Fetch Single Command
    $cmd_query = $conn->query("
        SELECT c.*, f.nom_frs, f.adrs_frs, f.tele_frs
        FROM commande c
        INNER JOIN fournisseur f ON c.N_frs = f.N_frs
        WHERE c.n_cmd = '$id'
    ");
    $commande = $cmd_query->fetch_assoc();

    // Fetch Details
    $details = $conn->query("
        SELECT d.*, p.NomPrd, p.PrixAch
        FROM detaille_cmd d
        INNER JOIN produit p ON d.NPrd = p.NPrd
        WHERE d.n_cmd = '$id'
    ");

} else {
    // Fetch All Commands
    $commandes = $conn->query("
        SELECT c.*, f.nom_frs
        FROM commande c
        INNER JOIN fournisseur f ON c.N_frs = f.N_frs
        ORDER BY c.n_cmd DESC
    ");

    $all_details = $conn->query("
        SELECT d.n_cmd, p.NomPrd, d.qteC
        FROM detaille_cmd d
        INNER JOIN produit p ON d.NPrd = p.NPrd
    ");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Impression <?php echo $is_single ? "Bon de Commande" : "Liste des Commandes"; ?></title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 40px;
    color: #333;
}
h1, h2, h3 {
    text-align: center;
    margin: 5px;
    color: #000;
}
p {
    text-align: center;
}
.header-info {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    margin-bottom: 30px;
    border-bottom: 2px solid #000;
    padding-bottom: 20px;
}
.header-info div {
    text-align: left;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    margin-bottom: 30px;
}
table, th, td {
    border: 1px solid #000;
}
th {
    background: #d9d9d9;
    padding: 12px 8px;
    font-weight: bold;
    text-transform: uppercase;
}
td {
    padding: 10px 8px;
    text-align: center;
}
.total-row th {
    text-align: right;
    padding-right: 15px;
}
.total-row td {
    font-weight: bold;
    font-size: 1.1em;
}
.footer-signature {
    margin-top: 60px;
    display: flex;
    justify-content: space-between;
}
button.print-btn {
    padding: 12px 24px;
    background: #333;
    color: #fff;
    border: none;
    font-size: 16px;
    cursor: pointer;
    display: block;
    margin: 40px auto;
}
button.back-btn {
    padding: 12px 24px;
    background: #ccc;
    color: #000;
    border: none;
    font-size: 16px;
    cursor: pointer;
    display: block;
    margin: 10px auto;
    text-decoration: none;
    text-align: center;
    width: fit-content;
}

@media print {
    button, .print-btn, .back-btn, .fab-container, a {
        display: none !important;
    }
    body {
        margin: 0;
    }
}
</style>
</head>
<body>

<h1>GESTION COMMERCIALE</h1>
<h2>Electro_Vente</h2>

<?php if ($is_single && $commande) { ?>

    <h3>BON DE COMMANDE</h3>

    <div class="header-info">
        <div>
            <strong>Commande N° :</strong> <?php echo $commande["n_cmd"]; ?><br>
            <strong>Date :</strong> <?php echo $commande["date_cmd"]; ?>
        </div>
        <div>
            <strong>Fournisseur :</strong> <?php echo htmlspecialchars($commande["nom_frs"]); ?><br>
            <strong>Adresse :</strong> <?php echo htmlspecialchars($commande["adrs_frs"]); ?><br>
            <strong>Téléphone :</strong> <?php echo htmlspecialchars($commande["tele_frs"]); ?>
        </div>
    </div>

    <table>
        <tr>
            <th>Produit</th>
            <th>Quantité</th>
            <th>Prix Unitaire</th>
            <th>Sous-Total</th>
        </tr>
        <?php while($d = $details->fetch_assoc()) { 
            $sous_total = $d["PrixAch"] * $d["qteC"];
        ?>
        <tr>
            <td style="text-align: left; padding-left: 15px;"><?php echo htmlspecialchars($d["NomPrd"]); ?></td>
            <td><?php echo $d["qteC"]; ?></td>
            <td><?php echo number_format($d["PrixAch"], 2); ?> DH</td>
            <td><?php echo number_format($sous_total, 2); ?> DH</td>
        </tr>
        <?php } ?>
        <tr class="total-row">
            <th colspan="3">MONTANT TOTAL</th>
            <td><?php echo number_format($commande["mt_cmd"], 2); ?> DH</td>
        </tr>
    </table>

    <div class="footer-signature">
        <div><strong>Signature Fournisseur :</strong></div>
        <div><strong>Signature Responsable :</strong></div>
    </div>

<?php } else { ?>

    <p><strong>Liste des Commandes Globales</strong></p>
    <p>Date : <?php echo date("d/m/Y"); ?></p>

    <table>
        <tr>
            <th>N° Commande</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Fournisseur</th>
        </tr>
        <?php while($c = $commandes->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $c["n_cmd"]; ?></td>
            <td><?php echo $c["date_cmd"]; ?></td>
            <td><?php echo number_format($c["mt_cmd"], 2); ?> DH</td>
            <td><?php echo htmlspecialchars($c["nom_frs"]); ?></td>
        </tr>
        <?php } ?>
    </table>

    <h3>Détails des Produits Commandés</h3>
    <table>
        <tr>
            <th>N° Commande</th>
            <th>Produit</th>
            <th>Quantité</th>
        </tr>
        <?php while($d = $all_details->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $d["n_cmd"]; ?></td>
            <td><?php echo htmlspecialchars($d["NomPrd"]); ?></td>
            <td><?php echo $d["qteC"]; ?></td>
        </tr>
        <?php } ?>
    </table>

    <div class="footer-signature">
        <div></div>
        <div><strong>Signature :</strong> ______________________</div>
    </div>

<?php } ?>

<button class="print-btn" onclick="window.print()">🖨️ Imprimer</button>
<a href="commande.php" class="back-btn">Retour aux Commandes</a>

<script>
window.onload = function(){
    window.print();
}
</script>

<?php include 'fab.php'; ?>
</body>
</html>