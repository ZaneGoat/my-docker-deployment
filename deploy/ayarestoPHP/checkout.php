<?php
// checkout.php
require 'db.php';
require 'includes/functions.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: menu.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom']);
    $prenom = sanitize($_POST['prenom']);
    $tel = sanitize($_POST['tel']);
    $email = sanitize($_POST['email']);
    $mode_paiement = sanitize($_POST['mode_paiement']);
    $no_ticket = strtoupper(uniqid('TICK-'));

    try {
        $pdo->beginTransaction();

        // Check if client exists by email (or create)
        $stmt = $pdo->prepare("SELECT id_client FROM Client WHERE email = ?");
        $stmt->execute([$email]);
        $client_id = $stmt->fetchColumn();

        if (!$client_id) {
            $stmt = $pdo->prepare("INSERT INTO Client (nom, prenom, tel, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $tel, $email]);
            $client_id = $pdo->lastInsertId();
        }

        // Create Commande
        $stmt = $pdo->prepare("INSERT INTO Commande (statut, No_ticket, mode_paiement, id_client) VALUES ('En attente', ?, ?, ?)");
        $stmt->execute([$no_ticket, $mode_paiement, $client_id]);
        $commande_id = $pdo->lastInsertId();

        // Insert Detail_Commande
        $stmt_detail = $pdo->prepare("INSERT INTO Detail_Commande (id_commande, id_produit, quantite) VALUES (?, ?, ?)");
        foreach ($cart as $id_produit => $item) {
            $stmt_detail->execute([$commande_id, $id_produit, $item['quantite']]);
        }

        $pdo->commit();
        
        // Clear cart
        unset($_SESSION['cart']);
        
        $success = $no_ticket;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Une erreur s'est produite lors de la commande.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Caisse - AyaResto</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="padding-top: 100px;">
    <header style="background: rgba(0,0,0,0.9);">
        <a href="index.php" class="logo">AyaResto</a>
        <nav>
            <ul>
                <li><a href="menu.php">Menu</a></li>
            </ul>
        </nav>
    </header>

    <div class="section">
        <div class="glass-panel" style="margin: 0 auto; max-width: 600px; padding: 3rem; text-align: left;">
            <?php if (isset($success)): ?>
                <h2 style="color: var(--primary); margin-bottom: 1rem;">Commande Confirmée !</h2>
                <p>Merci pour votre commande. Votre numéro de ticket est le <strong><?= $success ?></strong>.</p>
                <a href="index.php" class="btn" style="margin-top: 2rem;">Retour à l'accueil</a>
            <?php else: ?>
                <h1 style="color: var(--primary); margin-bottom: 2rem; text-align: center;">Finaliser la Commande</h1>
                
                <?php if (isset($error)): ?>
                    <p style="color: #e74c3c; margin-bottom: 1rem;"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; gap: 1rem;">
                        <input type="text" name="nom" placeholder="Nom" required class="input-field" style="flex: 1;">
                        <input type="text" name="prenom" placeholder="Prénom" required class="input-field" style="flex: 1;">
                    </div>
                    <input type="tel" name="tel" placeholder="Téléphone" required class="input-field">
                    <input type="email" name="email" placeholder="Email" required class="input-field">
                    <select name="mode_paiement" required class="input-field">
                        <option value="">Mode de paiement</option>
                        <option value="Espèces">Espèces à la livraison</option>
                        <option value="Carte Bancaire">Carte Bancaire (Sur place)</option>
                    </select>
                    <button type="submit" class="btn" style="margin-top: 1rem;">Confirmer la Commande</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
