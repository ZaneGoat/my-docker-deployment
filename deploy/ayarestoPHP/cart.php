<?php
// cart.php
require 'db.php';
require 'includes/functions.php';

// Remove item
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Votre Panier - AyaResto</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="padding-top: 100px;">
    <header style="background: rgba(0,0,0,0.9);">
        <a href="index.php" class="logo">AyaResto</a>
        <nav>
            <ul>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="cart.php" style="color: var(--primary);">Panier (<?= get_cart_count() ?>)</a></li>
            </ul>
        </nav>
    </header>

    <div class="section">
        <div class="glass-panel" style="margin: 0 auto; max-width: 800px; padding: 3rem;">
            <h1 style="color: var(--primary); margin-bottom: 2rem;">Votre Panier</h1>
            
            <?php if (empty($cart)): ?>
                <p>Votre panier est vide.</p>
                <a href="menu.php" class="btn" style="margin-top: 2rem;">Retour au menu</a>
            <?php else: ?>
                <table style="width: 100%; text-align: left; margin-bottom: 2rem; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid var(--surface-border);">
                        <th style="padding: 1rem 0;">Produit</th>
                        <th style="padding: 1rem 0;">Prix</th>
                        <th style="padding: 1rem 0;">Qté</th>
                        <th style="padding: 1rem 0;">Sous-total</th>
                        <th style="padding: 1rem 0;"></th>
                    </tr>
                    <?php foreach ($cart as $id => $item): 
                        $subtotal = $item['prix'] * $item['quantite'];
                        $total += $subtotal;
                    ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <td style="padding: 1rem 0;"><?= htmlspecialchars($item['libelle']) ?></td>
                        <td style="padding: 1rem 0;"><?= number_format($item['prix'], 2) ?> DH</td>
                        <td style="padding: 1rem 0;"><?= $item['quantite'] ?></td>
                        <td style="padding: 1rem 0;"><?= number_format($subtotal, 2) ?> DH</td>
                        <td style="padding: 1rem 0;"><a href="cart.php?remove=<?= $id ?>" style="color: #e74c3c; text-decoration: none;">Retirer</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <div style="text-align: right; margin-bottom: 2rem;">
                    <h3>Total: <span style="color: var(--primary);"><?= number_format($total, 2) ?> DH</span></h3>
                </div>
                <div style="text-align: right;">
                    <a href="checkout.php" class="btn">Passer à la caisse</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
