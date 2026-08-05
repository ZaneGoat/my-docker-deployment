<?php
// admin/index.php
require '../db.php';
require '../includes/functions.php';
check_admin_login();

// 1. Fetch Total Orders
$total_orders = $pdo->query("SELECT COUNT(*) FROM Commande")->fetchColumn();

// 2. Fetch Total Products
$total_prods = $pdo->query("SELECT COUNT(*) FROM Produit")->fetchColumn();

// 3. Fetch Total Categories
$total_cats = $pdo->query("SELECT COUNT(*) FROM Categorie")->fetchColumn();

// 4. Fetch Total Revenue (Chiffre d'Affaires)
$revenue_query = "SELECT SUM(d.quantite * p.prix) 
                  FROM Detail_Commande d 
                  JOIN Produit p ON d.id_produit = p.id_produit 
                  JOIN Commande c ON d.id_commande = c.id_commande 
                  WHERE c.statut != 'Annulée'";
$total_revenue = $pdo->query($revenue_query)->fetchColumn() ?: 0;

// 5. Fetch 5 Recent Orders
$recent_orders_stmt = $pdo->query("
    SELECT c.*, cl.nom, cl.prenom 
    FROM Commande c 
    JOIN Client cl ON c.id_client = cl.id_client 
    ORDER BY c.id_commande DESC 
    LIMIT 5
");
$recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - AyaResto</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--surface);
            backdrop-filter: blur(12px);
            border: 1px solid var(--surface-border);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
        }
        .stat-card h3 {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 400;
        }
        .stat-card .value {
            font-size: 2.5rem;
            color: var(--primary);
            font-weight: bold;
            font-family: var(--font-heading);
        }
    </style>
</head>
<body>
    <header style="position: static; background: rgba(0,0,0,0.8); border-bottom: 1px solid var(--surface-border);">
        <a href="index.php" class="logo">AyaAdmin</a>
        <nav>
            <ul>
                <li><a href="ventes.php">Ventes (POS)</a></li>
                <li><a href="categories.php">Catégories</a></li>
                <li><a href="produits.php">Produits</a></li>
                <li><a href="commandes.php">Commandes</a></li>
                <li><a href="logout.php" style="color: #e74c3c; font-weight: bold;">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="section" style="padding-top: 3rem;">
        <h1 style="color: var(--primary); margin-bottom: 2rem; font-family: var(--font-heading);">Tableau de Bord</h1>
        
        <!-- Key Metrics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Chiffre d'Affaires</h3>
                <div class="value"><?= number_format($total_revenue, 2) ?> DH</div>
            </div>
            <div class="stat-card">
                <h3>Commandes</h3>
                <div class="value"><?= $total_orders ?></div>
            </div>
            <div class="stat-card">
                <h3>Produits</h3>
                <div class="value"><?= $total_prods ?></div>
            </div>
            <div class="stat-card">
                <h3>Catégories</h3>
                <div class="value"><?= $total_cats ?></div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="glass-panel" style="margin: 0; padding: 2rem; max-width: 100%; text-align: left;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="color: var(--primary); margin: 0; font-size: 1.5rem;">Dernières Commandes</h2>
                <a href="commandes.php" class="btn" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Voir tout</a>
            </div>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Paiement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $cmd): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cmd['No_ticket']) ?></strong></td>
                        <td><?= htmlspecialchars($cmd['nom'] . ' ' . $cmd['prenom']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($cmd['date'])) ?></td>
                        <td>
                            <span style="padding: 0.2rem 0.6rem; border-radius: 5px; font-size: 0.8rem; background: <?= $cmd['statut'] == 'Livrée' ? '#2ecc71' : ($cmd['statut'] == 'Annulée' ? '#e74c3c' : '#f39c12') ?>; color: #fff;">
                                <?= htmlspecialchars($cmd['statut']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($cmd['mode_paiement']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_orders)): ?>
                    <tr><td colspan="5" style="text-align: center;">Aucune commande récente</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>
