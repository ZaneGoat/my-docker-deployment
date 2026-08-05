<?php
session_start();

if(!isset($_SESSION["username"])){
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

/* =======================
   AJOUT LIVRAISON
======================= */

if(isset($_POST["ajouter_livraison"])){

    $date = $_POST["date_livrs"];
    $nom = $_POST["nom_frs"];
    $id_frs = $_POST["N_frs"];

    $conn->query("INSERT INTO livraison(date_livrs,nom_frs,N_frs)
    VALUES('$date','$nom','$id_frs')");

    header("Location: livraison.php");
    exit();
}

/* =======================
   AJOUT DETAIL LIVRAISON
======================= */

if(isset($_POST["ajouter_detail"])){

    $n_livr = $_POST["n_livr"];
    $NPrd   = $_POST["NPrd"];
    $qte    = $_POST["qte_L"];

    $conn->query("INSERT INTO ditaille_L(n_livr,NPrd,qte_L)
    VALUES('$n_livr','$NPrd','$qte')");

    header("Location: livraison.php");
    exit();
}

/* =======================
   RECHERCHE LIVRAISON
======================= */

if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){

    $r = $_GET["recherche"];

    $resultLivraison = $conn->query("SELECT * FROM livraison
    WHERE nom_frs LIKE '%$r%'
    OR n_livr LIKE '%$r%'");

}else{

    $resultLivraison = $conn->query("SELECT * FROM livraison");

}

/* =======================
   DETAIL LIVRAISON
======================= */

$resultDetail = $conn->query("
SELECT
ditaille_L.n_livr,
ditaille_L.NPrd,
produit.NomPrd,
ditaille_L.qte_L
FROM ditaille_L
INNER JOIN produit
ON ditaille_L.NPrd = produit.NPrd
");
/* =======================
   LISTES
======================= */

$fournisseurs = $conn->query("SELECT * FROM fournisseur");


$livraisons = $conn->query("SELECT n_livr FROM livraison");

$produits = $conn->query("SELECT NPrd,NomPrd FROM produit");

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Livraison</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

    <!-- TOP BUTTONS -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
        <button id="btnOpenLivraison" class="btn" style="background: linear-gradient(to right, #38bdf8, #818cf8); font-size: 1.1rem; padding: 15px 30px;">
            ➕ Ajouter Livraison
        </button>
        <button id="btnOpenDetail" class="btn" style="background: linear-gradient(to right, #34d399, #059669); font-size: 1.1rem; padding: 15px 30px;">
            ➕ Ajouter un Détail
        </button>
    </div>

    <!-- MODAL LIVRAISON -->
    <div id="modalLivraison" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div class="modal" style="background: linear-gradient(145deg, #1e293b, #0f172a); border: 1px solid rgba(255,255,255,0.1); padding: 2.5rem; border-radius: 20px; width: 100%; max-width: 400px; text-align: center; position: relative;">
            <button onclick="closeModal('modalLivraison')" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; color: white; font-size: 1.5rem; cursor: pointer; box-shadow: none;">×</button>
            <h2 style="margin-top: 0;">Ajouter Livraison</h2>
            <form method="POST" style="margin: 0; width: 100%; background: transparent; border: none; box-shadow: none; padding: 0;">
                <input type="hidden" name="ajouter_livraison">
                Date livraison<br>
                <input type="date" name="date_livrs" required style="width:100%; margin-bottom: 15px;"><br>
                Nom fournisseur<br>
                <select id="nom_frs" name="nom_frs" onchange="changerNumero()" style="width:100%; margin-bottom: 15px;">
                    <?php while($f=$fournisseurs->fetch_assoc()){ ?>
                    <option value="<?php echo htmlspecialchars($f['nom_frs']); ?>" data-id="<?php echo $f['N_frs']; ?>">
                        <?php echo htmlspecialchars($f['nom_frs']); ?>
                    </option>
                    <?php } ?>
                </select><br>
                N° Fournisseur<br>
                <input type="text" id="N_frs" name="N_frs" readonly style="width:100%; margin-bottom: 20px;"><br>
                <button type="submit" style="width:100%;">Enregistrer</button>
            </form>
        </div>
    </div>

    <!-- MODAL DETAIL -->
    <div id="modalDetail" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div class="modal" style="background: linear-gradient(145deg, #1e293b, #0f172a); border: 1px solid rgba(255,255,255,0.1); padding: 2.5rem; border-radius: 20px; width: 100%; max-width: 400px; text-align: center; position: relative;">
            <button onclick="closeModal('modalDetail')" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; color: white; font-size: 1.5rem; cursor: pointer; box-shadow: none;">×</button>
            <h2 style="margin-top: 0;">Ajouter Détail</h2>
            <form method="POST" style="margin: 0; width: 100%; background: transparent; border: none; box-shadow: none; padding: 0;">
                <input type="hidden" name="ajouter_detail">
                Livraison<br>
                <select name="n_livr" style="width:100%; margin-bottom: 15px;">
                    <?php while($l=$livraisons->fetch_assoc()){ ?>
                    <option value="<?php echo $l['n_livr']; ?>">
                        N° <?php echo $l['n_livr']; ?>
                    </option>
                    <?php } ?>
                </select><br>
                Produit<br>
                <select name="NPrd" style="width:100%; margin-bottom: 15px;">
                    <?php while($p=$produits->fetch_assoc()){ ?>
                    <option value="<?php echo $p['NPrd']; ?>">
                        <?php echo $p['NPrd']." - ".htmlspecialchars($p['NomPrd']); ?>
                    </option>
                    <?php } ?>
                </select><br>
                Quantité<br>
                <input type="number" name="qte_L" required style="width:100%; margin-bottom: 20px;"><br>
                <button type="submit" style="width:100%;">Enregistrer</button>
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
        document.getElementById('btnOpenLivraison').addEventListener('click', () => openModal('modalLivraison'));
        document.getElementById('btnOpenDetail').addEventListener('click', () => openModal('modalDetail'));
    </script>

    <!-- MAIN CONTENT (Tables) -->
    <div class="main-content">
        <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <a href="accueil.php">
                <button type="button" style="background: rgba(255, 255, 255, 0.1);">Retour à l'accueil</button>
            </a>
            <form method="GET" style="margin: 0; display: flex; gap: 10px;">
                <input type="text" name="recherche" placeholder="Recherche" style="margin-bottom: 0;">
                <button type="submit">Rechercher</button>
                <?php if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){ ?>
                <a href="livraison.php">
                <button type="button">Retour</button>
                </a>
                <?php } ?>
            </form>
            <div>
                <button type="button" class="btn btn-print" onclick="window.print()">Imprimer la liste</button>
            </div>
        </div>

        <h2>Liste des livraisons</h2>
        <table border="1" cellpadding="10">
            <tr>
                <th>N°</th>
                <th>Date</th>
                <th>Nom fournisseur</th>
                <th>N° fournisseur</th>
                <th>Action</th>
            </tr>
            <?php while($row=$resultLivraison->fetch_assoc()){ ?>
            <tr>
                <td><?php echo $row["n_livr"]; ?></td>
                <td><?php echo $row["date_livrs"]; ?></td>
                <td><?php echo $row["nom_frs"]; ?></td>
                <td><?php echo $row["N_frs"]; ?></td>
                <td>
                <a class="btn" href="modifier_livraison.php?id=<?php echo $row['n_livr']; ?>">Modifier</a>
                <a class="btn delete" href="delete_livraison.php?id=<?php echo $row['n_livr']; ?>" onclick="return confirm('Supprimer cette livraison ?')">Supprimer</a>
                </td>
            </tr>
            <?php } ?>
        </table>

        <br><br>

        <h2>Liste des détails de livraison</h2>
        <table border="1" cellpadding="10">
            <tr>
                <th>N Livraison</th>
                <th>Nom Produit</th>
                <th>Quantité</th>
                <th>Action</th>
            </tr>
            <?php while($row=$resultDetail->fetch_assoc()){ ?>
            <tr>
                <td><?php echo $row["n_livr"]; ?></td>
                <td><?php echo $row["NomPrd"]; ?></td>
                <td><?php echo $row["qte_L"]; ?></td>
                <td>
                <a class="btn" href="modifier_ditaille_L.php?n_livr=<?php echo $row['n_livr']; ?>&NPrd=<?php echo $row['NPrd']; ?>">Modifier</a>
                <a class="btn delete" href="delete_ditaille_L.php?n_livr=<?php echo $row['n_livr']; ?>&NPrd=<?php echo $row['NPrd']; ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

</div>

<script>
function changerNumero(){
    var select=document.getElementById("nom_frs");
    var numero=select.options[select.selectedIndex].getAttribute("data-id");
    document.getElementById("N_frs").value=numero;
}
window.onload=changerNumero;
</script>

<?php include 'fab.php'; ?>
</body>
</html>