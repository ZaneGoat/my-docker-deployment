<?php
// menu.php
require 'db.php';
require 'includes/functions.php';

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $id_produit = intval($_POST['id_produit']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$id_produit])) {
        $_SESSION['cart'][$id_produit]['quantite'] += 1;
    } else {
        // Fetch product info
        $stmt = $pdo->prepare("SELECT libelle, prix FROM Produit WHERE id_produit = ?");
        $stmt->execute([$id_produit]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($prod) {
            $_SESSION['cart'][$id_produit] = [
                'libelle' => $prod['libelle'],
                'prix' => $prod['prix'],
                'quantite' => 1
            ];
        }
    }
    header("Location: menu.php");
    exit;
}

// Fetch categories and products
$stmt_cats = $pdo->query("SELECT * FROM Categorie");
$categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

$produits_by_cat = [];
$stmt_prods = $pdo->query("SELECT * FROM Produit");
while ($row = $stmt_prods->fetch(PDO::FETCH_ASSOC)) {
    $produits_by_cat[$row['id_categorie']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notre Menu - AyaResto</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="padding-top: 100px;">
    <header style="background: rgba(0,0,0,0.9);">
        <a href="index.php" class="logo">AyaResto</a>
        <nav>
            <ul>
                <li><a href="menu.php" style="color: var(--primary);">Menu</a></li>
                <li><a href="cart.php">Panier (<?= get_cart_count() ?>)</a></li>
            </ul>
        </nav>
    </header>

    <div class="section">
        <h1 style="text-align: center; color: var(--primary); margin-bottom: 3rem;">L'Élégance de Notre Carte</h1>

        <?php foreach ($categories as $cat): ?>
            <?php if (isset($produits_by_cat[$cat['id_categorie']])): ?>
                <h2 style="color: var(--text-main); margin-bottom: 1.5rem; border-bottom: 1px solid var(--surface-border); padding-bottom: 0.5rem;"><?= htmlspecialchars($cat['nom']) ?></h2>
                <div class="product-grid">
                    <?php foreach ($produits_by_cat[$cat['id_categorie']] as $prod): ?>
                        <div class="product-card glass-panel" style="margin: 0; padding: 1.5rem; text-align: left;">
                            <h3 style="color: var(--primary); margin-bottom: 0.5rem;"><?= htmlspecialchars($prod['libelle']) ?></h3>
                            <p style="font-size: 0.9rem; margin-bottom: 1rem;"><?= htmlspecialchars($prod['description']) ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: bold; color: var(--text-main);"><?= number_format($prod['prix'], 2) ?> DH</span>
                                <form method="POST">
                                    <input type="hidden" name="id_produit" value="<?= $prod['id_produit'] ?>">
                                    <button type="submit" name="add_to_cart" class="btn" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Ajouter</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <br><br>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>
