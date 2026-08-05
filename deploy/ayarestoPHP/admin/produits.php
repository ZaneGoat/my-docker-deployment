<?php
// admin/produits.php
require '../db.php';
require '../includes/functions.php';
check_admin_login();

// Fetch categories for dropdown
$stmt_cats = $pdo->query("SELECT * FROM Categorie");
$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $libelle = sanitize($_POST['libelle']);
    $prix = floatval($_POST['prix']);
    $description = sanitize($_POST['description']);
    $id_categorie = intval($_POST['id_categorie']);
    $photo = ''; // Simplified for now

    if ($action === 'edit' && isset($_POST['id_produit'])) {
        $id_produit = intval($_POST['id_produit']);
        $stmt = $pdo->prepare("UPDATE Produit SET libelle=?, prix=?, description=?, id_categorie=? WHERE id_produit=?");
        $stmt->execute([$libelle, $prix, $description, $id_categorie, $id_produit]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO Produit (libelle, prix, description, photo, id_categorie) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$libelle, $prix, $description, $photo, $id_categorie]);
    }
    header("Location: produits.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM Produit WHERE id_produit = ?")->execute([$id]);
    // Reset Auto Increment to tightly pack future IDs
    $pdo->query("ALTER TABLE Produit AUTO_INCREMENT = 1");
    header("Location: produits.php");
    exit;
}

// Fetch single product for Edit
$edit_prod = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM Produit WHERE id_produit = ?");
    $stmt->execute([$id]);
    $edit_prod = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch products
$stmt = $pdo->query("SELECT p.*, c.nom as categorie_nom FROM Produit p LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie ORDER BY p.id_produit ASC");
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Produits</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header style="position: static; background: rgba(0,0,0,0.8); border-bottom: 1px solid var(--surface-border);">
        <a href="index.php" class="logo">AyaAdmin</a>
        <nav>
            <ul>
                <li><a href="ventes.php">Ventes (POS)</a></li>
                <li><a href="categories.php">Catégories</a></li>
                <li><a href="produits.php" style="color: var(--primary);">Produits</a></li>
                <li><a href="commandes.php">Commandes</a></li>
                <li><a href="logout.php" style="color: #e74c3c; font-weight: bold;">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: var(--primary); margin: 0;">Produits</h1>
            <button onclick="window.print()" class="btn no-print" style="padding: 0.5rem 1rem;">Imprimer la Liste</button>
        </div>
        
        <div class="glass-panel" style="margin: 0 0 2rem 0; padding: 2rem; text-align: left;">
            <h3><?= $edit_prod ? 'Modifier le produit' : 'Ajouter un produit' ?></h3>
            <form method="POST" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; max-width: 500px;">
                <input type="hidden" name="action" value="<?= $edit_prod ? 'edit' : 'add' ?>">
                <?php if ($edit_prod): ?>
                    <input type="hidden" name="id_produit" value="<?= $edit_prod['id_produit'] ?>">
                <?php endif; ?>
                
                <input type="text" name="libelle" placeholder="Libellé" required class="input-field" value="<?= $edit_prod ? htmlspecialchars($edit_prod['libelle']) : '' ?>">
                <input type="number" step="0.01" name="prix" placeholder="Prix" required class="input-field" value="<?= $edit_prod ? htmlspecialchars($edit_prod['prix']) : '' ?>">
                <select name="id_categorie" required class="input-field">
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id_categorie'] ?>" <?= ($edit_prod && $edit_prod['id_categorie'] == $cat['id_categorie']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="description" placeholder="Description" rows="3" class="input-field"><?= $edit_prod ? htmlspecialchars($edit_prod['description']) : '' ?></textarea>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn" style="width: max-content;"><?= $edit_prod ? 'Enregistrer' : 'Ajouter' ?></button>
                    <?php if ($edit_prod): ?>
                        <a href="produits.php" class="btn" style="background: transparent; border-color: #a0a0a0; color: #a0a0a0; width: max-content; text-decoration:none;">Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Libellé</th>
                    <th>Prix</th>
                    <th>Catégorie</th>
                    <th class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                foreach ($produits as $prod): 
                ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td><?= htmlspecialchars($prod['libelle']) ?></td>
                    <td><?= htmlspecialchars($prod['prix']) ?> DH</td>
                    <td><?= htmlspecialchars($prod['categorie_nom']) ?></td>
                    <td class="no-print">
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="?edit=<?= $prod['id_produit'] ?>" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: #3498db; border:none; color:white; text-decoration:none;">Éditer</a>
                            <a href="?delete=<?= $prod['id_produit'] ?>" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: #e74c3c; border:none; color:white; text-decoration:none;" onclick="return confirm('Supprimer ce produit ?');">Supprimer</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($produits)): ?>
                <tr><td colspan="5" style="text-align: center;">Aucun produit</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="index.php" class="fab no-print" title="Retour au Dashboard">🏠</a>
</body>
</html>
