<?php
// admin/commandes.php
require '../db.php';
require '../includes/functions.php';
check_admin_login();

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande'], $_POST['statut'])) {
    $id_commande = intval($_POST['id_commande']);
    $statut = sanitize($_POST['statut']);
    
    $stmt = $pdo->prepare("UPDATE Commande SET statut = ? WHERE id_commande = ?");
    $stmt->execute([$statut, $id_commande]);
    header("Location: commandes.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM Commande WHERE id_commande = ?")->execute([$id]);
    header("Location: commandes.php");
    exit;
}

// Fetch orders with client info
$stmt = $pdo->query("SELECT c.*, cl.nom, cl.prenom, cl.tel FROM Commande c JOIN Client cl ON c.id_client = cl.id_client ORDER BY c.id_commande DESC");
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Commandes</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header style="position: static; background: rgba(0,0,0,0.8); border-bottom: 1px solid var(--surface-border);">
        <a href="index.php" class="logo">AyaAdmin</a>
        <nav>
            <ul>
                <li><a href="ventes.php">Ventes (POS)</a></li>
                <li><a href="categories.php">Catégories</a></li>
                <li><a href="produits.php">Produits</a></li>
                <li><a href="commandes.php" style="color: var(--primary);">Commandes</a></li>
                <li><a href="logout.php" style="color: #e74c3c; font-weight: bold;">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: var(--primary); margin: 0;">Commandes</h1>
            <button onclick="window.print()" class="btn no-print" style="padding: 0.5rem 1rem;">Imprimer la Liste</button>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Ticket</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $cmd): ?>
                <tr>
                    <td><?= $cmd['id_commande'] ?></td>
                    <td><?= $cmd['date'] ?></td>
                    <td><?= htmlspecialchars($cmd['nom'] . ' ' . $cmd['prenom']) ?> (<?= htmlspecialchars($cmd['tel']) ?>)</td>
                    <td><?= htmlspecialchars($cmd['No_ticket']) ?></td>
                    <td><?= htmlspecialchars($cmd['statut']) ?></td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <form method="POST" style="display:inline; margin:0;">
                                <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                                <select name="statut" class="input-field" style="padding: 0.3rem; width: auto;" onchange="this.form.submit()">
                                    <option value="En attente" <?= $cmd['statut'] == 'En attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="Validée" <?= $cmd['statut'] == 'Validée' ? 'selected' : '' ?>>Validée</option>
                                    <option value="Livrée" <?= $cmd['statut'] == 'Livrée' ? 'selected' : '' ?>>Livrée</option>
                                    <option value="Annulée" <?= $cmd['statut'] == 'Annulée' ? 'selected' : '' ?>>Annulée</option>
                                </select>
                            </form>
                            <a href="receipt.php?id=<?= $cmd['id_commande'] ?>" target="_blank" class="btn no-print" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; text-decoration: none;">Ticket</a>
                            <a href="?delete=<?= $cmd['id_commande'] ?>" class="btn no-print" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: #e74c3c; border:none; color:white; text-decoration: none;" onclick="return confirm('Supprimer cette commande ?');">Supprimer</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($commandes)): ?>
                <tr><td colspan="6" style="text-align: center;">Aucune commande</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="index.php" class="fab no-print" title="Retour au Dashboard">🏠</a>
</body>
</html>
