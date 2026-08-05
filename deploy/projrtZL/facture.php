<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion : ".$conn->connect_error);
}

/*=========================
  AJOUTER FACTURE
=========================*/
if(isset($_POST["ajouter_facture"])){

    $date = $_POST["date_fct"];
    $tva = $_POST["tva"];
    $N_frs = $_POST["N_frs"];

    $sql = "INSERT INTO facture(date_fct,tva,total_HT,TTC,N_frs)
            VALUES('$date','$tva',0,0,'$N_frs')";

    $conn->query($sql);

    header("Location: facture.php");
    exit();
}

/*=========================
  AJOUTER DETAIL FACTURE
=========================*/
if(isset($_POST["ajouter_detail"])){

    $n_fct = $_POST["n_fct"];
    $NPrd  = $_POST["NPrd"];
    $qteF  = $_POST["qteF"];

    // Récupérer le prix du produit
    $res = $conn->query("SELECT PrixVnt FROM produit WHERE NPrd='$NPrd'");
    $prod = $res->fetch_assoc();
    $prix = $prod["PrixVnt"];

    // Ajouter détail
    $sql = "INSERT INTO detaille_factur(n_fct,NPrd,qteF,prix)
            VALUES('$n_fct','$NPrd','$qteF','$prix')";

    $conn->query($sql);

    // Calcul Total HT
    $resTotal = $conn->query("
        SELECT SUM(prix*qteF) AS total
        FROM detaille_factur
        WHERE n_fct='$n_fct'
    ");

    $data = $resTotal->fetch_assoc();
    $totalHT = $data["total"];

    // TVA
    $resTVA = $conn->query("
        SELECT tva
        FROM facture
        WHERE n_fct='$n_fct'
    ");

    $facture = $resTVA->fetch_assoc();
    $tva = $facture["tva"];

    // TTC
    $ttc = $totalHT + ($totalHT * $tva / 100);

    // Mise à jour facture
    $conn->query("
        UPDATE facture
        SET total_HT='$totalHT',
            TTC='$ttc'
        WHERE n_fct='$n_fct'
    ");

    header("Location: facture.php");
    exit();
}

// Liste fournisseurs
$fournisseurs = $conn->query("SELECT * FROM fournisseur");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des Factures</title>
<link rel="stylesheet" href="style.css">
<script>
function changerPrix(){
    let produit = document.getElementById("NPrd");
    if (!produit || produit.selectedIndex === -1) return;
    let selectedOption = produit.options[produit.selectedIndex];
    let prix = parseFloat(selectedOption.getAttribute("data-prix")) || 0;
    document.getElementById("prix").value = prix.toFixed(2);
    calculSousTotal();
}

function calculSousTotal(){
    let prix = parseFloat(document.getElementById("prix").value) || 0;
    let qte = parseFloat(document.getElementById("qteF").value) || 0;
    document.getElementById("sous_total").value = (prix * qte).toFixed(2);
}

window.onload=function(){
    changerPrix();
};
</script>
</head>
<body>

<h1>Gestion des Factures</h1>

<div class="dashboard-layout">
    <!-- SIDEBAR FORMS -->
    <div class="sidebar-forms">
        <form method="POST">
            <h2>Ajouter une Facture</h2>
            Date :<br>
            <input type="date" name="date_fct" required><br><br>
            TVA :<br>
            <select name="tva" required>
                <option value="20">20%</option>
                <option value="14">14%</option>
                <option value="7">7%</option>
            </select><br><br>
            Fournisseur :<br>
            <select name="N_frs" required>
                <option value="">Sélectionner un fournisseur</option>
                <?php while($f=$fournisseurs->fetch_assoc()){ ?>
                <option value="<?php echo $f['N_frs']; ?>">
                    <?php echo htmlspecialchars($f['nom_frs']); ?>
                </option>
                <?php } ?>
            </select><br><br>
            <button type="submit" name="ajouter_facture">Ajouter Facture</button>
        </form>

        <form method="POST">
            <h2>Ajouter Détail Facture</h2>
            Facture :<br>
            <select name="n_fct" required>
                <option value="">Sélectionner la facture</option>
                <?php
                $factures=$conn->query("SELECT n_fct FROM facture ORDER BY n_fct DESC");
                while($fct=$factures->fetch_assoc()){
                ?>
                <option value="<?php echo $fct['n_fct']; ?>">Facture N° <?php echo $fct['n_fct']; ?></option>
                <?php } ?>
            </select><br><br>
            Produit :<br>
            <select name="NPrd" id="NPrd" onchange="changerPrix()" required>
                <option value="" data-prix="0">Sélectionner un produit</option>
                <?php
                $produits=$conn->query("SELECT NPrd,NomPrd,PrixVnt FROM produit");
                while($p=$produits->fetch_assoc()){
                ?>
                <option value="<?php echo $p['NPrd']; ?>" data-prix="<?php echo $p['PrixVnt']; ?>">
                    <?php echo htmlspecialchars($p['NomPrd']); ?>
                </option>
                <?php } ?>
            </select><br><br>
            Prix Unitaire :<br>
            <input type="number" name="prix" id="prix" readonly placeholder="0.00"><br><br>
            Quantité :<br>
            <input type="number" name="qteF" id="qteF" value="1" min="1" oninput="calculSousTotal()" required placeholder="Ex: 1"><br><br>
            Sous Total :<br>
            <input type="number" id="sous_total" readonly placeholder="0.00"><br><br>
            <button type="submit" name="ajouter_detail">Ajouter Détail</button>
        </form>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin: 0; text-align: left;">Liste des Factures</h2>
            <button onclick="window.print()" style="background: var(--theme-color); color: #000; margin: 0; padding: 10px 18px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">🖨️ Imprimer la liste</button>
        </div>

        <table border="1" cellpadding="10">
            <tr>
                <th>N°</th>
                <th>Date</th>
                <th>TVA</th>
                <th>Total HT</th>
                <th>TTC</th>
                <th>Fournisseur</th>
                <th>Action</th>
            </tr>
            <?php
            $liste = $conn->query("
            SELECT facture.*, fournisseur.nom_frs
            FROM facture
            INNER JOIN fournisseur
            ON facture.N_frs = fournisseur.N_frs
            ORDER BY facture.n_fct DESC
            ");
            while($row = $liste->fetch_assoc()){
            ?>
            <tr>
                <td><strong><?php echo $row['n_fct']; ?></strong></td>
                <td><?php echo $row['date_fct']; ?></td>
                <td><?php echo $row['tva']; ?>%</td>
                <td><?php echo number_format($row['total_HT'],2); ?> DH</td>
                <td><?php echo number_format($row['TTC'],2); ?> DH</td>
                <td><?php echo htmlspecialchars($row['nom_frs']); ?></td>
                <td>
                    <div class="actions">
                        <a class="btn-modifier" href="modifier_facture.php?id=<?php echo $row['n_fct']; ?>">Modifier</a>
                        <a class="btn-supprimer" href="delete_facture.php?id=<?php echo $row['n_fct']; ?>" onclick="return confirm('Voulez-vous supprimer cette facture ?')">Supprimer</a>
                        <a class="btn-imprimer" href="imprimer_facture.php?id=<?php echo $row['n_fct']; ?>" target="_blank">Imprimer</a>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>

        <br><hr style="border-color: var(--glass-border); opacity: 0.2; margin: 40px 0;"><br>

        <h2 style="margin: 0 0 20px 0; text-align: left;">Liste des Détails des Factures</h2>
        <table border="1" cellpadding="10">
            <tr>
                <th>Facture</th>
                <th>Produit</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Sous Total</th>
                <th>Action</th>
            </tr>
            <?php
            $details = $conn->query("
            SELECT d.*, p.NomPrd
            FROM detaille_factur d
            INNER JOIN produit p
            ON d.NPrd = p.NPrd
            ORDER BY d.n_fct DESC
            ");
            while($d = $details->fetch_assoc()){
            ?>
            <tr>
                <td><?php echo $d['n_fct']; ?></td>
                <td><?php echo htmlspecialchars($d['NomPrd']); ?></td>
                <td><?php echo number_format($d['prix'],2); ?> DH</td>
                <td><?php echo $d['qteF']; ?></td>
                <td><?php echo number_format($d['prix'] * $d['qteF'],2); ?> DH</td>
                <td>
                    <div class="actions">
                        <a class="btn-modifier" href="modifier_detaille_facture.php?n_fct=<?php echo $d['n_fct']; ?>&NPrd=<?php echo $d['NPrd']; ?>">Modifier</a>
                        <a class="btn-supprimer" href="delete_detaille_facture.php?n_fct=<?php echo $d['n_fct']; ?>&NPrd=<?php echo $d['NPrd']; ?>" onclick="return confirm('Supprimer ce détail ?')">Supprimer</a>
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