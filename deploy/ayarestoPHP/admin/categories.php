<?php
// admin/categories.php
require '../db.php';
require '../includes/functions.php';
check_admin_login();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom']);
    $description = sanitize($_POST['description']);
    $photo = ''; // Simplified for now, would handle upload here

    $stmt = $pdo->prepare("INSERT INTO Categorie (nom, description, photo) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $description, $photo]);
    header("Location: categories.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM Categorie WHERE id_categorie = ?")->execute([$id]);
    header("Location: categories.php");
    exit;
}

// Fetch categories
$stmt = $pdo->query("SELECT * FROM Categorie ORDER BY id_categorie DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Catégories</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header style="position: static; background: rgba(0,0,0,0.8); border-bottom: 1px solid var(--surface-border);">
        <a href="index.php" class="logo">AyaAdmin</a>
        <nav>
            <ul>
                <li><a href="ventes.php">Ventes (POS)</a></li>
                <li><a href="categories.php" style="color: var(--primary);">Catégories</a></li>
                <li><a href="produits.php">Produits</a></li>
                <li><a href="commandes.php">Commandes</a></li>
                <li><a href="logout.php" style="color: #e74c3c; font-weight: bold;">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: var(--primary); margin: 0;">Catégories</h1>
            <button onclick="window.print()" class="btn no-print" style="padding: 0.5rem 1rem;">Imprimer la Liste</button>
        </div>
        
        <div class="glass-panel" style="margin: 0 0 2rem 0; padding: 2rem; text-align: left;">
            <h3>Ajouter une catégorie</h3>
            <form method="POST" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; max-width: 500px;">
                <input type="text" name="nom" placeholder="Nom de la catégorie" required class="input-field">
                <textarea name="description" placeholder="Description" rows="3" class="input-field"></textarea>
                <button type="submit" class="btn" style="width: max-content;">Ajouter</button>
            </form>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= $cat['id_categorie'] ?></td>
                    <td><?= htmlspecialchars($cat['nom']) ?></td>
                    <td><?= htmlspecialchars($cat['description']) ?></td>
                    <td class="no-print">
                        <a href="?delete=<?= $cat['id_categorie'] ?>" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: #e74c3c; border:none; color:white; text-decoration:none;" onclick="return confirm('Supprimer cette catégorie ?');">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($categories)): ?>
                <tr><td colspan="3" style="text-align: center;">Aucune catégorie</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="index.php" class="fab no-print" title="Retour au Dashboard">🏠</a>
</body>
</html>
