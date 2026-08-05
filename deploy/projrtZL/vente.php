<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "my_project");

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Handle adding a sale
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action_vente"])) {
    $date = $_POST["date_vnt"];
    $montant = $_POST["mt_T_vnt"];
    $client = $_POST["n_clt"];

    $stmt = $conn->prepare("INSERT INTO vente (date_vnt, mt_T_vnt, n_clt) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $date, $montant, $client);
    $stmt->execute();
    $stmt->close();

    header("Location: vente.php");
    exit();
}

// Handle adding products to a sale (Vendre)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action_vendre"])) {
    $NPrd = $_POST["NPrd"];
    $n_vnt = $_POST["n_vnt"];
    $qteV = $_POST["qteV"];

    $stmt = $conn->prepare("INSERT INTO vendre (NPrd, n_vnt, qteV) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $NPrd, $n_vnt, $qteV);
    $stmt->execute();
    $stmt->close();

    header("Location: vente.php");
    exit();
}

// Fetch lists for forms
$clients = $conn->query("SELECT n_clt, nom_clt FROM client");
$produits = $conn->query("SELECT NPrd, NomPrd, PrixVnt FROM produit");
$ventes_dropdown = $conn->query("SELECT n_vnt, date_vnt FROM vente ORDER BY n_vnt DESC");

