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

$id = $conn->real_escape_string($_GET["id"]);

/* Récupérer la commande */
$commande = $conn->query("
    SELECT *
    FROM commande
    WHERE n_cmd='$id'
")->fetch_assoc();

/* Récupérer les détails existants de la commande */
$details_query = $conn->query("
    SELECT *
    FROM detaille_cmd
    WHERE n_cmd='$id'
");
$existing_details = [];
while ($d = $details_query->fetch_assoc()) {
    $existing_details[] = $d;
}

/* Modifier la commande complète (En-tête + Produits) */
if (isset($_POST["modifier"])) {
    $date = $_POST["date_cmd"];
    $N_frs = $_POST["N_frs"];
    $nprds = isset($_POST["NPrd"]) ? $_POST["NPrd"] : [];
    $qtes = isset($_POST["qteC"]) ? $_POST["qteC"] : [];

    // 1. Mettre à jour l'en-tête (Date, Fournisseur)
    $stmt = $conn->prepare("UPDATE commande SET date_cmd = ?, N_frs = ? WHERE n_cmd = ?");
    $stmt->bind_param("sii", $date, $N_frs, $id);
    $stmt->execute();
    $stmt->close();

    // 2. Supprimer les anciens détails pour les remplacer
    $conn->query("DELETE FROM detaille_cmd WHERE n_cmd = '$id'");

    $grand_total = 0;

    // 3. Insérer les nouveaux détails et calculer le total
    for ($i = 0; $i < count($nprds); $i++) {
        $NPrd = $nprds[$i];
        $qteC = $qtes[$i];

        if (!empty($NPrd) && $qteC > 0) {
            // Fetch PrixAchat
            $stmt = $conn->prepare("SELECT PrixAch FROM produit WHERE NPrd = ?");
            $stmt->bind_param("i", $NPrd);
            $stmt->execute();
            $res = $stmt->get_result();
            $prod = $res->fetch_assoc();
            $prix = $prod["PrixAch"];
            $stmt->close();

            $sous_total = $prix * $qteC;
            $grand_total += $sous_total;

            // Insérer le détail
            $stmt = $conn->prepare("INSERT INTO detaille_cmd (n_cmd, NPrd, qteC) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id, $NPrd, $qteC);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 4. Mettre à jour le montant total de la commande
    $conn->query("UPDATE commande SET mt_cmd = $grand_total WHERE n_cmd = '$id'");

    header("Location: commande.php");
    exit();
}

/* Listes pour les menus déroulants */
$fournisseurs = $conn->query("SELECT * FROM fournisseur");
$produits = $conn->query("SELECT NPrd, NomPrd, PrixAch FROM produit");
$produits_list = [];
while($p = $produits->fetch_assoc()) {
    $produits_list[] = $p;
}

// Générer les options HTML pour un select produit
function getProduitOptions($produits_list, $selected_nprd = "") {
    $html = '<option value="" data-prix="0">Sélectionner un produit</option>';
    foreach ($produits_list as $p) {
        $selected = ($p['NPrd'] == $selected_nprd) ? "selected" : "";
        $html .= '<option value="'.$p['NPrd'].'" data-prix="'.$p['PrixAch'].'" '.$selected.'>'.htmlspecialchars($p['NomPrd']).'</option>';
    }
    return $html;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Commande Multi-Produits</title>
<link rel="stylesheet" href="style.css">
<style>
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

window.onload = function() {
    calculTotal(); // Calcul initial au chargement de la page
};
</script>
</head>
<body>

<h2 style="text-align: center; margin-top: 20px;">Modifier la Commande N° <?php echo htmlspecialchars($id); ?></h2>

<div class="dashboard-layout" style="margin-top: 40px;">
    <div class="sidebar-forms" style="justify-content: center;">
        
        <form method="POST" style="max-width: 800px; width: 100%;">
            
            <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 20px;">
                <div style="flex: 1; text-align: left;">
                    <label>Date commande :</label><br>
                    <input type="date" name="date_cmd" value="<?php echo $commande['date_cmd']; ?>" required style="width: 100%;">
                </div>
                <div style="flex: 1; text-align: left;">
                    <label>Fournisseur :</label><br>
                    <select name="N_frs" required style="width: 100%;">
                        <?php while($f = $fournisseurs->fetch_assoc()) { ?>
                            <option value="<?php echo $f['N_frs']; ?>" <?php if($commande['N_frs'] == $f['N_frs']) echo "selected"; ?>>
                                <?php echo htmlspecialchars($f['nom_frs']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <hr style="border-color: var(--glass-border); opacity: 0.2; margin: 20px 0;">
            <h4 style="text-align: left; margin-bottom: 10px;">Produits commandés</h4>

            <div id="product-lines-container">
                <?php 
                // Si la commande n'a pas de produits (ne devrait pas arriver), afficher une ligne vide
                if (count($existing_details) == 0) {
                ?>
                    <div class="product-line">
                        <select name="NPrd[]" onchange="calculTotal()" required>
                            <?php echo getProduitOptions($produits_list, ""); ?>
                        </select>
                        <input type="number" name="qteC[]" min="1" oninput="calculTotal()" placeholder="Qté" required>
                        <input type="text" class="subtotal" readonly placeholder="Sous-total">
                        <button type="button" class="btn-supprimer-ligne" onclick="removeLine(this)">X</button>
                    </div>
                <?php 
                } else {
                    // Sinon on boucle sur les produits existants
                    foreach ($existing_details as $detail) { 
                ?>
                    <div class="product-line">
                        <select name="NPrd[]" onchange="calculTotal()" required>
                            <?php echo getProduitOptions($produits_list, $detail['NPrd']); ?>
                        </select>
                        <input type="number" name="qteC[]" min="1" value="<?php echo $detail['qteC']; ?>" oninput="calculTotal()" placeholder="Qté" required>
                        <input type="text" class="subtotal" readonly placeholder="Sous-total">
                        <button type="button" class="btn-supprimer-ligne" onclick="removeLine(this)">X</button>
                    </div>
                <?php 
                    } 
                }
                ?>
            </div>
            
            <div style="text-align: left; margin-top: 10px;">
                <button type="button" onclick="addProductLine()" style="background: rgba(255,255,255,0.1); color: var(--theme-color);">+ Ajouter un autre produit</button>
            </div>

            <hr style="border-color: var(--glass-border); opacity: 0.2; margin: 20px 0;">
            
            <div style="text-align: right; font-size: 18px; font-weight: bold;">
                <label>Grand Total (DH) :</label>
                <input type="number" id="grand_total" readonly value="<?php echo $commande['mt_cmd']; ?>" placeholder="0.00" style="width: 150px; display: inline-block; margin-left: 10px; font-size: 18px;">
            </div>
            <br>
            
            <button type="submit" name="modifier" style="width: 100%; padding: 15px; font-size: 16px;">
                Enregistrer les Modifications
            </button>

            <div style="margin-top: 15px; text-align: center;">
                <a href="commande.php" style="color: var(--text-secondary); text-decoration: none;">Annuler et retourner</a>
            </div>
        </form>

    </div>
</div>

<?php include 'fab.php'; ?>
</body>
</html>