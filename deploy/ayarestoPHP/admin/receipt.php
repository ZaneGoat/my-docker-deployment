<?php
// admin/receipt.php - Print POS Receipt
require '../db.php';
require '../includes/functions.php';
check_admin_login();

$id_commande = intval($_GET['id'] ?? 0);
$montant_paye = floatval($_GET['paye'] ?? 0);

if (!$id_commande) die("Commande invalide.");

// Fetch Commande
$stmt = $pdo->prepare("SELECT c.*, cl.nom as client_nom FROM Commande c JOIN Client cl ON c.id_client = cl.id_client WHERE c.id_commande = ?");
$stmt->execute([$id_commande]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) die("Commande introuvable.");

// Fetch details
$stmt_det = $pdo->prepare("SELECT d.quantite, p.libelle, p.prix FROM Detail_Commande d JOIN Produit p ON d.id_produit = p.id_produit WHERE d.id_commande = ?");
$stmt_det->execute([$id_commande]);
$details = $stmt_det->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Caisse - <?= htmlspecialchars($commande['No_ticket']) ?></title>
    <style>
        body { font-family: monospace; background: #e0e0e0; padding: 2rem; display: flex; justify-content: center; }
        .receipt { background: #fff; width: 300px; padding: 20px; color: #000; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .text-center { text-align: center; }
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="text-center">
            <h2>AyaResto</h2>
            <p>123 Avenue Culinaire<br>Casablanca, Maroc<br>Tel: +212 5 00 11 22 33</p>
        </div>
        
        <div class="divider"></div>
        
        <p><strong>Ticket:</strong> <?= htmlspecialchars($commande['No_ticket']) ?><br>
        <strong>Date:</strong> <?= date('d/m/Y H:i', strtotime($commande['date'])) ?><br>
        <strong>Mode:</strong> <?= htmlspecialchars($commande['mode_paiement']) ?></p>
        
        <div class="divider"></div>
        
        <?php foreach ($details as $d): 
            $subtotal = $d['prix'] * $d['quantite'];
            $total += $subtotal;
        ?>
            <div class="row">
                <span><?= $d['quantite'] ?>x <?= htmlspecialchars($d['libelle']) ?></span>
                <span><?= number_format($subtotal, 2) ?></span>
            </div>
        <?php endforeach; ?>
        
        <div class="divider"></div>
        
        <div class="row">
            <strong>TOTAL</strong>
            <strong><?= number_format($total, 2) ?> DH</strong>
        </div>
        
        <div class="row">
            <span>Espèces/Payé</span>
            <span><?= number_format($montant_paye, 2) ?> DH</span>
        </div>
        
        <div class="row">
            <span>Rendu</span>
            <span><?= number_format(max(0, $montant_paye - $total), 2) ?> DH</span>
        </div>
        
        <div class="divider"></div>
        
        <div class="text-center" style="margin-top: 20px;">
            <p>Merci de votre visite !<br>À très bientôt.</p>
        </div>

        <div class="text-center no-print" style="margin-top: 30px;">
            <button onclick="window.print()" style="padding: 10px 20px; font-weight:bold; cursor:pointer;">Imprimer le Ticket</button>
            <br><br>
            <a href="ventes.php" style="color: blue;">Retour aux Ventes</a>
        </div>
    </div>

    <script>
        // Auto-trigger print dialog when the page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
