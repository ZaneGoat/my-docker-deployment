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

/*=========================
  AJOUT COMMANDE COMPLETE
=========================*/
if (isset($_POST["ajouter_commande_complete"])) {
    $date_cmd = $_POST["date_cmd"];
    $N_frs    = $_POST["N_frs"];
    $nprds    = $_POST["NPrd"]; // Tableau des produits
    $qtes     = $_POST["qteC"]; // Tableau des quantités

    // 1. Créer la commande
    $stmt = $conn->prepare("INSERT INTO commande (date_cmd, mt_cmd, N_frs) VALUES (?, 0, ?)");
    $stmt->bind_param("si", $date_cmd, $N_frs);
    $stmt->execute();
    $n_cmd = $stmt->insert_id;
    $stmt->close();

    $grand_total = 0;

    // 2. Ajouter les détails et calculer le total
    for ($i = 0; $i < count($nprds); $i++) {
        $NPrd = $nprds[$i];
        $qteC = $qtes[$i];

        if (!empty($NPrd) && $qteC > 0) {
            $stmt = $conn->prepare("SELECT PrixAch FROM produit WHERE NPrd = ?");
            $stmt->bind_param("i", $NPrd);
            $stmt->execute();
            $res = $stmt->get_result();
            $prod = $res->fetch_assoc();
            $prix = $prod["PrixAch"];
            $stmt->close();

            $sous_total = $prix * $qteC;
            $grand_total += $sous_total;

            $stmt = $conn->prepare("INSERT INTO detaille_cmd (n_cmd, NPrd, qteC) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $n_cmd, $NPrd, $qteC);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 3. Update montant total
    $conn->query("UPDATE commande SET mt_cmd = $grand_total WHERE n_cmd = $n_cmd");

    header("Location: imprimer_commande.php?id=" . $n_cmd);
    exit();
}

/*=========================
  RECHERCHE
=========================*/
$search_cmd = isset($_GET["recherche"]) ? $conn->real_escape_string($_GET["recherche"]) : "";
if ($search_cmd != "") {
    $liste = $conn->query("
    SELECT c.*, f.nom_frs
    FROM commande c
    INNER JOIN fournisseur f ON c.N_frs = f.N_frs
    WHERE c.n_cmd LIKE '%$search_cmd%'
       OR c.date_cmd LIKE '%$search_cmd%'
       OR f.nom_frs LIKE '%$search_cmd%'
    ORDER BY c.n_cmd DESC
    ");
} else {
    $liste = $conn->query("
    SELECT c.*, f.nom_frs
    FROM commande c
    INNER JOIN fournisseur f ON c.N_frs = f.N_frs
    ORDER BY c.n_cmd DESC
    ");
}

$search_detail = isset($_GET["recherche_detail"]) ? $conn->real_escape_string($_GET["recherche_detail"]) : "";
if ($search_detail != "") {
    $details = $conn->query("
    SELECT d.*, p.NomPrd
    FROM detaille_cmd d
    INNER JOIN produit p ON d.NPrd = p.NPrd
    WHERE d.n_cmd LIKE '%$search_detail%'
       OR p.NomPrd LIKE '%$search_detail%'
       OR d.qteC LIKE '%$search_detail%'
    ORDER BY d.n_cmd DESC
    ");
} else {
    $details = $conn->query("
    SELECT d.*, p.NomPrd
    FROM detaille_cmd d
    INNER JOIN produit p ON d.NPrd = p.NPrd
    ORDER BY d.n_cmd DESC
    ");
}

// Fetch lists for dropdowns
$fournisseurs = $conn->query("SELECT * FROM fournisseur");
$produits = $conn->query("SELECT NPrd, NomPrd, PrixAch FROM produit");
$produits_options = '<option value="" data-prix="0">Sélectionner un produit</option>';
while($p = $produits->fetch_assoc()) {
    $produits_options .= '<option value="'.$p['NPrd'].'" data-prix="'.$p['PrixAch'].'">'.htmlspecialchars($p['NomPrd']).'</option>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des Commandes</title>
<link rel="stylesheet" href="style.css">
<style>
    /* Styling for the dynamic product lines to fit the glassmorphic theme */
    .product-line {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 15px;
        background: rgba(0,0,0,0.2);
        padding: 10px;
        border-radius: 10px;
        border: 1px solid var(--glass-border);
    }
    .product-line select {
        flex: 2;
        margin: 0;
    }
    .product-line input[type="number"], .product-line input[type="text"] {
        flex: 1;
        margin: 0;
    }
    .btn-supprimer-ligne {
        background: var(--danger);
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-supprimer-ligne:hover {
        background: var(--danger-hover);
    }
</style>
<script>
function calculTotal() {
    let grandTotal = 0;
    let lines = document.querySelectorAll('.product-line');
    
    lines.forEach(line => {
        let select = line.querySelector('select[name="NPrd[]"]');
        let qteInput = line.querySelector('input[name="qteC[]"]');
        let subTotalInput = line.querySelector('.subtotal');
        
        let prix = 0;
        if(select && select.selectedIndex !== -1) {
            let option = select.options[select.selectedIndex];
            prix = parseFloat(option.getAttribute('data-prix')) || 0;
        }
        
        let qte = parseFloat(qteInput.value) || 0;
        let subtotal = prix * qte;
        
        if(subTotalInput) {
            subTotalInput.value = subtotal.toFixed(2) + " DH";
        }
        
        grandTotal += subtotal;
    });
    
    document.getElementById('grand_total').value = grandTotal.toFixed(2);
}

function removeLine(btn) {
    let line = btn.closest('.product-line');
    if(document.querySelectorAll('.product-line').length > 1) {
        line.remove();
        calculTotal();
    } else {
        alert("Vous devez avoir au moins un produit dans la commande.");
    }
}

function addProductLine() {
    let container = document.getElementById('product-lines-container');
    let firstLine = container.querySelector('.product-line');
    
    let newLine = firstLine.cloneNode(true);
    // Reset inputs
    newLine.querySelector('select').selectedIndex = 0;
    newLine.querySelector('input[name="qteC[]"]').value = "";
    newLine.querySelector('.subtotal').value = "";
    
    container.appendChild(newLine);
    calculTotal();
}
</script>
</head>
<body>

<h2>Gestion des Commandes</h2>

<div class="dashboard-layout">
    <div class="sidebar-forms" style="justify-content: center;">
        <!-- Super Form: Nouvelle Commande avec Multi-Produits -->
        <form method="POST" style="max-width: 800px; width: 100%;">
            <h3>Nouvelle Commande (Multi-Produits)</h3>
            
            <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 20px;">
                <div style="flex: 1; text-align: left;">
                    <label>Date commande :</label><br>
                    <input type="date" name="date_cmd" style="width:100%;" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div style="flex: 1; text-align: left;">
                    <label>Fournisseur :</label><br>
                    <select name="N_frs" style="width:100%;" required>
                        <option value="">Sélectionner un fournisseur</option>
                        <?php while($f = $fournisseurs->fetch_assoc()) { ?>
                            <option value="<?php echo $f['N_frs']; ?>">
                                <?php echo htmlspecialchars($f['nom_frs']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <hr style="border-color: var(--glass-border); opacity: 0.2; margin: 20px 0;">
            <h4 style="text-align: left; margin-bottom: 10px;">Produits à commander</h4>

            <div id="product-lines-container">
                <div class="product-line">
                    <select name="NPrd[]" onchange="calculTotal()" required>
                        <?php echo $produits_options; ?>
                    </select>
                    <input type="number" name="qteC[]" min="1" oninput="calculTotal()" placeholder="Qté" required>
                    <input type="text" class="subtotal" readonly placeholder="Sous-total">
                    <button type="button" class="btn-supprimer-ligne" onclick="removeLine(this)">X</button>
                </div>
            </div>
            
            <div style="text-align: left; margin-top: 10px;">
                <button type="button" onclick="addProductLine()" style="background: rgba(255,255,255,0.1); color: var(--theme-color);">+ Ajouter un autre produit</button>
            </div>

            <hr style="border-color: var(--glass-border); opacity: 0.2; margin: 20px 0;">
            
            <div style="text-align: right; font-size: 18px; font-weight: bold;">
                <label>Grand Total (DH) :</label>
                <input type="number" id="grand_total" readonly placeholder="0.00" style="width: 150px; display: inline-block; margin-left: 10px; font-size: 18px;">
            </div>
            <br>
            <button type="submit" name="ajouter_commande_complete" style="width: 100%; padding: 15px; font-size: 16px;">Finaliser la Commande</button>
        </form>
    </div>

    <div class="main-content">
        <!-- Section 1: Liste des Commandes -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin: 0; text-align: left;">Liste des Commandes</h2>
            <button onclick="window.print()" style="background: var(--theme-color); color: #000; margin: 0; padding: 10px 18px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">🖨️ Imprimer la liste</button>
        </div>

        <form method="GET" style="max-width: 100%; margin-bottom: 25px; display: flex; gap: 10px; align-items: center; background: none; border: none; box-shadow: none; padding: 0;">
            <input type="text" name="recherche" placeholder="Rechercher une commande par N° ou fournisseur..." style="flex: 1; margin: 0; max-width: 100%;">
            <button type="submit" style="margin: 0; white-space: nowrap; padding: 12px 20px;">Rechercher</button>
            <?php if($search_cmd != ""){ ?>
                <a href="commande.php" class="btn" style="background: var(--theme-dark); color: white; margin: 0; white-space: nowrap; line-height: 24px; padding: 12px 20px;">Réinitialiser</a>
            <?php } ?>
        </form>

        <table border="1" cellpadding="10">
            <tr>
                <th>N° Commande</th>
                <th>Date</th>
                <th>Montant Total</th>
                <th>Fournisseur</th>
                <th>Action</th>
            </tr>
            <?php while($row = $liste->fetch_assoc()){ ?>
                <tr>
                    <td><strong><?php echo $row["n_cmd"]; ?></strong></td>
                    <td><?php echo $row["date_cmd"]; ?></td>
                    <td><?php echo number_format($row["mt_cmd"], 2); ?> DH</td>
                    <td><?php echo htmlspecialchars($row["nom_frs"]); ?></td>
                    <td>
                        <div class="actions">
                            <a class="btn-modifier" href="modifier_commande.php?id=<?php echo $row['n_cmd']; ?>">Modifier</a>
                            <a class="btn-supprimer" href="delete_commande.php?id=<?php echo $row['n_cmd']; ?>" onclick="return confirm('Supprimer cette commande ?')">Supprimer</a>
                            <a class="btn-imprimer" href="imprimer_commande.php?id=<?php echo $row['n_cmd']; ?>" target="_blank">Imprimer</a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <br><hr style="border-color: var(--glass-border); opacity: 0.2; margin: 40px 0;"><br>

        <!-- Section 2: Liste des Détails des Commandes -->
        <h2 style="margin: 0 0 20px 0; text-align: left;">Détails des Commandes</h2>

        <form method="GET" style="max-width: 100%; margin-bottom: 25px; display: flex; gap: 10px; align-items: center; background: none; border: none; box-shadow: none; padding: 0;">
            <input type="text" name="recherche_detail" placeholder="Rechercher dans les détails (produit, qté)..." style="flex: 1; margin: 0; max-width: 100%;">
            <button type="submit" style="margin: 0; white-space: nowrap; padding: 12px 20px;">Rechercher</button>
            <?php if($search_detail != ""){ ?>
                <a href="commande.php" class="btn" style="background: var(--theme-dark); color: white; margin: 0; white-space: nowrap; line-height: 24px; padding: 12px 20px;">Réinitialiser</a>
            <?php } ?>
        </form>

        <table border="1" cellpadding="10">
            <tr>
                <th>N° Commande</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Action</th>
            </tr>
            <?php while($d = $details->fetch_assoc()){ ?>
                <tr>
                    <td><?php echo $d["n_cmd"]; ?></td>
                    <td><?php echo htmlspecialchars($d["NomPrd"]); ?></td>
                    <td><?php echo $d["qteC"]; ?></td>
                    <td>
                        <div class="actions">
                            <a class="btn-modifier" href="modifier_detaille_cmd.php?n_cmd=<?php echo $d['n_cmd']; ?>&NPrd=<?php echo $d['NPrd']; ?>">Modifier</a>
                            <a class="btn-supprimer" href="delete_detaille_cmd.php?n_cmd=<?php echo $d['n_cmd']; ?>&NPrd=<?php echo $d['NPrd']; ?>" onclick="return confirm('Supprimer ce détail ?')">Supprimer</a>
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

<?php include 'fab.php'; ?>
</body>
</html>