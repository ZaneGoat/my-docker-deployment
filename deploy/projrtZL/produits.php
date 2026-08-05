<?php
session_start();

if(!isset($_SESSION["username"])){
    header("Location: login.php");
    exit();
}

$conn=new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $catg=$_POST["catg"];
    $nom=$_POST["NomPrd"];
    $qte=$_POST["QteStck"];
    $achat=$_POST["PrixAch"];
    $vente=$_POST["PrixVnt"];

    $conn->query("INSERT INTO produit(catg,NomPrd,QteStck,PrixAch,PrixVnt)
    VALUES('$catg','$nom','$qte','$achat','$vente')");

    header("Location: produits.php");
    exit();
}

if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){
    $r=$_GET["recherche"];
    $result=$conn->query("SELECT * FROM produit
    WHERE NomPrd LIKE '%$r%'
    OR catg LIKE '%$r%'");
}else{
    $result=$conn->query("SELECT * FROM produit");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Produits</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-layout">
    <div class="sidebar-forms">
        <form method="POST">
            <h3>Ajouter un produit</h3>
            
            Catégorie<br>
            <input type="text" name="catg" required><br><br>

            Nom Produit<br>
            <input type="text" name="NomPrd" required><br><br>

            Quantité<br>
            <input type="number" name="QteStck" required><br><br>

            Prix Achat<br>
            <input type="number" step="0.01" name="PrixAch" required><br><br>

            Prix Vente<br>
            <input type="number" step="0.01" name="PrixVnt" required><br><br>

            <button type="submit">Ajouter</button>
        </form>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin: 0; text-align: left;">Recherche & Liste des produits</h2>
            <button onclick="window.print()" style="background: var(--theme-color); color: #000; margin: 0; padding: 10px 18px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">🖨️ Imprimer la liste</button>
        </div>

        <form method="GET" style="max-width: 100%; margin-bottom: 25px; display: flex; gap: 10px; align-items: center; background: none; border: none; box-shadow: none; padding: 0;">
            <input type="text" name="recherche" placeholder="Rechercher un produit ou une catégorie..." style="flex: 1; margin: 0; max-width: 100%;">
            <button type="submit" style="margin: 0; white-space: nowrap; padding: 12px 20px;">Rechercher</button>
            <?php if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){ ?>
                <a href="produits.php" class="btn" style="background: var(--theme-dark); color: white; margin: 0; white-space: nowrap; line-height: 24px; padding: 12px 20px;">Réinitialiser</a>
            <?php } ?>
        </form>

        <table border="1" cellpadding="10">
        <tr>
            <th>N°</th>
            <th>Catégorie</th>
            <th>Nom</th>
            <th>Quantité</th>
            <th>Prix Achat</th>
            <th>Prix Vente</th>
            <th>Action</th>
        </tr>

        <?php while($row=$result->fetch_assoc()){ ?>
            <tr>
                <td><?php echo $row["NPrd"]; ?></td>
                <td><?php echo htmlspecialchars($row["catg"]); ?></td>
                <td><?php echo htmlspecialchars($row["NomPrd"]); ?></td>
                <td><?php echo $row["QteStck"]; ?></td>
                <td><?php echo number_format($row["PrixAch"], 2); ?> €</td>
                <td><?php echo number_format($row["PrixVnt"], 2); ?> €</td>
                <td>
                    <div class="actions">
                        <a class="btn-modifier" href="modifier_produit.php?id=<?php echo $row["NPrd"]; ?>">Modifier</a>
                        <a class="btn-supprimer" href="delete_produit.php?id=<?php echo $row["NPrd"]; ?>" onclick="return confirm('Supprimer ce produit ?')">Supprimer</a>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </table>
    </div>
</div>

<br>
<div class="center">
    <a href="accueil.php">
        <button type="button">Retour à l'accueil</button>
    </a>
</div>

<?php include 'fab.php'; ?>
</body>
</html>