// Search & Read combined Sales and Products list
if (isset($_GET["recherche"]) && $_GET["recherche"] != "") {
    $r = $conn->real_escape_string($_GET["recherche"]);
    $sql = "SELECT v.n_vnt, v.date_vnt, v.mt_T_vnt, v.n_clt, c.nom_clt, 
            GROUP_CONCAT(CONCAT(p.NomPrd, ' (x', ve.qteV, ')') SEPARATOR ', ') as produits_vendus 
            FROM vente v 
            LEFT JOIN client c ON v.n_clt = c.n_clt 
            LEFT JOIN vendre ve ON v.n_vnt = ve.n_vnt 
            LEFT JOIN produit p ON ve.NPrd = p.NPrd 
            WHERE v.n_vnt LIKE '%$r%' 
               OR c.nom_clt LIKE '%$r%' 
               OR p.NomPrd LIKE '%$r%'
            GROUP BY v.n_vnt 
            ORDER BY v.n_vnt DESC";
} else {
    $sql = "SELECT v.n_vnt, v.date_vnt, v.mt_T_vnt, v.n_clt, c.nom_clt, 
            GROUP_CONCAT(CONCAT(p.NomPrd, ' (x', ve.qteV, ')') SEPARATOR ', ') as produits_vendus 
            FROM vente v 
            LEFT JOIN client c ON v.n_clt = c.n_clt 
            LEFT JOIN vendre ve ON v.n_vnt = ve.n_vnt 
            LEFT JOIN produit p ON ve.NPrd = p.NPrd 
            GROUP BY v.n_vnt 
            ORDER BY v.n_vnt DESC";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des Ventes</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Gestion des Ventes & Produits vendus</h2>

<div class="dashboard-layout">
    <!-- TOP BUTTONS -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
        <button id="btnOpenVente" class="btn" style="background: linear-gradient(to right, #38bdf8, #818cf8); font-size: 1.1rem; padding: 15px 30px;">
            ➕ Nouvelle Vente
        </button>
        <button id="btnOpenVendre" class="btn" style="background: linear-gradient(to right, #34d399, #059669); font-size: 1.1rem; padding: 15px 30px;">
            ➕ Vendre un Produit
        </button>
    </div>

    <!-- MODAL VENTE -->
    <div id="modalVente" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div class="modal" style="background: linear-gradient(145deg, #1e293b, #0f172a); border: 1px solid rgba(255,255,255,0.1); padding: 2.5rem; border-radius: 20px; width: 100%; max-width: 400px; text-align: center; position: relative;">
            <button onclick="closeModal('modalVente')" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; color: white; font-size: 1.5rem; cursor: pointer; box-shadow: none;">×</button>
            <h2 style="margin-top: 0;">Nouvelle Vente</h2>
            <form method="POST" style="margin: 0; width: 100%; background: transparent; border: none; box-shadow: none; padding: 0;">
                <input type="hidden" name="action_vente" value="1">
                Date :<br>
                <input type="date" name="date_vnt" required style="width:100%; margin-bottom: 15px;"><br>
                Montant Total :<br>
                <input type="number" step="0.01" name="mt_T_vnt" required placeholder="0.00" style="width:100%; margin-bottom: 15px;"><br>
                Client :<br>
                <select name="n_clt" required style="width:100%; margin-bottom: 20px;">
                    <option value="">Sélectionner un client</option>
                    <?php while($c = $clients->fetch_assoc()) { ?>
                        <option value="<?php echo $c['n_clt']; ?>">
                            <?php echo $c['n_clt'] . " - " . htmlspecialchars($c['nom_clt']); ?>
                        </option>
                    <?php } ?>
                </select><br>
                <button type="submit" style="width:100%;">Enregistrer Vente</button>
            </form>
        </div>
    </div>

    <!-- MODAL VENDRE -->
    <div id="modalVendre" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div class="modal" style="background: linear-gradient(145deg, #1e293b, #0f172a); border: 1px solid rgba(255,255,255,0.1); padding: 2.5rem; border-radius: 20px; width: 100%; max-width: 400px; text-align: center; position: relative;">
            <button onclick="closeModal('modalVendre')" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; color: white; font-size: 1.5rem; cursor: pointer; box-shadow: none;">×</button>
            <h2 style="margin-top: 0;">Vendre un Produit</h2>
            <form method="POST" style="margin: 0; width: 100%; background: transparent; border: none; box-shadow: none; padding: 0;">
                <input type="hidden" name="action_vendre" value="1">
                Vente :<br>
                <select name="n_vnt" required style="width:100%; margin-bottom: 15px;">
                    <option value="">Sélectionner la vente</option>
                    <?php while($v = $ventes_dropdown->fetch_assoc()) { ?>
                        <option value="<?php echo $v['n_vnt']; ?>">
                            Vente N° <?php echo $v['n_vnt'] . " (" . $v['date_vnt'] . ")"; ?>
                        </option>
                    <?php } ?>
                </select><br>
                Produit :<br>
                <select name="NPrd" id="NPrd" onchange="calculSousTotal()" required style="width:100%; margin-bottom: 15px;">
                    <option value="" data-prix="0">Sélectionner un produit</option>
                    <?php while($p = $produits->fetch_assoc()) { ?>
                        <option value="<?php echo $p['NPrd']; ?>" data-prix="<?php echo $p['PrixVnt']; ?>">
                            <?php echo $p['NPrd'] . " - " . htmlspecialchars($p['NomPrd']); ?>
                        </option>
                    <?php } ?>
                </select><br>
                Prix Unitaire :<br>
                <input type="number" id="prix_unitaire" readonly placeholder="0.00" style="width:100%; margin-bottom: 15px;"><br>
                Quantité :<br>
                <input type="number" name="qteV" id="qteV" oninput="calculSousTotal()" required placeholder="Ex: 5" style="width:100%; margin-bottom: 15px;"><br>
                Sous-Total :<br>
                <input type="number" id="sous_total" readonly placeholder="0.00" style="width:100%; margin-bottom: 20px;"><br>
                <button type="submit" style="width:100%;">Ajouter le Produit</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.style.display = 'flex';
            setTimeout(() => modal.style.opacity = '1', 10);
        }
        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.style.opacity = '0';
            setTimeout(() => modal.style.display = 'none', 300);
        }
        document.getElementById('btnOpenVente').addEventListener('click', () => openModal('modalVente'));
        document.getElementById('btnOpenVendre').addEventListener('click', () => openModal('modalVendre'));
    </script>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin: 0; text-align: left;">Recherche & Liste des Ventes</h2>
            <button onclick="window.print()" style="background: var(--theme-color); color: #000; margin: 0; padding: 10px 18px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">🖨️ Imprimer la liste</button>
        </div>

        <form method="GET" style="max-width: 100%; margin-bottom: 25px; display: flex; gap: 10px; align-items: center; background: none; border: none; box-shadow: none; padding: 0;">
            <input type="text" name="recherche" placeholder="Rechercher par N° Vente, Client, ou Produit" style="flex: 1; margin: 0; max-width: 100%;">
            <button type="submit" style="margin: 0; white-space: nowrap; padding: 12px 20px;">Rechercher</button>
            <?php if (isset($_GET["recherche"]) && $_GET["recherche"] != "") { ?>
                <a href="vente.php" class="btn" style="background: var(--theme-dark); color: white; margin: 0; white-space: nowrap; line-height: 24px; padding: 12px 20px;">Réinitialiser</a>
            <?php } ?>
        </form>

        <table>
            <tr>
                <th>N° Vente</th>
                <th>Date</th>
                <th>Client</th>
                <th>Montant Total</th>
                <th>Produits Vendus</th>
                <th>Action</th>
            </tr>

            <?php while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><strong><?php echo $row["n_vnt"]; ?></strong></td>
                    <td><?php echo $row["date_vnt"]; ?></td>
                    <td><?php echo $row["n_clt"] . " - " . htmlspecialchars($row["nom_clt"]); ?></td>
                    <td><?php echo number_format($row["mt_T_vnt"], 2); ?> €</td>
                    <td style="color: var(--text-primary); font-weight: 500;">
                        <?php echo $row["produits_vendus"] ? htmlspecialchars($row["produits_vendus"]) : '<em>Aucun produit</em>'; ?>
                    </td>
                    <td>
                        <div class="actions">
                            <a class="btn-modifier" href="modifier_vente.php?id=<?php echo $row['n_vnt']; ?>">Modifier</a>
                            <a class="btn-supprimer" href="delete_vente.php?id=<?php echo $row['n_vnt']; ?>" onclick="return confirm('Supprimer cette vente ?')">Supprimer</a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

<br>
<div class="center">
    <a href="accueil.php"><button type="button">Retour à l'accueil</button></a>
</div>

<script>
function calculSousTotal() {
    let produit = document.getElementById("NPrd");
    if (!produit || produit.selectedIndex === -1) return;
    let selectedOption = produit.options[produit.selectedIndex];
    let prix = parseFloat(selectedOption.getAttribute("data-prix")) || 0;
    document.getElementById("prix_unitaire").value = prix.toFixed(2);
    
    let qte = parseFloat(document.getElementById("qteV").value) || 0;
    document.getElementById("sous_total").value = (prix * qte).toFixed(2);
}
window.onload = function() {
    calculSousTotal();
};
</script>

<?php include 'fab.php'; ?>
</body>
</html>